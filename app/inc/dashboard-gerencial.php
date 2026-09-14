<?php
$selectedEstablishmentId = (int) ($_GET['establishment_id'] ?? 0);
$filters = [
    'month' => $_GET['month'] ?? date('Y-m'),
    'field_id' => $_GET['field_id'] ?? 0,
];
$dashboard = Finance::getMonthlyAnalyticsData($selectedEstablishmentId, $filters);
$isSuperAdmin = (bool) ($dashboard['is_superadmin'] ?? false);
$canView = (bool) ($dashboard['can_view'] ?? false);
$featureEnabled = (bool) ($dashboard['feature_enabled'] ?? true);
$selectedEstablishmentId = (int) ($dashboard['selected_establishment_id'] ?? 0);
$selectedEstablishment = $dashboard['selected_establishment'] ?? null;
$establishments = $dashboard['establishments'] ?? [];
$fields = $dashboard['fields'] ?? [];
$selectedMonth = (string) ($dashboard['filters']['month'] ?? date('Y-m'));
$selectedFieldId = (int) ($dashboard['filters']['field_id'] ?? 0);
$stats = $dashboard['stats'] ?? [];
$dailySeries = $dashboard['daily_series'] ?? [];
$paymentMethodSummary = $stats['payment_method_summary'] ?? [];
$occupancy = $stats['occupancy'] ?? ['summary' => [], 'by_field' => [], 'by_bucket' => [], 'top_slots' => []];
$operational = $stats['operational'] ?? ['booking' => [], 'waitlist' => []];
$commercial = $stats['commercial'] ?? ['payment_mix' => [], 'field_revenue_rank' => [], 'establishment_revenue_rank' => []];
$signals = $stats['signals'] ?? [];
$insights = $stats['insights'] ?? [];
$establishmentRanking = $dashboard['establishment_ranking'] ?? [];
$comparison = $dashboard['comparison'] ?? ['metrics' => [], 'current_month' => $selectedMonth, 'previous_month' => $selectedMonth];
$monthLabel = date('m/Y', strtotime(($dashboard['from_date'] ?? date('Y-m-01'))));
$previousMonthLabel = date('m/Y', strtotime(($comparison['previous_month'] ?? $selectedMonth) . '-01'));
$comparisonMetrics = $comparison['metrics'] ?? [];
$exportDashboardPdfUrl = 'fetch/exportAnalyticsDashboardPdf?month=' . urlencode($selectedMonth) . '&field_id=' . urlencode((string) $selectedFieldId);
if ($selectedEstablishmentId > 0) {
    $exportDashboardPdfUrl .= '&establishment_id=' . urlencode((string) $selectedEstablishmentId);
}
$renderDelta = static function ($metric) {
    $delta = (float) ($metric['delta'] ?? 0);
    $percent = $metric['delta_percent'] ?? null;
    $class = $delta < 0 ? 'text-danger' : 'text-success';
    $prefix = $delta > 0 ? '+' : '';
    if ($percent === null) {
        return '<span class="' . $class . '">' . $prefix . number_format($delta, 2) . '</span>';
    }
    return '<span class="' . $class . '">' . $prefix . number_format($delta, 2) . ' (' . $prefix . number_format((float) $percent, 2) . '%)</span>';
};
$signalStyleMap = [
    'success' => ['card' => 'bg-light-success', 'text' => 'text-success'],
    'warning' => ['card' => 'bg-light-warning', 'text' => 'text-warning'],
    'danger' => ['card' => 'bg-light-danger', 'text' => 'text-danger'],
];
$insightStyleMap = [
    'success' => ['icon' => 'fa-solid fa-circle-check', 'class' => 'text-success'],
    'warning' => ['icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'text-warning'],
    'danger' => ['icon' => 'fa-solid fa-circle-xmark', 'class' => 'text-danger'],
    'info' => ['icon' => 'fa-solid fa-circle-info', 'class' => 'text-info'],
];
$chartPayload = [
    'dailySeries' => array_map(static function ($row) {
        return [
            'label' => (string) ($row['date_label'] ?? ''),
            'booking' => (float) ($row['booking_income_total'] ?? 0),
            'extra' => (float) ($row['extra_income_total'] ?? 0),
            'expense' => (float) ($row['expense_total'] ?? 0),
            'net' => (float) ($row['operating_net_total'] ?? 0),
        ];
    }, $dailySeries),
    'paymentMix' => array_map(static function ($row) {
        return [
            'label' => (string) ($row['label'] ?? ''),
            'value' => (float) ($row['in_total'] ?? 0),
            'share' => (float) ($row['share_rate'] ?? 0),
        ];
    }, $commercial['payment_mix'] ?? []),
    'occupancyBuckets' => array_map(static function ($row) {
        return [
            'label' => (string) ($row['bucket'] ?? ''),
            'rate' => (float) ($row['occupancy_rate'] ?? 0),
        ];
    }, $occupancy['by_bucket'] ?? []),
    'fieldRevenue' => array_map(static function ($row) {
        return [
            'label' => (string) ($row['field_name'] ?? ''),
            'value' => (float) ($row['total_commercial_total'] ?? 0),
        ];
    }, array_slice($commercial['field_revenue_rank'] ?? [], 0, 7)),
];
?>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <main id="contenido" tabindex="-1" class="content d-flex flex-column flex-column-fluid">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        <div class="d-flex flex-wrap flex-stack mb-8">
                            <div class="d-flex flex-column">
                                <h1 class="text-dark fw-bolder fs-2 mb-2">Dashboard Gerencial</h1>
                                <span class="text-muted fs-6">Seguimiento mensual de ingresos, egresos, caja y resultado operativo.</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="badge badge-light-primary fs-7">Mes analizado: <?php echo htmlspecialchars($monthLabel); ?></span>
                                <button type="button" id="btnExportarDashboardPDF" class="btn btn-light-primary" data-url="<?php echo htmlspecialchars($exportDashboardPdfUrl); ?>">
                                    <i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
                                </button>
                                <button type="button" id="btnImprimirDashboard" class="btn btn-light-secondary">
                                    <i class="fa-solid fa-print me-2"></i>Imprimir
                                </button>
                            </div>
                        </div>

                        <?php if (!$dashboard['ready']) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-triangle-exclamation fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Infraestructura pendiente</h4>
                                    <span>Primero hay que aplicar la migración financiera para habilitar el dashboard gerencial.</span>
                                </div>
                            </div>
                        <?php } elseif (!$canView) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-lock fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Módulo no habilitado</h4>
                                    <span>Este establecimiento no tiene activo mod_analytics.</span>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row g-5 g-xl-8 mb-8">
                            <?php foreach ($signals as $signal) {
                                $signalStyle = $signalStyleMap[(string) ($signal['status'] ?? '')] ?? ['card' => 'bg-light-secondary', 'text' => 'text-dark']; ?>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card <?php echo $signalStyle['card']; ?>">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="text-muted fs-7"><?php echo htmlspecialchars((string) ($signal['label'] ?? 'Indicador')); ?></div>
                                                <span class="badge badge-light <?php echo $signalStyle['text']; ?>"><?php echo htmlspecialchars((string) ($signal['status_label'] ?? 'Sin estado')); ?></span>
                                            </div>
                                            <div class="fw-bold fs-2 <?php echo $signalStyle['text']; ?>"><?php echo htmlspecialchars((string) ($signal['formatted_value'] ?? '0')); ?></div>
                                            <div class="text-muted fs-7 mt-1"><?php echo htmlspecialchars((string) ($signal['context'] ?? '')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="card mb-8">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Insights del mes</span>
                                    <span class="text-muted fw-semibold fs-7">Lectura automatica del estado del negocio segun los umbrales definidos.</span>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-5">
                                    <?php foreach ($insights as $insight) {
                                        $insightStyle = $insightStyleMap[(string) ($insight['type'] ?? 'info')] ?? $insightStyleMap['info']; ?>
                                        <div class="col-xl-6">
                                            <div class="d-flex align-items-start p-5 rounded bg-light">
                                                <i class="<?php echo htmlspecialchars((string) $insightStyle['icon']); ?> fs-2 <?php echo htmlspecialchars((string) $insightStyle['class']); ?> me-4 mt-1"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bolder text-gray-800 mb-1"><?php echo htmlspecialchars((string) ($insight['title'] ?? 'Insight')); ?></span>
                                                    <span class="text-muted fs-7"><?php echo htmlspecialchars((string) ($insight['message'] ?? '')); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-8">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Filtros</span>
                                    <span class="text-muted fw-semibold fs-7">Podés trabajar en consolidado general o bajar al detalle de un establecimiento.</span>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <form method="GET" class="row g-5 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Mes</label>
                                        <input type="month" name="month" class="form-control form-control-solid" value="<?php echo htmlspecialchars($selectedMonth); ?>">
                                    </div>
                                    <?php if ($isSuperAdmin) { ?>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Establecimiento</label>
                                            <select name="establishment_id" class="form-select form-select-solid">
                                                <option value="0">Todos los establecimientos</option>
                                                <?php foreach ($establishments as $establishment) { ?>
                                                    <option value="<?php echo (int) ($establishment->id ?? 0); ?>" <?php echo $selectedEstablishmentId === (int) ($establishment->id ?? 0) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) ($establishment->name ?? 'Establecimiento')); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($fields)) { ?>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Cancha</label>
                                            <select name="field_id" class="form-select form-select-solid">
                                                <option value="0">Todo el establecimiento</option>
                                                <?php foreach ($fields as $field) { ?>
                                                    <option value="<?php echo (int) ($field->id ?? 0); ?>" <?php echo $selectedFieldId === (int) ($field->id ?? 0) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) ($field->name ?? 'Cancha')); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-2 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-light-primary">Aplicar filtros</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-primary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Ingresos reservas</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['booking_income_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Ingresos extra</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['extra_income_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-danger">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Egresos</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['expense_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Resultado operativo</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['operating_net_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Ocupación mensual</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($occupancy['summary']['occupancy_rate'] ?? 0), 2); ?>%</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Caja neta</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['cash_net_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-primary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Entradas caja</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['cash_in_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-danger">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Salidas caja</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['cash_out_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-secondary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Promedio diario neto</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($stats['avg_daily_net_total'] ?? 0), 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-danger">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Tasa cancelación</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($operational['booking']['cancellation_rate'] ?? 0), 2); ?>%</div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($operational['booking']['cancelled_count'] ?? 0); ?> reservas canceladas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">No-show probable</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($operational['booking']['no_show_like_rate'] ?? 0), 2); ?>%</div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($operational['booking']['no_show_like_count'] ?? 0); ?> pendientes vencidas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Conversión waitlist</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($operational['waitlist']['conversion_rate'] ?? 0), 2); ?>%</div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($operational['waitlist']['converted_count'] ?? 0); ?> convertidas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Aceptación ofertas</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($operational['waitlist']['offer_acceptance_rate'] ?? 0), 2); ?>%</div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($operational['waitlist']['accepted_offer_count'] ?? 0); ?> ofertas aceptadas</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-primary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Ticket promedio</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($commercial['avg_ticket_total'] ?? 0), 2); ?></div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($commercial['paid_booking_count'] ?? 0); ?> reservas cobradas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Facturación comercial</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format((float) ($commercial['total_commercial_total'] ?? 0), 2); ?></div>
                                        <div class="text-muted fs-7 mt-1">Reservas + ingresos extra</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Tasa reembolso</div>
                                        <div class="fw-bold fs-2"><?php echo number_format((float) ($commercial['refund_rate'] ?? 0), 2); ?>%</div>
                                        <div class="text-muted fs-7 mt-1"><?php echo (int) ($commercial['refunded_booking_count'] ?? 0); ?> reservas reembolsadas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Ingresos extra</div>
                                        <div class="fw-bold fs-2"><?php echo (int) ($commercial['extra_income_count'] ?? 0); ?></div>
                                        <div class="text-muted fs-7 mt-1">$<?php echo number_format((float) ($commercial['extra_income_total'] ?? 0), 2); ?> acumulados</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-8">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Comparativa mensual</span>
                                    <span class="text-muted fw-semibold fs-7">Variación de <?php echo htmlspecialchars($monthLabel); ?> contra <?php echo htmlspecialchars($previousMonthLabel); ?>.</span>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bolder text-muted">
                                                <th>Métrica</th>
                                                <th class="text-end"><?php echo htmlspecialchars($monthLabel); ?></th>
                                                <th class="text-end"><?php echo htmlspecialchars($previousMonthLabel); ?></th>
                                                <th class="text-end">Variación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Ingresos reservas</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['booking_income_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['booking_income_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['booking_income_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Ingresos extra</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['extra_income_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['extra_income_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['extra_income_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Egresos</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['expense_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['expense_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['expense_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Resultado operativo</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['operating_net_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['operating_net_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['operating_net_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Caja neta</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['cash_net_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['cash_net_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['cash_net_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Ocupación</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['occupancy_rate']['current'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['occupancy_rate']['previous'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['occupancy_rate'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tasa cancelación</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['cancellation_rate']['current'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['cancellation_rate']['previous'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['cancellation_rate'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>No-show probable</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['no_show_like_rate']['current'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['no_show_like_rate']['previous'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['no_show_like_rate'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Conversión waitlist</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['waitlist_conversion_rate']['current'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['waitlist_conversion_rate']['previous'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['waitlist_conversion_rate'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Ticket promedio</td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['avg_ticket_total']['current'] ?? 0), 2); ?></td>
                                                <td class="text-end">$<?php echo number_format((float) ($comparisonMetrics['avg_ticket_total']['previous'] ?? 0), 2); ?></td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['avg_ticket_total'] ?? []); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tasa reembolso</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['refund_rate']['current'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo number_format((float) ($comparisonMetrics['refund_rate']['previous'] ?? 0), 2); ?>%</td>
                                                <td class="text-end"><?php echo $renderDelta($comparisonMetrics['refund_rate'] ?? []); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-8">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Tendencia comercial visual</span>
                                            <span class="text-muted fw-semibold fs-7">Evolución diaria de ingresos, egresos y resultado neto.</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-commercial-trend" style="min-height: 340px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Mix de pagos</span>
                                            <span class="text-muted fw-semibold fs-7">Participación de cada método sobre los ingresos del mes.</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-payment-mix" style="min-height: 340px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Visual de ocupación</span>
                                            <span class="text-muted fw-semibold fs-7">Comparativa por franja para detectar huecos comerciales.</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-occupancy-bucket" style="min-height: 320px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Top facturación por cancha</span>
                                            <span class="text-muted fw-semibold fs-7">Canchas con mejor rendimiento comercial dentro del período.</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-field-revenue" style="min-height: 320px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Riesgo operativo por cancha</span>
                                            <span class="text-muted fw-semibold fs-7">Combina cancelaciones y pendientes vencidas para detectar canchas inestables.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($operational['booking']['by_field'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay reservas en el período para medir riesgo operativo.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Cancha</th>
                                                            <th class="text-end">Reservas</th>
                                                            <th class="text-end">Canceladas</th>
                                                            <th class="text-end">No-show</th>
                                                            <th class="text-end">Reagendadas</th>
                                                            <th class="text-end">Riesgo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($operational['booking']['by_field'] ?? []) as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['field_name'] ?? 'Cancha')); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['total_bookings'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['cancelled_count'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['no_show_like_count'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['rescheduled_count'] ?? 0); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['risk_rate'] ?? 0) >= 20) ? 'text-danger' : 'text-success'; ?>">
                                                                    <?php echo number_format((float) ($row['risk_rate'] ?? 0), 2); ?>%
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Waitlist por canal</span>
                                            <span class="text-muted fw-semibold fs-7">Mide qué origen convierte mejor en reservas concretadas.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (!(bool) ($operational['waitlist']['ready'] ?? false)) { ?>
                                            <div class="text-muted py-10 text-center">La infraestructura de lista de espera todavía no está disponible.</div>
                                        <?php } elseif (empty($operational['waitlist']['by_channel'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay actividad de lista de espera para resumir por canal.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Canal</th>
                                                            <th class="text-end">Entradas</th>
                                                            <th class="text-end">Convertidas</th>
                                                            <th class="text-end">Expiradas</th>
                                                            <th class="text-end">Canceladas</th>
                                                            <th class="text-end">Conversión</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($operational['waitlist']['by_channel'] ?? []) as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['channel'] ?? 'sin_canal')); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['total_entries'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['converted_count'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['expired_count'] ?? 0); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['cancelled_count'] ?? 0); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['conversion_rate'] ?? 0) < 20) ? 'text-danger' : 'text-success'; ?>">
                                                                    <?php echo number_format((float) ($row['conversion_rate'] ?? 0), 2); ?>%
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isSuperAdmin && $selectedEstablishmentId === 0) { ?>
                            <div class="card mb-8">
                                <div class="card-header border-0 pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="card-label fw-bolder fs-3 mb-1">Ranking por establecimiento</span>
                                        <span class="text-muted fw-semibold fs-7">Comparativa mensual entre establecimientos.</span>
                                    </div>
                                </div>
                                <div class="card-body py-3">
                                    <?php if (empty($establishmentRanking)) { ?>
                                        <div class="text-muted py-10 text-center">No hay establecimientos para comparar.</div>
                                    <?php } else { ?>
                                        <div class="table-responsive">
                                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                <thead>
                                                    <tr class="fw-bolder text-muted">
                                                        <th>Establecimiento</th>
                                                        <th>Plan</th>
                                                        <th class="text-end">Reservas</th>
                                                        <th class="text-end">Extra</th>
                                                        <th class="text-end">Egresos</th>
                                                        <th class="text-end">Resultado</th>
                                                        <th class="text-end">Caja neta</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($establishmentRanking as $row) { ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-bolder text-gray-800"><?php echo htmlspecialchars((string) ($row['name'] ?? 'Establecimiento')); ?></span>
                                                                    <span class="text-muted fs-7">
                                                                        <?php echo htmlspecialchars((string) ($row['subscription_status'] ?? 'sin estado')); ?>
                                                                        <?php if (!(bool) ($row['analytics_enabled'] ?? false)) { ?>
                                                                            | mod_analytics inactivo
                                                                        <?php } ?>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars((string) ($row['plan_name'] ?? 'Sin plan')); ?></td>
                                                            <td class="text-end">$<?php echo number_format((float) ($row['booking_income_total'] ?? 0), 2); ?></td>
                                                            <td class="text-end">$<?php echo number_format((float) ($row['extra_income_total'] ?? 0), 2); ?></td>
                                                            <td class="text-end text-danger">$<?php echo number_format((float) ($row['expense_total'] ?? 0), 2); ?></td>
                                                            <td class="text-end fw-bold <?php echo ((float) ($row['operating_net_total'] ?? 0) < 0) ? 'text-danger' : 'text-success'; ?>">
                                                                $<?php echo number_format((float) ($row['operating_net_total'] ?? 0), 2); ?>
                                                            </td>
                                                            <td class="text-end fw-bold <?php echo ((float) ($row['cash_net_total'] ?? 0) < 0) ? 'text-danger' : 'text-info'; ?>">
                                                                $<?php echo number_format((float) ($row['cash_net_total'] ?? 0), 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Ranking comercial por cancha</span>
                                            <span class="text-muted fw-semibold fs-7">Ventas netas, ticket y extra income por unidad operativa.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($commercial['field_revenue_rank'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay datos comerciales por cancha para este período.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Cancha</th>
                                                            <th class="text-end">Reservas</th>
                                                            <th class="text-end">Extra</th>
                                                            <th class="text-end">Ticket</th>
                                                            <th class="text-end">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($commercial['field_revenue_rank'] ?? []) as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['field_name'] ?? 'Cancha')); ?></td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['booking_income_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['extra_income_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['avg_ticket_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end fw-bold text-success">$<?php echo number_format((float) ($row['total_commercial_total'] ?? 0), 2); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Ranking comercial por establecimiento</span>
                                            <span class="text-muted fw-semibold fs-7">Muy útil para `superAdmin` al comparar planes y performance global.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (!$isSuperAdmin || $selectedEstablishmentId !== 0) { ?>
                                            <div class="text-muted py-10 text-center">Disponible en vista consolidada de `superAdmin`.</div>
                                        <?php } elseif (empty($commercial['establishment_revenue_rank'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay datos comerciales por establecimiento para este período.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Establecimiento</th>
                                                            <th>Plan</th>
                                                            <th class="text-end">Ticket</th>
                                                            <th class="text-end">Reembolsos</th>
                                                            <th class="text-end">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($commercial['establishment_revenue_rank'] ?? []) as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['name'] ?? 'Establecimiento')); ?></td>
                                                                <td><?php echo htmlspecialchars((string) ($row['plan_name'] ?? 'Sin plan')); ?></td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['avg_ticket_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end"><?php echo (int) ($row['refunded_booking_count'] ?? 0); ?></td>
                                                                <td class="text-end fw-bold text-success">$<?php echo number_format((float) ($row['total_commercial_total'] ?? 0), 2); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Ocupación por cancha</span>
                                            <span class="text-muted fw-semibold fs-7">Capacidad configurada del mes frente a reservas tomadas.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($occupancy['by_field'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay capacidad configurada para medir ocupación.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Cancha</th>
                                                            <th class="text-end">Capacidad</th>
                                                            <th class="text-end">Ocupado</th>
                                                            <th class="text-end">Ocupación</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($occupancy['by_field'] as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['field_name'] ?? 'Cancha')); ?></td>
                                                                <td class="text-end"><?php echo number_format((float) ($row['capacity_units'] ?? 0), 0); ?></td>
                                                                <td class="text-end"><?php echo number_format((float) ($row['occupied_units'] ?? 0), 0); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['occupancy_rate'] ?? 0) < 40) ? 'text-danger' : 'text-success'; ?>">
                                                                    <?php echo number_format((float) ($row['occupancy_rate'] ?? 0), 2); ?>%
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Ocupación por franja</span>
                                            <span class="text-muted fw-semibold fs-7">Ayuda a detectar dónde conviene empujar promos o subir demanda.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($occupancy['by_bucket'])) { ?>
                                            <div class="text-muted py-10 text-center">No hay franjas para resumir en este período.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Franja</th>
                                                            <th class="text-end">Capacidad</th>
                                                            <th class="text-end">Ocupado</th>
                                                            <th class="text-end">Ocupación</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($occupancy['by_bucket'] as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['bucket'] ?? '-')); ?></td>
                                                                <td class="text-end"><?php echo number_format((float) ($row['capacity_units'] ?? 0), 0); ?></td>
                                                                <td class="text-end"><?php echo number_format((float) ($row['occupied_units'] ?? 0), 0); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['occupancy_rate'] ?? 0) < 40) ? 'text-danger' : 'text-success'; ?>">
                                                                    <?php echo number_format((float) ($row['occupancy_rate'] ?? 0), 2); ?>%
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-8">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Horarios más demandados</span>
                                    <span class="text-muted fw-semibold fs-7">Top de combinaciones cancha-hora según nivel de ocupación del mes.</span>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <?php if (empty($occupancy['top_slots'])) { ?>
                                    <div class="text-muted py-10 text-center">No hay suficientes datos para destacar horarios.</div>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bolder text-muted">
                                                    <th>Cancha</th>
                                                    <th>Hora</th>
                                                    <th>Franja</th>
                                                    <th class="text-end">Capacidad</th>
                                                    <th class="text-end">Ocupado</th>
                                                    <th class="text-end">Ocupación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($occupancy['top_slots'] as $row) { ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) ($row['field_name'] ?? 'Cancha')); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($row['hour12'] ?? $row['hour'] ?? '-')); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($row['bucket'] ?? '-')); ?></td>
                                                        <td class="text-end"><?php echo number_format((float) ($row['capacity_units'] ?? 0), 0); ?></td>
                                                        <td class="text-end"><?php echo number_format((float) ($row['occupied_units'] ?? 0), 0); ?></td>
                                                        <td class="text-end fw-bold <?php echo ((float) ($row['occupancy_rate'] ?? 0) < 40) ? 'text-danger' : 'text-success'; ?>">
                                                            <?php echo number_format((float) ($row['occupancy_rate'] ?? 0), 2); ?>%
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8">
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Resumen por método</span>
                                            <span class="text-muted fw-semibold fs-7">Mide la participación de cada método en el mes.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($paymentMethodSummary)) { ?>
                                            <div class="text-muted py-10 text-center">Sin datos para los filtros seleccionados.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Método</th>
                                                            <th class="text-end">Entradas</th>
                                                            <th class="text-end">Salidas</th>
                                                            <th class="text-end">Neto</th>
                                                            <th class="text-end">Mov.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($paymentMethodSummary as $row) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($row['label'] ?? '-')); ?></td>
                                                                <td class="text-end text-success">$<?php echo number_format((float) ($row['in_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end text-danger">$<?php echo number_format((float) ($row['out_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['net_total'] ?? 0) < 0) ? 'text-danger' : 'text-success'; ?>">
                                                                    $<?php echo number_format((float) ($row['net_total'] ?? 0), 2); ?>
                                                                </td>
                                                                <td class="text-end"><?php echo (int) ($row['count'] ?? 0); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Tendencia diaria</span>
                                            <span class="text-muted fw-semibold fs-7">Detalle día por día para detectar picos, fugas y semanas flojas.</span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if (empty($dailySeries)) { ?>
                                            <div class="text-muted py-10 text-center">No hay actividad mensual para mostrar.</div>
                                        <?php } else { ?>
                                            <div class="table-responsive">
                                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th>Día</th>
                                                            <th class="text-end">Reservas</th>
                                                            <th class="text-end">Extra</th>
                                                            <th class="text-end">Egresos</th>
                                                            <th class="text-end">Operativo</th>
                                                            <th class="text-end">Caja</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($dailySeries as $row) { ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex flex-column">
                                                                        <span class="fw-bolder text-gray-800"><?php echo htmlspecialchars((string) ($row['date_label'] ?? '-')); ?></span>
                                                                        <span class="text-muted fs-7"><?php echo htmlspecialchars((string) ($row['day_name'] ?? '')); ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['booking_income_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end">$<?php echo number_format((float) ($row['extra_income_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end text-danger">$<?php echo number_format((float) ($row['expense_total'] ?? 0), 2); ?></td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['operating_net_total'] ?? 0) < 0) ? 'text-danger' : 'text-success'; ?>">
                                                                    $<?php echo number_format((float) ($row['operating_net_total'] ?? 0), 2); ?>
                                                                </td>
                                                                <td class="text-end fw-bold <?php echo ((float) ($row['cash_net_total'] ?? 0) < 0) ? 'text-danger' : 'text-info'; ?>">
                                                                    $<?php echo number_format((float) ($row['cash_net_total'] ?? 0), 2); ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script id="dashboard-gerencial-data" type="application/json"><?php echo json_encode($chartPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
                    </div>
                </div>
            </main>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
