<?php

namespace Tests;

use App\Controllers\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\NotificationService;

class NotificationAndReportingTest extends TestCase
{
    public function testNotificationServiceCreatesNotification(): void
    {
        $result = (new NotificationService())->create([
            'user_id' => 1,
            'type' => 'appointment',
            'message' => 'Your appointment has been confirmed.',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('appointment', $result['notification']['type']);
    }

    public function testAdminReportsPageRendersSummary(): void
    {
        Session::set('user', [
            'id' => 99,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = (new AdminController())->reports(new Request('GET', '/admin/reports', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Appointment Reports', $response->content);
    }
}
