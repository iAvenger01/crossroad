<?php

namespace App\Foundation\Event;

use App\Foundation\Container;
use App\Foundation\Logger\LoggerInterface;

abstract class BaseEvent implements EventInterface
{
    public function toArray(): array
    {
        return [
            'event' => static::class,
            'data' => $this->getDataEvent()
        ];
    }

    abstract protected function getDataEvent(): array;

    public function dispatch(): void {

        $logger = Container::get(LoggerInterface::class);
        $logger->log($this->toArray());
    }
}