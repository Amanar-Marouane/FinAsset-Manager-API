<?php

namespace App\Http\Middleware;

use App\Helpers\Normalizer;
use App\Helpers\RTokenQueue;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Unk\LaravelApiResponse\Traits\HttpResponse;

/**
 * Class JWTGuard
 *
 * Custom JWT middleware that:
 *  - Authenticates incoming requests using an access token.
 *  - Transparently refreshes expired/blacklisted access tokens using a refresh token.
 *  - Rotates refresh tokens to prevent replay attacks.
 *
 * This ensures a seamless user experience while maintaining security.
 */
class JWTGuard
{
    use HttpResponse;

    private JWT $jwt;

    public function __construct()
    {
        // Get the underlying Tymon\JWTAuth\JWT instance from the facade.
        /** @var JWT $jwtAuth */
        $jwtAuth = JWTAuth::getFacadeRoot();
        $this->jwt = $jwtAuth;
    }

    /**
     * Handle incoming requests:
     *  - Extracts tokens from headers
     *  - Authenticates user with access token
     *  - Falls back to refresh flow if access token is expired/blacklisted
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');
        $accessToken = null;

        // Extract Bearer token from Authorization header
        if ($authorization && preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $accessToken = $matches[1];
        }

        // Extract refresh token (normalized for case-insensitive headers)
        $refreshToken = Normalizer::normalizeRequestHeader($request, 'refresh-token');

        // If either token is missing, reject immediately
        if (!$accessToken || !$refreshToken) {
            return $this->error('Session expirée. Veuillez vous reconnecter.', 401);
        }

        try {
            $this->jwt = $this->jwt->setToken($accessToken);
            $user = $this->jwt->authenticate();
            $request->setUserResolver(fn() => $user);

            return $next($request);
        } catch (TokenExpiredException | TokenBlacklistedException | JWTException $e) {
            return $this->handleTokenRefresh($request, $next, $refreshToken);
        }
    }

    /**
     * Handle token refresh when access token is invalid/expired.
     *
     * Refresh logic:
     *  - First attempt to refresh the access token directly.
     *  - If that fails (token blacklisted/expired), verify user by refresh token hash in DB.
     *  - If valid, issue a new access token.
     *  - Rotate refresh token for security.
     *  - Attach new tokens to response headers.
     */
    private function handleTokenRefresh(Request $request, Closure $next, string $refreshToken): Response
    {
        try {
            $newAccessToken = $this->jwt->refresh();
            /** @var User $user */
            $user = $this->jwt->authenticate();
        } catch (TokenBlacklistedException | TokenExpiredException $e) {
            $user = User::query()
                ->where('refresh_token_hash', $refreshToken)
                ->first();

            if (!$user || !$user->refresh_token_hash || !$user->expired_at) {
                return $this->error('Session expirée. Veuillez vous reconnecter.', 401);
            }

            if (Carbon::now()->greaterThan($user->expired_at)) {
                $user->update([
                    'refresh_token_hash' => null,
                    'expired_at' => null
                ]);
                return $this->error('Session expirée. Veuillez vous reconnecter.', 401);
            }

            $newAccessToken = $this->jwt->fromUser($user);
        } catch (JWTException $e) {
            if (isset($user)) {
                $user->update(['refresh_token_hash' => null, 'expired_at' => null]);
            }
            return $this->error('Erreur de sécurité. Veuillez vous reconnecter.', 401);
        } catch (\Exception $e) {
            return $this->error('Erreur serveur. Veuillez réessayer.', 500);
        }

        // Rotate refresh token to prevent reuse
        $newRefreshToken = RTokenQueue::rotateUserRT($user);

        // Rebind user to current request
        $request->setUserResolver(fn() => $user);

        // Let request continue, then inject new tokens into response headers
        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('Authorization', 'Bearer ' . $newAccessToken);
        $response->headers->set('refresh-token', $newRefreshToken);

        return $response;
    }
}
