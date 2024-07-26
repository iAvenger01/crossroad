<?php

namespace App\Foundation\Logger;

interface LoggerInterface
{
    public function log(array $data): void;
}