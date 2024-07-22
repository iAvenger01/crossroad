<?php

namespace App\Service;

class Orientator
{
    public const string ORIENTATION_HORIZONTAL = 'horizontal';
    public const string ORIENTATION_VERTICAL = 'vertical';

    public const string DIRECTION_LEFT = 'left';
    public const string DIRECTION_RIGHT = 'right';
    public const string DIRECTION_TOP = 'top';
    public const string DIRECTION_BOTTOM = 'bottom';


    public static function getOrientationByDirection($direction): string
    {
        return match ($direction) {
            self::DIRECTION_LEFT, self::DIRECTION_RIGHT => self::ORIENTATION_HORIZONTAL,
            self::DIRECTION_TOP, self::DIRECTION_BOTTOM => self::ORIENTATION_VERTICAL,
        };
    }
}