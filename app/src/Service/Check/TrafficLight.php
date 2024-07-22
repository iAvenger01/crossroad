<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

class TrafficLight implements CheckInterface
{

    public static function check(Car $car, Lane $lane): bool
    {
        $trafficLight = $lane->getRoad()->getCrossRoad()->getTrafficLight();
        if ($car->getPositionOnLane() < $car->getLane()->getLength()) {
            return false;
        }
        return $trafficLight !== null ? $trafficLight->canDriveRoad($lane->getOrientation()) : true;
    }
}