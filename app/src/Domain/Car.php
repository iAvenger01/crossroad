<?php

namespace App\Domain;

use App\Graphic\Figure;
use App\Graphic\Square;
use App\Service\Check\CarInFrontStopped;
use App\Service\Check\ChangeSpeed;
use App\Service\Check\CheckInterface;
use App\Service\Check\MaxSpeed;
use App\Service\Check\TrafficLight;
use App\Service\Orientator;

class Car
{
    private string $state = 'stop';

    private int $length = 14;

    private int $maxSpeed = 0;

    private int $speed = 0;

    private int $positionOnLane = 0;

    private Lane $lane;

    private Figure $figure;

    public function drive(): void
    {
        $this->beforeDrive();
        $this->maneuver();

        if ($this->getLane()->getReverse()) {
            if ($this->getPositionOnLane() < 0) {
                $this->lane->removeCar($this);
            } else {
                $this->setPositionOnLane($this->getPositionOnLane() + $this->speed);
            }
        }
    }

    private function beforeDrive(): void {
        $this->setState('driving');

        foreach (self::getStoppingChecks() as $check) {
            if ($check::check($this, $this->getLane())) {
                $this->setState('stop');
                return;
            } else {
                $this->setState('driving');
            }
        }

        if (ChangeSpeed::check($this, $this->getLane())) {
            $carInFront = $this->getLane()->getCarInFront($this);
            $carPosition = $this->getPositionOnLane();
            $caInFrontNextPosition = $this->getPositionOnLane() + $carInFront->getSpeed();
            $this->speed = $caInFrontNextPosition - $carPosition - $this->getLength() - 10 ;
            $this->setState('brake');

            return;
        }

        if (MaxSpeed::check($this, $this->getLane())) {
            $this->speed = min($this->getMaxSpeed(), $this->getLane()->getMaxSpeed());
        } else {
            $carInFront = $this->getLane()->getCarInFront($this);
            $this->speed = $carInFront->getSpeed();
        }
    }

    private function maneuver(): void {
        if ($this->state === 'driving' || $this->state === 'brake') {
            if ($this->getPositionOnLane() + $this->speed > $this->getLane()->getLength()) {
                $availableTurns = $this->getLane()->getRoad()->getCrossRoad()->availableTurns($this->getLane()->getRoad());
                $nextRoad = $availableTurns[array_rand($availableTurns)];

                $this->getLane()->removeCar($this);
                $nextRoad->getReverseLane()->addCar($this, $nextRoad->getReverseLane()->getLength());

                $this->setPositionOnLane($nextRoad->getLength() - $this->getLength());
            } else {
                if ($this->getLane()->getReverse()) {
                    $this->setPositionOnLane($this->getPositionOnLane() - $this->speed);
                } else {
                    $this->setPositionOnLane($this->getPositionOnLane() + $this->speed);
                }
            }
        }
    }

    private function updateCoordinateByPosition(int $positionOnLane): void
    {
        $lane = $this->getLane();
        $coordinate = clone $this->figure->getCoordinate();
        if ($lane->getOrientation() === Orientator::ORIENTATION_HORIZONTAL) {
            if ($lane->getDirection() === Orientator::DIRECTION_LEFT) {
                $coordinate->setX($positionOnLane);
            } else {
                $coordinate->setX($lane->getLength() * 2 + (36 - $this->getLength()) - $positionOnLane);
            }
        } else {
            if ($lane->getDirection() === Orientator::DIRECTION_TOP) {
                $coordinate->setY($positionOnLane);
            } else {
                $coordinate->setY($lane->getLength() * 2 + (36 - $this->getLength()) - $positionOnLane);
            }
        }
        $this->figure->setCoordinate($coordinate);
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
        $this->updateCoordinateByPosition($positionOnLane);
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

    public function setSpeed(int $speed): void
    {
        $this->speed = $speed;
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'position_on_lane' => $this->getPositionOnLane(),
            'max_speed' => $this->getMaxSpeed(),
            'speed' => $this->getSpeed(),
            'figure' => $this->figure->toArray(),
        ];
    }

    public static function fromArray($data): Car
    {
        $car = new self();
        $car->setSpeed($data['speed']);
        $car->setMaxSpeed($data['max_speed']);
        $car->setFigure(Square::fromArray($data['figure']));

        return $car;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return CheckInterface[]
     */
    protected function getStoppingChecks(): array
    {
        return [
            TrafficLight::class,
            CarInFrontStopped::class,
            // PriorityRoad TODO нужно сделать возможность проезда без светофора
        ];
    }

    protected function getChangeSpeedChecks(): array
    {
        return [
            ChangeSpeed::class
        ];
    }

    public function setFigure(Figure $figure): void
    {
        $this->figure = $figure;
    }
}