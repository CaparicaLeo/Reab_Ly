<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasUuids;
    protected $fillable = [
        'birth_date',
        'clinical_condition'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
