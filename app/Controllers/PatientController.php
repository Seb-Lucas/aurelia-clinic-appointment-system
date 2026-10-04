<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PatientRepository;
use App\Services\MedicalRecordService;

class PatientController
{
    public function profile(Request $request): Response
    {
        $user = Session::get('user');
        return Response::view('patient/profile', [
            'title' => 'My Profile',
            'user' => $user,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function records(Request $request): Response
    {
        $user = Session::get('user');
        $patient = $user ? (new PatientRepository())->findByUserId((int) ($user['id'] ?? 0)) : null;
        $records = $patient ? (new MedicalRecordService())->patientRecords($patient->id) : [];

        return Response::view('patient/records', [
            'title' => 'Medical Records',
            'user' => $user,
            'records' => $records,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }
}
