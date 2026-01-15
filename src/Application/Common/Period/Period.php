<?php

declare(strict_types=1);

namespace App\Application\Common\Period;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;

final class Period implements JsonSerializable
{
    private DateTimeImmutable $from;
    private DateTimeImmutable $to;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to)
    {
        if ($from > $to) {
            throw new InvalidArgumentException(
                'Period "from" date must be before or equal to "to" date.'
            );
        }

        $this->from = $from;
        $this->to = $to;
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    /**
     * Durée en jours calendaires, bornes incluses.
     * Ex: 01 → 09 = 9 jours
     */
    public function lengthInDays(): int
    {
        return (int) $this->from
            ->diff($this->to)
            ->days + 1;
    }

    public function jsonSerialize(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
        ];
    }
}
