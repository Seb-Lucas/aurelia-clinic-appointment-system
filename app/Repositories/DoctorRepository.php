<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Doctor;

class DoctorRepository
{
    public function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM doctors ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Doctor::fromArray($row), $rows);
    }

    public function findById(int $id): ?Doctor
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM doctors WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Doctor::fromArray($row) : null;
    }

    public function findByUserId(int $userId): ?Doctor
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM doctors WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return $row ? Doctor::fromArray($row) : null;
    }
}
