<?php

namespace App\Http\Controllers;

use App\Http\Requests\Treatment\CreateRequest;
use App\Http\Requests\Treatment\UpdateRequest;
use App\Http\Resources\TreatmentResource;
use App\Models\Treatment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Treatment::class);

        $doctor = $request->user()->doctor;

        $query = Treatment::where('doctor_id', $doctor->id)->with('patient.user');

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('patient.user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->query('end_date'));
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $treatments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return TreatmentResource::collection($treatments);
    }

    public function store(CreateRequest $request)
    {
        $this->authorize('create', Treatment::class);

        $treatment = Treatment::create($request->validated());

        return (new TreatmentResource($treatment))->response()->setStatusCode(201);
    }

    public function show(Treatment $treatment)
    {
        $this->authorize('view', $treatment);

        return (new TreatmentResource($treatment))->response();
    }

    public function update(UpdateRequest $request, Treatment $treatment)
    {
        $this->authorize('update', $treatment);

        $treatment->update($request->validated());

        return (new TreatmentResource($treatment))->response();
    }

    public function destroy(Treatment $treatment)
    {
        $this->authorize('delete', $treatment);

        $treatment->delete();

        return response()->json(null, 204);
    }

    public function myTreatments(Request $request)
    {
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $treatments = Treatment::where('patient_id', $patient->id)
            ->with('items.exercise')
            ->orderBy('start_date', 'desc')
            ->get();

        return TreatmentResource::collection($treatments);
    }
}
