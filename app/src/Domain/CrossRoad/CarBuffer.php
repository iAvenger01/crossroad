<?php

namespace App\Domain\CrossRoad;

use App\Domain\Car;
use App\Domain\Road;
use App\Foundation\ArrayableInterface;

class CarBuffer implements ArrayableInterface
{
    private ?Car $car = null;

    private CarBuffer $nextCarBuffer;

    private string $direction;

    private Road $availableTurnRoad;

    public function __construct(?CarBuffer $nextCarBufferElement) {
        if ($nextCarBufferElement !== null) {
            $this->setNextCarBuffer($nextCarBufferElement);
        }
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): void
    {
        $this->car = $car;
    }

    public function getNextCarBuffer(): CarBuffer
    {
        return $this->nextCarBuffer;
    }

    public function setNextCarBuffer(CarBuffer $nextCarBuffer): void
    {
        $this->nextCarBuffer = $nextCarBuffer;
    }

    public function toArray(): array
    {
        return [
            'car' => $this->getCar()?->toArray(),
            'nextCarBuffer' => $this->getNextCarBuffer()->toArray(),
            ''
        ];
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }

    public function getAvailableTurnRoad(): Road
    {
        return $this->availableTurnRoad;
    }

    public function setAvailableTurnRoad(Road $availableTurnRoad): void
    {
        $this->availableTurnRoad = $availableTurnRoad;
    }
}