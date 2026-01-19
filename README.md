<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Application\Admin\UseCase\GetUserMetrics;
use App\Application\Admin\UseCase\GetUserMetricsHandler;
use App\Application\Admin\UseCase\GetMessageMetrics;
use App\Application\Admin\UseCase\GetMessageMetricsHandler;
use App\Application\Common\Period\PeriodResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
final class KpiController extends AbstractController
{
    public function __construct(
        private readonly PeriodResolver $periodResolver,
        private readonly GetUserMetricsHandler $userHandler,
        private readonly GetMessageMetricsHandler $messageHandler,
    ) {
    }

    #[Route('/kpi/users', name: 'kpi_users', methods: ['GET'])]
    public function users(Request $request): JsonResponse
    {
        $resolvedPeriod = $this->resolvePeriod($request);
        $weekdays = $this->resolveWeekdays($request);

        $result = $this->userHandler->handle(
            new GetUserMetrics($resolvedPeriod, $weekdays)
        );

        return $this->json($result);
    }

    #[Route('/kpi/messages', name: 'kpi_messages', methods: ['GET'])]
    public function messages(Request $request): JsonResponse
    {
        $resolvedPeriod = $this->resolvePeriod($request);
        $weekdays = $this->resolveWeekdays($request);

        $result = $this->messageHandler->handle(
            new GetMessageMetrics($resolvedPeriod, $weekdays)
        );

        return $this->json($result);
    }

    /**
     * Résolution centralisée de la période (ISO users/messages)
     */
    private function resolvePeriod(Request $request)
    {
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        return $this->periodResolver->resolve($dateFrom, $dateTo);
    }

    /**
     * Normalisation du paramètre weekdays.
     *
     * Exemples acceptés :
     *  - null
     *  - "1,3,5"
     *
     * @return int[]|null
     */
    private function resolveWeekdays(Request $request): ?array
    {
        $weekdaysRaw = $request->query->get('weekdays');

        if ($weekdaysRaw === null || $weekdaysRaw === '') {
            return null;
        }

        $weekdays = array_filter(
            array_map('intval', explode(',', (string) $weekdaysRaw))
        );

        return $weekdays !== [] ? $weekdays : null;
    }
}
