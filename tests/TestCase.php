<?php

namespace Tests;

use App\Config;
use App\Core\Database;
use App\Core\Session;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
		Config::load();

        $pdo = Database::initialize([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $pdo->exec('DELETE FROM appointments');
        $pdo->exec('DELETE FROM appointment_status_history');
        $pdo->exec('DELETE FROM schedule_exceptions');
        $pdo->exec('DELETE FROM doctor_services');
        $pdo->exec('DELETE FROM doctor_schedules');
        $pdo->exec('DELETE FROM doctors');
        $pdo->exec('DELETE FROM patients');
        $pdo->exec('DELETE FROM departments');
        $pdo->exec('DELETE FROM services');
        $pdo->exec('DELETE FROM users');
        $pdo->exec('DELETE FROM notifications');
        $pdo->exec('DELETE FROM audit_logs');

        $pdo->exec("INSERT INTO departments (id, name, description) VALUES (1, 'General Medicine', 'Primary care')");
        $pdo->exec("INSERT INTO services (id, name, description, duration_minutes, price, is_active) VALUES (1, 'Consultation', 'General consultation', 30, 75, 1)");
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role, status, phone) VALUES (1, 'Patient User', 'patient@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'patient', 'active', '5550001')");
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role, status, phone) VALUES (2, 'Doctor User', 'doctor@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'doctor', 'active', '5550002')");
        $pdo->exec("INSERT INTO patients (id, user_id, date_of_birth, phone, address) VALUES (1, 1, '1990-01-01', '5550001', '123 Example Street')");
        $pdo->exec("INSERT INTO doctors (id, user_id, department_id, specialty, bio, status) VALUES (1, 2, 1, 'General Medicine', 'Demo doctor', 'active')");
        $pdo->exec("INSERT INTO doctor_services (doctor_id, service_id, is_active) VALUES (1, 1, 1)");
        $pdo->exec("INSERT INTO doctor_schedules (id, doctor_id, day_of_week, start_time, end_time, is_active) VALUES (1, 1, 1, '09:00', '17:00', 1)");

        Session::destroy();
    }
}
