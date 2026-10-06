<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Repositories\PatientRepository;
use App\Services\AppointmentService;
use App\Services\AvailabilityService;

class AppointmentController
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $appointments = [];
        if ($user && ($user['role'] ?? '') === 'patient') {
            $patient = (new PatientRepository())->findByUserId((int) ($user['id'] ?? 0));
            $appointments = $patient ? (new AppointmentRepository())->findDetailedByPatientId($patient->id) : [];
        }

        return Response::view('appointments/index', [
            'title' => 'Appointments',
            'user' => $user,
            'appointments' => $appointments,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Session::get('user');
        $appointmentRepository = new AppointmentRepository();
        $doctors = $appointmentRepository->activeDoctors();
        $services = $appointmentRepository->activeServices();
        $doctorId = (int) $request->input('doctor_id', $doctors[0]['id'] ?? 0);
        $serviceId = (int) $request->input('service_id', $services[0]['id'] ?? 0);
        $duration = $appointmentRepository->serviceDuration($serviceId) ?? 30;
        $requestedDate = (string) $request->input('appointment_date', '');
        $date = $requestedDate !== '' ? $requestedDate : ((new AvailabilityService())->nextAvailableDate($doctorId, $duration) ?? date('Y-m-d'));
        $slots = (new AvailabilityService())->availableSlots((int) $doctorId, $date, $duration);

        return Response::view('appointments/create', [
            'title' => 'Book an Appointment',
            'user' => $user,
            'slots' => $slots,
            'doctor_id' => $doctorId,
            'service_id' => $serviceId,
            'doctors' => $doctors,
            'services' => $services,
            'appointment_date' => $date,
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
        ]);
    }

    public function store(Request $request): Response
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/login');
        }

        $data = [
            'patient_id' => $this->resolvePatientId($user, $request),
            'doctor_id' => (int) $request->input('doctor_id', 0),
            'service_id' => (int) $request->input('service_id', 1),
            'appointment_date' => (string) $request->input('appointment_date', ''),
            'start_time' => (string) $request->input('start_time', ''),
            'end_time' => (string) $request->input('end_time', ''),
            'notes' => (string) $request->input('notes', ''),
        ];

        $token = $request->input('_csrf_token');
        if ($token !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect($this->bookingCreateUrl($data));
        }

        $result = (new AppointmentService())->bookAppointment($data);
        if (!$result['success']) {
            $errors = $result['errors'] ?? ['Unable to create the appointment.'];
            Session::setFlash('error', is_array($errors) ? reset($errors) : $errors);
            return redirect($this->bookingCreateUrl($data));
        }

        Session::setFlash('success', 'Appointment has been booked successfully.');
        return redirect('/appointments');
    }

    private function resolvePatientId(array $user, Request $request): int
    {
        if (($user['role'] ?? '') === 'patient') {
            $patient = (new PatientRepository())->findByUserId((int) ($user['id'] ?? 0));
            return $patient?->id ?? 0;
        }

        return (int) $request->input('patient_id', 0);
    }

    private function bookingCreateUrl(array $data): string
    {
        return '/appointments/create?' . http_build_query([
            'doctor_id' => $data['doctor_id'] ?? 0,
            'service_id' => $data['service_id'] ?? 0,
            'appointment_date' => $data['appointment_date'] ?? '',
        ]);
    }

    public function cancel(Request $request, int $id): Response
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/login');
        }

        $token = $request->input('_csrf_token');
        if ($token !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/appointments');
        }

        $cancelled = (new AppointmentService())->cancelAppointment($id, $user);
        if (!$cancelled) {
            Session::setFlash('error', 'The appointment could not be cancelled.');
            return redirect('/appointments');
        }

        Session::setFlash('success', 'Appointment cancelled.');
        return redirect('/appointments');
    }
}
