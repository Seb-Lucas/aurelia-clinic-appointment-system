<?php

namespace App\Services;

use App\Core\Session;
use App\Repositories\UserRepository;

class AuthService
{
    public function __construct(private readonly UserRepository $userRepository = new UserRepository())
    {
    }

    public function login(string $email, string $password): array
    {
        $email = trim(strtolower($email));
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->passwordHash)) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if ($user->status !== 'active') {
            return ['success' => false, 'message' => 'Your account is inactive.'];
        }

        Session::regenerate();
        Session::set('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ]);

        $this->userRepository->updateLastLogin($user->id);
        (new AuditLogService())->log($user->id, 'login', 'user', $user->id, 'success', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        $user = self::currentUser();
        if ($user) {
            (new AuditLogService())->log((int) ($user['id'] ?? 0), 'logout', 'user', (int) ($user['id'] ?? 0), 'success');
        }
        Session::destroy();
    }

    public static function currentUser(): ?array
    {
        return Session::get('user');
    }
}
