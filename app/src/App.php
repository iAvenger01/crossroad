<?php

namespace App;

use App\Domain\Car;
use App\Domain\CrossRoad;
use App\Domain\CrossRoad\TrafficLight\TrafficLight;
use App\Domain\Lane;
use App\Domain\Road;
use App\Factory\ColorFactory;
use App\Factory\CoordinateFactory;
use App\Factory\FigureFactory;
use App\Foundation\ArrayableInterface;
use App\Foundation\Storage\StorageDisk;
use App\Foundation\Storage\StorageInterface;
use App\Graphic\Square;
use App\Service\Orientator;

class App implements ArrayableInterface
{
    private StorageInterface $storage;
    private $logger = null;

    private CrossRoad $crossRoad;
    private int $step = 0;
    private int $maxCarSpeed = 20;

    public function __construct()
    {
        $this->storage = new StorageDisk();
        $this->storage->configure();

        $this->loadState();
    }

    private function loadState(): void
    {
        $data = $this->storage->getData();
        if (empty($data)) {
            $this->init();
            $this->saveState();
            return;
        }
        $this->crossRoad = CrossRoad::fromArray($data['cross_road']);
        $this->step = $data['step'];
        $this->maxCarSpeed = $data['car_max_speed'];
    }

    private function saveState(): void
    {
        $this->storage->saveData($this->toArray());
    }

    public function run(): void
    {
        if (
            $this->crossRoad->getTrafficLight() !== null
            && $this->step % $this->crossRoad->getTrafficLight()->getDuration()
        ) {
            $this->crossRoad->getTrafficLight()->switch();
        }

        $this->crossRoad->run();
        foreach ($this->crossRoad->getRoads() as $road) {
            if ($this->step % $road->getTrafficIntensity() === 0) {
                if ($road->getDirection() === 'left')
                $this->addCarToLine($road->getStraightLane());
            }
        }

        $this->step = 0;
        $this->saveState();
    }

    private function init(): void {
        $defaultLength = 500;

        $roadDirections = [
            'left' => ['priority' => false],
            'right' => ['priority' => false],
            'top' => ['priority' => true],
            'bottom' => ['priority' => true]
        ];
        /**
         * @var Road[] $roads
         */
        $roads = [];
        foreach ($roadDirections as $roadDirection => $roadSetting) {
            $orientation = Orientator::getOrientationByDirection($roadDirection);
            $road = new Road($defaultLength);
            $road->setTrafficIntensity(111);
            $road->setPriority($roadSetting['priority']);
            $road->setDirection($roadDirection);
            $coordinate = CoordinateFactory::createRoadCoordinate($defaultLength, $roadDirection, 36);
            $figure = FigureFactory::createRectangle($defaultLength, 18 * 2, $orientation);
            $figure->setCoordinate($coordinate);
            $road->setFigure($figure);

            // TODO Стоит вынести в фабрики
            $straightLane = new Lane($defaultLength, 40, false);
            $straightLane->setRoad($road);
            $straightLane->setOrientation($orientation);
            $straightLane->setDirection($roadDirection);
            $figure = FigureFactory::createRectangle($defaultLength, 14, $orientation);
            $figure->setCoordinate(CoordinateFactory::createLaneCoordinate($road, true));
            $straightLane->setFigure($figure);

            $reverseLane = new Lane($defaultLength, 40, true);
            $reverseLane->setRoad($road);
            $reverseLane->setOrientation($orientation);
            $reverseLane->setDirection($roadDirection);
            $figure = FigureFactory::createRectangle($defaultLength, 14, $orientation);
            $figure->setCoordinate(CoordinateFactory::createLaneCoordinate($road, false));
            $reverseLane->setFigure($figure);


            $road->setStraightLane($straightLane);
            $road->setReverseLane($reverseLane);

            $roads[] = $road;
        }

        $this->crossRoad = new CrossRoad(...$roads);
        $this->crossRoad->setTrafficLight(new TrafficLight(200));
        $figure = new Square(36);
        $coordinate = CoordinateFactory::create(36, $roadDirection, $defaultLength-36, $defaultLength-36);
        $figure->setCoordinate($coordinate);
        $figure->setColor(ColorFactory::create());
        $this->crossRoad->setFigure($figure);
    }

    public function toArray(): array
    {
        return [
            'traffic_light' => $this->crossRoad->getTrafficLight()?->toArray(),
            'cross_road' => $this->crossRoad->toArray(),
            'car_max_speed' => $this->maxCarSpeed,
            'step' => $this->step,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self();
    }

    private function addCarToLine(Lane $lane): Car
    {
        $car = new Car();
        $coordinate = CoordinateFactory::createCarCoordinate($lane, $car->getLength());
        $figure = new Square($car->getLength());
        $figure->setColor(ColorFactory::create());
        $figure->setCoordinate($coordinate);
        $car->setFigure($figure);
        $car->setMaxSpeed(rand(14, max($lane->getMaxSpeed(), $this->maxCarSpeed)));
        $lane->addCar($car, 0);

        return $car;
    }

    public function setSettings(array $settings): void
    {
        if (isset($settings['traffic_light_need'])) {
            $this->crossRoad->setTrafficLight(new TrafficLight($settings['traffic_light_duration']));
        } else {
            $this->crossRoad->setTrafficLight();
        }

        if (isset($settings['road_max_speed'])) {
            foreach ($this->crossRoad->getRoads() as $road) {
                foreach ($road->getLanes() as $lane) {
                    $lane->setMaxSpeed($settings['road_max_speed']);
                }
            };
        }

        if (isset($settings['car_max_speed']) && $settings['car_max_speed'] > 0) {
            foreach ($this->crossRoad->getRoads() as $road) {
                foreach ($road->getLanes() as $lane) {
                    foreach ($lane->getCars() as $car) {
                        $car->setMaxSpeed($settings['car_max_speed']);
                    }
                }
            }
        }

        $this->saveState();
    }
}