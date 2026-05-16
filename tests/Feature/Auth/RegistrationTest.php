<?php

test('new users can register', function () {
    $response = $this->postJson('/api/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'phone_number'          => '(11) 99999-9999',
        'role'                  => 'doctor',
        'crefito'               => 'CREFITO-12345-F',
        'specialty'             => 'Ortopedia',
    ]);

    $response->assertNoContent();
});
