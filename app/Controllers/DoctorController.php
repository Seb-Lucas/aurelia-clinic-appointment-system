<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Services\AppointmentService;
use App\Services\ScheduleService;

class DoctorController
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $doctorId = $this->resolveDoctorId($user);
        $appointments = $doctorId > 0 ? (new AppointmentRepository())->findDetailedByDoctorId($doctorId) : [];
        $today = date('Y-m-d');
        $todayAppointments = array_values(array_filter($appointments, fn(array $appointment) => $appointment['appointment_date'] === $today));
        $pendingCount = count(array_filter($appointments, fn(array $appointment) => $appointment['status'] === 'pending'));
        $upcomingAppointments = array_filter($appointments, function (array $appointment) use ($today): bool {
            return $appointment['appointment_date'] >= $today
                && in_array($appointment['status'], ['pending', 'confirmed', 'checked_in', 'in_progress'], true);
        });

        return Response::view('doctor/index', [
            'title' => 'Doctor Dashboard',
            'user' => $user,
            'appointments' => $appointments,
            'todayAppointments' => $todayAppointments,
            'pendingCount' => $pendingCount,
            'upcomingCount' => count($upcomingAppointments),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function updateAppointmentStatus(Request $request, int $id): Response
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/login');
        }

        if ($request->input('_csrf_token') !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/doctor');
        }

        $status = trim((string) $request->input('status', ''));
        $updated = (new AppointmentService())->updateStatus($id, $status, $user, trim((string) $request->input('notes', '')));
        Session::setFlash($updated ? 'success' : 'error', $updated ? 'Appointment status updated.' : 'This appointment cannot be updated with that action.');

        return redirect('/doctor');
    }

    public function schedule(Request $request): Response
    {
        $user = Session::get('user');
        $doctorId = $this->resolveDoctorId($user);
        $schedules = $doctorId > 0 ? (new ScheduleService())->doctorSchedules($doctorId) : [];

        return Response::view('doctor/schedule', [
            'title' => 'Schedule Management',
            'user' => $user,
            'doctor_id' => $doctorId,
            'schedules' => $schedules,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function saveSchedule(Request $request): Response
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/login');
        }

        $token = $request->input('_csrf_token');
        if ($token !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/doctor/schedule');
        }

        $data = $request->all();
        $data['doctor_id'] = $this->resolveDoctorId($user);

        $result = (new ScheduleService())->create($data);
        if (!$result['success']) {
            Session::setFlash('error', $result['message']);
            return redirect('/doctor/schedule');
        }

        Session::setFlash('success', 'Schedule saved successfully.');
        return redirect('/doctor/schedule?doctor_id=' . $result['schedule']['doctor_id']);
    }

    protected function resolveDoctorId(?array $user): int
    {
        if (!$user) {
            return 0;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => (int) ($user['id'] ?? 0)]);
        $row = $stmt->fetch();

        return $row ? (int) $row['id'] : 0;
    }
}
