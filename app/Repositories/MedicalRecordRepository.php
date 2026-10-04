<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\MedicalRecord;

class MedicalRecordRepository
{
    public function findByPatientId(int $patientId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM medical_records WHERE patient_id = :patient_id ORDER BY created_at DESC');
        $stmt->execute(['patient_id' => $patientId]);
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => MedicalRecord::fromArray($row), $rows);
    }
}
