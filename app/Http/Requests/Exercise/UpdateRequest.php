<?php

namespace App\Http\Requests\Exercise;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:255',
            'video_url'   => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string'         => 'The title must be a string.',
            'title.max'            => 'The title may not be greater than 255 characters.',
            'description.string'   => 'The description must be a string.',
            'category.string'      => 'The category must be a string.',
            'category.max'         => 'The category may not be greater than 255 characters.',
            'video_url.string'     => 'The video URL must be a string.',
            'video_url.max'        => 'The video URL may not be greater than 255 characters.',
        ];
    }
}
