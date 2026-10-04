<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\DoctorScheduleRepository;

class ScheduleService
{
    public function __construct(private readonly DoctorScheduleRepository $repository = new DoctorScheduleRepository())
    {
    }

    public function create(array $data): array
    {
        $doctorId = (int) ($data['doctor_id'] ?? 0);
        $dayOfWeek = (int) ($data['day_of_week'] ?? 0);
        $startTime = trim((string) ($data['start_time'] ?? ''));
        $endTime = trim((string) ($data['end_time'] ?? ''));

        if ($doctorId <= 0 || $dayOfWeek < 1 || $dayOfWeek > 7) {
            return ['success' => false, 'message' => 'A valid doctor and weekday are required.'];
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            return ['success' => false, 'message' => 'Use valid 24-hour times such as 09:00 and 17:00.'];
        }

        $startSeconds = strtotime($startTime);
        $endSeconds = strtotime($endTime);
        if ($startSeconds === false || $endSeconds === false || $startSeconds >= $endSeconds) {
            return ['success' => false, 'message' => 'The schedule start time must be earlier than the end time.'];
        }

        $pdo = Database::getConnection();
        $doctorStmt = $pdo->prepare('SELECT id FROM doctors WHERE id = :doctor_id LIMIT 1');
        $doctorStmt->execute(['doctor_id' => $doctorId]);
        if ($doctorStmt->fetch() === false) {
            return ['success' => false, 'message' => 'The selected doctor could not be found.'];
        }

        if ($this->repository->overlaps($doctorId, $dayOfWeek, $startTime, $endTime)) {
            return ['success' => false, 'message' => 'This schedule overlaps an existing time block for the same doctor and day.'];
        }

        $schedule = $this->repository->create([
            'doctor_id' => $doctorId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return ['success' => true, 'schedule' => $schedule];
    }

    public function doctorSchedules(int $doctorId): array
    {
        return $this->repository->allByDoctorId($doctorId);
    }
}
