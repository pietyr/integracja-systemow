<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtWebAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        $token = $request->cookie('jwt_token') ?? $request->bearerToken();

        if ($token) {
            try {
                $user = JWTAuth::setToken($token)->authenticate();
                auth()->setUser($user);
            } catch (JWTException) {
                //
            }
        }

        if (! auth()->check()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
