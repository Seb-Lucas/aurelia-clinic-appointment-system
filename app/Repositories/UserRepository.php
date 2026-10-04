<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    public function create(array $data): User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status, phone, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, :status, :phone, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'patient',
            'status' => $data['status'] ?? 'active',
            'phone' => $data['phone'] ?? null,
        ]);

        $id = (int) $pdo->lastInsertId();
        return $this->findById($id);
    }

    public function updateLastLogin(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => User::fromArray($row), $rows);
    }
}
