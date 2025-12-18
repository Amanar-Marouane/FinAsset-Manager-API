<?php

namespace App\Http\Controllers;

use App\Helpers\Normalizer;
use App\Helpers\RTokenQueue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Log, Mail};
use Illuminate\Support\Carbon;
use Unk\LaravelApiResponse\Traits\HttpResponse;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\PasswordResetRequest;
use App\Mail\TemporaryPasswordMail;
use Exception;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
    use HttpResponse;

    private JWT $jwt;
    private JWTGuard $auth;

    public function __construct()
    {
        /** @var JWT $jwtAuth */
        $jwtAuth = JWTAuth::getFacadeRoot();
        $this->jwt = $jwtAuth;

        /** @var JWTGuard $auth */
        $auth = Auth::guard('api');
        $this->auth = $auth;
    }

    /**
     * Login with credentials, return access + refresh tokens
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        // Attempt login via guard
        $token = $this->auth->attempt($credentials);
        if (!$token) {
            return $this->error('Identifiants invalides', 401);
        }

        $user = $this->auth->user();

        // Create refresh token and persist in DB
        $refresh_token = $this->refreshGenerate($user);

        $message = 'Vous vous êtes connecté avec succès';

        return $this->success([
            'access_token' => $token,
            'refresh_token' => $refresh_token,
            'user' => $user,
        ], $message);
    }

    /**
     * Generate refresh token for a user and persist in DB
     */
    private function refreshGenerate(User $user): string
    {
        $refresh_token = Str::uuid()->toString();
        $hashedRefreshToken = hash('sha256', $refresh_token);

        $user->update([
            'refresh_token_hash' => $hashedRefreshToken,
            'expired_at' => now()->addMinutes((int)config('auth.refresh_token_ttl', 10080)),
        ]);

        return $hashedRefreshToken;
    }

    /**
     * Logout user and invalidate refresh token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $user->update([
                'refresh_token_hash' => null,
                'expired_at' => null,
            ]);

            $this->auth->logout();
        } catch (Exception $e) {
            return $this->error('Erreur lors de la déconnexion', 500);
        }

        return $this->success(null, 'Compte déconnecté avec succès', 204);
    }

    /**
     * Check if user is authenticated (used for frontend auto-login)
     * - Validates access token
     * - Falls back to refresh token if needed
     */
    public function isLogged(Request $request): JsonResponse
    {
        $authorization = $request->header('Authorization');
        $accessToken = null;

        if ($authorization && preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $accessToken = $matches[1];
        }

        $refreshToken = Normalizer::normalizeRequestHeader($request, 'refresh-token');

        if (empty($accessToken) || empty($refreshToken)) {
            return $this->unauthenticated('Tokens manquants');
        }

        try {
            $user = $this->jwt->setToken($accessToken)->authenticate();
            return $this->authenticated($user);
        } catch (TokenExpiredException | TokenBlacklistedException | JWTException $e) {
            try {
                $newAccessToken = $this->jwt->setToken($accessToken)->refresh();
                /** @var User $user */
                $user = $this->jwt->authenticate();
                $newRefreshToken = RTokenQueue::rotateUserRT($user);
                return $this->authenticated($user)
                    ->header('Authorization', 'Bearer ' . $newAccessToken)
                    ->header('refresh-token', $newRefreshToken);
            } catch (TokenExpiredException | TokenBlacklistedException | JWTException $e) {
                return $this->handleTokenRefreshForCheck($refreshToken);
            } catch (Exception $e) {
                return $this->unauthenticated('Un error est sevenu', 500);
            }
        } catch (Exception $e) {
            return $this->unauthenticated('Un error est sevenu', 500);
        }
    }

    /**
     * Refresh tokens for "isLogged" check
     */
    private function handleTokenRefreshForCheck(string $refreshToken): JsonResponse
    {
        $user = null;

        try {
            // Validate refresh token and find user
            $user = $this->findUserByRefreshToken($refreshToken);
            if (!$user) {
                return $this->unauthenticated('Session expirée. Veuillez vous reconnecter.');
            }

            if (!$user->refresh_token_hash || !$user->expired_at) {
                return $this->unauthenticated('Session expirée. Veuillez vous reconnecter.');
            }

            if (Carbon::now()->greaterThan($user->expired_at)) {
                $user->update(['refresh_token_hash' => null, 'expired_at' => null]);
                return $this->unauthenticated('Session expirée. Veuillez vous reconnecter.');
            }

            // Generate fresh tokens
            $newAccessToken = $this->jwt->fromUser($user);
            $newRefreshToken = Str::uuid()->toString();
            $hashedNewRefreshToken = hash('sha256', $newRefreshToken);

            $user->update([
                'refresh_token_hash' => $hashedNewRefreshToken,
            ]);

            // Return authenticated response with headers (hashed version)
            return $this->authenticated($user)
                ->header('Authorization', 'Bearer ' . $newAccessToken)
                ->header('refresh-token', $hashedNewRefreshToken);
        } catch (TokenBlacklistedException | TokenExpiredException $e) {
            if ($user) {
                $user->update(['refresh_token_hash' => null, 'expired_at' => null]);
            }
            return $this->unauthenticated('Échec du renouvellement du token. Veuillez vous reconnecter.');
        } catch (JWTException $e) {
            if ($user) {
                $user->update(['refresh_token_hash' => null, 'expired_at' => null]);
            }
            return $this->unauthenticated('Erreur de sécurité. Veuillez vous reconnecter.');
        } catch (Exception $e) {
            return $this->unauthenticated('Erreur serveur. Veuillez réessayer.');
        }
    }

    /**
     * Find user by refresh token hash
     */
    private function findUserByRefreshToken(string $refreshToken): ?User
    {
        $user = User::query()
            ->where('refresh_token_hash', $refreshToken)
            ->first();
        return $user;
    }

    /**
     * Authenticated response
     */
    private function authenticated(User $user): JsonResponse
    {
        return $this->success([
            'authenticated' => true,
            'id' => $user->id,
            'user' => $user,
        ], 'L\'utilisateur est authentifié');
    }

    /**
     * Unauthenticated response
     */
    private function unauthenticated(string $message = 'Non authentifié', int $code = 401): JsonResponse
    {
        return $this->success(['authenticated' => false], $message, $code);
    }

    /**
     * Change password for logged-in user
     */
    public function changePassword(PasswordResetRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Le mot de passe actuel est incorrect', 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return $this->success(code: 204);
    }

    /**
     * Forgot password — send temporary one via email
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        return $this->multiProcess(function () use ($request) {
            $request->validate(['email' => 'required|email']);

            $user = User::query()
                ->where('email', Normalizer::normalizeRequestPayload($request, 'email'))
                ->first();

            if (!$user) {
                return $this->error('Utilisateur non trouvé', 404);
            }

            $temporaryPassword = Str::random(10);
            $user->update(['password' => Hash::make($temporaryPassword)]);

            Mail::to($user->email)->queue(new TemporaryPasswordMail($user, $temporaryPassword));

            return $this->success('Un mot de passe temporaire a été envoyé à votre adresse email.');
        });
    }
}
