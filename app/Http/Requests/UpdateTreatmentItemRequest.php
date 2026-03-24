<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentItemRequest extends FormRequest
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
            'sets' => 'nullable|integer|min:1',
            'repetitions' => 'nullable|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:1',
            'frequency_text' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sets.integer' => 'The sets field must be an integer.',
            'repetitions.integer' => 'The repetitions field must be an integer.',
            'duration_seconds.integer' => 'The duration_seconds field must be an integer.',
            'frequency_text.string' => 'The frequency_text field must be a string.',
        ];
    }
}
