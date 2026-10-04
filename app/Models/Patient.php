<?php

namespace App\Models;

class Patient
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?string $dateOfBirth = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['user_id'] ?? 0),
            $row['date_of_birth'] ?? null,
            $row['phone'] ?? null,
            $row['address'] ?? null,
            $row['created_at'] ?? null,
        );
    }
}
