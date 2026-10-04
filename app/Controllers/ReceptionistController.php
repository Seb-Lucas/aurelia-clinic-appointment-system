<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Services\AppointmentService;

class ReceptionistController
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $date = (string) $request->input('date', date('Y-m-d'));
        if (strtotime($date) === false) {
            $date = date('Y-m-d');
        }
        $appointments = (new AppointmentRepository())->findDetailedByDate($date);

        return Response::view('receptionist/index', [
            'title' => 'Reception Workspace',
            'user' => $user,
            'queueDate' => $date,
            'appointments' => $appointments,
            'waitingCount' => count(array_filter($appointments, fn(array $appointment) => $appointment['status'] === 'checked_in')),
            'pendingCount' => count(array_filter($appointments, fn(array $appointment) => $appointment['status'] === 'pending')),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function checkIn(Request $request, int $id): Response
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/login');
        }

        if ($request->input('_csrf_token') !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/receptionist');
        }

        $updated = (new AppointmentService())->updateStatus($id, 'checked_in', $user, 'Patient marked arrived by reception.');
        Session::setFlash($updated ? 'success' : 'error', $updated ? 'Patient marked as arrived.' : 'This appointment is not eligible for check-in.');

        return redirect('/receptionist');
    }
}
