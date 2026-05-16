<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'category',
        'video_url',
    ];

    public function treatmentItems()
    {
        return $this->hasMany(TreatmentItem::class);
    }

    public function scopeFromTreatments($query, $doctorId = null, $patientId = null)
    {
        $query->whereHas('treatmentItems.treatment', function ($q) use ($doctorId, $patientId) {
            if ($doctorId) {
                $q->where('doctor_id', $doctorId);
            }
            if ($patientId) {
                $q->where('patient_id', $patientId);
            }
        });
    }
}
