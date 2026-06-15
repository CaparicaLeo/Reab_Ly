<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiarySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'treatment_item_id' => 'required|exists:treatment_items,id',
            'session_date' => 'required|date',
            'pain_level' => 'nullable|integer|min:1|max:5',
            'fatigue_level' => 'nullable|integer|min:1|max:5',
            'difficulty_level' => 'nullable|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'treatment_item_id.required' => 'O ID do item do tratamento é obrigatório.',
            'treatment_item_id.exists' => 'O item do tratamento informado não existe.',
            'session_date.required' => 'A data da sessão é obrigatória.',
            'session_date.date' => 'A data da sessão deve ser uma data válida.',
            'pain_level.integer' => 'O nível de dor deve ser um número inteiro.',
            'pain_level.min' => 'O nível de dor deve ser no mínimo 1.',
            'pain_level.max' => 'O nível de dor deve ser no máximo 5.',
            'fatigue_level.integer' => 'O nível de fadiga deve ser um número inteiro.',
            'fatigue_level.min' => 'O nível de fadiga deve ser no mínimo 1.',
            'fatigue_level.max' => 'O nível de fadiga deve ser no máximo 5.',
            'difficulty_level.integer' => 'O nível de dificuldade deve ser um número inteiro.',
            'difficulty_level.min' => 'O nível de dificuldade deve ser no mínimo 1.',
            'difficulty_level.max' => 'O nível de dificuldade deve ser no máximo 5.',
        ];
    }
}
