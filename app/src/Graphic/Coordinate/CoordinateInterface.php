<?php

namespace App\Graphic\Coordinate;

interface CoordinateInterface
{
    public function setCoordinate(Coordinate $coordinate): void;

    public function getCoordinate(): Coordinate;
}