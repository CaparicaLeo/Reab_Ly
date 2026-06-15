<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasUuids, HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'birth_date',
        'clinical_condition',
        'doctor_id',
        'consentimento_lgpd',
        'consentimento_em',
    ];

    protected function casts(): array
    {
        return [
            'consentimento_lgpd' => 'boolean',
            'consentimento_em' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'patient_id');
    }
}
