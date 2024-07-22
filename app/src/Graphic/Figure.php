<?php

namespace App\Graphic;

use App\Foundation\ArrayableInterface;
use App\Graphic\Coordinate\CoordinateInterface;

interface Figure extends ArrayableInterface, CoordinateInterface
{
    public const string ORIENTATION_HORIZONTAL = 'horizontal';
    public const string ORIENTATION_VERTICAL = 'vertical';
}