<?php

namespace App\Factory;

use App\Domain\Lane;
use App\Domain\Road;
use App\Graphic\Coordinate\Coordinate;
use App\Service\Orientator;

class CoordinateFactory
{
    public static function create(
        int $length,
        string $direction,
        int $offsetX = 0,
        int $offsetY = 0
    ): Coordinate
    {
        list($offsetX, $offsetY) = match ($direction) {
            'left', => [0, $offsetY],
            'top' => [$offsetY, 0],
            'right' => [$offsetX, $offsetY],
            'bottom' => [$offsetY, $offsetX],
        };

        return match ($direction) {
            'left' => new Coordinate(0 + $offsetX, $length + $offsetY),
            'right' => new Coordinate($length + $offsetX, $length + $offsetY),
            'top' => new Coordinate($length + $offsetX, 0 + $offsetY),
            'bottom' => new Coordinate($length + $offsetX, $length + $offsetY)
        };
    }

    public static function createLaneCoordinate(
        Road $road,
        bool $reverse,
        int $widthLane = 14,
        int $crossRoadLength = 36
    ): Coordinate {
        list($startX, $startY) = match ($road->getDirection()) {
            'left', => [0, $road->getLength() + 2],
            'right' => [$road->getLength() + $crossRoadLength, $road->getLength() + 2],
            'top' => [$road->getLength() + 2, 0],
            'bottom' => [$road->getLength() + 2, $road->getLength() + $crossRoadLength],
        };
        $x = $startX;
        $y = $startY;
        list($reverseOffsetX, $reverseOffsetY) = match ($road->getDirection()) {
            'left' => [0, $reverse ? $widthLane + 4 : 0],
            'right' => [0, $reverse ? 0 : $widthLane + 4],
            'top' => [$reverse ? 0 : $widthLane + 4, 0],
            'bottom' => [$reverse ? $widthLane + 4 : 0, 0],
        };
        $x += $reverseOffsetX;
        $y += $reverseOffsetY;
        return match ($road->getDirection()) {
            'left', 'right' => new Coordinate($x, $y),
            'top', 'bottom' => new Coordinate($x, $y),
        };
    }

    public static function createRoadCoordinate(int $length, string $direction, int $offset = 0): Coordinate
    {
        list($offsetX, $offsetY) = match ($direction) {
            'left', 'top', => [0, 0],
            'right' => [$offset, 0],
            'bottom' => [0, $offset],
        };

        return match ($direction) {
            'left' => new Coordinate(0 + $offsetX, $length + $offsetY),
            'right' => new Coordinate($length + $offsetX, $length + $offsetY),
            'top' => new Coordinate($length + $offsetX, 0 + $offsetY),
            'bottom' => new Coordinate($length + $offsetX, $length + $offsetY),
        };
    }

    public static function createCarCoordinate(Lane $lane, $length): Coordinate
    {
        $coordinate = clone $lane->getFigure()->getCoordinate();
        if ([Orientator::DIRECTION_BOTTOM, Orientator::DIRECTION_RIGHT]) {
            $editCoordinate = $lane->getOrientation() === Orientator::ORIENTATION_VERTICAL ? 'setY' : 'setX';
            $coordinate->{$editCoordinate}($lane->getLength() * 2 + 36 - $length);
        }

        return $coordinate;
    }
}