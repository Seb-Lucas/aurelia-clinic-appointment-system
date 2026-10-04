<?php

namespace Tests;

use App\Controllers\DashboardController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class PatientDashboardTest extends TestCase
{
    public function testPatientDashboardShowsUpcomingAppointments(): void
    {
        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $response = (new DashboardController())->dashboard(new Request('GET', '/dashboard', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Upcoming appointments', $response->content);
    }
}
