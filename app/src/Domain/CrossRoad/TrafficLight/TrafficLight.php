<?php

namespace App\Domain\CrossRoad\TrafficLight;

use App\Foundation\ArrayableInterface;
use App\Service\Orientator;

class TrafficLight implements ArrayableInterface
{
    private int $duration;

    /**
     * @var BaseLight[] $signals
     */
    private array $signals;

    public function __construct(int $duration, ?array $signals = null) {
        $this->signals = $signals ?? [
            Orientator::ORIENTATION_HORIZONTAL => new RedLight(),
            Orientator::ORIENTATION_VERTICAL => new GreenLight(),
        ];
        $this->duration = $duration;
    }

    public function switch(): void {
        $this->signals = [
            Orientator::ORIENTATION_HORIZONTAL => $this->signals[Orientator::ORIENTATION_HORIZONTAL]->next(),
            Orientator::ORIENTATION_VERTICAL => $this->signals[Orientator::ORIENTATION_VERTICAL]->next(),
        ];
    }

    public function getDuration(): int {
        return $this->duration;
    }

    public function canDriveRoad(string $orientation): bool {
        return $this->signals[$orientation] instanceof GreenLight;
    }

    public function toArray(): array
    {
        return [
            'duration' => $this->duration,
            'signals' => [
                Orientator::ORIENTATION_HORIZONTAL => $this->signals[Orientator::ORIENTATION_HORIZONTAL]->toArray(),
                Orientator::ORIENTATION_VERTICAL => $this->signals[Orientator::ORIENTATION_VERTICAL]->toArray(),
            ],
        ];
    }

    public static function fromArray(array $data): self
    {
        $signals = [
            Orientator::ORIENTATION_HORIZONTAL => BaseLight::fromArray($data['signals'][Orientator::ORIENTATION_HORIZONTAL]),
            Orientator::ORIENTATION_VERTICAL => BaseLight::fromArray($data['signals'][Orientator::ORIENTATION_HORIZONTAL]),
        ];
        return new self($data['duration'], $signals);
    }
}