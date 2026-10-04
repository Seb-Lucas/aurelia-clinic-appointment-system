<?php

namespace App\Validators;

class AppointmentValidator
{
    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['patient_id'])) {
            $errors['patient_id'] = 'A patient is required.';
        }
        if (empty($data['doctor_id'])) {
            $errors['doctor_id'] = 'A doctor is required.';
        }
        if (empty($data['service_id'])) {
            $errors['service_id'] = 'A service is required.';
        }
        if (empty($data['appointment_date'])) {
            $errors['appointment_date'] = 'Appointment date is required.';
        }
        if (empty($data['start_time'])) {
            $errors['start_time'] = 'Start time is required.';
        }
        if (empty($data['end_time'])) {
            $errors['end_time'] = 'End time is required.';
        }

        if (!empty($data['appointment_date']) && strtotime($data['appointment_date']) < strtotime('today')) {
            $errors['appointment_date'] = 'Past dates are not allowed.';
        }

        if (!empty($data['start_time']) && !empty($data['end_time']) && strtotime($data['start_time']) >= strtotime($data['end_time'])) {
            $errors['end_time'] = 'End time must be later than start time.';
        }

        return $errors;
    }
}
