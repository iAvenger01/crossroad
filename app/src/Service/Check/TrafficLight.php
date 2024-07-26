<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class TrafficLight implements CheckInterface
{
    public static function check(Car $car, Lane $lane): bool
    {
        if ($car->getPositionOnLane() === $lane->getLength()) {
            if (($trafficLight = $lane->getRoad()->getCrossRoad()->getTrafficLight()) !== null) {
                return !$trafficLight->canDriveRoad($lane->getOrientation());
            }
        }

        return false;
    }
}