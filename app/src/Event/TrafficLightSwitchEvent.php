<?php

namespace App\Event;

use App\Domain\CrossRoad\TrafficLight\TrafficLight;
use App\Foundation\Event\BaseEvent;

class TrafficLightSwitchEvent extends BaseEvent
{
    public function __construct(
        protected TrafficLight $trafficLight,
    )
    {
    }

    protected function getDataEvent(): array
    {
        return [
            'signals' => $this->trafficLight->getSignals()
        ];
    }
}