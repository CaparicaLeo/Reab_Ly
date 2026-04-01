<?php

namespace App\Http\Requests\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'patient_id' => 'sometimes|uuid|exists:patients,id',
            'doctor_id'  => 'sometimes|uuid|exists:doctors,id',
            'title'      => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date|after_or_equal:start_date',
            'status'     => 'sometimes|in:ongoing,completed,cancelled',
        ];
    }
    public function messages(): array
    {
        return [
            'patient_id.uuid'         => 'The patient ID must be a valid UUID.',
            'patient_id.exists'       => 'The selected patient does not exist.',
            'doctor_id.uuid'          => 'The doctor ID must be a valid UUID.',
            'doctor_id.exists'        => 'The selected doctor does not exist.',
            'title.string'            => 'The title must be a string.',
            'title.max'               => 'The title may not be greater than 255 characters.',
            'start_date.date'         => 'The start date must be a valid date.',
            'end_date.date'           => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'status.in'               => 'The status must be one of: ongoing, completed, cancelled.',
        ];
    }
}
