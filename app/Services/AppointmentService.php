<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Validators\AppointmentValidator;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository = new AppointmentRepository(),
        private readonly AppointmentValidator $validator = new AppointmentValidator(),
        private readonly AvailabilityService $availabilityService = new AvailabilityService(),
        private readonly AppointmentAuthorizationService $authorization = new AppointmentAuthorizationService(),
    ) {
    }

    public function bookAppointment(array $data): array
    {
        $errors = $this->validator->validate($data);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        if (!$this->appointmentRepository->patientExists((int) $data['patient_id'])) {
            return ['success' => false, 'errors' => ['The selected patient could not be found.']];
        }

        if (!$this->appointmentRepository->doctorIsActive((int) $data['doctor_id'])) {
            return ['success' => false, 'errors' => ['The selected doctor is not available.']];
        }
        $duration = $this->appointmentRepository->serviceDuration((int) $data['service_id']);
        if ($duration === null || !$this->appointmentRepository->doctorOffersService((int) $data['doctor_id'], (int) $data['service_id'])) {
            return ['success' => false, 'errors' => ['The selected doctor does not offer this service.']];
        }
        $expectedEnd = date('H:i', strtotime($data['start_time']) + ($duration * 60));
        if ($expectedEnd !== $data['end_time'] || !$this->availabilityService->isSlotAvailable((int) $data['doctor_id'], $data['appointment_date'], $data['start_time'], $data['end_time'], $duration)) {
            return ['success' => false, 'errors' => ['The selected time is not available for this service.']];
        }

        $exclusion = $this->appointmentRepository->findConflicts(
            (int) $data['doctor_id'],
            $data['appointment_date'],
            $data['start_time'],
            $data['end_time']
        );

        if ($exclusion) {
            return ['success' => false, 'errors' => ['The selected doctor is not available at that time.']];
        }

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();
            $appointment = $this->appointmentRepository->create([
                'patient_id' => (int) $data['patient_id'],
                'doctor_id' => (int) $data['doctor_id'],
                'service_id' => (int) $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            $this->appointmentRepository->createStatusHistory($appointment->id, 'pending', $data['notes'] ?? '');
            $pdo->commit();

            $patientUserId = $this->patientUserId((int) ($data['patient_id'] ?? 0));
            (new AuditLogService())->log($patientUserId, 'appointment_created', 'appointment', $appointment->id, 'success', [
                'doctor_id' => (int) $data['doctor_id'],
                'date' => $data['appointment_date'],
                'start_time' => $data['start_time'],
            ]);
            (new NotificationService())->create([
                'user_id' => $patientUserId,
                'type' => 'appointment',
                'message' => 'Your appointment request is pending review for ' . $data['appointment_date'] . ' at ' . $data['start_time'] . '.',
            ]);
            (new NotificationService())->create([
                'user_id' => $this->doctorUserId((int) $data['doctor_id']),
                'type' => 'appointment',
                'message' => 'A new appointment request is awaiting your review for ' . $data['appointment_date'] . ' at ' . $data['start_time'] . '.',
            ]);

            return ['success' => true, 'appointment' => $appointment];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'errors' => ['Unable to create the appointment right now.']];
        }
    }

    public function cancelAppointment(int $id, ?array $user = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $appointment = Appointment::fromArray($row);
        if (!$this->authorization->canCancel($user, $appointment)) {
            return false;
        }

        $pdo->beginTransaction();
        $this->appointmentRepository->updateStatus($id, 'cancelled');
        $this->appointmentRepository->createStatusHistory($id, 'cancelled', 'Cancellation requested by user.');
        $pdo->commit();

        (new AuditLogService())->log($this->patientUserId((int) ($row['patient_id'] ?? 0)), 'appointment_cancelled', 'appointment', $id, 'success');
        (new NotificationService())->create([
            'user_id' => $this->patientUserId((int) ($row['patient_id'] ?? 0)),
            'type' => 'appointment',
            'message' => 'Your appointment has been cancelled.',
        ]);

        return true;
    }

    public function updateStatus(int $id, string $status, ?array $user = null, string $notes = ''): bool
    {
        $appointment = $this->appointmentRepository->findById($id);
        if (!$appointment || !$this->authorization->canTransition($user, $appointment, $status)) {
            return false;
        }

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();
            $this->appointmentRepository->updateStatus($id, $status);
            $this->appointmentRepository->createStatusHistory($id, $status, $notes);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }

        $patientUserId = $this->patientUserId($appointment->patientId);
        $message = match ($status) {
            'confirmed' => 'Your appointment has been confirmed by the doctor.',
            'declined' => 'Your appointment request was declined. Please choose another time.',
            'checked_in' => 'You have been checked in for your appointment.',
            'in_progress' => 'Your visit has started.',
            'completed' => 'Your visit has been completed.',
            'no_show' => 'Your appointment was recorded as a no-show.',
            'cancelled' => 'Your appointment has been cancelled.',
            default => 'Your appointment status has been updated.',
        };
        (new NotificationService())->create([
            'user_id' => $patientUserId,
            'type' => 'appointment',
            'message' => $message,
        ]);
        (new AuditLogService())->log((int) ($user['id'] ?? 0), 'appointment_status_changed', 'appointment', $id, 'success', [
            'from' => $appointment->status,
            'to' => $status,
        ]);

        return true;
    }

    private function patientUserId(int $patientId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT user_id FROM patients WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $patientId]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function doctorUserId(int $doctorId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT user_id FROM doctors WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $doctorId]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }
}
