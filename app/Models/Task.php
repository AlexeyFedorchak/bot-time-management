<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'description',
        'creator_id',
        'message_id',
        'is_tracking',
        'photo',
    ];

    const CATEGORY_GIDRAVLIK = 'Гідравліка';
    const CATEGORY_ELECTRICITY = 'Електрика';
    const CATEGORY_MECHANIC = 'Механіка';
    const CATEGORY_GENERAL = 'Загальна';

    /**
     * @return array
     */
    public static function categories(): array
    {
        return (new \ReflectionClass(self::class))->getConstants();
    }

    /**
     * @TODO remove hard code
     *
     * @return string|null
     */
    public function getChatIdByCategory(): ?string
    {
        switch ($this->category) {
            case self::CATEGORY_GIDRAVLIK:
                return '-676109028';
            case self::CATEGORY_MECHANIC:
                return '-875087632';
            case self::CATEGORY_ELECTRICITY:
                return '-882515336';
        }

        return null;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        $lastUpdate = $this->updates->last();
        return $lastUpdate->status;
    }

    /**
     * @return HasMany
     */
    public function updates(): HasMany
    {
        return $this->hasMany(TaskUpdate::class, 'task_id', 'id');
    }

    /**
     * in seconds
     *
     * @return int|null|string
     */
    public function getDuration()
    {
        $inProgress = TaskUpdate::where('task_id', $this->id)
            ->where('status', TaskUpdate::STATUS_IN_PROGRESS)
            ->first();

        $done = TaskUpdate::where('task_id', $this->id)
            ->where('status', TaskUpdate::STATUS_DONE)
            ->first();

        $cancelled = TaskUpdate::where('task_id', $this->id)
            ->where('status', TaskUpdate::STATUS_CANCELLED)
            ->first();

        if ($inProgress) {
            if ($done) {
                return Carbon::parse($inProgress->created_at)->diffInSeconds($done->created_at);
            } else if ($cancelled) {
                return Carbon::parse($inProgress->created_at)->diffInSeconds($cancelled->created_at);
            } else {
                return 'Завдання в процесі виконання';
            }
        } else if ($done) {
            return Carbon::parse($this->created_at)->diffInSeconds($done->created_at);
        } else if ($cancelled) {
            return Carbon::parse($this->created_at)->diffInSeconds($cancelled->created_at);
        }

        return 'Завдання не взяте до виконання';
    }
}
