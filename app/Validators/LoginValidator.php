<?php

namespace App\Validators;

class LoginValidator
{
    public function validate(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['email'] ?? '')) === '') {
            $errors['email'] = 'Email is required.';
        }

        if (trim((string) ($data['password'] ?? '')) === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }
}
