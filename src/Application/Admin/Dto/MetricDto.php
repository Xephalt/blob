<?php

declare(strict_types=1);

namespace App\Application\Admin\Dto;

final class MetricDto
{
    public function __construct(
        public readonly int $count,
        public readonly int $previousCount,
        public readonly int $delta,
        public readonly float $evolutionPercent,
        public readonly Trend $trend,
    ) {
    }
}
