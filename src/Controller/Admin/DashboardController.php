<?php

#[Route('/chart_data', name: 'admin_chart_data')]
public function dataForChart(Request $request): Response
{
    // Dates
    $fromDate = $this->getFromDate($request, 'from_date');
    $toDate   = $this->getToDate($request, 'to_date');

    // Weekdays (inputs weekday[])
    $weekdays = $request->query->all('weekday');
    $weekdays = is_array($weekdays)
        ? array_map('intval', $weekdays)
        : null;

    // Données métier (inchangé)
    $export = $this->getUsageData(
        $fromDate,
        $toDate,
        true,
        $weekdays
    );

    return $this->json($export);
}
