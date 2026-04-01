<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::apiResource('addresses', AddressController::class)->except(['index']);
    Route::apiResource('treatments', \App\Http\Controllers\TreatmentController::class);
    Route::apiResource('treatments.items', \App\Http\Controllers\TreatmentItemController::class)
        ->shallow();
    Route::apiResource('treatment-items', \App\Http\Controllers\TreatmentItemController::class)
        ->only(['show', 'update', 'destroy', 'store']);
});

require __DIR__ . '/auth.php';
