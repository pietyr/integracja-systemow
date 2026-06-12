<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class ClearJwtOnLogout
{
    public function handle(Logout $event): void
    {
        cookie()->queue(cookie()->forget('jwt_token'));
    }
}
