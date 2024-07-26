<?php

namespace App\Domain;

use App\Domain\CrossRoad\CarBuffer;
use App\Domain\CrossRoad\PriorityRoad;
use App\Domain\CrossRoad\TrafficLight\TrafficLight;
use App\Event\CarTurnedIntoLaneEvent;
use App\Foundation\ArrayableInterface;

class CrossRoad implements ArrayableInterface
{
    private const array ROAD_TURN_AVAILABLE_FROM_BUFFER_BY_DIRECTION = [
        'left' => 'bottom',
        'bottom' => 'right',
        'right' => 'top',
        'top' => 'left',
    ];

    private array $roadDirections = ['left', 'right', 'top', 'bottom'];

    private ?TrafficLight $trafficLight = null;

    private ?PriorityRoad $priorityRoad = null;

    private ?array $constraintTurns = [
        'left' => ['right', 'top', 'bottom'],
        'right' => ['left', 'top', 'bottom'],
        'top' => ['left', 'right', 'bottom'],
        'bottom' => ['left', 'right', 'top']
    ];

    /**
     * @var CarBuffer[] $carsBuffer
     */
    private array $carsBuffer = [

    ];


    public function __construct(
        private ?Road $leftRoad = null,
        private ?Road $rightRoad = null,
        private ?Road $topRoad = null,
        private ?Road $bottomRoad = null
    )
    {
        $this->leftRoad?->setCrossRoad($this);
        $this->rightRoad?->setCrossRoad($this);
        $this->topRoad?->setCrossRoad($this);
        $this->bottomRoad?->setCrossRoad($this);

        $this->setCarsBuffer([]);
    }

    public function run(): void
    {
        // Перемещаем по перекрестку уже въехавшие на него машины
        $this->driveCrossRoad();

        /**
         * @var Car[] $allCars
         */
        $allCars = [];
        foreach ($this->getRoads() as $road) {
            foreach ($road->getLanes() as $lane) {
                $allCars = array_merge($allCars, $lane->getCars());
            }
        }

        foreach ($allCars as $car) {
            $car->drive();
        }
    }

    public function canDriveCrossRoad(Car $car): bool {
        if ($this->carsBuffer[$car->getLane()->getDirection()]->getCar() !== null) {
            return false;
        }
        return true;
    }

    public function addCarInBuffer(Car $car): void {
        $this->carsBuffer[$car->getLane()->getDirection()]->setCar($car);
    }

    public function driveCrossRoad(): void {
        $directions = array_reverse(array_keys(self::ROAD_TURN_AVAILABLE_FROM_BUFFER_BY_DIRECTION), true);
        foreach ($directions as $direction) {
            $carBuffer = $this->carsBuffer[$direction];
            $availableRoad = $carBuffer->getAvailableTurnRoad();
            $car = $carBuffer->getCar();
            if ($car !== null) {
                if (
                    $availableRoad->getDirection() === $car->getPlannedManeuver()
                    && $availableRoad->getReverseLane()->canAddCar()
                ) {
                    $car->setPlannedManeuver('front');
                    (new CarTurnedIntoLaneEvent($car, $availableRoad->getReverseLane()))->dispatch();
                    $availableRoad->getReverseLane()->addCar($car);
                    $carBuffer->setCar(null);
                } else if (
                    $carBuffer->getNextCarBuffer()->getCar() === null
                    && $availableRoad->getDirection() !== $car->getPlannedManeuver()
                ) {
                    $carBuffer->getNextCarBuffer()->setCar($car);
                    $carBuffer->setCar(null);
                }
            }
        }
    }

    /**
     * @return Road[]
     */
    public function getRoads(): array {
        $result = [];
        foreach ($this->roadDirections as $roadDirection) {
            $road = $this->getRoad($roadDirection);
            if ($road !== null) {
                $result[$roadDirection] = $road;
            }
        }

        return $result;
    }

    /**
     * @param Road $currentRoad
     * @return Road[]
     */
    public function availableTurns(Road $currentRoad): array {
        $res = [];
        foreach ($this->getRoads() as $road) {
            if (spl_object_hash($road) !== spl_object_hash($currentRoad)) {
                $res[] = $road;
            }
        }

        if ($this->constraintTurns !== null) {
            $constraintTurn = $this->constraintTurns[$currentRoad->getDirection()];
            $res = array_filter($res, fn (Road $road) => in_array($road->getDirection(), $constraintTurn));
        }
        return $res;
    }

    public function toArray(): array
    {
        $result = [
            'traffic_light' => $this->getTrafficLight()?->toArray(),
            'roads' => [],
            'cars_buffer' => [],
        ];

        foreach ($this->carsBuffer as $carBuffer) {
            $result['cars_buffer'][$carBuffer->getDirection()] = $carBuffer->getCar()?->toArray();
        }
        foreach ($this->getRoads() as $direction => $road) {
            $result['roads'][$direction . '_road'] = $road->toArray();
        }

        return $result;
    }

    public static function fromArray(array $data): self
    {
        $roads = [
            Road::fromArray($data['roads']['left_road']),
            Road::fromArray($data['roads']['right_road']),
            Road::fromArray($data['roads']['top_road']),
            Road::fromArray($data['roads']['bottom_road']),
        ];

        $crossRoad = new self(...$roads);
        $crossRoad->setCarsBuffer($data['cars_buffer'] ?? []);
        $crossRoad->setTrafficLight($data['traffic_light'] ? TrafficLight::fromArray($data['traffic_light']) : null);

        return $crossRoad;
    }

    public function getRoad($direction): ?Road {
        $road = $direction . 'Road';
        return $this->{$road};
    }

    public function getTrafficLight(): ?TrafficLight
    {
        return $this->trafficLight;
    }

    public function setTrafficLight(?TrafficLight $trafficLight = null): void
    {
        $this->trafficLight = $trafficLight;
    }

    private function setCarsBuffer($carsByDirection): void
    {
        $firstBufferElement = new CarBuffer(null);
        $bufferElement = $firstBufferElement;
        foreach (self::ROAD_TURN_AVAILABLE_FROM_BUFFER_BY_DIRECTION as $roadDirection) {
            $availableRoad = $this->getRoad(self::ROAD_TURN_AVAILABLE_FROM_BUFFER_BY_DIRECTION[$roadDirection]);
            $bufferElement->setDirection($roadDirection);
            $bufferElement->setAvailableTurnRoad($availableRoad);
            $this->carsBuffer[$roadDirection] = $bufferElement;
            $bufferElement = new CarBuffer(null);

            $this->carsBuffer[$roadDirection]->setNextCarBuffer($bufferElement);
        }
        $lastCarBufferElement = end($this->carsBuffer);
        $lastCarBufferElement->setNextCarBuffer($firstBufferElement);

        foreach ($carsByDirection as $direction => $car) {
            if ($car !== null) {
                $this->carsBuffer[$direction]->setCar(Car::fromArray($car));
            }
        }
    }

    public function setConstraintTurns(?array $constraintTurns): void
    {
        $this->constraintTurns = $constraintTurns;
    }

    public function getConstraintTurns(): ?array
    {
        return $this->constraintTurns;
    }
}