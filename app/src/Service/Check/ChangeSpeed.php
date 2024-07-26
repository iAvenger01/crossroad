<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class ChangeSpeed implements CheckInterface
{
    public static function check(Car $car, Lane $lane, int $speed = null): bool
    {
        if ($car->getPositionOnLane() + $car->getSpeed() > $lane->getLength()) {
            return true;
        }
        $carNextPosition = $car->getPositionOnLane() + $car->getSpeed() + $car->getLength() + 5;
        $carInFront = $lane->getCarInFront($car);
        if (is_null($carInFront)) {
            return false;
        }

        if ($carInFront->isStopped() && $carNextPosition >= $carInFront->getPositionOnLane() ) {
            return true;
        }

        $carInFrontNextPosition = $carInFront->getPositionOnLane() + $carInFront->getSpeed();

        if ($carNextPosition >= $carInFrontNextPosition) {
            return true;
        }

        return false;
    }
}