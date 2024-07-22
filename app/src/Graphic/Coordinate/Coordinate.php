<?php

namespace App\Graphic\Coordinate;

use App\Foundation\ArrayableInterface;

class Coordinate implements ArrayableInterface
{

    public function __construct(
        private int $x,
        private int $y
    )
    {

    }



    public function toArray(): array
    {
        return [
            'x' => $this->x, 'y' => $this->y
        ];
    }

    public static function fromArray(array $data): Coordinate
    {
        return new self($data['x'], $data['y']);
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setX(int $x): void
    {
        $this->x = $x;
    }

    public function setY(int $y): void
    {
        $this->y = $y;
    }
}