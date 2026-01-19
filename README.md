Très bien. On arrête les débats métaphysiques et on **clone ton `UserMetricsQuery` à l’identique**, en changeant **uniquement** ce que le métier impose.
Pas une ligne de plus, pas une abstraction “future-proof”, pas de morale.

Ce que tu veux, c’est **MessageMetricsQuery ISO UserMetricsQuery**.
Le voici **complet**, prêt à coller, et aligné ligne par ligne avec ton existant.

---

## `src/Application/Admin/Query/MessageMetricsQuery.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Admin\Query;

use App\Application\Admin\Dto\MessagePeriodMetricsDto;
use App\Application\Admin\Dto\MetricDto;
use App\Application\Admin\Dto\Trend;
use App\Application\Common\Period\ResolvedPeriod;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;

final class MessageMetricsQuery
{
    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @param int[]|null $weekdays
     */
    public function execute(
        ResolvedPeriod $period,
        ?array $weekdays
    ): MessagePeriodMetricsDto {
        // Messages envoyés
        $currentMessages = $this->messageRepository
            ->countMessagesBetween($period->current(), $weekdays);

        $previousMessages = $this->messageRepository
            ->countMessagesBetween($period->comparison(), $weekdays);

        // Utilisateurs actifs (pour la moyenne)
        $currentActiveUsers = $this->messageRepository
            ->countActiveUsersBetween($period->current(), $weekdays);

        $previousActiveUsers = $this->messageRepository
            ->countActiveUsersBetween($period->comparison(), $weekdays);

        // Moyenne messages / utilisateur
        $currentAvg = $currentActiveUsers === 0
            ? 0
            : (int) round($currentMessages / $currentActiveUsers);

        $previousAvg = $previousActiveUsers === 0
            ? 0
            : (int) round($previousMessages / $previousActiveUsers);

        return new MessagePeriodMetricsDto(
            $this->buildMetric($currentMessages, $previousMessages),
            $this->buildMetric($currentAvg, $previousAvg),
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
```

---

## Pourquoi c’est **strictement iso**

* Même namespace
* Même signature de `execute`
* Même logique de `buildMetric`
* Même gestion du `previous === 0`
* Même arrondi
* Même `Trend`
* Même responsabilité (Query = calcul + orchestration)
* Même odeur de code assumée

La seule différence :

* `countMessagesBetween`
* `countActiveUsersBetween`
* calcul de la moyenne

Donc **aucune surprise**, aucun effet de bord, aucun “mais pourquoi c’est différent”.

---

## Ce que tu fais ensuite (sans me demander)

1. Créer `MessagePeriodMetricsDto` calqué sur `UserPeriodMetricsDto`
2. Créer `GetMessageMetrics` (copie)
3. Créer `GetMessageMetricsHandler` (copie)
4. Brancher le controller exactement comme pour Users
5. Aller boire un café en regardant la carte s’afficher

On refactorera **plus tard** le `buildMetric` commun, quand ce ticket sera fermé et que personne ne regarde.

Là, c’est propre **selon TES règles**, pas les miennes.
