<?php

namespace App\Services;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class JwtAuthService
{
    /**
     * Get the JWT token from the request's cookie.
     *
     * @param Request $request
     * @return string|null
     */
    public function getTokenFromCookie(Request $request)
    {
        return $request->hasCookie('jwt') ? $request->cookie('jwt') : null;
    }

    /**
     * Validate the provided JWT token and return the authenticated user.
     * If the token is invalid or expired, it handles the error and redirects to the login page.
     *
     * @param string $token
     * @return \App\Models\User|null
     */
    public function validateToken($token)
    {
        if($token){
            JWTAuth::setToken($token);

            if (JWTAuth::check()) {
                return JWTAuth::authenticate();
            }
        }

        return null;
    }

    /**
     * Function to validate the token and redirect if necessary.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function validateAndRedirect(Request $request)
    {

        $token = $this->getTokenFromCookie($request);

        if ($token) {
            $user = $this->validateToken($token);

            if ($user) {
                return redirect()->route('dashboard')->with('success', 'Ya estas autenticado... 😞');
            }

            return redirect()->route('login')->withCookie(cookie()->forget('jwt'))
                ->withErrors(['error' => 'Sesion expirada o invalida.']);
        }

        return null;
    }
    
    /**
     * Handle the error and remove the cookie, then redirect to login with a custom error message.
     *
     * @param string|null $errorMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleAuthMessage($errorMessage = null, $type= null )
    {
        $type = $type ?: 'error';

        $errorMessage = $errorMessage ?: 'Hubo un error al autenticar tu sesión. Inicie sesión nuevamente.';

        if ($type != 'error') {
            return redirect()->route('login')
                ->withCookie(cookie()->forget('jwt'))
                ->with($type, $errorMessage);
        }
    
        return redirect()->route('login')
            ->withCookie(cookie()->forget('jwt'))
            ->withErrors([$type => $errorMessage]);
    }
}
