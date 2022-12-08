<?php

namespace App\Helpers;

class Strings
{
    /**
     * @param string $str
     * @param int $length
     * @return string
     */
    public static function limit(string $str, int $length = 32): string
    {
        if (strlen($str) < $length) {
            return $str;
        } else {
            return mb_substr($str, 0, $length);
        }
    }

}
