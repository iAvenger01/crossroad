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
            'color' => static::light(),
            'previous_light' => $this->previousLight?->toArray(),
        ];
    }

    public static function fromArray(array $data): BaseLight
    {
        if (isset($data['previous_light'])) {
            $previousLight = new $data['previous_light']['light']();
        } else {
            $previousLight = null;
        }
        return new $data['light']($previousLight);
    }

    abstract public function light(): string;
}