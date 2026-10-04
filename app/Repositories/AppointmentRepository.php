<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Appointment;

class AppointmentRepository
{
    public function create(array $data): Appointment
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, service_id, appointment_date, start_time, end_time, status, notes, created_at, updated_at) VALUES (:patient_id, :doctor_id, :service_id, :appointment_date, :start_time, :end_time, :status, :notes, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'service_id' => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        $id = (int) $pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?Appointment
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Appointment::fromArray($row) : null;
    }

    public function findByPatientId(int $patientId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE patient_id = :patient_id ORDER BY appointment_date DESC, start_time DESC');
        $stmt->execute(['patient_id' => $patientId]);
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Appointment::fromArray($row), $rows);
    }

    public function findByDoctorId(int $doctorId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE doctor_id = :doctor_id ORDER BY appointment_date ASC, start_time ASC');
        $stmt->execute(['doctor_id' => $doctorId]);
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Appointment::fromArray($row), $rows);
    }

    public function findDetailedByDoctorId(int $doctorId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT a.id, a.patient_id, a.doctor_id, a.service_id, a.appointment_date, a.start_time, a.end_time, a.status, a.notes, p_user.name AS patient_name, s.name AS service_name FROM appointments a INNER JOIN patients p ON p.id = a.patient_id INNER JOIN users p_user ON p_user.id = p.user_id INNER JOIN services s ON s.id = a.service_id WHERE a.doctor_id = :doctor_id ORDER BY a.appointment_date ASC, a.start_time ASC');
        $stmt->execute(['doctor_id' => $doctorId]);

        return $stmt->fetchAll();
    }

    public function findDetailedByDate(string $date): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT a.id, a.patient_id, a.doctor_id, a.service_id, a.appointment_date, a.start_time, a.end_time, a.status, p_user.name AS patient_name, d_user.name AS doctor_name, s.name AS service_name FROM appointments a INNER JOIN patients p ON p.id = a.patient_id INNER JOIN users p_user ON p_user.id = p.user_id INNER JOIN doctors d ON d.id = a.doctor_id INNER JOIN users d_user ON d_user.id = d.user_id INNER JOIN services s ON s.id = a.service_id WHERE a.appointment_date = :appointment_date ORDER BY a.start_time ASC');
        $stmt->execute(['appointment_date' => $date]);

        return $stmt->fetchAll();
    }

    public function doctorOffersService(int $doctorId, int $serviceId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM doctor_services WHERE doctor_id = :doctor_id AND service_id = :service_id AND is_active = 1 LIMIT 1');
        $stmt->execute(['doctor_id' => $doctorId, 'service_id' => $serviceId]);

        return $stmt->fetchColumn() !== false;
    }

    public function serviceDuration(int $serviceId): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT duration_minutes FROM services WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute(['id' => $serviceId]);
        $duration = $stmt->fetchColumn();

        return $duration === false ? null : (int) $duration;
    }

    public function doctorIsActive(int $doctorId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM doctors WHERE id = :id AND status = 'active' LIMIT 1");
        $stmt->execute(['id' => $doctorId]);

        return $stmt->fetchColumn() !== false;
    }

    public function patientExists(int $patientId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM patients WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $patientId]);

        return $stmt->fetchColumn() !== false;
    }

    public function activeDoctors(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT d.id, u.name, d.specialty FROM doctors d INNER JOIN users u ON u.id = d.user_id WHERE d.status = 'active' AND u.status = 'active' ORDER BY u.name ASC")->fetchAll();
    }

    public function activeServices(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('SELECT id, name, duration_minutes FROM services WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
    }

    public function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM appointments ORDER BY appointment_date DESC, start_time DESC');
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Appointment::fromArray($row), $rows);
    }

    public function reportSummary(): array
    {
        $pdo = Database::getConnection();
        $statusSummary = $pdo->query('SELECT status, COUNT(*) as total FROM appointments GROUP BY status ORDER BY total DESC')->fetchAll();
        $doctorSummary = $pdo->query('SELECT doctor_id, COUNT(*) as total FROM appointments GROUP BY doctor_id ORDER BY total DESC')->fetchAll();
        $serviceSummary = $pdo->query('SELECT service_id, COUNT(*) as total FROM appointments GROUP BY service_id ORDER BY total DESC')->fetchAll();

        return [
            'by_status' => $statusSummary,
            'by_doctor' => $doctorSummary,
            'by_service' => $serviceSummary,
            'total' => count($this->all()),
        ];
    }

    public function findConflicts(int $doctorId, string $date, string $startTime, string $endTime, ?int $excludeId = null): ?Appointment
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM appointments WHERE doctor_id = :doctor_id AND appointment_date = :date AND status NOT IN ('cancelled', 'declined') AND ((start_time < :end_time) AND (end_time > :start_time))";
        if ($excludeId) {
            $sql .= ' AND id != :exclude_id';
        }
        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $params = ['doctor_id' => $doctorId, 'date' => $date, 'start_time' => $startTime, 'end_time' => $endTime];
        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? Appointment::fromArray($row) : null;
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE appointments SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $appointmentId]);
    }

    public function createStatusHistory(int $appointmentId, string $status, string $notes = ''): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO appointment_status_history (appointment_id, status, notes, created_at) VALUES (:appointment_id, :status, :notes, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'appointment_id' => $appointmentId,
            'status' => $status,
            'notes' => $notes,
        ]);
    }
}
