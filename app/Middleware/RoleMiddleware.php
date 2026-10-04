<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\Response;

class RoleMiddleware
{
    public static function requireRoles(array $roles): ?Response
    {
        $user = Session::get('user');
        if (!$user || !in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            echo 'Forbidden.';
            exit;
        }

        return null;
    }
}
