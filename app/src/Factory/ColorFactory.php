<?php

namespace App\Factory;

class ColorFactory
{
    private const array COLORS = [
        '#DA70D6',
        '#800080',
        '#0000FF',
        '#FF00FF',
        '#FF0000',
        '#FFFF00',
        '#00FF00',
        '#00FFFF'
    ];

    public static function create(): string
    {
        return self::COLORS[array_rand(self::COLORS)];
    }
}