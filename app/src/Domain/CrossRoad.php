<?php

namespace App\Domain;

use App\Domain\CrossRoad\PriorityRoad;
use App\Domain\CrossRoad\TrafficLight\TrafficLight;
use App\Foundation\ArrayableInterface;
use App\Graphic\Figure;
use App\Graphic\Square;

class CrossRoad implements ArrayableInterface
{
    private array $roadDirections = ['left', 'right', 'top', 'bottom'];

    private ?TrafficLight $trafficLight = null;

    private ?PriorityRoad $priorityRoad = null;

    private Figure $figure;

    /**
     * // TODO Сделать так, чтобы при проезде перекрестка машина действительно по нему перемещалась (по клеткам)
     * @var Car[][] $cars
     */
    private array $carBuffer = [

    ];


    public function __construct(
        private ?Road $leftRoad = null,
        private ?Road $rightRoad = null,
        private ?Road $topRoad = null,
        private ?Road $bottomRoad = null
    ) {
        $this->leftRoad?->setCrossRoad($this);
        $this->rightRoad?->setCrossRoad($this);
        $this->topRoad?->setCrossRoad($this);
        $this->bottomRoad?->setCrossRoad($this);
    }

    public function run(): void
    {
        /**
         * @var Car[] $allCars
         */
        $allCars = [];
        foreach ($this->getRoads() as $road) {
            foreach ($road->getLanes() as $lane) {
                $allCars = array_merge($allCars, $lane->getCars());
            }
        }

        usort($allCars, function (Car $firstCar, Car $secondCar) {
            return - $firstCar->getPositionOnLane() + $secondCar->getPositionOnLane();
        });

        foreach ($allCars as $car) {
            $car->drive();
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
        return $res;
    }

    public function toArray(): array
    {
        $result = [
            'figure' => $this->getFigure()->toArray(),
            'roads' => []
        ];
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
        $crossRoad->setFigure(Square::fromArray($data['figure']));

        return $crossRoad;
    }

    private function getRoad($direction): ?Road {
        $road = $direction . 'Road';
        return $this->{$road};
    }

    public function getFigure(): Figure
    {
        return $this->figure;
    }

    public function setFigure(Figure $figure): void
    {
        $this->figure = $figure;
    }

    public function getTrafficLight(): ?TrafficLight
    {
        return $this->trafficLight;
    }

    public function setTrafficLight(?TrafficLight $trafficLight = null): void
    {
        $this->trafficLight = $trafficLight;
    }
}