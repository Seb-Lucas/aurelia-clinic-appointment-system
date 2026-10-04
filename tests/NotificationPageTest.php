<?php

namespace Tests;

use App\Controllers\NotificationController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class NotificationPageTest extends TestCase
{
    public function testNotificationsPageRendersList(): void
    {
        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $response = (new NotificationController())->index(new Request('GET', '/notifications', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Notifications', $response->content);
    }
}
