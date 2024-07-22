<?php

namespace App\Graphic;

use App\Factory\ColorFactory;
use App\Graphic\Coordinate\Coordinate;

class Square implements Figure
{
    private ?string $color;
    private Coordinate $coordinate;

    public function __construct(
        private int $length
    )
    {
    }

    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->length,
            'color' => $this->color,
            'coordinate' => $this->getCoordinate()->toArray()
        ];
    }

    public static function fromArray(array $data): self
    {
        $object = new self($data['length']);
        $object->setCoordinate(Coordinate::fromArray($data['coordinate']));
        $object->setColor($data['color']);

        return $object;
    }

    public function setCoordinate(Coordinate $coordinate): void
    {
        $this->coordinate = $coordinate;
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }
}