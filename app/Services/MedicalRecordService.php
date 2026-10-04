<?php

namespace App\Services;

use App\Repositories\MedicalRecordRepository;

class MedicalRecordService
{
    public function __construct(private readonly MedicalRecordRepository $medicalRecordRepository = new MedicalRecordRepository())
    {
    }

    public function patientRecords(int $patientId): array
    {
        if ($patientId <= 0) {
            return [];
        }

        return $this->medicalRecordRepository->findByPatientId($patientId);
    }
}
