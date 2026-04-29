<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'           => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number'       => ['nullable', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'clinical_condition' => ['nullable', 'string'],
            'doctor_id' => ['nullable', 'uuid', 'exists:doctors,id'],
        ];
    }
}
