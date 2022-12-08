<?php

namespace App\Helpers;

class Time
{
    /**
     * @param int|string $seconds
     * @return string
     */
    public static function formatTimeInSeconds($seconds): string
    {
        if (is_string($seconds)) {
            return $seconds;
        }

        $hours = $seconds / 3600;

        if ($hours < 1) {
            $minutes = $seconds / 60;

            if ($minutes >= 1) {
                $minutes = intdiv($seconds, 60);
                $seconds = $seconds - $minutes * 60;

                return "$minutes хвилин $seconds секунд";
            } else {
                return "$seconds секунд";
            }

        } else {
            $hours = intdiv($seconds, 3600);
            $seconds = $seconds - $hours * 3600;

            if ($seconds > 60) {
                $minutes = intdiv($seconds, 60);
                $seconds = $seconds - $minutes * 60;

                if ($seconds > 0) {
                    return "$hours годин $minutes хвилин $seconds секунд";
                } else {
                    return "$hours годин $minutes хвилин";
                }

            } else {
                return "$hours годин $seconds секунд";
            }
        }
    }
}
