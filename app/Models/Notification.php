<?php

namespace App\Models;

class Notification
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $type,
        public string $message,
        public bool $isRead,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['user_id'] ?? 0),
            (string) ($row['type'] ?? 'info'),
            (string) ($row['message'] ?? ''),
            (bool) ($row['is_read'] ?? false),
            $row['created_at'] ?? null,
        );
    }
}
