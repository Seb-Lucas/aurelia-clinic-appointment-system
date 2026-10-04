<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;

class AdminController
{
    public function __construct(private readonly UserService $userService = new UserService())
    {
    }

    public function index(Request $request): Response
    {
        $user = Session::get('user');
        return Response::view('admin/index', [
            'title' => 'Administration',
            'user' => $user,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function users(Request $request): Response
    {
        $user = Session::get('user');
        $users = (new UserRepository())->all();

        return Response::view('admin/users', [
            'title' => 'User Management',
            'user' => $user,
            'users' => $users,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function reports(Request $request): Response
    {
        $user = Session::get('user');
        $summary = (new AppointmentRepository())->reportSummary();

        return Response::view('admin/reports', [
            'title' => 'Appointment Reports',
            'user' => $user,
            'report' => $summary,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function store(Request $request): Response
    {
        $token = $request->input('_csrf_token');
        if ($token !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/admin/users');
        }

        $result = $this->userService->createUser($request->all());
        if (!$result['success']) {
            Session::setFlash('error', $result['message']);
            return redirect('/admin/users');
        }

        Session::setFlash('success', 'User created successfully.');
        return redirect('/admin/users');
    }
}
