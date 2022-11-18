<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUpdate extends Model
{
    use HasFactory;

    const STATUS_OPEN = 'Відкрите';
    const STATUS_IN_PROGRESS = 'В процесі';
    const STATUS_CANCELLED = 'Скасовано';
    const STATUS_DONE = 'Закінчено';

    const REPLY_IN_PROGRESS = '+';
    const REPLY_CANCELLED = '-';
    const REPLY_DONE = '++';

    protected $fillable = [
        'status',
        'executor_id',
        'task_id',
    ];
}
