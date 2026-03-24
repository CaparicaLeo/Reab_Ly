<?php

namespace App\Policies;

use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TreatmentItemPolicy
{
    public function viewAny(User $user, Treatment $treatment): bool
    {
        return $user->id === $treatment->patient->user_id
            || $user->id === $treatment->doctor->user_id;
    }

    public function view(User $user, TreatmentItem $treatmentItem): bool
    {
        return $user->id === $treatmentItem->treatment->patient->user_id
            || $user->id === $treatmentItem->treatment->doctor->user_id;
    }

    public function create(User $user, Treatment $treatment): bool
    {
        return $user->id === $treatment->doctor->user_id;
    }

    public function update(User $user, TreatmentItem $treatmentItem): bool
    {
        return $user->id === $treatmentItem->treatment->doctor->user_id;
    }

    public function delete(User $user, TreatmentItem $treatmentItem): bool
    {
        return $user->id === $treatmentItem->treatment->doctor->user_id;
    }
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TreatmentItem $treatmentItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TreatmentItem $treatmentItem): bool
    {
        return false;
    }
}
