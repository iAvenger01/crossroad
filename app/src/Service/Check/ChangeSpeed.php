<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class ChangeSpeed implements CheckInterface
{
    public static function check(Car $car, Lane $lane, int $speed = null): bool
    {
        $carInFront = $lane->getCarInFront($car);

        if (is_null($carInFront)) {
            return false;
        }

        $carInFrontNextPosition = $carInFront->getPositionOnLane() + $carInFront->getSpeed();
        $carNextPosition = $car->getPositionOnLane() + $car->getSpeed();
        if ($carNextPosition >= $carInFrontNextPosition) {
            return true;
        }

        return false;
    }
}