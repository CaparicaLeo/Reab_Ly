<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionFeedbackRequest extends FormRequest
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
            'patient_id' => 'sometimes|exists:patients,id',
            'treatment_plan_id' => 'sometimes|exists:treatment_items,id',
            'pain_level' => 'sometimes|integer|min:0|max:10',
            'mobility_level' => 'sometimes|integer|min:0|max:10',
            'feedback_text' => 'nullable|string',
            'performed_at' => 'sometimes|date',
        ];
    }
}
