<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class CarInFrontStopped implements CheckInterface
{
    public static function check(Car $car, Lane $lane): bool
    {
//        return false;
        $carInFront = $lane->getCarInFront($car);
        if (is_null($carInFront)) {
            return false;
        }
        return $carInFront->isStopped();
    }
}