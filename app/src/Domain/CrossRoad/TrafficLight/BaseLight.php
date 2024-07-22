<?php

namespace App\Domain\CrossRoad\TrafficLight;

use App\Foundation\ArrayableInterface;

abstract class BaseLight implements LightInterface, ArrayableInterface
{
    protected ?LightInterface $previousLight = null;

    public function __construct(?LightInterface $previousLight = null) {
        $this->previousLight = $previousLight;
    }

    public function toArray(): array
    {
        return [
            'light' => static::class,
            'previous_light' => $this->previousLight?->toArray(),
        ];
    }

    public static function fromArray(array $data): BaseLight
    {
        return new $data['light']($data['previous_light'] ?? null);
    }
}