<?php

namespace App\Models;

class Doctor
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?string $specialty = null,
        public ?int $departmentId = null,
        public ?string $bio = null,
        public string $status = 'active',
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['user_id'] ?? 0),
            $row['specialty'] ?? null,
            isset($row['department_id']) ? (int) $row['department_id'] : null,
            $row['bio'] ?? null,
            $row['status'] ?? 'active',
            $row['created_at'] ?? null,
        );
    }
}
