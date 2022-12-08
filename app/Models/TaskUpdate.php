<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaskUpdate extends Model
{
    use HasFactory;

    const STATUS_OPEN = 'Створено';
    const STATUS_IN_PROGRESS = 'В процесі';
    const STATUS_CANCELLED = 'Скасовано';
    const STATUS_DONE = 'Закінчено';

    const REPLY_IN_PROGRESS = '+';
    const REPLY_CANCELLED = '-';
    const REPLY_DONE = '++';
    const REPLY_CHANGE_CATEGORY = '*';

    protected $fillable = [
        'status',
        'executor_id',
        'task_id',
        'reason'
    ];

    /**
     * @return HasOne
     */
    public function executor(): HasOne
    {
        return $this->hasOne(RegisterRequest::class, 'chat_id', 'executor_id');
    }
}
