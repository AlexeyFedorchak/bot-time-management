<?php

namespace App\Models;

use App\Helpers\Telegram;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    const CATEGORY_ELECTRIC = 'Електрика';
    const CATEGORY_MECHANIC = 'Механіка';
    const CATEGORY_SANTEHNIK = 'Сантехніка';
    const CATEGORY_CLIMATE = 'Клімат';
    const CATEGORY_VENTILATSIYA = 'Вентиляція';
    const CATEGORY_GAS = 'Газ';
    const CATEGORY_GENERAL = 'Загальна';

    /**
     * @return array
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_ELECTRIC,
            self::CATEGORY_MECHANIC,
            self::CATEGORY_SANTEHNIK,
            self::CATEGORY_CLIMATE,
            self::CATEGORY_VENTILATSIYA,
            self::CATEGORY_GAS,
        ];
    }

    /**
     * @TODO remove hard code
     *
     * @return string|null
     */
    public function getChatIdByCategory(): ?string
    {
        return Telegram::getChatIdByCategory($this->category);
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

    /**
     * @return HasOne
     */
    public function author(): HasOne
    {
        return $this->hasOne(RegisterRequest::class, 'chat_id', 'creator_id');
    }

    /**
     * @param string $category
     * @param array $message
     * @return void
     */
    public function cancelAndCreateNew(string $category, array $message)
    {
        TaskUpdate::create([
            'task_id' => $this->id,
            'status' => TaskUpdate::STATUS_CANCELLED,
            'executor_id' => $message['from']['id'],
            'reason' => 'Завдання перенесено в інший чат: ' . $category,
        ]);

        $this->is_tracking = false;
        $this->save();

        $task = self::create([
            'category' => $category,
            'description' => $this->description,
            'photo' => $this->photo,
            'creator_id' => $this->creator_id,
            'message_id' => null,
            'is_tracking' => true,
        ]);

        TaskUpdate::create([
            'executor_id' => $this->creator_id,
            'task_id' => $task->id,
        ]);

        $message = app('Telegram')->getClient()->sendMessage([
            'chat_id' => Telegram::getChatIdByCategory($category),
            'text' => "Завдання створено.\r\nКатегорія: {$task->category}.\r\nОпис: {$task->description}.",
        ]);

        $task->message_id = $message->toArray()['message_id'];
        $task->save();
    }

    public function getPhotoLink()
    {
        $response = app('Telegram')->getFile($this);

    }
}
