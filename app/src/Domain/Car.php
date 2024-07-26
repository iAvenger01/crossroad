<?php

namespace App\Domain;

use App\Event\CarDriveOnLaneEvent;
use App\Event\CarStoppedEvent;
use Exception;

class Car
{
    private string $state = 'stop';

    private int $length = 14;

    private int $maxSpeed = 0;

    private int $speed = 0;

    private int $positionOnLane = 0;

    private Lane $lane;

    private string $plannedManeuver = 'front';

    private string $color;

    /**
     * @throws Exception
     */
    public function drive(): void
    {
        $this->beforeDrive();
        $this->maneuver();
        $this->afterDrive();
    }

    private function beforeDrive(): void
    {
        $this->setState('driving');

        $lane = $this->lane;

        if ($this->getPositionOnLane() + $this->getSpeed() > $lane->getLength()) {
            $this->setSpeed($lane->getLength() - $this->getPositionOnLane());
            $this->setState('brake');
        }
        // Должна ли текущая (если она ближайшая машина к перекрестку) остановиться из-за светофора (TrafficLightCheck)
        if ($this->getPositionOnLane() === $lane->getLength()) {
            if (($trafficLight = $lane->getRoad()->getCrossRoad()->getTrafficLight()) !== null) {
                if (!$trafficLight->canDriveRoad($lane->getOrientation())) {
                    $this->setState('stop');
                } else {
                    $this->setState('driving');
                }
            }
        }
        // TODO Должна ли ближайшая машина к перекрестку остановиться из-за приоритета

        // Должна ли текущая машина остановиться или замедлиться из-за автомобиля впереди
        $carInFront = $lane->getCarInFront($this);
        if (!is_null($carInFront)) {
            if ($this->positionOnLane < $lane->getLength()) {
                if ($carInFront->isStopped() || $carInFront->isDriving()) {
                    $carInFrontNextPosition = $carInFront->getPositionOnLane() - $carInFront->getLength() / 2;
                } else if ($carInFront->isBrake()) {
                    $carInFrontNextPosition = $carInFront->getPositionOnLane() - $carInFront->getLength() / 2;
                }
                $thisSpeed = $carInFrontNextPosition - $this->positionOnLane - $this->length;

                if ($thisSpeed < 0) {
                    $thisSpeed = 0;
                }
                if ($this->positionOnLane + $this->speed + $this->length > $carInFrontNextPosition) {
                    $this->setSpeed($thisSpeed);

                    $this->setState('brake');
                    if ($thisSpeed === 0) {
                        $this->setState('stop');
                    }
                }
            }
        }
    }

    private function afterDrive(): void
    {
        if ($this->getLane()->getReverse() && $this->getPositionOnLane() >= $this->getLane()->getLength()) {
            $this->getLane()->removeCar($this);
        }
    }

    private function maneuver(): void {
        if ($this->state === 'driving' || $this->state === 'brake') {
            (new CarDriveOnLaneEvent($this, $this->getLane()))->dispatch();
            if (
                !$this->getLane()->getReverse()
                && $this->getPositionOnLane() === $this->getLane()->getLength()
            ) {
                $this->setPositionOnLane($this->getLane()->getLength());
                $crossRoad = $this->getLane()->getRoad()->getCrossRoad();
                if ($this->getPlannedManeuver() === 'front') {
                    $availableTurns = $crossRoad->availableTurns($this->getLane()->getRoad());
                    $nextRoad = $availableTurns[array_rand($availableTurns)];
                    $this->setPlannedManeuver($nextRoad->getDirection());
                }

                if ($crossRoad->canDriveCrossRoad($this)) {
                    $this->getLane()->removeCar($this);
                    $this->setPositionOnLane(0);
                    $crossRoad->addCarInBuffer($this);
                }
            } else {
                $this->setPositionOnLane($this->getPositionOnLane() + $this->getSpeed());
            }
        } elseif ($this->state === 'stop') {
            (new CarStoppedEvent($this, $this->getLane()))->dispatch();
        }
    }

    /**
     * @return int
     */
    public function getPositionOnLane(): int
    {
        return $this->positionOnLane;
    }

    public function setPositionOnLane(int $positionOnLane): void
    {
        $this->positionOnLane = $positionOnLane;
    }

    /**
     * @param string $state
     */
    private function setState(string $state): void
    {
        $this->state = $state;
    }

    public function isStopped(): bool
    {
        return $this->state === 'stop';
    }

    public function isBrake(): bool
    {
        return $this->state === 'brake';
    }

    public function isDriving(): bool
    {
        return $this->state === 'driving';
    }

    public function getLane(): Lane
    {
        return $this->lane;
    }

    public function setLane(Lane $lane): void
    {
        $this->lane = $lane;
    }

    public function getMaxSpeed(): int
    {
        return $this->maxSpeed;
    }

    public function setMaxSpeed(int $maxSpeed): void
    {
        $this->maxSpeed = $maxSpeed;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    /**
     * @throws Exception
     */
    public function setSpeed(int $speed): void
    {
        if ($speed < 0) {
            throw new Exception('Speed cannot be negative');
        }
        $this->speed = $speed;
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'position_on_lane' => $this->getPositionOnLane(),
            'max_speed' => $this->getMaxSpeed(),
            'speed' => $this->getSpeed(),
            'color' => $this->getColor(),
            'planned_maneuver' => $this->getPlannedManeuver(),
        ];
    }

    /**
     * @throws Exception
     */
    public static function fromArray($data): Car
    {
        $car = new self();
        $car->setSpeed($data['speed']);
        $car->setMaxSpeed($data['max_speed']);
        $car->setColor($data['color']);
        $car->setPositionOnLane($data['position_on_lane']);
        $car->setPlannedManeuver($data['planned_maneuver']);

        return $car;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getPlannedManeuver(): string
    {
        return $this->plannedManeuver;
    }

    public function setPlannedManeuver(string $plannedManeuver): void
    {
        $this->plannedManeuver = $plannedManeuver;
    }

}