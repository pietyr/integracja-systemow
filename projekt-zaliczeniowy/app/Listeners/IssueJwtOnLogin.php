<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class IssueJwtOnLogin
{
    public function handle(Login $event): void
    {
        $token = JWTAuth::fromUser($event->user);

        cookie()->queue(cookie(
            'jwt_token',
            $token,
            config('jwt.ttl'),
            '/',
            null,
            false,
            true,
            false,
            'lax',
        ));
    }
}
