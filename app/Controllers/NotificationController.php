<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\NotificationService;

class NotificationController
{
    public function __construct(private readonly NotificationService $notificationService = new NotificationService())
    {
    }

    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $notifications = [];

        if ($user) {
            $notifications = $this->notificationService->forUser((int) ($user['id'] ?? 0));
        }

        return Response::view('notifications/index', [
            'title' => 'Notifications',
            'user' => $user,
            'notifications' => $notifications,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }
}
