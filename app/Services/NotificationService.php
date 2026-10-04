<?php

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    public function __construct(private readonly NotificationRepository $notificationRepository = new NotificationRepository())
    {
    }

    public function create(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $type = trim((string) ($data['type'] ?? 'info'));
        $message = trim((string) ($data['message'] ?? ''));

        if ($userId <= 0 || $type === '' || $message === '') {
            return ['success' => false, 'message' => 'User, type, and message are required for notifications.'];
        }

        $notification = $this->notificationRepository->create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'is_read' => $data['is_read'] ?? 0,
        ]);

        return ['success' => true, 'notification' => [
            'id' => $notification->id,
            'user_id' => $notification->userId,
            'type' => $notification->type,
            'message' => $notification->message,
            'is_read' => $notification->isRead,
            'created_at' => $notification->createdAt,
        ]];
    }

    public function forUser(int $userId): array
    {
        return $this->notificationRepository->allByUserId($userId);
    }
}
