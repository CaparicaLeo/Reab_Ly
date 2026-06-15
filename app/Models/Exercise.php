<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Exercise extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'category',
        'video_url',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Exercise $exercise) {
            if ($exercise->video_url && Storage::disk('s3')->exists($exercise->video_url)) {
                Storage::disk('s3')->delete($exercise->video_url);
            }
        });
    }

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
