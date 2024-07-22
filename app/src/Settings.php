<?php

namespace App;

class Settings
{
    private array $roadPriorities = [
        'left' => false,
        'right' => false,
        'top' => true,
        'bottom' => true,
    ];

    private array $roadTrafficLight = [
        ''
    ];

    public function getRoadPriorities(): array
    {
        return $this->roadPriorities;
    }
}