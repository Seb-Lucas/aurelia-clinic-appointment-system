<?php

namespace Tests;

use App\Controllers\AppointmentController;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

class DeploymentReadinessTest extends TestCase
{
    public function testHealthRouteReturnsNoSensitiveDetails(): void
    {
        $router = new Router();
        $router->load(base_path('routes/web.php'));

        $response = $router->dispatch(new Request('GET', '/health', [], [], $_SERVER));

        $this->assertSame(200, $response->status);
        $this->assertSame('OK', $response->content);
        $this->assertSame('text/plain; charset=utf-8', $response->headers['Content-Type']);
    }

    public function testInvalidBookingCsrfTokenReturnsSafeRedirect(): void
    {
        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $response = (new AppointmentController())->store(new Request(
            'POST',
            '/appointments/store',
            [],
            [
                '_csrf_token' => 'invalid-token',
                'doctor_id' => 1,
                'service_id' => 1,
                'appointment_date' => '2026-12-01',
                'start_time' => '09:00',
                'end_time' => '09:30',
            ],
            $_SERVER
        ));

        $this->assertSame(302, $response->status);
        $this->assertSame(
            '/appointments/create?doctor_id=1&service_id=1&appointment_date=2026-12-01',
            $response->headers['Location']
        );
    }

    public function testLogoutIsPostOnlyAndCsrfProtected(): void
    {
        $router = new Router();
        $router->load(base_path('routes/web.php'));
        $getResponse = $router->dispatch(new Request('GET', '/logout', [], [], $_SERVER));
        $this->assertSame(404, $getResponse->status);

        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $invalidResponse = $router->dispatch(new Request(
            'POST',
            '/logout',
            [],
            ['_csrf_token' => 'invalid-token'],
            $_SERVER
        ));
        $this->assertSame(302, $invalidResponse->status);
        $this->assertSame('/dashboard', $invalidResponse->headers['Location']);
        $this->assertNotNull(Session::get('user'));

        $token = csrf_token();
        $validResponse = $router->dispatch(new Request(
            'POST',
            '/logout',
            [],
            ['_csrf_token' => $token],
            $_SERVER
        ));
        $this->assertSame(302, $validResponse->status);
        $this->assertSame('/login', $validResponse->headers['Location']);
        $this->assertNull(Session::get('user'));
    }
}
