<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiarySession extends Model
{
    /** @use HasFactory<\Database\Factories\DiarySessionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'treatment_item_id',
        'session_date',
        'completed',
        'pain_level',
        'fatigue_level',
        'difficulty_level',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date:Y-m-d',
            'completed' => 'boolean',
            'pain_level' => 'integer',
            'fatigue_level' => 'integer',
            'difficulty_level' => 'integer',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatmentItem()
    {
        return $this->belongsTo(TreatmentItem::class);
    }
}
