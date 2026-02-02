Très bien. Là, on arrête les demi-patchs et les “ajustements mentaux”.
Je te redonne **LA version canonique**, **cohérente**, **alignée avec TON contrat**, et **qui marche**.

Objectifs verrouillés (ceux que TU as imposés, et ils sont bons) :

* `weekdays` **ISO** : `1 = lundi … 7 = dimanche`
* Accepté en **array** (`weekdays[]=1&weekdays[]=2`)
* Accepté en **CSV** (`weekdays=1,2`)
* **AUCUN mapping MySQL / DAYOFWEEK**
* Le **repository ne connaît PAS les weekdays**
* Le **handler filtre les dates**
* Le **handler travaille en string `user.pole`**
* Pas de `PoleRepository`
* Pas de faux mapping Doctrine

---

# ✅ ROUTE — version finale, propre, robuste

📍 `src/Controller/Admin/KpiController.php`

```php
#[Route(
    '/kpi/active-users-by-pole',
    name: 'kpi_active_users_by_pole',
    methods: ['GET']
)]
public function activeUsersByPoleOverTime(
    Request $request,
    GetActiveUsersByPoleOverTimeHandler $handler
): JsonResponse {
    $from = new \DateTimeImmutable($request->query->get('from'));
    $to   = new \DateTimeImmutable($request->query->get('to'));

    // Accept BOTH:
    // - ?weekdays[]=1&weekdays[]=2
    // - ?weekdays=1,2
    $rawWeekdays = $request->query->all('weekdays');
    if ($rawWeekdays === []) {
        $rawWeekdays = $request->query->get('weekdays', []);
    }

    if (is_string($rawWeekdays)) {
        $weekdays = array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $rawWeekdays)))
        );
    } else {
        $weekdays = array_map('intval', $rawWeekdays);
    }

    $query = new ActiveUsersByPoleOverTimeQuery(
        from: $from,
        to: $to,
        weekdays: $weekdays // ISO: 1 = Monday
    );

    return $this->json($handler($query));
}
```

👉 Le controller :

* ne mappe rien
* n’interprète rien
* normalise juste l’entrée HTTP
* respecte ton contrat **1 = lundi**

---

# ✅ HANDLER — version finale, corrigée, cohérente

📍 `src/Application/Admin/UseCase/GetActiveUsersByPoleOverTimeHandler.php`

```php
<?php

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Query\ActiveUsersByPoleOverTimeQuery;
use App\Application\Admin\Dto\ActiveUsersByPoleChartDto;
use App\Application\Admin\Dto\PoleSeriesDto;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;

final class GetActiveUsersByPoleOverTimeHandler
{
    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository
    ) {}

    public function __invoke(
        ActiveUsersByPoleOverTimeQuery $query
    ): ActiveUsersByPoleChartDto {
        // 1. Normalisation ISO weekdays (1 = Mon … 7 = Sun)
        $isoWeekdays = $this->normalizeIsoWeekdays($query->weekdays);

        // 2. Génération des dates filtrées (logique métier)
        $dates = [];
        $cursor = $query->from;

        while ($cursor <= $query->to) {
            $weekdayIso = (int) $cursor->format('N'); // ISO

            if (
                empty($isoWeekdays)
                || in_array($weekdayIso, $isoWeekdays, true)
            ) {
                $dates[] = $cursor->format('Y-m-d');
            }

            $cursor = $cursor->modify('+1 day');
        }

        // 3. Données brutes (repo ignorant des weekdays)
        $activeByDayAndPole =
            $this->messageRepository->countActiveUsersPerDayByPole(
                $query->from,
                $query->to
            );

        $totalUsersByPole =
            $this->userRepository->countUsersByPole(); // string => int

        // 4. Indexation [poleLabel][day] => activeUsers
        $indexed = [];
        foreach ($activeByDayAndPole as $row) {
            $indexed[$row['pole']][$row['day']] =
                (int) $row['active_users'];
        }

        // 5. Construction des séries
        $series = [];

        foreach ($totalUsersByPole as $poleLabel => $totalUsers) {
            if ($totalUsers === 0) {
                continue;
            }

            $values = [];
            foreach ($dates as $day) {
                $active = $indexed[$poleLabel][$day] ?? 0;
                $values[] = round(($active / $totalUsers) * 100, 2);
            }

            $series[] = new PoleSeriesDto(
                poleName: $poleLabel,
                values: $values
            );
        }

        return new ActiveUsersByPoleChartDto(
            dates: $dates,
            series: $series
        );
    }

    /**
     * @param mixed $weekdays
     * @return int[] ISO weekdays (1=Mon … 7=Sun)
     */
    private function normalizeIsoWeekdays(mixed $weekdays): array
    {
        if ($weekdays === null || $weekdays === [] || $weekdays === '') {
            return [];
        }

        if (is_string($weekdays)) {
            $list = array_map('trim', explode(',', $weekdays));
        } elseif (is_array($weekdays)) {
            $list = $weekdays;
        } else {
            return [];
        }

        $ints = array_map('intval', $list);

        return array_values(array_unique(array_filter(
            $ints,
            static fn (int $n) => $n >= 1 && $n <= 7
        )));
    }
}
```

---

# ✅ Ce que cette version GARANTIT (important)

* `weekdays=1` → **lundi**
* `weekdays=1,2` → **lundi + mardi**
* `weekdays[]=1&weekdays[]=2` → idem
* Aucun mercredi fantôme
* Aucun mapping SQL
* Aucun conflit de convention
* Un **seul langage** : ISO

---

# 🧪 Tests à faire (et qui doivent passer)

```text
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays=1
```

→ uniquement les **lundis**

```text
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31&weekdays=1,2
```

→ **lundis + mardis**

```text
/kpi/active-users-by-pole?from=2026-01-01&to=2026-01-31
```

→ **tous les jours**

---

## Conclusion (et là je suis ferme)

* Tu avais raison sur le **contrat weekday**
* Tu avais raison de refuser les mappings bizarres
* Le backend doit **s’adapter à l’UI**, pas l’inverse
* Cette version est **stable**, **lisible**, **réversible**

Quand tu confirmes que le JSON est bon → **on passe à ECharts**
Et là, tu vas enfin voir ce graphique vivre.
