<?php

namespace App\Models;

class MedicalRecord
{
    public function __construct(
        public int $id,
        public int $patientId,
        public ?int $doctorId,
        public ?string $recordType,
        public ?string $summary,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['patient_id'] ?? 0),
            isset($row['doctor_id']) ? (int) $row['doctor_id'] : null,
            $row['record_type'] ?? null,
            $row['summary'] ?? null,
            $row['created_at'] ?? null,
        );
    }
}
