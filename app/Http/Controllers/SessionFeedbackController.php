<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionFeedbackRequest;
use App\Http\Requests\UpdateSessionFeedbackRequest;
use App\Models\SessionFeedback;
use Illuminate\Support\Facades\Auth;

class SessionFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $sessions = $user->sessionFeedbacks;
        return response()->json($sessions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSessionFeedbackRequest $request)
    {
        $validated = $request->validated();
        $sessionFeedback = SessionFeedback::create($validated);
        return response()->json($sessionFeedback, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SessionFeedback $sessionFeedback)
    {
        return response()->json($sessionFeedback);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSessionFeedbackRequest $request, SessionFeedback $sessionFeedback)
    {
        $validated = $request->validated();
        $sessionFeedback->update($validated);
        return response()->json($sessionFeedback);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SessionFeedback $sessionFeedback)
    {
        $sessionFeedback->delete();
        return response()->json(null, 204);
    }
}
