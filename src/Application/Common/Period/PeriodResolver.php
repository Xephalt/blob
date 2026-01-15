<?php

declare(strict_types=1);

namespace App\Application\Common\Period;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;

final class PeriodResolver
{
    public function resolve(
        ?string $dateFrom,
        ?string $dateTo,
    ): ResolvedPeriod {
        // Déterminer la période courante
        if ($dateFrom !== null && $dateTo !== null) {
            $currentFrom = $this->normalizeFrom($dateFrom);
            $currentTo = $this->normalizeTo($dateTo);
        } else {
            // Période par défaut : aujourd’hui inclus, 30 jours glissants
            $currentTo = (new DateTimeImmutable('now'))
                ->setTime(23, 59, 59);

            $currentFrom = $currentTo
                ->sub(new DateInterval('P29D'))
                ->setTime(0, 0, 0);
        }

        if ($currentFrom > $currentTo) {
            throw new InvalidArgumentException('date_from must be before date_to');
        }

        $currentPeriod = new Period($currentFrom, $currentTo);

        // Construire la période de comparaison STRICTEMENT à partir de la courante
        // Règle absolue :
        // comparison.to = current.from - 1 seconde
        // comparison.length == current.length

        $comparisonTo = $currentFrom->modify('-1 second');

        $comparisonFrom = $comparisonTo
            ->sub(new DateInterval('P' . ($currentPeriod->lengthInDays() - 1) . 'D'))
            ->setTime(0, 0, 0);

        $comparisonPeriod = new Period($comparisonFrom, $comparisonTo);

        return new ResolvedPeriod(
            $currentPeriod,
            $comparisonPeriod,
        );
    }

    private function normalizeFrom(string $date): DateTimeImmutable
    {
        return (new DateTimeImmutable($date))
            ->setTime(0, 0, 0);
    }

    private function normalizeTo(string $date): DateTimeImmutable
    {
        return (new DateTimeImmutable($date))
            ->setTime(23, 59, 59);
    }
}
