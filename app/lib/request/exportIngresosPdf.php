<?php

require '../../int.php';

function resolveTcpdfPath()
{
    $candidates = [
        __DIR__ . '/../third_party/tcpdf/tcpdf.php',
        __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php',
        '/usr/share/php/tcpdf/tcpdf.php',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

$tcpdfPath = resolveTcpdfPath();
if ($tcpdfPath === null) {
    http_response_code(500);
    die('TCPDF no esta instalado en el servidor.');
}
require_once $tcpdfPath;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function parseReportDate($rawDate)
{
    $raw = trim((string) $rawDate);
    if ($raw === '') {
        return date('Y-m-d');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return $raw;
    }
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $raw)) {
        $parts = explode('/', $raw);
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
    return date('Y-m-d');
}

function esc($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($value)
{
    $n = (float) $value;
    $prefix = $n < 0 ? '-$' : '$';
    return $prefix . number_format(abs($n), 2, ',', '.');
}

$scope = strtolower(trim((string) ($_GET['scope'] ?? 'mi')));
$date = parseReportDate($_GET['date'] ?? date('Y-m-d'));
$user = Users::getById($_SESSION['canchero']);

$params = [':report_date' => $date];
$whereField = '';
$scopeLabel = 'Mi cancha';

if ($scope === 'admin' && ($user->rol ?? '') === 'superAdmin') {
    $scopeLabel = 'Vista administrativa';
    $requestedField = trim((string) ($_GET['cancha'] ?? '%'));
    if ($requestedField !== '' && $requestedField !== '%') {
        $whereField = ' AND b.id_field = :field_id ';
        $params[':field_id'] = (int) $requestedField;
        $scopeLabel = 'Cancha #' . (int) $requestedField;
        $fieldName = query('SELECT full_name FROM soccer_field WHERE id = ?', '', [(int) $requestedField]);
        if (!empty($fieldName->full_name)) {
            $scopeLabel = (string) $fieldName->full_name;
        }
    }
} else {
    $whereField = ' AND b.id_field = :field_id ';
    $params[':field_id'] = (int) Users::infoUser('id_field');
    $fieldName = query('SELECT full_name FROM soccer_field WHERE id = ?', '', [(int) Users::infoUser('id_field')]);
    if (!empty($fieldName->full_name)) {
        $scopeLabel = (string) $fieldName->full_name;
    }
}

$rows = query(
    "SELECT
        b.id AS nroReserva,
        DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) AS date,
        CASE
            WHEN b.payment_status = 'refunded' THEN 'Reembolsado'
            WHEN COALESCE(b.paid_amount, 0) > 0 THEN 'Aprobado'
            ELSE 'Pendiente'
        END AS estado,
        CASE
            WHEN COALESCE(pw.cash_amount, 0) > 0 AND COALESCE(v.method_name, '') <> '' THEN 'Mixto'
            WHEN COALESCE(pw.cash_amount, 0) > 0 THEN 'Efectivo'
            WHEN COALESCE(v.method_name, '') <> '' THEN CONCAT(UCASE(LEFT(v.method_name, 1)), SUBSTRING(v.method_name, 2))
            ELSE 'Online'
        END AS metodo,
        ROUND(
            CASE
                WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                ELSE ABS(COALESCE(b.paid_amount, 0))
            END,
            2
        ) AS signed_total
    FROM booking b
    LEFT JOIN (
        SELECT
            id_booking,
            MAX(date_create) AS last_voucher_date,
            SUBSTRING_INDEX(GROUP_CONCAT(method_name ORDER BY date_create DESC), ',', 1) AS method_name
        FROM vouchers
        GROUP BY id_booking
    ) v ON v.id_booking = b.id
    LEFT JOIN (
        SELECT
            id_booking,
            SUM(amount_payment) AS cash_amount
        FROM payment_app_web
        GROUP BY id_booking
    ) pw ON pw.id_booking = b.id
    WHERE DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) = :report_date
      $whereField
      AND (
          COALESCE(b.paid_amount, 0) > 0
          OR b.payment_status = 'refunded'
      )
    ORDER BY b.id DESC",
    'ARRAY_ALL',
    $params
);

$summaryByDay = [];
$totalIngresos = 0.0;
$totalDevoluciones = 0.0;
$totalNeto = 0.0;
$rowsHtml = '';

foreach (($rows ?: []) as $row) {
    $signedTotal = (float) ($row['signed_total'] ?? 0);
    $dayLabel = showDate((string) ($row['date'] ?? $date));

    if (!isset($summaryByDay[$dayLabel])) {
        $summaryByDay[$dayLabel] = [
            'cantidad' => 0,
            'ingresos' => 0.0,
            'devoluciones' => 0.0,
            'neto' => 0.0,
        ];
    }
    $summaryByDay[$dayLabel]['cantidad']++;
    if ($signedTotal >= 0) {
        $summaryByDay[$dayLabel]['ingresos'] += $signedTotal;
        $totalIngresos += $signedTotal;
    } else {
        $summaryByDay[$dayLabel]['devoluciones'] += abs($signedTotal);
        $totalDevoluciones += abs($signedTotal);
    }
    $summaryByDay[$dayLabel]['neto'] += $signedTotal;
    $totalNeto += $signedTotal;

    $rowsHtml .= '<tr>'
        . '<td>#' . esc($row['nroReserva'] ?? '-') . '</td>'
        . '<td>' . esc($dayLabel) . '</td>'
        . '<td>' . esc($row['estado'] ?? '-') . '</td>'
        . '<td>' . esc($row['metodo'] ?? '-') . '</td>'
        . '<td align="right">' . esc(money($signedTotal)) . '</td>'
        . '</tr>';
}

if ($rowsHtml === '') {
    $rowsHtml = '<tr><td colspan="5" align="center">No hay ingresos para los filtros seleccionados</td></tr>';
}

$summaryRowsHtml = '';
foreach ($summaryByDay as $day => $summary) {
    $summaryRowsHtml .= '<tr>'
        . '<td>' . esc($day) . '</td>'
        . '<td align="right">' . esc((string) $summary['cantidad']) . '</td>'
        . '<td align="right">' . esc(money($summary['ingresos'])) . '</td>'
        . '<td align="right">' . esc(money(-1 * $summary['devoluciones'])) . '</td>'
        . '<td align="right">' . esc(money($summary['neto'])) . '</td>'
        . '</tr>';
}

if ($summaryRowsHtml === '') {
    $summaryRowsHtml = '<tr><td colspan="5" align="center">Sin movimientos</td></tr>';
}

$html = '
<h2 style="font-size:16px; margin:0;">Reporte de Ingresos</h2>
<p style="font-size:10px; color:#4b5563; margin:4px 0 10px 0;">
Fecha: ' . esc(showDate($date)) . ' | Alcance: ' . esc($scopeLabel) . ' | Generado: ' . esc(date('d/m/Y H:i')) . '
</p>
<table cellpadding="5" cellspacing="0" border="1" style="font-size:9px;">
    <tr style="background-color:#f3f4f6; font-weight:bold;">
        <td width="25%">Ganancias</td>
        <td width="25%">Devoluciones</td>
        <td width="25%">Total Neto</td>
        <td width="25%">Movimientos</td>
    </tr>
    <tr>
        <td align="right">' . esc(money($totalIngresos)) . '</td>
        <td align="right">' . esc(money(-1 * $totalDevoluciones)) . '</td>
        <td align="right">' . esc(money($totalNeto)) . '</td>
        <td align="right">' . esc((string) count($rows ?: [])) . '</td>
    </tr>
</table>
<br>
<h3 style="font-size:12px; margin:0 0 6px 0;">Resumen por dia</h3>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.8px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="22%">Dia</td>
        <td width="18%" align="right">Movimientos</td>
        <td width="20%" align="right">Ingresos</td>
        <td width="20%" align="right">Devoluciones</td>
        <td width="20%" align="right">Neto</td>
    </tr>
    ' . $summaryRowsHtml . '
</table>
<br>
<h3 style="font-size:12px; margin:0 0 6px 0;">Detalle de movimientos</h3>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.8px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="18%">N° Reserva</td>
        <td width="20%">Fecha</td>
        <td width="22%">Estado</td>
        <td width="20%">Metodo</td>
        <td width="20%" align="right">Importe</td>
    </tr>
    ' . $rowsHtml . '
</table>
';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('BotCanchero');
$pdf->SetAuthor(COMPANY);
$pdf->SetTitle('Reporte de Ingresos');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 8, 10);
$pdf->SetAutoPageBreak(true, 8);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Ingresos_' . $date . '.pdf', 'D');
exit;
