<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class MaxSpeed implements CheckInterface
{

    public static function check(Car $car, Lane $lane): bool
    {
        $carInFront = $lane->getCarInFront($car);

        if (is_null($carInFront)) {
            return true;
        }

        if ($car->getMaxSpeed() <= $carInFront->getSpeed()) {
            return true;
        }

        return false;
    }
}