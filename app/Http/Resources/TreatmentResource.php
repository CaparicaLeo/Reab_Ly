<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'status'     => $this->status,
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
            'doctor_id'  => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'created_at' => $this->created_at,
            'items' => $this->whenLoaded('items', fn() =>
                $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'sets' => $item->sets,
                    'repetitions' => $item->repetitions,
                    'duration_seconds' => $item->duration_seconds,
                    'frequency_text' => $item->frequency_text,
                    'exercise' => $item->relationLoaded('exercise') && $item->exercise ? [
                        'id' => $item->exercise->id,
                        'title' => $item->exercise->title,
                        'category' => $item->exercise->category,
                    ] : null,
                ])
            ),
        ];
    }
}
