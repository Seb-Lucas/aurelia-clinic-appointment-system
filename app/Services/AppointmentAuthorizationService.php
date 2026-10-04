<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;

class AppointmentAuthorizationService
{
    public function __construct(
        private readonly PatientRepository $patientRepository = new PatientRepository(),
        private readonly DoctorRepository $doctorRepository = new DoctorRepository(),
    ) {
    }

    public function canView(?array $user, Appointment $appointment): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user['role'] ?? '', ['admin', 'receptionist'], true)) {
            return true;
        }

        if (($user['role'] ?? '') === 'patient') {
            $patient = $this->patientRepository->findByUserId((int) ($user['id'] ?? 0));
            return $patient !== null && $patient->id === $appointment->patientId;
        }

        if (($user['role'] ?? '') === 'doctor') {
            $doctor = $this->doctorRepository->findByUserId((int) ($user['id'] ?? 0));
            return $doctor !== null && $doctor->id === $appointment->doctorId;
        }

        return false;
    }

    public function canCancel(?array $user, Appointment $appointment): bool
    {
        if (!$this->canView($user, $appointment)) {
            return false;
        }

        if (!AppointmentStatusPolicy::canTransition($appointment->status, 'cancelled')) {
            return false;
        }

        return in_array($user['role'] ?? '', ['patient', 'doctor', 'receptionist', 'admin'], true);
    }

    public function canTransition(?array $user, Appointment $appointment, string $newStatus): bool
    {
        if (!$this->canView($user, $appointment)) {
            return false;
        }

        $role = $user['role'] ?? '';
        if ($role === 'receptionist' && $newStatus === 'checked_in') {
            return in_array($appointment->status, ['pending', 'confirmed'], true);
        }
        if (!AppointmentStatusPolicy::canTransition($appointment->status, $newStatus)) {
            return false;
        }

        if ($role === 'admin') {
            return true;
        }
        if ($role === 'doctor') {
            return in_array($newStatus, ['confirmed', 'declined', 'checked_in', 'in_progress', 'completed', 'no_show', 'cancelled'], true);
        }
        if ($role === 'receptionist') {
            return in_array($newStatus, ['checked_in', 'cancelled'], true);
        }

        return $role === 'patient' && $newStatus === 'cancelled';
    }
}
