<?php

namespace App\Models;

class Appointment
{
    public function __construct(
        public int $id,
        public int $patientId,
        public int $doctorId,
        public int $serviceId,
        public string $appointmentDate,
        public string $startTime,
        public string $endTime,
        public string $status,
        public ?string $notes = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['patient_id'] ?? 0),
            (int) ($row['doctor_id'] ?? 0),
            (int) ($row['service_id'] ?? 0),
            (string) ($row['appointment_date'] ?? ''),
            (string) ($row['start_time'] ?? ''),
            (string) ($row['end_time'] ?? ''),
            (string) ($row['status'] ?? 'pending'),
            $row['notes'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null,
        );
    }
}
