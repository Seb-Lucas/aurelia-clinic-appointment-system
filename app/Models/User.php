<?php

namespace App\Models;

class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $passwordHash,
        public string $role,
        public string $status,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $lastLoginAt = null,
        public ?string $phone = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['password_hash'] ?? ''),
            (string) ($row['role'] ?? 'patient'),
            (string) ($row['status'] ?? 'active'),
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null,
            $row['last_login_at'] ?? null,
            $row['phone'] ?? null,
        );
    }
}
