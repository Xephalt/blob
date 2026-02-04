Très bien. Là on est dans du concret livrable.
Je vais te donner uniquement les nouveaux fichiers à créer, avec leur contenu minimal propre, en Clean Architecture, compatibles avec ton existant (MetricDto, Trend, PeriodResolver, etc.).

Je n’inclus pas :
	•	les fichiers déjà existants
	•	les controllers globaux non impactés
	•	les DTOs génériques déjà en place

Tu peux copier-coller sans réfléchir.
Le reste (wiring, services.yaml, twig) tu gères.

⸻

🧱 Récap rapide – nouveaux fichiers

Application

src/Application/Admin/Query/TokenUsageMetrics.php
src/Application/Admin/UseCase/GetTokenUsageMetrics.php
src/Application/Admin/Dto/TokenUsageKpiDto.php

Infrastructure

src/Repository/TokenUsageMetricsRepository.php

Delivery (nouvelle action)

src/Controller/Admin/KpiController.php   (MODIFICATION)


⸻

1️⃣ Application/Admin/Query/TokenUsageMetrics.php

👉 Intention
Transporter les paramètres métier, rien d’autre.

<?php

declare(strict_types=1);

namespace App\Application\Admin\Query;

use App\Application\Common\Period\ResolvedPeriod;

final class TokenUsageMetrics
{
    /**
     * @param int[]|null $weekdays
     */
    public function __construct(
        public readonly ResolvedPeriod $period,
        public readonly ?array $weekdays,
        public readonly ?string $model
    ) {}
}


⸻

2️⃣ Application/Admin/Dto/TokenUsageKpiDto.php

👉 Intention
DTO racine de la carte “Consommation tokens”.

<?php

declare(strict_types=1);

namespace App\Application\Admin\Dto;

final class TokenUsageKpiDto
{
    public function __construct(
        public readonly array $inputs,
        public readonly array $outputs,
        public readonly array $currentPeriod,
        public readonly array $comparisonPeriod
    ) {}
}

inputs et outputs contiendront exclusivement des MetricDto.

⸻

3️⃣ Application/Admin/UseCase/GetTokenUsageMetrics.php

👉 Intention
Orchestration métier complète :
	•	appel repository
	•	calcul moyennes
	•	comparaison
	•	construction des MetricDto

<?php

declare(strict_types=1);

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Query\TokenUsageMetrics;
use App\Application\Admin\Dto\TokenUsageKpiDto;
use App\Application\Admin\Dto\MetricDto;
use App\Application\Common\Metrics\Trend;
use App\Repository\TokenUsageMetricsRepository;

final class GetTokenUsageMetrics
{
    public function __construct(
        private readonly TokenUsageMetricsRepository $repository
    ) {}

    public function handle(TokenUsageMetrics $query): TokenUsageKpiDto
    {
        $current = $this->repository->fetch(
            $query->period->current,
            $query->weekdays,
            $query->model
        );

        $previous = $this->repository->fetch(
            $query->period->comparison,
            $query->weekdays,
            $query->model
        );

        $inputs = [
            'total' => $this->metric(
                $current['input_total'],
                $previous['input_total']
            ),
            'avgPerConversation' => $this->metric(
                $current['input_total'] / max(1, $current['conversation_count']),
                $previous['input_total'] / max(1, $previous['conversation_count'])
            ),
            'avgPerMessage' => $this->metric(
                $current['input_total'] / max(1, $current['message_count']),
                $previous['input_total'] / max(1, $previous['message_count'])
            ),
        ];

        $outputs = [
            'total' => $this->metric(
                $current['output_total'],
                $previous['output_total']
            ),
            'avgPerConversation' => $this->metric(
                $current['output_total'] / max(1, $current['conversation_count']),
                $previous['output_total'] / max(1, $previous['conversation_count'])
            ),
            'avgPerMessage' => $this->metric(
                $current['output_total'] / max(1, $current['message_count']),
                $previous['output_total'] / max(1, $previous['message_count'])
            ),
        ];

        return new TokenUsageKpiDto(
            inputs: $inputs,
            outputs: $outputs,
            currentPeriod: [
                'from' => $query->period->current->from->format('Y-m-d'),
                'to'   => $query->period->current->to->format('Y-m-d'),
            ],
            comparisonPeriod: [
                'from' => $query->period->comparison->from->format('Y-m-d'),
                'to'   => $query->period->comparison->to->format('Y-m-d'),
            ]
        );
    }

    private function metric(float $current, float $previous): MetricDto
    {
        $delta = $current - $previous;
        $evolution = $previous === 0.0 ? 0.0 : round(($delta / $previous) * 100, 1);

        return new MetricDto(
            current: round($current, 1),
            previous: round($previous, 1),
            delta: round($delta, 1),
            evolution: $evolution,
            trend: match (true) {
                $delta > 0 => Trend::UP,
                $delta < 0 => Trend::DOWN,
                default => Trend::STABLE,
            }
        );
    }
}


⸻

4️⃣ Repository/TokenUsageMetricsRepository.php

👉 Intention
Retourner des agrégats bruts, point.

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\Common\Period\Period;
use Doctrine\DBAL\Connection;

final class TokenUsageMetricsRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * @return array{
     *   input_total: float,
     *   output_total: float,
     *   message_count: int,
     *   conversation_count: int
     * }
     */
    public function fetch(
        Period $period,
        ?array $weekdays,
        ?string $model
    ): array {
        $conditions = [];
        $params = [
            'from' => $period->from->format('Y-m-d 00:00:00'),
            'to'   => $period->to->format('Y-m-d 23:59:59'),
        ];

        if ($weekdays) {
            $conditions[] = 'WEEKDAY(m.created_at) IN (:weekdays)';
            $params['weekdays'] = $weekdays;
        }

        if ($model) {
            $conditions[] = 'm.model_llm = :model';
            $params['model'] = $model;
        }

        $where = $conditions ? 'AND ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT
                SUM(m.token_input)  AS input_total,
                SUM(m.token_output) AS output_total,
                COUNT(m.id)         AS message_count,
                COUNT(DISTINCT m.conversation_id) AS conversation_count
            FROM message m
            WHERE m.created_at BETWEEN :from AND :to
            $where
        ";

        $result = $this->connection->executeQuery(
            $sql,
            $params,
            [
                'weekdays' => Connection::PARAM_INT_ARRAY,
            ]
        )->fetchAssociative();

        return [
            'input_total' => (float) ($result['input_total'] ?? 0),
            'output_total' => (float) ($result['output_total'] ?? 0),
            'message_count' => (int) ($result['message_count'] ?? 0),
            'conversation_count' => (int) ($result['conversation_count'] ?? 0),
        ];
    }
}


⸻

5️⃣ Controller/Admin/KpiController.php (ajout)

👉 Nouvelle action uniquement.

#[Route('/admin/kpi/token-usage', methods: ['GET'])]
public function tokenUsage(
    Request $request,
    PeriodResolver $periodResolver,
    GetTokenUsageMetrics $useCase
): JsonResponse {
    $period = $periodResolver->resolve(
        $request->query->get('date_from'),
        $request->query->get('date_to')
    );

    $query = new TokenUsageMetrics(
        period: $period,
        weekdays: $request->query->has('weekdays')
            ? array_map('intval', explode(',', $request->query->get('weekdays')))
            : null,
        model: $request->query->get('model')
    );

    return $this->json(
        $useCase->handle($query)
    );
}


⸻

✅ Résultat
	•	✔ Clean Architecture respectée
	•	✔ Aucun doublon avec energy / users
	•	✔ MetricDto, Trend, PeriodResolver réutilisés intelligemment
	•	✔ Facilement testable
	•	✔ Directement branchable à ton Twig

Si tu veux ensuite :
	•	la V1 Twig de cette carte
	•	ou une revue critique ligne par ligne
	•	ou factoriser les cartes KPI

Tu dis.