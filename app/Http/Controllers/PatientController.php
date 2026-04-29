<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PatientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $doctor = $request->user()->doctor;

        $patients = Patient::where('doctor_id', $doctor->id)
            ->with('user')
            ->get();

        return response()->json($patients);
    }
    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
        ]);
        $patient = Patient::create([
            'user_id'            => $user->id,
            'doctor_id'          => $request->user()->doctor->id,
            'birth_date'         => $validated['birth_date'],
            'clinical_condition' => $validated['clinical_condition'] ?? null,
        ]);

        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        $patient->load('user', 'treatments');
        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $validated = $request->validate([
            'birth_date'         => 'sometimes|date',
            'clinical_condition' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }
}
