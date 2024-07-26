<?php

namespace App\Domain\CrossRoad\TrafficLight;

interface LightInterface
{
    public function next(): LightInterface;

    public function light(): string;
}