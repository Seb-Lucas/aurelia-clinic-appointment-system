<?php

namespace Tests;

use App\Controllers\AppointmentController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AvailabilityService;
use App\Services\AppointmentService;

class AppointmentTest extends TestCase
{
    public function testValidAppointmentBookingCreatesRecord(): void
    {
        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => date('Y-m-d', strtotime('next monday')),
            'start_time' => '09:30',
            'end_time' => '10:00',
            'notes' => 'Initial consultation',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['appointment']);
    }

    public function testConflictingAppointmentIsRejected(): void
    {
        $baseDate = date('Y-m-d', strtotime('next monday'));

        $first = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => $baseDate,
            'start_time' => '10:00',
            'end_time' => '10:30',
            'notes' => 'First booking',
        ]);

        $this->assertTrue($first['success']);

        $second = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => $baseDate,
            'start_time' => '10:15',
            'end_time' => '10:45',
            'notes' => 'Second booking',
        ]);

        $this->assertFalse($second['success']);
    }

    public function testBookingRejectsUnknownPatientId(): void
    {
        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 999,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => date('Y-m-d', strtotime('next monday')),
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('The selected patient could not be found.', $result['errors']['0']);
    }

    public function testPatientCannotCancelAnotherPatientsAppointment(): void
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role, status) VALUES (3, 'Second Patient', 'second@example.com', 'hash', 'patient', 'active')");
        $pdo->exec("INSERT INTO patients (id, user_id) VALUES (2, 3)");

        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 1,
            'appointment_date' => date('Y-m-d', strtotime('next monday')),
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]);

        $this->assertTrue($result['success']);
        $this->assertFalse((new AppointmentService())->cancelAppointment($result['appointment']->id, [
            'id' => 3,
            'role' => 'patient',
        ]));
    }

    public function testInvalidStatusTransitionIsRejected(): void
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
        $this->assertFalse((new AppointmentService())->updateStatus($result['appointment']->id, 'completed', [
            'id' => 2,
            'role' => 'doctor',
        ]));
    }

    public function testServiceDurationAndScheduleExceptionAreRespected(): void
    {
        $pdo = Database::getConnection();
        $pdo->exec("INSERT INTO services (id, name, duration_minutes, is_active) VALUES (2, 'Follow-up', 20, 1)");
        $pdo->exec("INSERT INTO doctor_services (doctor_id, service_id, is_active) VALUES (1, 2, 1)");
        $date = date('Y-m-d', strtotime('next monday'));

        $result = (new AppointmentService())->bookAppointment([
            'patient_id' => 1,
            'doctor_id' => 1,
            'service_id' => 2,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:20',
        ]);

        $this->assertTrue($result['success']);
        $pdo->exec("INSERT INTO schedule_exceptions (doctor_id, date, is_available, reason) VALUES (1, '{$date}', 0, 'Holiday')");
        $this->assertSame([], (new AvailabilityService())->availableSlots(1, $date, 20));
    }

    public function testDoctorCanConfirmAssignedAppointmentAndPatientIsNotified(): void
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
        $appointmentId = $result['appointment']->id;
        $updated = (new AppointmentService())->updateStatus($appointmentId, 'confirmed', [
            'id' => 2,
            'role' => 'doctor',
        ]);

        $this->assertTrue($updated);
        $appointment = (new \App\Repositories\AppointmentRepository())->findById($appointmentId);
        $this->assertSame('confirmed', $appointment?->status);

        $history = Database::getConnection()->query("SELECT status FROM appointment_status_history WHERE appointment_id = {$appointmentId} ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertSame('confirmed', $history);
        $notification = Database::getConnection()->query("SELECT message FROM notifications WHERE user_id = 1 AND message LIKE '%confirmed%' ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertSame('Your appointment has been confirmed by the doctor.', $notification);
    }

    public function testPatientBookingPageProvidesAnEnabledActionOnNextAvailableDate(): void
    {
        Session::set('user', [
            'id' => 1,
            'name' => 'Patient User',
            'email' => 'patient@example.com',
            'role' => 'patient',
            'status' => 'active',
        ]);

        $response = (new AppointmentController())->create(new Request('GET', '/appointments/create', [], [], $_SERVER));

        $this->assertStringContainsString('Review and book appointment', $response->content);
        $this->assertStringNotContainsString('type="submit" disabled', $response->content);
        $this->assertStringContainsString('name="start_time"', $response->content);
    }

    public function testCompletedAppointmentDoesNotShowPatientCancellationAction(): void
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
        $appointmentId = $result['appointment']->id;
        $doctor = ['id' => 2, 'role' => 'doctor'];
        foreach (['confirmed', 'checked_in', 'in_progress', 'completed'] as $status) {
            $this->assertTrue((new AppointmentService())->updateStatus($appointmentId, $status, $doctor));
        }

        Session::set('user', ['id' => 1, 'role' => 'patient', 'name' => 'Patient User']);
        $response = (new AppointmentController())->index(new Request('GET', '/appointments', [], [], $_SERVER));

        $this->assertStringNotContainsString('/appointments/' . $appointmentId . '/cancel', $response->content);
    }
}
