<?php

namespace Tests;

use App\Controllers\DoctorController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AppointmentService;
use App\Services\ScheduleService;

class DoctorScheduleTest extends TestCase
{
    public function testDoctorScheduleServiceCreatesSchedule(): void
    {
        $result = (new ScheduleService())->create([
            'doctor_id' => 1,
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '16:00',
            'is_active' => 1,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['schedule']['day_of_week']);
    }

    public function testDoctorSchedulePageRendersScheduleList(): void
    {
        Session::set('user', [
            'id' => 2,
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'role' => 'doctor',
            'status' => 'active',
        ]);

        $response = (new DoctorController())->schedule(new Request('GET', '/doctor/schedule', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Schedule Management', $response->content);
    }

    public function testDoctorDashboardShowsApprovalActionForAssignedRequest(): void
    {
        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => date('Y-m-d', strtotime('next monday')),
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $this->assertTrue($result['success']);
        Session::set('user', [
            'id' => 2,
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'role' => 'doctor',
            'status' => 'active',
        ]);

        $response = (new DoctorController())->index(new Request('GET', '/doctor', [], [], $_SERVER));

        $this->assertStringContainsString('Confirm', $response->content);
        $this->assertStringContainsString('Patient User', $response->content);
    }

    public function testDoctorDashboardDoesNotCountCompletedHistoryAsUpcoming(): void
    {
        $date = date('Y-m-d', strtotime('next monday'));
        $completed = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);
        $cancelled = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]);

        $this->assertTrue($completed['success']);
        $this->assertTrue($cancelled['success']);
        $doctor = ['id' => 2, 'role' => 'doctor'];
        foreach (['confirmed', 'checked_in', 'in_progress', 'completed'] as $status) {
            $this->assertTrue((new AppointmentService())->updateStatus($completed['appointment']->id, $status, $doctor));
        }
        $this->assertTrue((new AppointmentService())->cancelAppointment($cancelled['appointment']->id, $doctor));

        Session::set('user', [
            'id' => 2,
            'name' => 'Doctor User',
            'email' => 'doctor@example.com',
            'role' => 'doctor',
            'status' => 'active',
        ]);
        $response = (new DoctorController())->index(new Request('GET', '/doctor', [], [], $_SERVER));

        $this->assertStringContainsString('<span class="stat-label">Upcoming appointments</span><span class="stat-value">0</span>', $response->content);
    }
}
