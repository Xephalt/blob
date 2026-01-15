<?php

declare(strict_types=1);

namespace App\Application\Admin\Dto;

enum Trend: string
{
    case UP = 'up';
    case DOWN = 'down';
    case STABLE = 'stable';
}
