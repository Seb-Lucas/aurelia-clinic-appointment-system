<?php

namespace Tests;

use App\Controllers\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\UserService;

class UserManagementTest extends TestCase
{
    public function testUserServiceCreatesUserRecord(): void
    {
        $result = (new UserService())->createUser([
            'name' => 'New Receptionist',
            'email' => 'newreception@example.com',
            'password' => 'password123',
            'role' => 'receptionist',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('receptionist', $result['user']->role);
    }

    public function testAdminUsersPageRendersUserList(): void
    {
        Session::set('user', [
            'id' => 99,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = (new AdminController())->users(new Request('GET', '/admin/users', [], [], $_SERVER));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('User Management', $response->content);
    }
}
