<?php

namespace App\Domain;

use App\Foundation\ArrayableInterface;
use App\Graphic\Figure;
use App\Graphic\Rectangle;

class Lane implements ArrayableInterface
{
    private Road $road;

    /**
     * @var Car[]
     */
    private array $cars = [];
    private Figure $figure;

    private string $orientation;
    private string $direction;

    public function __construct(
        private int $length,
        private int $maxSpeed = 60,
        private readonly bool $reverse = false
    )
    {
    }

    public function addCar(Car $car, int $position): void
    {
        /**
         * @var Car|false $lastCar
         */
        if ($this->canAddCar()) {
            $car->setSpeed(min($car->getMaxSpeed(), $this->getMaxSpeed()));
            $car->setMaxSpeed(min($car->getMaxSpeed(), $this->getMaxSpeed()));
            $car->setLane($this);
            $car->setPositionOnLane($position);
            $this->cars[spl_object_hash($car)] = $car;

            usort($this->cars, function (Car $firstCar, Car $secondCar) {
                return - $firstCar->getPositionOnLane() + $secondCar->getPositionOnLane();
            });
        }
    }

    private function canAddCar(): bool {
        $lastCar = end($this->cars);
        return $lastCar === false || $lastCar->getPositionOnLane() > $lastCar->getLength();
    }

    public function removeCar(Car $car): void {
        unset($this->cars[spl_object_hash($car)]);
    }

    public function getCarInFront(Car $myCar): ?Car
    {
        $myCarPosition = $myCar->getPositionOnLane();
        $carInFront = null;

        foreach ($this->cars as $car) {
            $carPosition = $car->getPositionOnLane();

            if (
                $carPosition > $myCarPosition &&
                ($carInFront === null || $carPosition < $carInFront->getPositionOnLane())
            ) {
                $carInFront = $car;
            }
        }
        return $carInFront;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return Car[]
     */
    public function getCars(): array {
        return $this->cars;
    }

    /**
     * @param Road $road
     * @return void
     */
    public function setRoad(Road $road): void
    {
        $this->road = $road;
    }

    public function getRoad(): Road
    {
        return $this->road;
    }

    public function setFigure(Figure $figure): void
    {
        $this->figure = $figure;
    }

    public function getFigure(): Figure
    {
        return $this->figure;
    }

    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'max_speed' => $this->getMaxSpeed(),
            'cars' => array_map(fn (Car $car) => $car->toArray(), $this->cars),
            'reverse' => $this->reverse,
            'figure' => $this->figure->toArray(),
            'orientation' => $this->orientation,
            'direction' => $this->direction
        ];
    }

    public static function fromArray(array $data): Lane
    {
        $lane = new self($data['length'], $data['max_speed'], $data['reverse']);
        $lane->setOrientation($data['orientation']);
        $lane->setDirection($data['direction']);
        $lane->setFigure(Rectangle::fromArray($data['figure']));
        foreach ($data['cars'] as $carData) {
            $car = Car::fromArray($carData);
            $car->setLane($lane);
            $lane->addCar($car, $carData['position_on_lane']);
        }

        return $lane;
    }

    public function getMaxSpeed(): int
    {
        return $this->maxSpeed;
    }
    public function setMaxSpeed(int $maxSpeed): void
    {
        $this->maxSpeed = $maxSpeed;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation): void
    {
        $this->orientation = $orientation;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }

    public function getReverse(): bool
    {
        return $this->reverse;
    }
}