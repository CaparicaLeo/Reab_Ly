<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'birth_date',
        'clinical_condition'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
