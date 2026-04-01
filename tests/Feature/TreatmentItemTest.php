<?php

use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------


function makeTreatment(Doctor $doctor, Patient $patient): Treatment
{
    return Treatment::factory()->create([
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
    ]);
}

// =============================================================================
// INDEX
// =============================================================================

it('doctor can list items of their treatment', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    TreatmentItem::factory()->count(3)->for($treatment)->create();

    $this->actingAs($doctor->user)
        ->getJson("/api/treatments/{$treatment->id}/items")
        ->assertOk()
        ->assertJsonCount(3);
});

it('patient can list items of their treatment', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    TreatmentItem::factory()->count(2)->for($treatment)->create();

    $this->actingAs($patient->user)
        ->getJson("/api/treatments/{$treatment->id}/items")
        ->assertOk()
        ->assertJsonCount(2);
});

it('stranger cannot list treatment items', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $stranger  = User::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    TreatmentItem::factory()->count(2)->for($treatment)->create();

    $this->actingAs($stranger)
        ->getJson("/api/treatments/{$treatment->id}/items")
        ->assertForbidden();
});

it('unauthenticated user cannot list treatment items', function () {
    $treatment = Treatment::factory()->create();

    $this->getJson("/api/treatments/{$treatment->id}/items")
        ->assertUnauthorized();
});

// =============================================================================
// SHOW
// =============================================================================

it('doctor can view a treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($doctor->user)
        ->getJson("/api/treatment-items/{$item->id}")
        ->assertOk()
        ->assertJsonFragment(['id' => $item->id]);
});

it('patient can view their treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($patient->user)
        ->getJson("/api/treatment-items/{$item->id}")
        ->assertOk();
});

it('stranger cannot view a treatment item', function () {
    $stranger = User::factory()->create();
    $item     = TreatmentItem::factory()->create();

    $this->actingAs($stranger)
        ->getJson("/api/treatment-items/{$item->id}")
        ->assertForbidden();
});

// =============================================================================
// STORE
// =============================================================================

it('doctor can create a treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $payload = [
        'treatment_id'     => $treatment->id,
        'sets'             => 3,
        'repetitions'      => 12,
        'duration_seconds' => null,
        'frequency_text'   => '3x per week',
    ];

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', $payload)
        ->assertCreated()
        ->assertJsonFragment([
            'sets'           => 3,
            'repetitions'    => 12,
            'frequency_text' => '3x per week',
        ]);

    $this->assertDatabaseHas('treatment_items', [
        'treatment_id' => $treatment->id,
        'sets'         => 3,
        'repetitions'  => 12,
    ]);
});

it('doctor can create a timed treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $payload = [
        'treatment_id'     => $treatment->id,
        'duration_seconds' => 60,
        'frequency_text'   => 'Daily',
    ];

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', $payload)
        ->assertCreated()
        ->assertJsonFragment(['duration_seconds' => 60]);
});

it('patient cannot create a treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($patient->user)
        ->postJson('/api/treatment-items', [
            'treatment_id' => $treatment->id,
            'sets'         => 3,
            'repetitions'  => 10,
        ])
        ->assertForbidden();
});

it('stranger cannot create a treatment item', function () {
    $stranger  = User::factory()->create();
    $treatment = Treatment::factory()->create();

    $this->actingAs($stranger)
        ->postJson('/api/treatment-items', [
            'treatment_id' => $treatment->id,
            'sets'         => 3,
        ])
        ->assertForbidden();
});

// Store — validation

it('store requires treatment id', function () {
    $doctor = User::factory()->create();

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', ['sets' => 3])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['treatment_id']);
});

it('store rejects non existent treatment id', function () {
    $doctor = User::factory()->create();

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id' => '00000000-0000-0000-0000-000000000000',
            'sets'         => 3,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['treatment_id']);
});

it('store rejects sets below one', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id' => $treatment->id,
            'sets'         => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sets']);
});

it('store rejects repetitions below one', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id' => $treatment->id,
            'repetitions'  => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['repetitions']);
});

it('store rejects duration seconds below one', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id'     => $treatment->id,
            'duration_seconds' => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['duration_seconds']);
});

it('store rejects frequency text exceeding 255 chars', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id'   => $treatment->id,
            'frequency_text' => str_repeat('a', 256),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['frequency_text']);
});

it('store accepts all nullable fields as null', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);

    $this->actingAs($doctor->user)
        ->postJson('/api/treatment-items', [
            'treatment_id'     => $treatment->id,
            'sets'             => null,
            'repetitions'      => null,
            'duration_seconds' => null,
            'frequency_text'   => null,
        ])
        ->assertCreated();
});

// =============================================================================
// UPDATE
// =============================================================================

it('doctor can update their treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->exercise()->create();

    $this->actingAs($doctor->user)
        ->putJson("/api/treatment-items/{$item->id}", [
            'sets'        => 5,
            'repetitions' => 20,
        ])
        ->assertOk()
        ->assertJsonFragment(['sets' => 5, 'repetitions' => 20]);

    $this->assertDatabaseHas('treatment_items', [
        'id'          => $item->id,
        'sets'        => 5,
        'repetitions' => 20,
    ]);
});

it('patient cannot update a treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($patient->user)
        ->putJson("/api/treatment-items/{$item->id}", ['sets' => 5])
        ->assertForbidden();
});

it('stranger cannot update a treatment item', function () {
    $stranger = User::factory()->create();
    $item     = TreatmentItem::factory()->create();

    $this->actingAs($stranger)
        ->putJson("/api/treatment-items/{$item->id}", ['sets' => 5])
        ->assertForbidden();
});

// Update — validation

it('update rejects sets below one', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($doctor->user)
        ->putJson("/api/treatment-items/{$item->id}", ['sets' => 0])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sets']);
});

it('update rejects non integer repetitions', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($doctor->user)
        ->putJson("/api/treatment-items/{$item->id}", ['repetitions' => 'abc'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['repetitions']);
});

// =============================================================================
// DESTROY
// =============================================================================

it('doctor can delete their treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($doctor->user)
        ->deleteJson("/api/treatment-items/{$item->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('treatment_items', ['id' => $item->id]);
});

it('patient cannot delete a treatment item', function () {
    $doctor  = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $treatment = makeTreatment($doctor, $patient);
    $item      = TreatmentItem::factory()->for($treatment)->create();

    $this->actingAs($patient->user)
        ->deleteJson("/api/treatment-items/{$item->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('treatment_items', ['id' => $item->id]);
});

it('stranger cannot delete a treatment item', function () {
    $stranger = User::factory()->create();
    $item     = TreatmentItem::factory()->create();

    $this->actingAs($stranger)
        ->deleteJson("/api/treatment-items/{$item->id}")
        ->assertForbidden();
});

it('deleting nonexistent item returns 404', function () {
    $doctor = User::factory()->create();

    $this->actingAs($doctor->user)
        ->deleteJson('/api/treatment-items/00000000-0000-0000-0000-000000000000')
        ->assertNotFound();
});
