<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'           => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number'       => ['nullable', 'string', 'max:20'],
            'role'               => ['required', Rule::in(['admin', 'patient', 'doctor'])],
            'crefito'            => ['sometimes', Rule::requiredIf($this->role === 'doctor'), 'string', 'max:255'],
            'specialty'          => ['sometimes', Rule::requiredIf($this->role === 'doctor'), 'string', 'max:255'],
            'birth_date'         => ['sometimes', Rule::requiredIf($this->role === 'patient'), 'date'],
            'clinical_condition' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in'                => 'O tipo de usuário é inválido.',
            'crefito.required_if'    => 'O CREFITO é obrigatório para fisioterapeutas.',
            'specialty.required_if'  => 'A especialidade é obrigatória para fisioterapeutas.',
            'birth_date.required_if' => 'A data de nascimento é obrigatória para pacientes.',
        ];
    }
}