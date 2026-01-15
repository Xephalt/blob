<?php

declare(strict_types=1);

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Dto\UserPeriodMetricsDto;
use App\Application\Admin\Query\UserMetricsQuery;

final class GetUserMetricsHandler
{
    public function __construct(
        private UserMetricsQuery $query,
    ) {
    }

    public function handle(GetUserMetrics $command): UserPeriodMetricsDto
    {
        return $this->query->execute(
            $command->period,
            $command->weekdays,
        );
    }
}
