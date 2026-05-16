<?php

use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});

test('users can logout', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Logout realizado com sucesso.']);
});
