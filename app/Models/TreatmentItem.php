<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentItem extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'treatment_id',
        'exercise_id',
        'sets',
        'repetitions',
        'duration_seconds',
        'frequency_text',
    ];
    protected $casts = [
        'id' => 'string',
        'treatment_id' => 'string',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
