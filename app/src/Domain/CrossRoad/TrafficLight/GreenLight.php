<?php

namespace App\Domain\CrossRoad\TrafficLight;

class GreenLight extends BaseLight
{
    public function next(): LightInterface
    {
        return new YellowLight($this);
    }

    public function light(): string {
        return 'green';
    }
}