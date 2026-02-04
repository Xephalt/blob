Très bien. Maintenant on arrête de discuter théorie et on **livre les fichiers**, propres, exploitables, **alignés avec ton existant** (`PeriodResolver`, `MetricDto`, `Trend`, etc.), et **robustes** (raw + display + évolution).

Je vais te donner **uniquement les fichiers nécessaires**, avec **leur rôle exact**, et **leur contenu**.
Tu peux copier-coller sans réfléchir. L’UI sera stupide. Le métier pourra changer d’avis. Le backend tiendra.

---

# 🧱 Vue d’ensemble des fichiers à AJOUTER

```
src/
├── Application/
│   └── Admin/
│       ├── Query/
│       │   └── EnergyUsageMetricsQuery.php
│       ├── Dto/
│       │   ├── MetricDisplayDto.php
│       │   └── EnergyUsageKpiDto.php
│       └── UseCase/
│           └── GetEnergyUsageMetricsHandler.php
│
├── Repository/
│   └── EnergyUsageMetricsRepository.php
│
└── Controller/
    └── Admin/
        └── KpiController.php   (à modifier : ajout d’une action)
```

👉 **Tout le reste est réutilisé tel quel** :

* `MetricDto`
* `Trend`
* `Period`
* `ResolvedPeriod`
* `PeriodResolver`

---

# 1️⃣ Query

📄 `src/Application/Admin/Query/EnergyUsageMetricsQuery.php`

> Porte **l’intention métier**, rien d’autre.

```php
<?php

namespace App\Application\Admin\Query;

use App\Application\Common\Period\ResolvedPeriod;

final class EnergyUsageMetricsQuery
{
    /**
     * @param int[] $weekdays
     * @param string[] $models
     */
    public function __construct(
        public readonly ResolvedPeriod $period,
        public readonly array $weekdays,
        public readonly array $models
    ) {}
}
```

---

# 2️⃣ DTO d’affichage (robuste)

📄 `src/Application/Admin/Dto/MetricDisplayDto.php`

> Ce DTO rend l’UI **complètement bête**.

```php
<?php

namespace App\Application\Admin\Dto;

final class MetricDisplayDto
{
    public function __construct(
        public readonly string $current,
        public readonly string $previous,
        public readonly string $delta,
        public readonly string $evolution,
        public readonly string $unit
    ) {}
}
```

---

# 3️⃣ DTO final de la carte

📄 `src/Application/Admin/Dto/EnergyUsageKpiDto.php`

> Agrégat final retourné par l’API.

```php
<?php

namespace App\Application\Admin\Dto;

use App\Application\Common\Metrics\MetricDto;

final class EnergyUsageKpiDto
{
    public function __construct(
        public readonly MetricDto $carbonImpact,
        public readonly MetricDisplayDto $carbonImpactDisplay,

        public readonly MetricDto $averagePerConversation,
        public readonly MetricDisplayDto $averagePerConversationDisplay,

        public readonly MetricDto $energyPerToken,
        public readonly MetricDisplayDto $energyPerTokenDisplay,

        public readonly array $currentPeriod,
        public readonly array $comparisonPeriod
    ) {}
}
```

---

# 4️⃣ Use Case

📄 `src/Application/Admin/UseCase/GetEnergyUsageMetricsHandler.php`

> **Cœur métier** : comparaison, unités, arrondis, DTO final.

```php
<?php

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Query\EnergyUsageMetricsQuery;
use App\Application\Admin\Dto\EnergyUsageKpiDto;
use App\Application\Admin\Dto\MetricDisplayDto;
use App\Application\Common\Metrics\MetricDto;
use App\Application\Common\Metrics\Trend;
use App\Repository\EnergyUsageMetricsRepository;

final class GetEnergyUsageMetricsHandler
{
    public function __construct(
        private readonly EnergyUsageMetricsRepository $repository
    ) {}

    public function handle(EnergyUsageMetricsQuery $query): EnergyUsageKpiDto
    {
        $current = $this->repository->fetchForPeriod(
            $query->period->current(),
            $query->weekdays,
            $query->models
        );

        $previous = $this->repository->fetchForPeriod(
            $query->period->comparison(),
            $query->weekdays,
            $query->models
        );

        [$carbonMetric, $carbonDisplay] =
            $this->buildMetric($current['carbon_kg'], $previous['carbon_kg'], 'co2');

        [$avgMetric, $avgDisplay] =
            $this->buildMetric($current['avg_carbon_per_conversation_kg'], $previous['avg_carbon_per_conversation_kg'], 'co2');

        [$energyMetric, $energyDisplay] =
            $this->buildMetric($current['energy_per_token_kwh'], $previous['energy_per_token_kwh'], 'energy');

        return new EnergyUsageKpiDto(
            carbonImpact: $carbonMetric,
            carbonImpactDisplay: $carbonDisplay,

            averagePerConversation: $avgMetric,
            averagePerConversationDisplay: $avgDisplay,

            energyPerToken: $energyMetric,
            energyPerTokenDisplay: $energyDisplay,

            currentPeriod: [
                'from' => $query->period->current()->from()->format('Y-m-d'),
                'to'   => $query->period->current()->to()->format('Y-m-d'),
            ],
            comparisonPeriod: [
                'from' => $query->period->comparison()->from()->format('Y-m-d'),
                'to'   => $query->period->comparison()->to()->format('Y-m-d'),
            ]
        );
    }

    private function buildMetric(float $current, float $previous, string $type): array
    {
        $delta = $current - $previous;
        $evolution = $previous == 0.0 ? 0.0 : round(($delta / $previous) * 100, 1);

        $trend = $delta > 0 ? Trend::UP : ($delta < 0 ? Trend::DOWN : Trend::STABLE);

        $metric = new MetricDto(
            current: $current,
            previous: $previous,
            delta: $delta,
            evolution: $evolution,
            trend: $trend
        );

        $display = $this->buildDisplay($current, $previous, $delta, $evolution, $type);

        return [$metric, $display];
    }

    private function buildDisplay(
        float $current,
        float $previous,
        float $delta,
        float $evolution,
        string $type
    ): MetricDisplayDto {
        if ($type === 'co2') {
            [$c, $p, $d, $unit] = $this->normalizeCo2($current, $previous, $delta);
        } else {
            $c = $this->round($current, 6);
            $p = $this->round($previous, 6);
            $d = $this->round($delta, 6);
            $unit = 'kWh';
        }

        return new MetricDisplayDto(
            current: $c,
            previous: $p,
            delta: $this->signed($d),
            evolution: $this->signed($evolution) . '%',
            unit: $unit
        );
    }

    private function normalizeCo2(float $c, float $p, float $d): array
    {
        if (abs($c) < 1) {
            return [
                $this->round($c * 1000, 3),
                $this->round($p * 1000, 3),
                $this->round($d * 1000, 3),
                'gCO2eq'
            ];
        }

        return [
            $this->round($c, 2),
            $this->round($p, 2),
            $this->round($d, 2),
            'kgCO2eq'
        ];
    }

    private function round(float $value, int $precision): string
    {
        return rtrim(rtrim(number_format($value, $precision, '.', ''), '0'), '.');
    }

    private function signed(float|string $value): string
    {
        return ($value > 0 ? '+' : '') . $value;
    }
}
```

---

# 5️⃣ Repository (SQL brut, propre)

📄 `src/Repository/EnergyUsageMetricsRepository.php`

> Retourne **uniquement des bruts canoniques**.

```php
<?php

namespace App\Repository;

use App\Application\Common\Period\Period;
use Doctrine\DBAL\Connection;

final class EnergyUsageMetricsRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * @param int[] $weekdays
     * @param string[] $models
     */
    public function fetchForPeriod(
        Period $period,
        array $weekdays,
        array $models
    ): array {
        $sql = "
            SELECT
                SUM(eu.kgco2eq_usage) AS carbon_kg,
                SUM(eu.kgco2eq_usage) / NULLIF(COUNT(DISTINCT m.conversation_id), 0) AS avg_carbon_per_conversation_kg,
                SUM(eu.kwh_usage) / NULLIF(SUM(m.token_input + m.token_output), 0) AS energy_per_token_kwh
            FROM energy_usage eu
            JOIN message m ON m.id = eu.message_id
            WHERE m.created_at BETWEEN :from AND :to
              AND WEEKDAY(m.created_at) IN (:weekdays)
              AND eu.model IN (:models)
        ";

        return $this->connection->executeQuery(
            $sql,
            [
                'from' => $period->from()->format('Y-m-d 00:00:00'),
                'to' => $period->to()->format('Y-m-d 23:59:59'),
                'weekdays' => $weekdays,
                'models' => $models,
            ],
            [
                'weekdays' => Connection::PARAM_INT_ARRAY,
                'models' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAssociative() ?: [
            'carbon_kg' => 0.0,
            'avg_carbon_per_conversation_kg' => 0.0,
            'energy_per_token_kwh' => 0.0,
        ];
    }
}
```

---

# 6️⃣ Controller (ajout d’une action)

📄 `src/Controller/Admin/KpiController.php`

```php
#[Route('/admin/kpi/energy-usage', methods: ['GET'])]
public function energyUsage(
    Request $request,
    PeriodResolver $periodResolver,
    GetEnergyUsageMetricsHandler $handler
): JsonResponse {
    $period = $periodResolver->resolve(
        $request->query->get('date_from'),
        $request->query->get('date_to')
    );

    $weekdays = array_map('intval', explode(',', $request->query->get('weekdays', '')));
    $models = array_map('trim', explode(',', $request->query->get('model', '')));

    $query = new EnergyUsageMetricsQuery(
        period: $period,
        weekdays: $weekdays,
        models: $models
    );

    return $this->json($handler->handle($query));
}
```

---

## ✅ Résultat

* DTO **robuste**
* backend **responsable**
* UI **stupide**
* métier **libre de changer d’avis**
* aucune dette structurelle

Tu peux brancher, tester, ship.
Oui, c’est un peu long. Mais au moins, **personne ne te redemandera “juste une petite modif” qui casse tout**.
