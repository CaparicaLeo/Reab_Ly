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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Treatment::class);

        $doctor = $request->user()->doctor;

        $treatments = Treatment::where('doctor_id', $doctor->id)->get();

        return TreatmentResource::collection($treatments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $this->authorize('create', Treatment::class);

        $treatment = Treatment::create($request->validated());

        return (new TreatmentResource($treatment))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Treatment $treatment)
    {
        $this->authorize('view', $treatment);

        return (new TreatmentResource($treatment))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Treatment $treatment)
    {
        $this->authorize('update', $treatment);

        $treatment->update($request->validated());

        return (new TreatmentResource($treatment))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Treatment $treatment)
    {
        $this->authorize('delete', $treatment);

        $treatment->delete();

        return response()->json(null, 204);
    }
}
