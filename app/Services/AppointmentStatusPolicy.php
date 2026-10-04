<?php

namespace App\Services;

class AppointmentStatusPolicy
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'declined', 'cancelled'],
        'confirmed' => ['checked_in', 'cancelled', 'no_show'],
        'checked_in' => ['in_progress', 'no_show'],
        'in_progress' => ['completed'],
        'declined' => [],
        'cancelled' => [],
        'completed' => [],
        'no_show' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function statuses(): array
    {
        return array_keys(self::TRANSITIONS);
    }
}
