<?php

namespace Tests\Feature\Exercise;

use App\Models\Doctor;
use App\Models\Exercise;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentItem;

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

function createLinkedExercise(string $doctorId, string $patientId): Exercise
{
    $treatment = Treatment::factory()->create([
        'doctor_id'  => $doctorId,
        'patient_id' => $patientId,
    ]);

    $exercise = Exercise::factory()->create();

    TreatmentItem::factory()->create([
        'treatment_id' => $treatment->id,
        'exercise_id'  => $exercise->id,
    ]);

    return $exercise;
}

// -------------------------------------------------------
// INDEX
// -------------------------------------------------------

test('doctor can list exercises from own treatments', function () {
    [$doctor, $user] = doctorWithUser();
    [$patient]       = patientWithUser();

    $exercise = createLinkedExercise($doctor->id, $patient->id);

    $this->actingAs($user)
        ->getJson('/api/exercises')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $exercise->id);
});

test('doctor does not see exercises not linked to any of their treatments', function () {
    [$doctorA, $userA] = doctorWithUser();
    [$doctorB]         = doctorWithUser();
    [$patient]         = patientWithUser();

    createLinkedExercise($doctorB->id, $patient->id);

    $this->actingAs($userA)
        ->getJson('/api/exercises')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('patient can list exercises from own treatments', function () {
    [$doctor]  = doctorWithUser();
    [$patient, $user] = patientWithUser();

    createLinkedExercise($doctor->id, $patient->id);

    $this->actingAs($user)
        ->getJson('/api/exercises')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('patient does not see exercises not linked to any of their treatments', function () {
    [$doctor]          = doctorWithUser();
    [$patientA, $userA] = patientWithUser();
    [$patientB]        = patientWithUser();

    createLinkedExercise($doctor->id, $patientB->id);

    $this->actingAs($userA)
        ->getJson('/api/exercises')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('unauthenticated user cannot list exercises', function () {
    $this->getJson('/api/exercises')
        ->assertUnauthorized();
});

// -------------------------------------------------------
// SHOW
// -------------------------------------------------------

test('doctor can view exercise from own treatment', function () {
    [$doctor, $user] = doctorWithUser();
    [$patient]       = patientWithUser();

    $exercise = createLinkedExercise($doctor->id, $patient->id);

    $this->actingAs($user)
        ->getJson("/api/exercises/{$exercise->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $exercise->id);
});

test('patient can view exercise from own treatment', function () {
    [$doctor]  = doctorWithUser();
    [$patient, $user] = patientWithUser();

    $exercise = createLinkedExercise($doctor->id, $patient->id);

    $this->actingAs($user)
        ->getJson("/api/exercises/{$exercise->id}")
        ->assertOk();
});

test('doctor cannot view exercise not linked to any of their treatments', function () {
    [$doctorA, $userA] = doctorWithUser();
    [$doctorB]         = doctorWithUser();
    [$patient]         = patientWithUser();

    $exercise = createLinkedExercise($doctorB->id, $patient->id);

    $this->actingAs($userA)
        ->getJson("/api/exercises/{$exercise->id}")
        ->assertForbidden();
});

test('patient cannot view exercise not linked to any of their treatments', function () {
    [$doctor]           = doctorWithUser();
    [$patientA, $userA] = patientWithUser();
    [$patientB]         = patientWithUser();

    $exercise = createLinkedExercise($doctor->id, $patientB->id);

    $this->actingAs($userA)
        ->getJson("/api/exercises/{$exercise->id}")
        ->assertForbidden();
});

test('unauthenticated user cannot view exercise', function () {
    $exercise = Exercise::factory()->create();

    $this->getJson("/api/exercises/{$exercise->id}")
        ->assertUnauthorized();
});

// -------------------------------------------------------
// STORE
// -------------------------------------------------------

test('doctor can create exercise', function () {
    [, $user] = doctorWithUser();

    $payload = [
        'title'       => 'Agachamento',
        'description' => 'Agachamento com peso corporal',
        'category'    => 'forca',
        'video_url'   => 'https://example.com/video.mp4',
    ];

    $this->actingAs($user)
        ->postJson('/api/exercises', $payload)
        ->assertCreated()
        ->assertJsonPath('data.title', 'Agachamento');
});

test('patient cannot create exercise', function () {
    [, $user] = patientWithUser();

    $this->actingAs($user)
        ->postJson('/api/exercises', [
            'title' => 'Agachamento',
        ])
        ->assertForbidden();
});

test('store fails without title', function () {
    [, $user] = doctorWithUser();

    $this->actingAs($user)
        ->postJson('/api/exercises', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('unauthenticated user cannot create exercise', function () {
    $this->postJson('/api/exercises', [])
        ->assertUnauthorized();
});

// -------------------------------------------------------
// UPDATE
// -------------------------------------------------------

test('doctor can update exercise', function () {
    [, $user] = doctorWithUser();

    $exercise = Exercise::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/exercises/{$exercise->id}", [
            'title' => 'Novo exercicio',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Novo exercicio');
});

test('patient cannot update exercise', function () {
    [, $user]  = patientWithUser();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/exercises/{$exercise->id}", [
            'title' => 'Hack',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot update exercise', function () {
    $exercise = Exercise::factory()->create();

    $this->putJson("/api/exercises/{$exercise->id}", [
        'title' => 'Hack',
    ])
        ->assertUnauthorized();
});

// -------------------------------------------------------
// DESTROY
// -------------------------------------------------------

test('doctor can delete exercise', function () {
    [, $user]  = doctorWithUser();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)
        ->deleteJson("/api/exercises/{$exercise->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
});

test('patient cannot delete exercise', function () {
    [, $user]  = patientWithUser();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)
        ->deleteJson("/api/exercises/{$exercise->id}")
        ->assertForbidden();
});

test('unauthenticated user cannot delete exercise', function () {
    $exercise = Exercise::factory()->create();

    $this->deleteJson("/api/exercises/{$exercise->id}")
        ->assertUnauthorized();
});
