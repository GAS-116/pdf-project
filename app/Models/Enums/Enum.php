<?php

namespace App\Models\Enums;

use ReflectionClass;

class Enum
{
    /**
     * @return array
     */
    public static function list()
    {
        $currentClass = new ReflectionClass(static::class);

        return $currentClass->getConstants();
    }
}
