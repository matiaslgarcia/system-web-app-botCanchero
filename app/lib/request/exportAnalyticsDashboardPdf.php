<?php

require '../../int.php';

$tcpdfPath = __DIR__ . '/../third_party/tcpdf/tcpdf.php';
if (!file_exists($tcpdfPath)) {
    http_response_code(500);
    die('TCPDF no esta instalado en el servidor.');
}
require_once $tcpdfPath;

function analyticsMonth($rawMonth)
{
    $raw = trim((string) $rawMonth);
    if ($raw !== '' && preg_match('/^\d{4}-\d{2}$/', $raw)) return $raw;
    return date('Y-m');
}

function analyticsEsc($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function analyticsMoney($value)
{
    $n = (float) $value;
    $prefix = $n < 0 ? '-$' : '$';
    return $prefix . number_format(abs($n), 2, ',', '.');
}

function analyticsPercent($value)
{
    return number_format((float) $value, 2, ',', '.') . '%';
}

$selectedEstablishmentId = (int) ($_GET['establishment_id'] ?? 0);
$filters = [
    'month' => analyticsMonth($_GET['month'] ?? date('Y-m')),
    'field_id' => (int) ($_GET['field_id'] ?? 0),
];
$dashboard = Finance::getMonthlyAnalyticsData($selectedEstablishmentId, $filters);

$selectedEstablishmentId = (int) ($dashboard['selected_establishment_id'] ?? 0);
$selectedEstablishment = $dashboard['selected_establishment'] ?? null;
$fields = $dashboard['fields'] ?? [];
$selectedFieldId = (int) ($dashboard['filters']['field_id'] ?? 0);
$month = (string) ($dashboard['filters']['month'] ?? date('Y-m'));
$monthLabel = date('m/Y', strtotime($month . '-01'));
$previousMonthLabel = date('m/Y', strtotime((string) (($dashboard['comparison']['previous_month'] ?? $month)) . '-01'));
$stats = $dashboard['stats'] ?? [];
$signals = $stats['signals'] ?? [];
$insights = $stats['insights'] ?? [];
$comparison = $dashboard['comparison']['metrics'] ?? [];
$operational = $stats['operational'] ?? ['booking' => [], 'waitlist' => []];
$commercial = $stats['commercial'] ?? ['payment_mix' => [], 'field_revenue_rank' => [], 'establishment_revenue_rank' => []];
$occupancy = $stats['occupancy'] ?? ['by_bucket' => []];

$fieldName = 'Todo el alcance';
if ($selectedFieldId > 0) {
    foreach ($fields as $field) {
        if ((int) ($field->id ?? 0) === $selectedFieldId) {
            $fieldName = (string) ($field->name ?? $fieldName);
            break;
        }
    }
}

$signalRowsHtml = '';
foreach ($signals as $signal) {
    $signalRowsHtml .= '<tr>'
        . '<td>' . analyticsEsc($signal['label'] ?? 'Indicador') . '</td>'
        . '<td align="right">' . analyticsEsc($signal['formatted_value'] ?? '0') . '</td>'
        . '<td>' . analyticsEsc($signal['status_label'] ?? 'Sin estado') . '</td>'
        . '<td>' . analyticsEsc($signal['context'] ?? '') . '</td>'
        . '</tr>';
}
if ($signalRowsHtml === '') {
    $signalRowsHtml = '<tr><td colspan="4" align="center">Sin senales disponibles</td></tr>';
}

$insightsHtml = '';
foreach ($insights as $insight) {
    $insightsHtml .= '<tr>'
        . '<td width="22%"><b>' . analyticsEsc($insight['title'] ?? 'Insight') . '</b></td>'
        . '<td width="15%">' . analyticsEsc(strtoupper((string) ($insight['type'] ?? 'info'))) . '</td>'
        . '<td width="63%">' . analyticsEsc($insight['message'] ?? '') . '</td>'
        . '</tr>';
}
if ($insightsHtml === '') {
    $insightsHtml = '<tr><td colspan="3" align="center">Sin insights para el periodo</td></tr>';
}

$comparisonRows = [
    ['Ingresos reservas', analyticsMoney($comparison['booking_income_total']['current'] ?? 0), analyticsMoney($comparison['booking_income_total']['previous'] ?? 0), analyticsMoney($comparison['booking_income_total']['delta'] ?? 0)],
    ['Resultado operativo', analyticsMoney($comparison['operating_net_total']['current'] ?? 0), analyticsMoney($comparison['operating_net_total']['previous'] ?? 0), analyticsMoney($comparison['operating_net_total']['delta'] ?? 0)],
    ['Ocupacion', analyticsPercent($comparison['occupancy_rate']['current'] ?? 0), analyticsPercent($comparison['occupancy_rate']['previous'] ?? 0), analyticsPercent($comparison['occupancy_rate']['delta'] ?? 0)],
    ['Cancelacion', analyticsPercent($comparison['cancellation_rate']['current'] ?? 0), analyticsPercent($comparison['cancellation_rate']['previous'] ?? 0), analyticsPercent($comparison['cancellation_rate']['delta'] ?? 0)],
    ['Waitlist conversion', analyticsPercent($comparison['waitlist_conversion_rate']['current'] ?? 0), analyticsPercent($comparison['waitlist_conversion_rate']['previous'] ?? 0), analyticsPercent($comparison['waitlist_conversion_rate']['delta'] ?? 0)],
    ['Ticket promedio', analyticsMoney($comparison['avg_ticket_total']['current'] ?? 0), analyticsMoney($comparison['avg_ticket_total']['previous'] ?? 0), analyticsMoney($comparison['avg_ticket_total']['delta'] ?? 0)],
];
$comparisonRowsHtml = '';
foreach ($comparisonRows as $row) {
    $comparisonRowsHtml .= '<tr>'
        . '<td>' . analyticsEsc($row[0]) . '</td>'
        . '<td align="right">' . analyticsEsc($row[1]) . '</td>'
        . '<td align="right">' . analyticsEsc($row[2]) . '</td>'
        . '<td align="right">' . analyticsEsc($row[3]) . '</td>'
        . '</tr>';
}

$paymentMixRowsHtml = '';
foreach (($commercial['payment_mix'] ?? []) as $row) {
    $paymentMixRowsHtml .= '<tr>'
        . '<td>' . analyticsEsc($row['label'] ?? '-') . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsMoney($row['in_total'] ?? 0)) . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsPercent($row['share_rate'] ?? 0)) . '</td>'
        . '</tr>';
}
if ($paymentMixRowsHtml === '') {
    $paymentMixRowsHtml = '<tr><td colspan="3" align="center">Sin datos de mix de pagos</td></tr>';
}

$fieldRankHtml = '';
foreach (array_slice(($commercial['field_revenue_rank'] ?? []), 0, 6) as $row) {
    $fieldRankHtml .= '<tr>'
        . '<td>' . analyticsEsc($row['field_name'] ?? 'Cancha') . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsMoney($row['total_commercial_total'] ?? 0)) . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsMoney($row['avg_ticket_total'] ?? 0)) . '</td>'
        . '</tr>';
}
if ($fieldRankHtml === '') {
    $fieldRankHtml = '<tr><td colspan="3" align="center">Sin ranking de canchas</td></tr>';
}

$establishmentRankHtml = '';
foreach (array_slice(($commercial['establishment_revenue_rank'] ?? []), 0, 6) as $row) {
    $establishmentRankHtml .= '<tr>'
        . '<td>' . analyticsEsc($row['name'] ?? 'Establecimiento') . '</td>'
        . '<td>' . analyticsEsc($row['plan_name'] ?? 'Sin plan') . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsMoney($row['total_commercial_total'] ?? 0)) . '</td>'
        . '</tr>';
}
if ($establishmentRankHtml === '') {
    $establishmentRankHtml = '<tr><td colspan="3" align="center">Sin ranking consolidado</td></tr>';
}

$bucketHtml = '';
foreach (($occupancy['by_bucket'] ?? []) as $row) {
    $bucketHtml .= '<tr>'
        . '<td>' . analyticsEsc($row['bucket'] ?? '-') . '</td>'
        . '<td align="right">' . analyticsEsc((string) number_format((float) ($row['capacity_units'] ?? 0), 0, ',', '.')) . '</td>'
        . '<td align="right">' . analyticsEsc((string) number_format((float) ($row['occupied_units'] ?? 0), 0, ',', '.')) . '</td>'
        . '<td align="right">' . analyticsEsc(analyticsPercent($row['occupancy_rate'] ?? 0)) . '</td>'
        . '</tr>';
}
if ($bucketHtml === '') {
    $bucketHtml = '<tr><td colspan="4" align="center">Sin ocupacion por franja</td></tr>';
}

$html = '
<h2 style="font-size:16px; margin:0;">Dashboard Gerencial</h2>
<p style="font-size:10px; color:#4b5563; margin:4px 0 10px 0;">
Mes: ' . analyticsEsc($monthLabel) . ' | Establecimiento: ' . analyticsEsc((string) ($selectedEstablishment['name'] ?? 'Vista consolidada')) . ' | Alcance: ' . analyticsEsc($fieldName) . ' | Generado: ' . analyticsEsc(date('d/m/Y H:i')) . '
</p>
<table cellpadding="5" cellspacing="0" border="1" style="font-size:9px;">
    <tr style="background-color:#f3f4f6; font-weight:bold;">
        <td width="20%" align="right">Resultado</td>
        <td width="20%" align="right">Caja Neta</td>
        <td width="20%" align="right">Ocupacion</td>
        <td width="20%" align="right">Cancelacion</td>
        <td width="20%" align="right">Waitlist</td>
    </tr>
    <tr>
        <td align="right">' . analyticsEsc(analyticsMoney($stats['operating_net_total'] ?? 0)) . '</td>
        <td align="right">' . analyticsEsc(analyticsMoney($stats['cash_net_total'] ?? 0)) . '</td>
        <td align="right">' . analyticsEsc(analyticsPercent($occupancy['summary']['occupancy_rate'] ?? 0)) . '</td>
        <td align="right">' . analyticsEsc(analyticsPercent($operational['booking']['cancellation_rate'] ?? 0)) . '</td>
        <td align="right">' . analyticsEsc(analyticsPercent($operational['waitlist']['conversion_rate'] ?? 0)) . '</td>
    </tr>
</table>
<br>
<h3 style="font-size:12px; margin:0 0 6px 0;">Semaforos</h3>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.5px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="26%">Indicador</td>
        <td width="16%" align="right">Valor</td>
        <td width="16%">Estado</td>
        <td width="42%">Lectura</td>
    </tr>
    ' . $signalRowsHtml . '
</table>
<br>
<h3 style="font-size:12px; margin:0 0 6px 0;">Insights del mes</h3>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.4px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="22%">Insight</td>
        <td width="15%">Tipo</td>
        <td width="63%">Descripcion</td>
    </tr>
    ' . $insightsHtml . '
</table>
<br>
<h3 style="font-size:12px; margin:0 0 6px 0;">Comparativa vs ' . analyticsEsc($previousMonthLabel) . '</h3>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.4px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="28%">Metrica</td>
        <td width="24%" align="right">' . analyticsEsc($monthLabel) . '</td>
        <td width="24%" align="right">' . analyticsEsc($previousMonthLabel) . '</td>
        <td width="24%" align="right">Delta</td>
    </tr>
    ' . $comparisonRowsHtml . '
</table>
<br>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:8.4px;">
    <tr>
        <td width="50%">
            <h3 style="font-size:12px; margin:0 0 6px 0;">Mix de pagos</h3>
            <table cellpadding="4" cellspacing="0" border="1" style="font-size:8.3px;">
                <tr style="background-color:#e5e7eb; font-weight:bold;">
                    <td width="45%">Metodo</td>
                    <td width="30%" align="right">Ingresos</td>
                    <td width="25%" align="right">Participacion</td>
                </tr>
                ' . $paymentMixRowsHtml . '
            </table>
        </td>
        <td width="50%">
            <h3 style="font-size:12px; margin:0 0 6px 0;">Ocupacion por franja</h3>
            <table cellpadding="4" cellspacing="0" border="1" style="font-size:8.3px;">
                <tr style="background-color:#e5e7eb; font-weight:bold;">
                    <td width="28%">Franja</td>
                    <td width="24%" align="right">Capacidad</td>
                    <td width="24%" align="right">Ocupado</td>
                    <td width="24%" align="right">Ocupacion</td>
                </tr>
                ' . $bucketHtml . '
            </table>
        </td>
    </tr>
</table>
<br>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:8.4px;">
    <tr>
        <td width="50%">
            <h3 style="font-size:12px; margin:0 0 6px 0;">Ranking comercial por cancha</h3>
            <table cellpadding="4" cellspacing="0" border="1" style="font-size:8.2px;">
                <tr style="background-color:#e5e7eb; font-weight:bold;">
                    <td width="44%">Cancha</td>
                    <td width="28%" align="right">Facturacion</td>
                    <td width="28%" align="right">Ticket</td>
                </tr>
                ' . $fieldRankHtml . '
            </table>
        </td>
        <td width="50%">
            <h3 style="font-size:12px; margin:0 0 6px 0;">Ranking comercial por establecimiento</h3>
            <table cellpadding="4" cellspacing="0" border="1" style="font-size:8.2px;">
                <tr style="background-color:#e5e7eb; font-weight:bold;">
                    <td width="42%">Establecimiento</td>
                    <td width="22%">Plan</td>
                    <td width="36%" align="right">Facturacion</td>
                </tr>
                ' . $establishmentRankHtml . '
            </table>
        </td>
    </tr>
</table>
';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('BotCanchero');
$pdf->SetAuthor(COMPANY);
$pdf->SetTitle('Dashboard Gerencial');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 8);
$pdf->SetFont('helvetica', '', 8.7);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('DashboardGerencial_' . $month . '.pdf', 'D');
exit;
