<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastOffset extends Model
{
    use HasFactory;

    protected $table = 'last_offset';

    protected $fillable = [
        'offset',
    ];
}
