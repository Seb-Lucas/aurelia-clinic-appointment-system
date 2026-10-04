<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class DoctorScheduleRepository
{
    public function create(array $data): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_active, created_at, updated_at) VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :is_active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'doctor_id' => (int) ($data['doctor_id'] ?? 0),
            'day_of_week' => (int) ($data['day_of_week'] ?? 0),
            'start_time' => (string) ($data['start_time'] ?? ''),
            'end_time' => (string) ($data['end_time'] ?? ''),
            'is_active' => (int) ($data['is_active'] ?? 1),
        ]);

        $id = (int) $pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM doctor_schedules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allByDoctorId(int $doctorId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id ORDER BY day_of_week, start_time');
        $stmt->execute(['doctor_id' => $doctorId]);
        return $stmt->fetchAll();
    }

    public function overlaps(int $doctorId, int $dayOfWeek, string $startTime, string $endTime): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM doctor_schedules WHERE doctor_id = :doctor_id AND day_of_week = :day_of_week AND is_active = 1 AND start_time < :end_time AND end_time > :start_time LIMIT 1');
        $stmt->execute([
            'doctor_id' => $doctorId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return $stmt->fetch() !== false;
    }
}
