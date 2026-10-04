<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Notification;

class NotificationRepository
{
    public function create(array $data): Notification
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES (:user_id, :type, :message, :is_read, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'user_id' => (int) ($data['user_id'] ?? 0),
            'type' => (string) ($data['type'] ?? 'info'),
            'message' => (string) ($data['message'] ?? ''),
            'is_read' => (int) ($data['is_read'] ?? 0),
        ]);

        $id = (int) $pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?Notification
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Notification::fromArray($row) : null;
    }

    public function allByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10');
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => Notification::fromArray($row), $rows);
    }
}
