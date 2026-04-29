<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasUuids, HasFactory;
    
    protected $fillable = [
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
}
