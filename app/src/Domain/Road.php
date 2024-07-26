<?php

namespace App\Domain;

use App\Foundation\ArrayableInterface;

class Road implements ArrayableInterface
{
    private bool $priority = false;
    private int $trafficIntensity = 100;
    private Lane $straightLane;
    private Lane $reverseLane;
    private string $direction;
    private CrossRoad $crossRoad;

    public function __construct(
        private int $length,
    ){
    }
    
    public function setCrossRoad(CrossRoad $crossRoad): void
    {
        $this->crossRoad = $crossRoad;
    }

    public function getCrossRoad(): CrossRoad
    {
        return $this->crossRoad;
    }

    public function setStraightLane(Lane $lane): void
    {
        $this->straightLane = $lane;
    }

    public function getStraightLane(): Lane
    {
        return $this->straightLane;
    }

    public function getReverseLane(): Lane
    {
        return $this->reverseLane;
    }

    public function setReverseLane(Lane $lane): void
    {
        $this->reverseLane = $lane;
    }

    public function getPriority(): bool
    {
        return $this->priority;
    }

    public function setPriority(bool $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return Lane[]
     */
    public function getLanes(): array
    {
        return [
            $this->straightLane,
            $this->reverseLane,
        ];
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function toArray(): array
    {
        return [
            'priority' => $this->getPriority(),
            'length' => $this->getLength(),
            'direction' => $this->getDirection(),
            'straight_lane' => $this->straightLane->toArray(),
            'reverse_lane' => $this->reverseLane->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $road = new self($data['length']);
        $road->setPriority($data['priority']);
        $road->setDirection($data['direction']);
        $straightLane = Lane::fromArray($data['straight_lane']);
        $straightLane->setRoad($road);
        $reverseLane = Lane::fromArray($data['reverse_lane']);
        $reverseLane->setRoad($road);
        $road->setStraightLane($straightLane);
        $road->setReverseLane($reverseLane);

        return $road;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }
}