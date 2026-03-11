<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::guard('web')->user();

        Log::info('User after authenticate:', ['user' => $user]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Token created:', ['token' => $token]);

        return response()->json(['token' => $token]);
    }

    public function destroy(Request $request): JsonResponse
    {
        Log::info('Destroy called', [
            'user' => $request->user() ? $request->user()->toArray() : null,
            'token' => $request->bearerToken(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
