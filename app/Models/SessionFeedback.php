<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Patient;
use App\Models\TreatmentItem;
use Illuminate\Database\Eloquent\Model;

class SessionFeedback extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id',
        'treatment_plan_id',
        'pain_level',
        'mobility_level',
        'feedback_text',
        'performed_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function treatmentPlan()
    {
        return $this->belongsTo(TreatmentItem::class);
    }
}
