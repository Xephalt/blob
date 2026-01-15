<?php

declare(strict_types=1);

namespace App\Application\Admin\Dto;

use App\Application\Common\Period\Period;

final class UserPeriodMetricsDto
{
    public function __construct(
        public readonly MetricDto $registeredUsers,
        public readonly MetricDto $activeUsers,
        public readonly Period $currentPeriod,
        public readonly Period $comparisonPeriod,
    ) {
    }
}
