<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository = new UserRepository())
    {
    }

    public function createUser(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($name === '' || $email === '' || strlen($password) < 8) {
            return ['success' => false, 'message' => 'Name, valid email, and password of at least 8 characters are required.'];
        }

        if ($this->userRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'A user with that email already exists.'];
        }

        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'patient',
            'status' => $data['status'] ?? 'active',
            'phone' => $data['phone'] ?? null,
        ]);

        return ['success' => true, 'user' => $user];
    }
}
