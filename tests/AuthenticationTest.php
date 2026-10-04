<?php

namespace Tests;

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
}
