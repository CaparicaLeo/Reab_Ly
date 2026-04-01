<?php

namespace App\Policies;

use App\Models\Treatment;
use App\Models\User;

class TreatmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->doctor()->exists();
    }

    public function view(User $user, Treatment $treatment): bool
    {
        $doctor  = $user->doctor()->first();
        $patient = $user->patient()->first();

        return ($doctor  && $treatment->doctor_id  === $doctor->id)
            || ($patient && $treatment->patient_id === $patient->id);
    }

    public function create(User $user): bool
    {
        return $user->doctor()->exists();
    }

    public function update(User $user, Treatment $treatment): bool
    {
        $doctor = $user->doctor()->first();

        return $doctor && $treatment->doctor_id === $doctor->id;
    }

    public function delete(User $user, Treatment $treatment): bool
    {
        $doctor = $user->doctor()->first();

        return $doctor && $treatment->doctor_id === $doctor->id;
    }

    public function restore(User $user, Treatment $treatment): bool
    {
        $doctor = $user->doctor()->first();

        return $doctor && $treatment->doctor_id === $doctor->id;
    }

    public function forceDelete(User $user, Treatment $treatment): bool
    {
        return false;
    }
}