<?php

declare(strict_types=1);

namespace App\Application\Admin\Query;

use App\Application\Admin\Dto\MetricDto;
use App\Application\Admin\Dto\Trend;
use App\Application\Admin\Dto\UserPeriodMetricsDto;
use App\Application\Common\Period\ResolvedPeriod;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;

final class UserMetricsQuery
{
    public function __construct(
        private UserRepository $userRepository,
        private MessageRepository $messageRepository,
    ) {
    }

    /**
     * @param int[]|null $weekdays
     */
    public function execute(ResolvedPeriod $period, ?array $weekdays): UserPeriodMetricsDto
    {
        $currentRegistered = $this->userRepository
            ->countRegisteredBetween($period->current(), $weekdays);

        $previousRegistered = $this->userRepository
            ->countRegisteredBetween($period->comparison(), $weekdays);

        $currentActive = $this->messageRepository
            ->countActiveUsersBetween($period->current(), $weekdays);

        $previousActive = $this->messageRepository
            ->countActiveUsersBetween($period->comparison(), $weekdays);

        return new UserPeriodMetricsDto(
            $this->buildMetric($currentRegistered, $previousRegistered),
            $this->buildMetric($currentActive, $previousActive),
            $period->current(),
            $period->comparison(),
        );
    }

    private function buildMetric(int $current, int $previous): MetricDto
    {
        $delta = $current - $previous;

        if ($previous === 0) {
            $evolution = $current === 0 ? 0.0 : 100.0;
        } else {
            $evolution = ($delta / $previous) * 100;
        }

        $trend = match (true) {
            $evolution > 0 => Trend::UP,
            $evolution < 0 => Trend::DOWN,
            default => Trend::STABLE,
        };

        return new MetricDto(
            $current,
            $previous,
            $delta,
            round($evolution, 1),
            $trend,
        );
    }
}
