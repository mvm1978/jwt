<?php

namespace App\Helpers;

class Helpers
{

    static function getDefault(&$value, $default=FALSE)
    {
        return isset($value) ? $value : $default;
    }

}