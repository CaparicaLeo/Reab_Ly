<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exercise\StoreRequest;
use App\Http\Requests\Exercise\UpdateRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Exercise::class);

        $doctor  = $request->user()->doctor()->first();
        $patient = $request->user()->patient()->first();

        $perPage = min((int) $request->query('per_page', 30), 100);

        if ($doctor) {
            $exercises = Exercise::orderBy('created_at', 'desc')->paginate($perPage);
        } elseif ($patient) {
            $exercises = Exercise::fromTreatments(null, $patient->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return ExerciseResource::collection($exercises);
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Exercise::class);

        $data = $request->validated();

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('exercises', 's3');
            $data['video_url'] = Storage::disk('s3')->url($path);
        }

        $exercise = Exercise::create($data);

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

        $data = $request->validated();

        if ($request->hasFile('video')) {
            if ($exercise->video_url && Storage::disk('s3')->exists($exercise->video_url)) {
                Storage::disk('s3')->delete($exercise->video_url);
            }
            $path = $request->file('video')->store('exercises', 's3');
            $data['video_url'] = Storage::disk('s3')->url($path);
        }

        $exercise->update($data);

        return (new ExerciseResource($exercise))->response();
    }

    public function destroy(Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return response()->json(null, 204);
    }
}
