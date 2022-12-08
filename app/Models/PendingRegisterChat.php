<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingRegisterChat extends Model
{
    use HasFactory;

    protected $table = 'pending_register_chat';

    protected $fillable = [
        'chat_id',
    ];
}
