<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'user_id',
        'birth_date',
        'clinical_condition',
        'doctor_id'
    ];

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
