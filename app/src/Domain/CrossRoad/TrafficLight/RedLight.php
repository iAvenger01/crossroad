<?php

namespace App\Domain\CrossRoad\TrafficLight;

class RedLight extends BaseLight
{
    public function next(): LightInterface
    {
        return new YellowLight($this);
    }
}