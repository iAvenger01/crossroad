<?php

namespace App\Domain\CrossRoad\TrafficLight;

class YellowLight extends BaseLight
{
    public function next(): LightInterface
    {
        if ($this->previousLight instanceof RedLight) {
            $this->previousLight = null;
            return new GreenLight($this);
        } else {
            $this->previousLight = null;
            return new RedLight($this);
        }
    }

    public function light(): string
    {
        return 'yellow';
    }
}