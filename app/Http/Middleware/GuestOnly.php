<?php

namespace App\Http\Middleware;

use App\Helpers\Normalizer;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Unk\LaravelApiResponse\Traits\HttpResponse;
use Tymon\JWTAuth\JWT;

/**
 * Middleware to ensure only *guests* (non-authenticated users) 
 * can access a given route.
 *
 * If a valid access or refresh token is present, we block access 
 * because the user is already authenticated.
 */
class GuestOnly
{
    use HttpResponse;

    /**
     * Handle an incoming request.
     *
     * If a valid access token exists → deny.
     * If access token is invalid but refresh token is still valid → deny.
     * Otherwise → allow request through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = Normalizer::normalizeRequestHeader($request, 'access-token');
        $refreshToken = Normalizer::normalizeRequestHeader($request, 'refresh-token');

        // Case 1: Access token provided
        if ($accessToken) {
            try {
                // If token is valid and authenticates a user → deny access
                /** @var JWT $jwtAuth */
                $jwtAuth = JWTAuth::getFacadeRoot();
                $jwtAuth->setToken($accessToken)->authenticate();
                return $this->error('Déjà connecté. Veuillez vous déconnecter d\'abord.', 403);
            } catch (JWTException $e) {
                // Invalid/expired token, check refresh token
                if (!$refreshToken) {
                    return $next($request); // No refresh token → guest
                }

                // Case 2: Refresh token exists and is still valid → deny access
                $user = User::query()
                    ->where('refresh_token_hash', $refreshToken)
                    ->where('expired_at', '>', now())
                    ->first();

                if ($user) {
                    return $this->error('Déjà connecté. Veuillez vous déconnecter d\'abord.', 403);
                }
            }
        }

        // Default: No valid access/refresh token → treat as guest
        return $next($request);
    }
}
