<?php

namespace App\Domain\CrossRoad\TrafficLight;

use App\Event\TrafficLightSwitchEvent;
use App\Foundation\ArrayableInterface;
use App\Service\Orientator;

class TrafficLight implements ArrayableInterface
{
    private int $duration;

    /**
     * @var BaseLight[] $signals
     */
    private array $signals;

    private ?int $durationYellow = null;

    public function __construct(int $duration, ?array $signals = null, ?int $durationYellow = 0) {
        $this->signals = $signals ?? [
            Orientator::ORIENTATION_HORIZONTAL => new RedLight(),
            Orientator::ORIENTATION_VERTICAL => new GreenLight(),
        ];
        $this->duration = $duration;
        if ($durationYellow > 0) {
            $this->durationYellow = $durationYellow - 1;
        }
    }

    public function switch(): void
    {
        $this->signals = [
            Orientator::ORIENTATION_HORIZONTAL => $this->signals[Orientator::ORIENTATION_HORIZONTAL]->next(),
            Orientator::ORIENTATION_VERTICAL => $this->signals[Orientator::ORIENTATION_VERTICAL]->next(),
        ];
        $this->durationYellow = $this->signals[Orientator::ORIENTATION_HORIZONTAL]->light() === 'yellow' ? 3 : null;
        (new TrafficLightSwitchEvent($this))->dispatch();
    }

    public function getDuration(): int {
        return $this->duration;
    }

    public function needSwitchYellowSignal(): bool {
        return $this->durationYellow === 0;
    }

    public function canDriveRoad(string $orientation): bool {
        return $this->signals[$orientation] instanceof GreenLight;
    }

    public function getSignals(): array {
        return [
            Orientator::ORIENTATION_HORIZONTAL => $this->signals[Orientator::ORIENTATION_HORIZONTAL]->toArray(),
            Orientator::ORIENTATION_VERTICAL => $this->signals[Orientator::ORIENTATION_VERTICAL]->toArray(),
        ];
    }

    public function toArray(): array
    {
        return [
            'duration' => $this->duration,
            'duration_yellow' => $this->durationYellow,
            'signals' => [
                Orientator::ORIENTATION_HORIZONTAL => $this->signals[Orientator::ORIENTATION_HORIZONTAL]->toArray(),
                Orientator::ORIENTATION_VERTICAL => $this->signals[Orientator::ORIENTATION_VERTICAL]->toArray(),
            ],
        ];
    }

    public static function fromArray(?array $data): self
    {
        $signals = [
            Orientator::ORIENTATION_HORIZONTAL => BaseLight::fromArray($data['signals'][Orientator::ORIENTATION_HORIZONTAL]),
            Orientator::ORIENTATION_VERTICAL => BaseLight::fromArray($data['signals'][Orientator::ORIENTATION_VERTICAL]),
        ];
        return new self($data['duration'], $signals, $data['duration_yellow']);
    }
}