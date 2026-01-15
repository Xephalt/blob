<?php

declare(strict_types=1);

namespace App\Application\Admin\UseCase;

use App\Application\Common\Period\ResolvedPeriod;

final class GetUserMetrics
{
    /**
     * @param int[]|null $weekdays
     */
    public function __construct(
        public readonly ResolvedPeriod $period,
        public readonly ?array $weekdays,
    ) {
    }
}
