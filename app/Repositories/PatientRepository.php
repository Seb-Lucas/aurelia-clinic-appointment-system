<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Patient;

class PatientRepository
{
    public function findByUserId(int $userId): ?Patient
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM patients WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? Patient::fromArray($row) : null;
    }

    public function findById(int $id): ?Patient
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM patients WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Patient::fromArray($row) : null;
    }

    public function create(array $data): Patient
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO patients (user_id, date_of_birth, phone, address, created_at, updated_at) VALUES (:user_id, :date_of_birth, :phone, :address, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $id = (int) $pdo->lastInsertId();
        $row = $pdo->prepare('SELECT * FROM patients WHERE id = :id LIMIT 1');
        $row->execute(['id' => $id]);
        $patientRow = $row->fetch();
        return Patient::fromArray($patientRow);
    }

    public function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM patients ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => Patient::fromArray($row), $rows);
    }
}
