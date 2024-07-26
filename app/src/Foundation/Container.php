<?php

namespace App\Foundation;

class Container
{
    private static array $instances = [];
    public static function get(string $class): mixed {
        return static::$instances[$class];
    }

    public static function set(string $key, string $class, $args): void
    {
        static::$instances[$key] = new $class(...$args);
    }
}