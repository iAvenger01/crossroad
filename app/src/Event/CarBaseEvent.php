<?php

namespace App\Event;

use App\Domain\Car;
use App\Domain\Lane;
use App\Foundation\Event\BaseEvent;

class CarBaseEvent extends BaseEvent
{
    public function __construct(
        protected Car $car,
        protected Lane $lane
    ) {
    }

    protected function getDataEvent(): array
    {
        return [
            'car' => $this->car,
            'lane' => $this->lane
        ];
    }
}