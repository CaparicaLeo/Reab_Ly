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
            'video'       => 'nullable|file|mimes:mp4,mov,avi,webm,mkv|max:204800',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string'         => 'O título deve ser um texto.',
            'title.max'            => 'O título não pode ter mais de 255 caracteres.',
            'description.string'   => 'A descrição deve ser um texto.',
            'category.string'      => 'A categoria deve ser um texto.',
            'category.max'         => 'A categoria não pode ter mais de 255 caracteres.',
            'video_url.string'     => 'A URL do vídeo deve ser um texto.',
            'video_url.max'        => 'A URL do vídeo não pode ter mais de 255 caracteres.',
            'video.file'           => 'O vídeo deve ser um arquivo.',
            'video.mimes'          => 'O vídeo deve ser do tipo: mp4, mov, avi, webm ou mkv.',
            'video.max'            => 'O vídeo não pode ter mais de 200MB.',
        ];
    }
}
