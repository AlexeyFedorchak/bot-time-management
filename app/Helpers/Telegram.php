<?php

namespace App\Helpers;

use App\Models\Task;

class Telegram
{
    /**
     * @param string $categoryName
     * @return string|null
     */
    public static function getChatIdByCategory(string $categoryName): ?string
    {
        switch ($categoryName) {
            case Task::CATEGORY_ELECTRIC:
                return '-875598677';
            case Task::CATEGORY_MECHANIC:
                return '-897291409';
            case Task::CATEGORY_SANTEHNIK:
                return '-701849861';
            case Task::CATEGORY_CLIMATE:
                return '-878041298';
            case Task::CATEGORY_VENTILATSIYA:
                return '-725944540';
            case Task::CATEGORY_GAS:
                return '-887315359';
            default:
                return null;
        }
    }

    /**
     * @param string $expectedCategory
     * @return string|null
     */
    public static function recognizeCategory(string $expectedCategory): ?string
    {
        foreach (Task::categories() as $category) {
            if (str_starts_with(mb_strtolower($category), mb_strtolower($expectedCategory))) {
                return $category;
            }
        }

        return null;
    }
}
