<?php

namespace App\Http\Requests\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'patient_id' => 'required|uuid|exists:patients,id',
            'doctor_id' => 'sometimes|uuid|exists:doctors,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:ongoing,completed,cancelled',
        ];
    }

    public function messages()
    {
        return [
            'patient_id.required' => 'The patient field is required.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'patient_id.uuid' => 'The patient ID must be a valid UUID.',
            'doctor_id.uuid'  => 'The doctor ID must be a valid UUID.',
            'doctor_id.exists' => 'The selected doctor does not exist.',
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'start_date.required' => 'The start date field is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'status.in' => 'The status must be one of the following: ongoing, completed, cancelled.',
        ];
    }
}
