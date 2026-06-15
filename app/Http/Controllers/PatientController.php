<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\User;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $doctor = $request->user()->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'Acesso negado. Apenas médicos podem acessar.'], 403);
        }

        $query = Patient::where('doctor_id', $doctor->id)->with('user');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('clinical_condition')) {
            $query->where('clinical_condition', 'like', '%' . $request->query('clinical_condition') . '%');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $patients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $patients->getCollection()->transform(function ($patient) {
            $patient->email = $patient->user?->email;
            $patient->phone_number = $patient->user?->phone_number;
            return $patient;
        });

        return response()->json($patients);
    }

    public function store(StorePatientRequest $request)
    {
        $doctor = $request->user()->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'Acesso negado. Apenas médicos podem criar pacientes.'], 403);
        }

        try {
            $validated = $request->validated();

            \Log::info('Creating patient with data:', $validated);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'] ?? '(00) 00000-0000',
            ]);

            \Log::info('User created:', ['user_id' => $user->id]);

            $patient = Patient::create([
                'user_id'            => $user->id,
                'doctor_id'          => $doctor->id,
                'birth_date'         => $validated['birth_date'],
                'clinical_condition' => $validated['clinical_condition'] ?? null,
            ]);

            \Log::info('Patient created:', ['patient_id' => $patient->id]);

            $patient->load('user');
            return response()->json($patient, 201);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar paciente: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Patient $patient)
    {
        try {
            $doctor = request()->user()->doctor;

            if (!$doctor || $patient->doctor_id !== $doctor->id) {
                return response()->json(['message' => 'Acesso negado'], 403);
            }

            $patient->load('user', 'treatments');

            return response()->json([
                'id' => $patient->id,
                'user_id' => $patient->user_id,
                'doctor_id' => $patient->doctor_id,
                'birth_date' => $patient->birth_date,
                'clinical_condition' => $patient->clinical_condition,
                'active' => $patient->active,
                'email' => $patient->user?->email,
                'phone_number' => $patient->user?->phone_number,
                'user' => $patient->user,
                'treatments' => $patient->treatments,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro show patient: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json(['message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function treatments(Patient $patient)
    {
        $doctor = request()->user()->doctor;

        if (!$doctor || $patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $treatments = Treatment::where('patient_id', $patient->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($treatments);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $doctor = request()->user()->doctor;

        if (!$doctor || $patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $validated = $request->validate([
            'birth_date'         => 'sometimes|date',
            'clinical_condition' => 'nullable|string',
        ]);

        $patient->update($validated);
        $patient->load('user');

        return response()->json($patient);
    }

    public function toggleActive(Patient $patient)
    {
        $doctor = request()->user()->doctor;

        if (!$doctor || $patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $patient->update(['active' => !$patient->active]);

        return response()->json([
            'message' => $patient->active ? 'Paciente ativado.' : 'Paciente inativado.',
            'active' => $patient->active,
        ]);
    }

    public function destroy(Patient $patient)
    {
        $doctor = request()->user()->doctor;

        if (!$doctor || $patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $patient->delete();
        return response()->json(null, 204);
    }

    public function destroySelf(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Apenas pacientes podem excluir a própria conta.'], 403);
        }

        $patient->delete();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Conta excluída com sucesso.']);
    }
}
