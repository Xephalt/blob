<?php

declare(strict_types=1);

namespace App\Application\Common\Period;

final class ResolvedPeriod
{
    private Period $current;
    private Period $comparison;

    public function __construct(Period $current, Period $comparison)
    {
        $this->current = $current;
        $this->comparison = $comparison;
    }

    public function current(): Period
    {
        return $this->current;
    }

    public function comparison(): Period
    {
        return $this->comparison;
    }
}
