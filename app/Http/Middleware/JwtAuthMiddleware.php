<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JwtAuthService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class JwtAuthMiddleware
{
    /**
     * JWT authentication service.
     *
     * @var JwtAuthService
     */
    protected $jwtAuthService;

    /**
     * Create a new service instance.
     *
     * @param JwtAuthService $jwtAuthService JWT authentication middleware.
     */
    public function __construct(JwtAuthService $jwtAuthService)
    {
        $this->jwtAuthService = $jwtAuthService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function handle(Request $request, Closure $next)
    {
        $token = $this->jwtAuthService->getTokenFromCookie($request);

        if (!$token) {
            return $this->jwtAuthService->handleAuthMessage('Se deberá iniciar sesión para continuar.');
        }

        $user = $this->jwtAuthService->validateToken($token);

        if (!$user) {
            return $this->jwtAuthService->handleAuthMessage('Token inválido o expirado.');
        }

        auth()->setUser($user);

        return $next($request);
    }
     
}
