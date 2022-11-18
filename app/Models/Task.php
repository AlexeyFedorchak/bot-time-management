<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'description',
        'creator_id',
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
}
