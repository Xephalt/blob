Très bien. On va faire ça proprement, exhaustivement, et sans zones grises.
Tu veux copier-coller, compiler, appeler la route, voir le JSON. Fin de l’histoire.

Je te donne CHAQUE fichier, dans l’ordre, avec le code exact.
Rien d’elliptique. Rien de “à adapter”.

⸻

🆕 1️⃣ Query

📍 src/Application/Admin/Query/TopConversatorsByMessageCountQuery.php

<?php

namespace App\Application\Admin\Query;

final class TopConversatorsByMessageCountQuery
{
    /**
     * @param int[] $weekdays ISO weekdays (1 = Monday … 7 = Sunday)
     *                        Empty array = all days
     */
    public function __construct(
        public readonly \DateTimeImmutable $from,
        public readonly \DateTimeImmutable $to,
        public readonly array $weekdays
    ) {}
}


⸻

🆕 2️⃣ DTO de sortie (pie chart)

📍 src/Application/Admin/Dto/TopConversatorsChartDto.php

<?php

namespace App\Application\Admin\Dto;

final class TopConversatorsChartDto
{
    /**
     * @param array<int, array{label: string, value: int}> $items
     */
    public function __construct(
        public readonly array $items
    ) {}
}


⸻

🆕 3️⃣ Handler (Use Case)

📍 src/Application/Admin/UseCase/GetTopConversatorsByMessageCountHandler.php

<?php

namespace App\Application\Admin\UseCase;

use App\Application\Admin\Query\TopConversatorsByMessageCountQuery;
use App\Application\Admin\Dto\TopConversatorsChartDto;
use App\Repository\MessageRepository;

final class GetTopConversatorsByMessageCountHandler
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    public function __invoke(
        TopConversatorsByMessageCountQuery $query
    ): TopConversatorsChartDto {
        // 1️⃣ Récupération du volume de messages par entité
        $messagesByPole = $this->messageRepository->countMessagesByPole(
            $query->from,
            $query->to,
            $query->weekdays
        );

        // 2️⃣ Tri décroissant (volume brut)
        arsort($messagesByPole);

        // 3️⃣ Top 5 + Autre
        $top5 = array_slice($messagesByPole, 0, 5, true);
        $rest = array_slice($messagesByPole, 5, null, true);

        $items = [];

        foreach ($top5 as $pole => $count) {
            $items[] = [
                'label' => $pole,
                'value' => $count,
            ];
        }

        if (!empty($rest)) {
            $items[] = [
                'label' => 'Autre',
                'value' => array_sum($rest),
            ];
        }

        return new TopConversatorsChartDto($items);
    }
}


⸻

✏️ 4️⃣ MessageRepository

📍 src/Repository/MessageRepository.php

👉 Ajoute cette méthode (ne touche pas aux autres)

/**
 * @param int[] $weekdays ISO weekdays (1 = Monday … 7 = Sunday)
 * @return array<string, int> poleLabel => messageCount
 */
public function countMessagesByPole(
    \DateTimeImmutable $from,
    \DateTimeImmutable $to,
    array $weekdays = []
): array {
    $qb = $this->createQueryBuilder('m');

    $qb
        ->select(
            'u.pole AS pole',
            'COUNT(m.id) AS message_count'
        )
        ->join('m.conversation', 'c')
        ->join('c.user', 'u')
        ->where('u.pole IS NOT NULL')
        ->andWhere('m.createdAt >= :from')
        ->andWhere('m.createdAt <= :to')
        ->groupBy('u.pole')
        ->setParameter('from', $from)
        ->setParameter('to', $to);

    // Filtre weekday ISO → MySQL DAYOFWEEK
    if (!empty($weekdays)) {
        $qb
            ->andWhere('DAYOFWEEK(m.createdAt) IN (:weekdays)')
            ->setParameter(
                'weekdays',
                array_map(
                    static fn (int $iso): int => ($iso % 7) + 1,
                    $weekdays
                )
            );
    }

    $rows = $qb->getQuery()->getArrayResult();

    $result = [];
    foreach ($rows as $row) {
        $result[$row['pole']] = (int) $row['message_count'];
    }

    return $result;
}

Oui, le mapping ISO → MySQL est ici, et uniquement ici.
Le reste de l’app reste sain.

⸻

✏️ 5️⃣ Controller KPI (route)

📍 src/Controller/Admin/KpiController.php

👉 Ajoute cette route (sans casser les autres)

use App\Application\Admin\Query\TopConversatorsByMessageCountQuery;
use App\Application\Admin\UseCase\GetTopConversatorsByMessageCountHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/kpi/top-conversators',
    name: 'kpi_top_conversators',
    methods: ['GET']
)]
public function topConversators(
    Request $request,
    GetTopConversatorsByMessageCountHandler $handler
): JsonResponse {
    $from = new \DateTimeImmutable($request->query->get('from'));
    $to   = new \DateTimeImmutable($request->query->get('to'));

    // --- Weekdays normalization ---
    // Accept:
    // - weekdays[]=1&weekdays[]=2
    // - weekdays=1,2
    // ISO: 1 = Monday … 7 = Sunday

    $rawWeekdays = $request->query->all('weekdays');
    if ($rawWeekdays === []) {
        $rawWeekdays = $request->query->get('weekdays', []);
    }

    if ($rawWeekdays === null || $rawWeekdays === '') {
        $weekdays = [];
    } elseif (is_string($rawWeekdays)) {
        $weekdays = array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $rawWeekdays)))
        );
    } elseif (is_array($rawWeekdays)) {
        $weekdays = array_map('intval', $rawWeekdays);
    } else {
        $weekdays = [];
    }

    $query = new TopConversatorsByMessageCountQuery(
        from: $from,
        to: $to,
        weekdays: $weekdays
    );

    return $this->json($handler($query));
}


⸻

🧪 6️⃣ URLs de test (à copier-coller)

Tous les jours

/kpi/top-conversators?from=2026-01-01&to=2026-01-31

Lundi seulement

/kpi/top-conversators?from=2026-01-01&to=2026-01-31&weekdays=1

Lundi + mardi

/kpi/top-conversators?from=2026-01-01&to=2026-01-31&weekdays=1,2


⸻

✅ JSON attendu

{
  "items": [
    { "label": "CIB", "value": 12450 },
    { "label": "PF", "value": 9870 },
    { "label": "COMGPE", "value": 8421 },
    { "label": "ARVAL", "value": 7312 },
    { "label": "AM", "value": 6980 },
    { "label": "Autre", "value": 15420 }
  ]
}


⸻

Dernier mot (et après je te lâche)

Ce que tu viens de monter :
	•	est cohérent métier
	•	est Clean Architecture réel, pas décoratif
	•	est extensible sans refactor
	•	te permet de dériver la version pondérée sans tout casser

Quand tu veux, on enchaîne sur :
	•	la version pondérée (messages / user / entité)
	•	ou ECharts pie config ultra propre (tooltip qui explique “Autre”, légende claire)

Mais là, franchement : c’est du solide.