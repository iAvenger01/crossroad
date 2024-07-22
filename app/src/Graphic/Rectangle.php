<?php

namespace App\Graphic;

use App\Graphic\Coordinate\Coordinate;

class Rectangle implements Figure
{
    private Coordinate $coordinate;
    private string $orientation;
    private ?string $color = null;

    public function __construct(
        private int $length,
        private int $width
    ){
    }

    public function setCoordinate(Coordinate $coordinate): void
    {
        $this->coordinate = $coordinate;
    }

    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'orientation' => $this->getOrientation(),
            'coordinate' => $this->getCoordinate()->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $object = new self($data['length'], $data['width']);
        $object->setOrientation($data['orientation']);
        $object->setCoordinate(Coordinate::fromArray($data['coordinate']));

        return $object;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation): void
    {
        $this->orientation = $orientation;
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }
}