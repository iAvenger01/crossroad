<?php

namespace App\Foundation\Logger;

class FileLogger implements LoggerInterface
{
    private $stream;

    public function __construct() {
        $this->stream = fopen(__DIR__ . '/../../../storage/app.log', 'a');
    }

    public function log(array $data): void
    {
        fwrite($this->stream, json_encode($data) . PHP_EOL);
    }
}