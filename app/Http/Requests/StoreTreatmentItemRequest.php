<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentItemRequest extends FormRequest
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
            'treatment_id' => 'required|exists:treatments,id',
            'sets' => 'nullable|integer|min:1',
            'repetitions' => 'nullable|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:1',
            'frequency_text' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'treatment_id.required' => 'The treatment ID is required.',
            'treatment_id.exists' => 'The specified treatment ID does not exist.',
            'sets.integer' => 'Sets must be an integer.',
            'sets.min' => 'Sets must be at least 1.',
            'repetitions.integer' => 'Repetitions must be an integer.',
            'repetitions.min' => 'Repetitions must be at least 1.',
            'duration_seconds.integer' => 'Duration in seconds must be an integer.',
            'duration_seconds.min' => 'Duration in seconds must be at least 1.',
            'frequency_text.string' => 'Frequency text must be a string.',
            'frequency_text.max' => 'Frequency text may not be greater than 255 characters.',
        ];
    }
}
