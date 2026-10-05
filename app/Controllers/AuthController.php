<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;
use App\Validators\LoginValidator;

class AuthController
{
    public function showLogin(Request $request): Response
    {
        return Response::view('auth/login', [
            'title' => 'Login',
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
        ]);
    }

    public function login(Request $request): Response
    {
        $token = $request->input('_csrf_token');
        if ($token !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/login');
        }

        $validator = new LoginValidator();
        $errors = $validator->validate($request->all());
        if ($errors !== []) {
            Session::setFlash('error', reset($errors));
            return redirect('/login');
        }

        $result = (new AuthService())->login((string) $request->input('email'), (string) $request->input('password'));
        if (!$result['success']) {
            Session::setFlash('error', $result['message']);
            return redirect('/login');
        }

        return redirect(match ($result['user']->role ?? '') {
            'doctor' => '/doctor',
            'admin' => '/admin',
            default => '/dashboard',
        });
    }

    public function logout(Request $request): Response
    {
        if ($request->input('_csrf_token') !== csrf_token()) {
            Session::setFlash('error', 'Invalid CSRF token.');
            return redirect('/dashboard');
        }

        (new AuthService())->logout();
        Session::setFlash('success', 'You have been logged out successfully.');
        return redirect('/login');
    }
}
