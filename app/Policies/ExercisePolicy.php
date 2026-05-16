<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->doctor()->exists() || $user->patient()->exists();
    }

    public function view(User $user, Exercise $exercise): bool
    {
        $doctor  = $user->doctor()->first();
        $patient = $user->patient()->first();

        if ($doctor) {
            return $exercise->treatmentItems()
                ->whereHas('treatment', fn($q) => $q->where('doctor_id', $doctor->id))
                ->exists();
        }

        if ($patient) {
            return $exercise->treatmentItems()
                ->whereHas('treatment', fn($q) => $q->where('patient_id', $patient->id))
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->doctor()->exists();
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->doctor()->exists();
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->doctor()->exists();
    }

    public function restore(User $user, Exercise $exercise): bool
    {
        return $user->doctor()->exists();
    }

    public function forceDelete(User $user, Exercise $exercise): bool
    {
        return false;
    }
}
