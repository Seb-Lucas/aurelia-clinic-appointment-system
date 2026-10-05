<?php

namespace Tests;

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuthService;

class AuthenticationTest extends TestCase
{
    public function testSuccessfulLogin(): void
    {
        $result = (new AuthService())->login('patient@example.com', 'password123');

        $this->assertTrue($result['success']);
        $this->assertSame('patient', Session::get('user')['role']);
    }

    public function testRejectsIncorrectPassword(): void
    {
        $result = (new AuthService())->login('patient@example.com', 'wrong-password');

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid email or password.', $result['message']);
    }

    public function testAdministratorLoginRedirectsToAdministrationWorkspace(): void
    {
        $pdo = \App\Core\Database::getConnection();
        $insert = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, status) VALUES (:name, :email, :password_hash, :role, :status)'
        );
        $insert->execute([
            'name' => 'Portfolio Administrator',
            'email' => 'admin.demo@example.test',
            'password_hash' => password_hash('secure-admin-password', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $token = csrf_token();

        $response = (new AuthController())->login(new Request(
            'POST',
            '/login',
            [],
            [
                '_csrf_token' => $token,
                'email' => 'admin.demo@example.test',
                'password' => 'secure-admin-password',
            ],
            $_SERVER
        ));

        $this->assertSame('/admin', $response->headers['Location']);
        $this->assertSame('admin', Session::get('user')['role']);

        $dashboard = (new DashboardController())->dashboard(new Request('GET', '/dashboard', [], [], $_SERVER));
        $this->assertSame('/admin', $dashboard->headers['Location']);
    }
}
