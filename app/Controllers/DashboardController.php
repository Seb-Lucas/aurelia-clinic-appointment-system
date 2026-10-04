<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Repositories\PatientRepository;

class DashboardController
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        if ($user) {
            return redirect('/dashboard');
        }

        return Response::view('home', [
            'title' => 'Aurelia Private Clinic',
            'user' => null,
        ]);
    }

    public function dashboard(Request $request): Response
    {
        $user = Session::get('user');
        $role = $user['role'] ?? 'patient';
        if ($role === 'doctor') {
            return redirect('/doctor');
        }

        $stats = [
            'Appointments' => 0,
            'Pending' => 0,
            'Doctors' => 0,
        ];
        $upcomingAppointments = [];

        if (($user['role'] ?? '') === 'patient') {
            $patient = (new PatientRepository())->findByUserId((int) ($user['id'] ?? 0));
            if ($patient) {
                $appointments = (new AppointmentRepository())->findByPatientId($patient->id);
                $upcomingAppointments = array_values(array_filter($appointments, function ($appointment) {
                    $isFuture = $appointment->appointmentDate >= date('Y-m-d');
                    $isActive = !in_array($appointment->status, ['cancelled'], true);
                    return $isFuture && $isActive;
                }));
                usort($upcomingAppointments, fn($a, $b) => strcmp($a->appointmentDate . $a->startTime, $b->appointmentDate . $b->startTime));
                $upcomingAppointments = array_slice($upcomingAppointments, 0, 3);

                $stats['Appointments'] = count($appointments);
                $stats['Pending'] = count(array_filter($appointments, fn($appointment) => $appointment->status === 'pending'));
                $stats['Doctors'] = count(array_unique(array_map(fn($appointment) => $appointment->doctorId, $appointments)));
            }
        }

        return Response::view('dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'upcomingAppointments' => $upcomingAppointments,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }
}
