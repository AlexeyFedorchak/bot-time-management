<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespondedRegisterMessage extends Model
{
    use HasFactory;

    protected $table = 'responded_register_message';

    protected $fillable = [
        'message_id',
        'chat_id',
    ];
}
