<?php

namespace App;

use App\Domain\Car;
use App\Domain\CrossRoad;
use App\Domain\CrossRoad\TrafficLight\TrafficLight;
use App\Domain\Lane;
use App\Domain\Road;
use App\Factory\ColorFactory;
use App\Foundation\ArrayableInterface;
use App\Foundation\Container;
use App\Foundation\Logger\FileLogger;
use App\Foundation\Logger\LoggerInterface;
use App\Foundation\Storage\FileStorage;
use App\Foundation\Storage\StorageInterface;
use App\Service\Orientator;

class App implements ArrayableInterface
{
    private StorageInterface $storage;

    private CrossRoad $crossRoad;
    private int $step = 0;
    private int $maxCarSpeed = 20;
    private int $maxLaneSpeed = 40;
    private int $roadLength = 500;
    private int $trafficIntensity = 100;

    public function __construct()
    {
        $this->storage = new FileStorage();
        $this->storage->configure();
        Container::set(LoggerInterface::class, FileLogger::class, []);

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
        $this->roadLength = $data['road_length'];
    }

    private function saveState(): void
    {
        $this->storage->saveData($this->toArray());
    }

    public function run(): void
    {
        if (
            (
                $this->crossRoad->getTrafficLight() !== null
                && $this->step % $this->crossRoad->getTrafficLight()->getDuration() === 0
            )
            || (
                $this->crossRoad->getTrafficLight() !== null
                && $this->crossRoad->getTrafficLight()->needSwitchYellowSignal()
            )
        ) {
            $this->crossRoad->getTrafficLight()->switch();
        }

        $this->crossRoad->run();
        foreach ($this->crossRoad->getRoads() as $road) {
            if ($this->step % $this->trafficIntensity === 0) {
                $this->addCarToLine($road->getStraightLane());
            }
        }

        $this->step++;
        $this->saveState();
    }

    private function init(): void {
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
            $road = new Road($this->roadLength);
            $road->setPriority($roadSetting['priority']);
            $road->setDirection($roadDirection);

            // TODO Стоит вынести в фабрики
            $straightLane = new Lane($this->roadLength, $this->maxLaneSpeed, false);
            $straightLane->setRoad($road);
            $straightLane->setOrientation($orientation);
            $straightLane->setDirection($roadDirection);

            $reverseLane = new Lane($this->roadLength, $this->maxLaneSpeed, true);
            $reverseLane->setRoad($road);
            $reverseLane->setOrientation($orientation);
            $reverseLane->setDirection($roadDirection);

            $road->setStraightLane($straightLane);
            $road->setReverseLane($reverseLane);

            $roads[] = $road;
        }

        $this->crossRoad = new CrossRoad(...$roads);
        $this->crossRoad->setTrafficLight(new TrafficLight(200));
    }

    private function addCarToLine(Lane $lane): void
    {
        $car = new Car();
        $car->setColor(ColorFactory::create());
        $car->setMaxSpeed(rand(14, max($lane->getMaxSpeed(), $this->maxCarSpeed)));
        $lane->addCar($car);
    }

    public function getSettings(): array
    {
        $crossRoad = $this->crossRoad;
        return [
            'traffic_light_need' => $crossRoad->getTrafficLight() ? true : false,
            'traffic_light_duration' => $crossRoad->getTrafficLight()? $crossRoad->getTrafficLight()->getDuration() : 0,
            'constraint_turns' => $crossRoad->getConstraintTurns(),
            'car_max_speed' => $this->maxCarSpeed,
            'road_length' => $this->roadLength,
            'max_lane_speed' => $this->maxLaneSpeed,
            'traffic_intensity' => $this->trafficIntensity,
            'priority' => $this->crossRoad->getRoad('left')->getPriority() === true ? 'horizontal' : 'vertical'
        ];
    }

    public function setSettings(array $settings): void
    {
        if (
            isset($settings['traffic_light_need'])
            && $settings['traffic_light_need'] === "true"
        ) {
            $this->crossRoad->setTrafficLight(new TrafficLight($settings['traffic_light_duration']));
        } else {
            $this->crossRoad->setTrafficLight();
        }

        if (isset($settings['traffic_intensity'])) {
            $this->trafficIntensity = $settings['traffic_intensity'];
        }

        if (isset($settings['max_lane_speed'])) {
            $this->maxLaneSpeed = $settings['max_lane_speed'];
            foreach ($this->crossRoad->getRoads() as $road) {
                foreach ($road->getLanes() as $lane) {
                    $lane->setMaxSpeed($settings['max_lane_speed']);
                }
            }
        }

        if (isset($settings['road_length'])) {
            foreach ($this->crossRoad->getRoads() as $road) {
                $road->setLength($settings['road_length']);
                $road->getReverseLane()->setLength($settings['road_length']);
                $road->getStraightLane()->setLength($settings['road_length']);
            }
        }

        if (isset($settings['car_max_speed'])) {
            $this->maxCarSpeed = $settings['car_max_speed'];
        }

        if (isset($settings['constraint_turns'])) {
            $this->crossRoad->setConstraintTurns($settings['constraint_turns']);
        }

        if (isset($settings['priority'])) {
            foreach ($this->crossRoad->getRoads() as $road) {
                if (Orientator::getOrientationByDirection($road->getDirection()) === $settings['priority']) {
                    $road->setPriority($settings['priority']);
                } else {
                    $road->setPriority(false);
                }
            }
        }

        $this->saveState();
    }

    public function toArray(): array
    {
        return [
            'cross_road' => $this->crossRoad->toArray(),
            'car_max_speed' => $this->maxCarSpeed,
            'step' => $this->step,
            'road_length' => $this->roadLength,
            'traffic_intensity' => $this->trafficIntensity,
            'max_lane_speed' => $this->maxLaneSpeed,
        ];
    }
}