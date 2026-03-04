<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'phone_number' => $data['phone_number'] ?? null,
            'role'         => $data['role'],
        ]);

        $this->createProfile($user, $data);

        return $user;
    }

    private function createProfile(User $user, array $data): void
    {
        match ($user->role) {
            'doctor' => $this->createDoctor($user, $data),
            'patient' => $this->createPatient($user, $data),
            default => null,
        };
    }

    private function createDoctor(User $user, array $data): void
    {
        $user->doctor()->create([
            'crefito' => $data['crefito'],
            'specialty' => $data['specialty'],
        ]);
    }
    private function createPatient(User $user, array $data): void
    {
        $user->patient()->create([
            'birth_date' => $data['birth_date'],
            'clinical_condition' => $data['clinical_condition'] ?? null,
        ]);
    }
}
