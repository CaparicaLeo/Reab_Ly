<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'crefito',
        'specialty'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
