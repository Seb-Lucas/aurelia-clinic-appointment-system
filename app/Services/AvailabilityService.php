<?php

namespace App\Services;

use App\Core\Database;

class AvailabilityService
{
    public function availableSlots(int $doctorId, string $date, int $durationMinutes = 30): array
    {
        if ($doctorId <= 0 || $durationMinutes <= 0 || strtotime($date) === false) {
            return [];
        }

        $pdo = Database::getConnection();
        $exceptionStmt = $pdo->prepare('SELECT * FROM schedule_exceptions WHERE doctor_id = :doctor_id AND date = :date ORDER BY start_time ASC');
        $exceptionStmt->execute(['doctor_id' => $doctorId, 'date' => $date]);
        $exceptions = $exceptionStmt->fetchAll();
        foreach ($exceptions as $exception) {
            if ((int) $exception['is_available'] === 0) {
                return [];
            }
        }

        $scheduleStmt = $pdo->prepare('SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id AND day_of_week = :day_of_week AND is_active = 1');
        $scheduleStmt->execute([
            'doctor_id' => $doctorId,
            'day_of_week' => date('N', strtotime($date)),
        ]);
        $schedules = $scheduleStmt->fetchAll();
        $customExceptions = array_values(array_filter($exceptions, fn(array $exception) => $exception['start_time'] && $exception['end_time']));
        if ($customExceptions !== []) {
            $schedules = $customExceptions;
        }

        if ($schedules === []) {
            return [];
        }

        $slots = [];
        foreach ($schedules as $schedule) {
            $start = strtotime($date . ' ' . $schedule['start_time']);
            $end = strtotime($date . ' ' . $schedule['end_time']);
            $current = $start;
            while ($current + ($durationMinutes * 60) <= $end) {
                $slotStart = date('H:i', $current);
                $slotEnd = date('H:i', $current + ($durationMinutes * 60));
                $slots[] = ['start' => $slotStart, 'end' => $slotEnd];
                $current += $durationMinutes * 60;
            }
        }

        $appointmentStmt = $pdo->prepare("SELECT start_time, end_time FROM appointments WHERE doctor_id = :doctor_id AND appointment_date = :date AND status NOT IN ('cancelled', 'declined')");
        $appointmentStmt->execute(['doctor_id' => $doctorId, 'date' => $date]);
        $appointments = $appointmentStmt->fetchAll();

        foreach ($appointments as $appointment) {
            $slots = array_values(array_filter($slots, function ($slot) use ($appointment) {
                $slotStart = strtotime($appointment['start_time']);
                $slotEnd = strtotime($appointment['end_time']);
                $candidateStart = strtotime($slot['start']);
                $candidateEnd = strtotime($slot['end']);
                return !($candidateStart < $slotEnd && $candidateEnd > $slotStart);
            }));
        }

        return $slots;
    }

    public function isSlotAvailable(int $doctorId, string $date, string $startTime, string $endTime, int $durationMinutes): bool
    {
        foreach ($this->availableSlots($doctorId, $date, $durationMinutes) as $slot) {
            if ($slot['start'] === $startTime && $slot['end'] === $endTime) {
                return true;
            }
        }

        return false;
    }

    public function nextAvailableDate(int $doctorId, int $durationMinutes, ?string $fromDate = null): ?string
    {
        $date = $fromDate ?: date('Y-m-d');
        for ($offset = 0; $offset <= 60; $offset++) {
            $candidate = date('Y-m-d', strtotime($date . ' +' . $offset . ' days'));
            if ($this->availableSlots($doctorId, $candidate, $durationMinutes) !== []) {
                return $candidate;
            }
        }

        return null;
    }
}
