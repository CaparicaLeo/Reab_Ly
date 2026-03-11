<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request, RegisterAction $action): Response
    {
        $user = $action->execute($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
