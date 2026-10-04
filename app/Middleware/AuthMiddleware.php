<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class AuthMiddleware
{
    public static function requireAuth(): ?Response
    {
        if (!Session::get('user')) {
            return redirect('/login');
        }

        return null;
    }
}
