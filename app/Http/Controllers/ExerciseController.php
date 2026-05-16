<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exercise\StoreRequest;
use App\Http\Requests\Exercise\UpdateRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Exercise::class);

        $doctor  = $request->user()->doctor()->first();
        $patient = $request->user()->patient()->first();

        $exercises = Exercise::fromTreatments(
            $doctor?->id,
            $patient?->id
        )->get();

        return ExerciseResource::collection($exercises);
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Exercise::class);

        $exercise = Exercise::create($request->validated());

        return (new ExerciseResource($exercise))->response()->setStatusCode(201);
    }

    public function show(Exercise $exercise)
    {
        $this->authorize('view', $exercise);

        return (new ExerciseResource($exercise))->response();
    }

    public function update(UpdateRequest $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $exercise->update($request->validated());

        return (new ExerciseResource($exercise))->response();
    }

    public function destroy(Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return response()->json(null, 204);
    }
}
