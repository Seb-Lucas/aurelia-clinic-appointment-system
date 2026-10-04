<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/helpers.php';

use App\Config;
use App\Core\Database;

$password = getenv('PORTFOLIO_DEMO_PASSWORD');
if ($password === false || strlen($password) < 16) {
    fwrite(STDERR, "Set PORTFOLIO_DEMO_PASSWORD to a unique password of at least 16 characters.\n");
    exit(1);
}

Config::load();
$dbConfig = config('app.db');
$driver = $dbConfig['default'] ?? 'sqlite';
$connectionConfig = $dbConfig[$driver] ?? $dbConfig['sqlite'];
$connectionConfig['seed_demo_data'] = false;
$connectionConfig['environment'] = 'production';
$pdo = Database::initialize($connectionConfig);

if ($pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn() === false) {
    fwrite(STDERR, "Create the private administrator account before provisioning portfolio demo accounts.\n");
    exit(1);
}

$demoEmails = [
    'patient.demo@example.test',
    'doctor.demo@example.test',
    'reception.demo@example.test',
];
$checkEmail = $pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
foreach ($demoEmails as $email) {
    $checkEmail->execute(['email' => $email]);
    if ($checkEmail->fetchColumn() !== false) {
        fwrite(STDERR, "A portfolio demo account already exists; refusing to overwrite demo data.\n");
        exit(1);
    }
}

$randomPassword = static fn(): string => bin2hex(random_bytes(24));
$doctorDemoPassword = $randomPassword();
$receptionDemoPassword = $randomPassword();
$nextWeekday = new DateTimeImmutable('tomorrow');
while ((int) $nextWeekday->format('N') > 5) {
    $nextWeekday = $nextWeekday->modify('+1 day');
}

try {
    $pdo->beginTransaction();

    $department = $pdo->prepare('SELECT id FROM departments WHERE name = :name LIMIT 1');
    $department->execute(['name' => 'General Medicine']);
    $departmentId = $department->fetchColumn();
    if ($departmentId === false) {
        $insertDepartment = $pdo->prepare('INSERT INTO departments (name, description) VALUES (:name, :description)');
        $insertDepartment->execute([
            'name' => 'General Medicine',
            'description' => 'Portfolio demonstration department using fictional data.',
        ]);
        $departmentId = $pdo->lastInsertId();
    }

    $service = $pdo->prepare('SELECT id FROM services WHERE name = :name LIMIT 1');
    $service->execute(['name' => 'Portfolio Consultation']);
    $serviceId = $service->fetchColumn();
    if ($serviceId === false) {
        $insertService = $pdo->prepare(
            'INSERT INTO services (name, description, duration_minutes, price, is_active) VALUES (:name, :description, :duration, :price, :active)'
        );
        $insertService->execute([
            'name' => 'Portfolio Consultation',
            'description' => 'Fictional appointment used only to demonstrate the portfolio system.',
            'duration' => 30,
            'price' => 0,
            'active' => 1,
        ]);
        $serviceId = $pdo->lastInsertId();
    }

    $insertUser = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, role, status, phone) VALUES (:name, :email, :password_hash, :role, :status, :phone)'
    );
    $createUser = static function (string $name, string $email, string $role, string $plainPassword) use ($insertUser, $pdo): int {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new RuntimeException('Unable to hash a portfolio demo password.');
        }

        $insertUser->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role,
            'status' => 'active',
            'phone' => null,
        ]);

        return (int) $pdo->lastInsertId();
    };

    $patientUserId = $createUser('Portfolio Demo Patient', $demoEmails[0], 'patient', $password);
    $doctorUserId = $createUser('Portfolio Demo Doctor', $demoEmails[1], 'doctor', $doctorDemoPassword);
    $receptionUserId = $createUser('Portfolio Demo Reception', $demoEmails[2], 'receptionist', $receptionDemoPassword);

    $insertPatient = $pdo->prepare(
        'INSERT INTO patients (user_id, date_of_birth, phone, address) VALUES (:user_id, :date_of_birth, :phone, :address)'
    );
    $insertPatient->execute([
        'user_id' => $patientUserId,
        'date_of_birth' => '1990-01-01',
        'phone' => null,
        'address' => 'Fictional portfolio address',
    ]);
    $patientId = (int) $pdo->lastInsertId();

    $insertDoctor = $pdo->prepare(
        'INSERT INTO doctors (user_id, department_id, specialty, bio, status) VALUES (:user_id, :department_id, :specialty, :bio, :status)'
    );
    $insertDoctor->execute([
        'user_id' => $doctorUserId,
        'department_id' => $departmentId,
        'specialty' => 'General Medicine',
        'bio' => 'Fictional clinician account for portfolio demonstration only.',
        'status' => 'active',
    ]);
    $doctorId = (int) $pdo->lastInsertId();

    $insertStaff = $pdo->prepare(
        'INSERT INTO staff (user_id, department_id, role_title, status) VALUES (:user_id, :department_id, :role_title, :status)'
    );
    $insertStaff->execute([
        'user_id' => $receptionUserId,
        'department_id' => $departmentId,
        'role_title' => 'Portfolio Demo Reception',
        'status' => 'active',
    ]);

    $doctorService = $pdo->prepare(
        'INSERT INTO doctor_services (doctor_id, service_id, is_active) VALUES (:doctor_id, :service_id, :active)'
    );
    $doctorService->execute([
        'doctor_id' => $doctorId,
        'service_id' => $serviceId,
        'active' => 1,
    ]);

    $schedule = $pdo->prepare(
        'INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_active) VALUES (:doctor_id, :day, :start, :end, :active)'
    );
    for ($day = 1; $day <= 5; $day++) {
        $schedule->execute([
            'doctor_id' => $doctorId,
            'day' => $day,
            'start' => '09:00',
            'end' => '17:00',
            'active' => 1,
        ]);
    }

    $appointment = $pdo->prepare(
        'INSERT INTO appointments (patient_id, doctor_id, service_id, appointment_date, start_time, end_time, status, notes) VALUES (:patient_id, :doctor_id, :service_id, :date, :start, :end, :status, :notes)'
    );
    $appointment->execute([
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'service_id' => $serviceId,
        'date' => $nextWeekday->format('Y-m-d'),
        'start' => '10:00',
        'end' => '10:30',
        'status' => 'confirmed',
        'notes' => 'Fictional appointment for portfolio demonstration only.',
    ]);
    $appointmentId = (int) $pdo->lastInsertId();

    $history = $pdo->prepare(
        'INSERT INTO appointment_status_history (appointment_id, status, notes) VALUES (:appointment_id, :status, :notes)'
    );
    $history->execute([
        'appointment_id' => $appointmentId,
        'status' => 'confirmed',
        'notes' => 'Fictional portfolio demonstration appointment.',
    ]);

    $notification = $pdo->prepare(
        'INSERT INTO notifications (user_id, type, message, is_read) VALUES (:user_id, :type, :message, :is_read)'
    );
    $notification->execute([
        'user_id' => $patientUserId,
        'type' => 'appointment',
        'message' => 'Your portfolio demo appointment is confirmed for ' . $nextWeekday->format('Y-m-d') . ' at 10:00.',
        'is_read' => 0,
    ]);
    $notification->execute([
        'user_id' => $doctorUserId,
        'type' => 'appointment',
        'message' => 'A fictional portfolio demo appointment is on your schedule.',
        'is_read' => 0,
    ]);

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw new RuntimeException('Unable to provision portfolio demo accounts.', 0, $exception);
}

fwrite(STDOUT, "Portfolio demo accounts created with synthetic records.\n");
fwrite(STDOUT, "Save these generated passwords securely; they are not stored in source control.\n");
fwrite(STDOUT, "Patient sign-in: {$demoEmails[0]} / {$password}\n");
fwrite(STDOUT, "Doctor workspace: {$demoEmails[1]} / {$doctorDemoPassword}\n");
fwrite(STDOUT, "Reception workspace: {$demoEmails[2]} / {$receptionDemoPassword}\n");
fwrite(STDOUT, "Publish the patient password only if you intentionally want public demo sign-in access.\n");
