<?php

namespace App\Factory;

use App\Graphic\Figure;
use App\Graphic\Rectangle;

class FigureFactory
{
    public static function createRectangle(
        int $width, int $height, string $orientation
    ): Figure
    {
        $figure = new Rectangle($width, $height);
        $figure->setOrientation($orientation);

        return $figure;
    }
}