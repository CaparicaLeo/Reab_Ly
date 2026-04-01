<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// -------------------------------------------------------
// Helpers
// -------------------------------------------------------

function doctorWithUser(): array
{
    $doctor = Doctor::factory()->create();
    $user   = $doctor->user;
    return [$doctor, $user];
}

function patientWithUser(): array
{
    $patient = Patient::factory()->create();
    $user    = $patient->user;
    return [$patient, $user];
}

// -------------------------------------------------------
// INDEX
// -------------------------------------------------------

test('doctor can list own treatments', function () {
    [$doctor, $user] = doctorWithUser();
    Treatment::factory()->count(3)->create(['doctor_id' => $doctor->id]);
    Treatment::factory()->count(2)->create(); // de outro doctor

    $this->actingAs($user)
        ->getJson('/api/treatments')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('patient cannot list treatments', function () {
    [, $user] = patientWithUser();

    $this->actingAs($user)
        ->getJson('/api/treatments')
        ->assertForbidden();
});

test('unauthenticated user cannot list treatments', function () {
    $this->getJson('/api/treatments')
        ->assertUnauthorized();
});

// -------------------------------------------------------
// SHOW
// -------------------------------------------------------

test('doctor can view own treatment', function () {
    [$doctor, $user] = doctorWithUser();
    $treatment = Treatment::factory()->create(['doctor_id' => $doctor->id]);

    $this->actingAs($user)
        ->getJson("/api/treatments/{$treatment->id}")
        ->assertOk();
});

test('patient can view own treatment', function () {
    [$patient, $user] = patientWithUser();
    $treatment = Treatment::factory()->create(['patient_id' => $patient->id]);

    $this->actingAs($user)
        ->getJson("/api/treatments/{$treatment->id}")
        ->assertOk();
});

test('doctor cannot view treatment of another doctor', function () {
    [, $user] = doctorWithUser();
    $treatment = Treatment::factory()->create(); // outro doctor

    $this->actingAs($user)
        ->getJson("/api/treatments/{$treatment->id}")
        ->assertForbidden();
});

test('patient cannot view treatment of another patient', function () {
    [, $user] = patientWithUser();
    $treatment = Treatment::factory()->create(); // outro patient

    $this->actingAs($user)
        ->getJson("/api/treatments/{$treatment->id}")
        ->assertForbidden();
});

// -------------------------------------------------------
// STORE
// -------------------------------------------------------

test('doctor can create treatment', function () {
    [$doctor, $user] = doctorWithUser();
    [$patient]       = patientWithUser();

    $payload = [
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
        'title'      => 'Tratamento X',
        'start_date' => '2025-01-01',
        'end_date'   => '2025-06-01',
        'status'     => 'ongoing',
    ];

    $this->actingAs($user)
        ->postJson('/api/treatments', $payload)
        ->assertCreated();
});

test('patient cannot create treatment', function () {
    [, $user]  = patientWithUser();
    [$doctor]  = doctorWithUser();
    [$patient] = patientWithUser();

    $payload = [
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
        'title'      => 'Tratamento X',
        'start_date' => '2025-01-01',
        'end_date'   => '2025-06-01',
        'status'     => 'ongoing',
    ];

    $this->actingAs($user)
        ->postJson('/api/treatments', $payload)
        ->assertForbidden();
});

test('unauthenticated user cannot create treatment', function () {
    $this->postJson('/api/treatments', [])
        ->assertUnauthorized();
});

// -------------------------------------------------------
// STORE - Validação
// -------------------------------------------------------

test('store fails without required fields', function () {
    [0 => $doctor, 1 => $user] = doctorWithUser();

    $this->actingAs($user)
        ->postJson('/api/treatments', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['patient_id', 'doctor_id', 'title', 'start_date', 'status']);
});

test('store fails with invalid patient_id', function () {
    [$doctor, $user] = doctorWithUser();

    $payload = [
        'doctor_id'  => $doctor->id,
        'patient_id' => 'id-inexistente',
        'title'      => 'Tratamento X',
        'start_date' => '2025-01-01',
        'status'     => 'ongoing',
    ];

    $this->actingAs($user)
        ->postJson('/api/treatments', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['patient_id']);
});

test('store fails when end_date is before start_date', function () {
    [$doctor, $user] = doctorWithUser();
    [$patient]       = patientWithUser();

    $payload = [
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
        'title'      => 'Tratamento X',
        'start_date' => '2025-06-01',
        'end_date'   => '2025-01-01',
        'status'     => 'ongoing',
    ];

    $this->actingAs($user)
        ->postJson('/api/treatments', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});

test('store fails with invalid status', function () {
    [$doctor, $user] = doctorWithUser();
    [$patient]       = patientWithUser();

    $payload = [
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
        'title'      => 'Tratamento X',
        'start_date' => '2025-01-01',
        'status'     => 'invalido',
    ];

    $this->actingAs($user)
        ->postJson('/api/treatments', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

// -------------------------------------------------------
// UPDATE
// -------------------------------------------------------

test('doctor can update own treatment', function () {
    [$doctor, $user] = doctorWithUser();
    $treatment = Treatment::factory()->create(['doctor_id' => $doctor->id]);

    $this->actingAs($user)
        ->putJson("/api/treatments/{$treatment->id}", ['title' => 'Novo título', 'status' => 'completed'])
        ->assertOk();
});

test('doctor cannot update treatment of another doctor', function () {
    [, $user]  = doctorWithUser();
    $treatment = Treatment::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/treatments/{$treatment->id}", ['title' => 'Hack'])
        ->assertForbidden();
});

// -------------------------------------------------------
// DESTROY
// -------------------------------------------------------

test('doctor can delete own treatment', function () {
    [$doctor, $user] = doctorWithUser();
    $treatment = Treatment::factory()->create(['doctor_id' => $doctor->id]);

    $this->actingAs($user)
        ->deleteJson("/api/treatments/{$treatment->id}")
        ->assertNoContent();
});

test('doctor cannot delete treatment of another doctor', function () {
    [, $user]  = doctorWithUser();
    $treatment = Treatment::factory()->create();

    $this->actingAs($user)
        ->deleteJson("/api/treatments/{$treatment->id}")
        ->assertForbidden();
});

test('patient cannot delete treatment', function () {
    [, $user]  = patientWithUser();
    $treatment = Treatment::factory()->create();

    $this->actingAs($user)
        ->deleteJson("/api/treatments/{$treatment->id}")
        ->assertForbidden();
});