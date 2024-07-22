<?php

namespace App\Factory;

use App\Domain\Lane;
use App\Domain\Road;

class LaneFactory
{
    public static function create(
        Road $road,
        string $roadDirection,
        $options = [],
    ): Lane
    {
        $lane = new Lane($maxSpeed, $reverse);
        $figure = new FigureFactory::create();
        $lane->setFigure();
        $lane->setCoordinate();
    }
}