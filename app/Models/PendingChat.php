<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'counter',
        'message_id',
    ];

    const NEW_TASK_MESSAGE_COUNTER = 2;
}
