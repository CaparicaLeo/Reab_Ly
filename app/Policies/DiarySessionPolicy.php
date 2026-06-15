<?php

namespace App\Policies;

use App\Models\DiarySession;
use App\Models\User;

class DiarySessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->doctor()->exists() || $user->patient()->exists();
    }

    public function view(User $user, DiarySession $diarySession): bool
    {
        $doctor = $user->doctor()->first();
        $patient = $user->patient()->first();

        if ($doctor) {
            return $diarySession->patient->doctor_id === $doctor->id;
        }

        if ($patient) {
            return $diarySession->patient_id === $patient->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->patient()->exists();
    }

    public function update(User $user, DiarySession $diarySession): bool
    {
        return false;
    }

    public function delete(User $user, DiarySession $diarySession): bool
    {
        return false;
    }
}
