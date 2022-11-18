<?php

namespace App\Models;

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
                return '-1001486227212';
            case self::CATEGORY_MECHANIC:
                return '-1001821311967';
            case self::CATEGORY_ELECTRICITY:
                return '-1001867607313';
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

}
