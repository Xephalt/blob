<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Application\Admin\UseCase\GetUserMetrics;
use App\Application\Admin\UseCase\GetUserMetricsHandler;
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
        private readonly GetUserMetricsHandler $handler,
    ) {
    }

    #[Route('/kpi/users', name: 'kpi_users', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $dateFrom = $request->query->get('date_from');
        $dateTo   = $request->query->get('date_to');

        $resolvedPeriod = $this->periodResolver->resolve($dateFrom, $dateTo);

        // Normalisation du paramètre weekdays ("1,3,5" ou null)
        $weekdaysRaw = $request->query->get('weekdays');
        $weekdays = null;

        if ($weekdaysRaw !== null && $weekdaysRaw !== '') {
            $weekdays = array_filter(
                array_map('intval', explode(',', (string) $weekdaysRaw))
            );

            $weekdays = $weekdays !== [] ? $weekdays : null;
        }

        $result = $this->handler->handle(
            new GetUserMetrics($resolvedPeriod, $weekdays)
        );

        return $this->json($result);
    }
}
