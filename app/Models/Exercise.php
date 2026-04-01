<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasUuids;
    protected $fillable = [
        'id',
        'title',
        'description',
        'video_url',
        'category',
    ];

    
}
