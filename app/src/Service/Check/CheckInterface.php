<?php

namespace App\Service\Check;

use App\Domain\Car;
use App\Domain\Lane;

interface CheckInterface
{
    public static function check(Car $car, Lane $lane): bool;
}