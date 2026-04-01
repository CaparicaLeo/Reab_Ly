<?php

use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\User;
use App\Policies\TreatmentItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

function makeContext(): array
{
    $doctor    = User::factory()->create();
    $patient   = User::factory()->create();
    $stranger  = User::factory()->create();
    $treatment = Treatment::factory()->create([
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
    ]);
    $item = TreatmentItem::factory()->for($treatment)->create();

    return compact('doctor', 'patient', 'stranger', 'treatment', 'item');
}

$policy = new TreatmentItemPolicy();

// =============================================================================
// viewAny
// =============================================================================

it('viewAny allows doctor', function () {
    ['doctor' => $doctor, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->viewAny($doctor, $treatment))->toBeTrue();
});

it('viewAny allows patient', function () {
    ['patient' => $patient, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->viewAny($patient, $treatment))->toBeTrue();
});

it('viewAny denies stranger', function () {
    ['stranger' => $stranger, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->viewAny($stranger, $treatment))->toBeFalse();
});

// =============================================================================
// view
// =============================================================================

it('view allows doctor', function () {
    ['doctor' => $doctor, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->view($doctor, $item))->toBeTrue();
});

it('view allows patient', function () {
    ['patient' => $patient, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->view($patient, $item))->toBeTrue();
});

it('view denies stranger', function () {
    ['stranger' => $stranger, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->view($stranger, $item))->toBeFalse();
});

// =============================================================================
// create
// =============================================================================

it('create allows doctor', function () {
    ['doctor' => $doctor, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->create($doctor, $treatment))->toBeTrue();
});

it('create denies patient', function () {
    ['patient' => $patient, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->create($patient, $treatment))->toBeFalse();
});

it('create denies stranger', function () {
    ['stranger' => $stranger, 'treatment' => $treatment] = makeContext();
    expect((new TreatmentItemPolicy())->create($stranger, $treatment))->toBeFalse();
});

// =============================================================================
// update
// =============================================================================

it('update allows doctor', function () {
    ['doctor' => $doctor, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->update($doctor, $item))->toBeTrue();
});

it('update denies patient', function () {
    ['patient' => $patient, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->update($patient, $item))->toBeFalse();
});

it('update denies stranger', function () {
    ['stranger' => $stranger, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->update($stranger, $item))->toBeFalse();
});

// =============================================================================
// delete
// =============================================================================

it('delete allows doctor', function () {
    ['doctor' => $doctor, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->delete($doctor, $item))->toBeTrue();
});

it('delete denies patient', function () {
    ['patient' => $patient, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->delete($patient, $item))->toBeFalse();
});

it('delete denies stranger', function () {
    ['stranger' => $stranger, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->delete($stranger, $item))->toBeFalse();
});

// =============================================================================
// restore / forceDelete — always false
// =============================================================================

it('restore always returns false', function () {
    ['doctor' => $doctor, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->restore($doctor, $item))->toBeFalse();
});

it('forceDelete always returns false', function () {
    ['doctor' => $doctor, 'item' => $item] = makeContext();
    expect((new TreatmentItemPolicy())->forceDelete($doctor, $item))->toBeFalse();
});