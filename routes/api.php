<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user()->load('doctor', 'patient'));
    Route::apiResource('addresses', AddressController::class);
    Route::apiResource('treatments', \App\Http\Controllers\TreatmentController::class);
    Route::apiResource('treatments.items', \App\Http\Controllers\TreatmentItemController::class)
        ->shallow();
    Route::apiResource('treatment-items', \App\Http\Controllers\TreatmentItemController::class)
        ->only(['show', 'update', 'destroy', 'store']);
    Route::apiResource('exercises', \App\Http\Controllers\ExerciseController::class);
    Route::apiResource('patients', \App\Http\Controllers\PatientController::class);
    Route::get('patients/{patient}/treatments', [\App\Http\Controllers\PatientController::class, 'treatments']);
    Route::get('patients/{patient}/report', [\App\Http\Controllers\ReportController::class, 'generate']);
    Route::patch('patients/{patient}/toggle-active', [\App\Http\Controllers\PatientController::class, 'toggleActive']);

    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts']);
    Route::get('/my/treatments', [\App\Http\Controllers\TreatmentController::class, 'myTreatments']);

    Route::get('/consent', [\App\Http\Controllers\ConsentController::class, 'show']);
    Route::post('/consent', [\App\Http\Controllers\ConsentController::class, 'store']);

    Route::middleware('consent')->group(function () {
        Route::get('/diary/stats', [\App\Http\Controllers\DiarySessionController::class, 'stats']);
        Route::apiResource('diary', \App\Http\Controllers\DiarySessionController::class)
            ->parameters(['diary' => 'diarySession'])
            ->only(['index', 'store', 'show']);
    });
});

require __DIR__ . '/auth.php';
