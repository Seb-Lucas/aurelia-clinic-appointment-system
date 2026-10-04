<?php

namespace Tests;

use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Controllers\ReceptionistController;
use App\Services\AppointmentService;

class ReceptionistTest extends TestCase
{
    private function receptionistUser(): array
    {
        Database::getConnection()->exec("INSERT INTO users (id, name, email, password_hash, role, status) VALUES (3, 'Reception User', 'reception@example.com', 'hash', 'receptionist', 'active')");

        return [
            'id' => 3,
            'name' => 'Reception User',
            'email' => 'reception@example.com',
            'role' => 'receptionist',
            'status' => 'active',
        ];
    }

    public function testReceptionQueueShowsOperationalAppointmentData(): void
    {
        $user = $this->receptionistUser();
        $date = date('Y-m-d', strtotime('next monday'));
        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $this->assertTrue($result['success']);
        Session::set('user', $user);
        $response = (new ReceptionistController())->index(new Request('GET', '/receptionist', ['date' => $date], [], $_SERVER));

        $this->assertStringContainsString("Today's appointment queue", $response->content);
        $this->assertStringContainsString('Patient User', $response->content);
        $this->assertStringContainsString('Doctor User', $response->content);
        $this->assertStringContainsString('Consultation', $response->content);
        $this->assertStringContainsString('Mark Arrived', $response->content);
        $this->assertStringNotContainsString('/appointments/create', $response->content);
        $this->assertStringNotContainsString('Initial consultation', $response->content);
    }

    public function testReceptionistCanMarkPendingAppointmentAsArrived(): void
    {
        $user = $this->receptionistUser();
        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => date('Y-m-d', strtotime('next monday')),
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $this->assertTrue($result['success']);
        $this->assertTrue((new AppointmentService())->updateStatus($result['appointment']->id, 'checked_in', $user, 'Patient marked arrived by reception.'));
        $status = Database::getConnection()->query('SELECT status FROM appointments WHERE id = ' . (int) $result['appointment']->id)->fetchColumn();

        $this->assertSame('checked_in', $status);
    }
}
