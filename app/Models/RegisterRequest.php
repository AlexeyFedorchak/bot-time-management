<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @TODO rename into workers or make it middle request table.
 * This table represents workers | not users, but workers.
 */
class RegisterRequest extends Model
{
    use HasFactory;

    protected $table = 'register_requests';

    protected $fillable = [
        'chat_id',
        'telegram_first_name',
        'telegram_last_name',
        'name_pib',
        'category',
    ];
}
