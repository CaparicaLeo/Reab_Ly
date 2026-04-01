<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request){
        $user = User::auth()->user();
        $exercises = $user->treatment->treatmentItems()->with('exercise')->get()->pluck('exercise');
        return response()->json($exercises);
    }
     /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExerciseRequest $request)
    {
        $exercise = Exercise::create($request->validated());
        return response()->json($exercise, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Exercise $exercise)
    {
        $exercise = Exercise::find($exercise->id);
        if (!$exercise) {
            return response()->json(['message' => 'Exercise not found'], 404);
            }
        return response()->json($exercise);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        $exercise->update($request->validated());
        return response()->json($exercise);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        $exercise->delete();
        return response()->json(['message' => 'Exercise deleted']);
    }
}
