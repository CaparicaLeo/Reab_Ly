<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->doctor !== null;
    }

    public function view(User $user, Patient $patient): bool
    {
        $doctor = $user->doctor;
        
        return $doctor && $patient->doctor_id === $doctor->id;
    }

    public function create(User $user): bool
    {
        return $user->doctor !== null;
    }

    public function update(User $user, Patient $patient): bool
    {
        $doctor = $user->doctor;

        return $doctor && $patient->doctor_id === $doctor->id;
    }

    public function delete(User $user, Patient $patient): bool
    {
        $doctor = $user->doctor;

        return $doctor && $patient->doctor_id === $doctor->id;
    }
}