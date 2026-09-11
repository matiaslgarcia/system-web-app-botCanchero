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
    return '$' . number_format((float) $value, 2, ',', '.');
}

$fecha = parseReportDate($_GET['fecha'] ?? date('Y-m-d'));
$requestedField = isset($_GET['field_id']) ? (int) $_GET['field_id'] : 0;
$user = Users::getById($_SESSION['canchero']);

$whereBookings = ["b.date_booking = :fecha", "b.status IN (1, 6)"];
$whereRecurring = [
    "rb.status IN ('active', 'pending_payment')",
    "rb.day_of_week = (WEEKDAY(:fecha_rec_dow) + 1)",
    "(rb.valid_from IS NULL OR rb.valid_from <= :fecha_rec_from)",
    "(rb.valid_until IS NULL OR rb.valid_until >= :fecha_rec_until)"
];
$commonParams = [];
$bookingParams = [':fecha' => $fecha];
$recurringParams = [
    ':fecha_rec_dow' => $fecha,
    ':fecha_rec_from' => $fecha,
    ':fecha_rec_until' => $fecha,
    ':fecha_rec_day_select' => $fecha,
    ':fecha_rec_day_exists' => $fecha,
];

if ($user->rol !== 'superAdmin') {
    $field = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [(int) $user->id_field]);
    $estId = (int) ($field->establishment_id ?? 0);
    if ($estId > 0) {
        $whereBookings[] = "f.establishment_id = :est_id";
        $whereRecurring[] = "sf.establishment_id = :est_id";
        $commonParams[':est_id'] = $estId;
    } else {
        $whereBookings[] = "f.id = :my_field_id";
        $whereRecurring[] = "sf.id = :my_field_id";
        $commonParams[':my_field_id'] = (int) $user->id_field;
    }
}

if ($requestedField > 0) {
    if ($user->rol !== 'superAdmin') {
        $allowed = query(
            "SELECT sf.id
               FROM soccer_field sf
               INNER JOIN soccer_field me ON me.id = :my_field_a
              WHERE sf.id = :requested
                AND (
                    sf.id = :my_field_b
                    OR (me.establishment_id IS NOT NULL AND me.establishment_id > 0 AND sf.establishment_id = me.establishment_id)
                )
              LIMIT 1",
            'ARRAY',
            [
                ':my_field_a' => (int) $user->id_field,
                ':my_field_b' => (int) $user->id_field,
                ':requested' => $requestedField,
            ]
        );
        if (!$allowed) {
            $requestedField = 0;
        }
    }
    if ($requestedField > 0) {
        $whereBookings[] = "f.id = :field_id";
        $whereRecurring[] = "sf.id = :field_id";
        $commonParams[':field_id'] = $requestedField;
    }
}

$whereBookingsSql = implode(' AND ', $whereBookings);
$whereRecurringSql = implode(' AND ', $whereRecurring);

$bookings = query(
    "SELECT
        CAST(b.id AS CHAR) AS id,
        b.id_field,
        b.date_booking,
        b.day_booking,
        b.time_booking,
        h.time AS hour_label,
        b.status,
        b.is_fixed,
        b.recurring_booking_id,
        b.source,
        COALESCE(
            NULLIF(b.total_amount, 0),
            (
                SELECT pr.price
                FROM price_ranges pr
                INNER JOIN schedules shp ON shp.id = b.time_booking
                WHERE pr.id_field = f.id
                  AND shp.hour >= pr.start_time
                  AND shp.hour < pr.end_time
                ORDER BY pr.start_time DESC
                LIMIT 1
            ),
            f.price_hour
        ) AS total_amount,
        COALESCE(b.paid_amount, 0) AS paid_amount,
        f.threshold AS threshold,
        f.full_name AS cancha,
        c.full_name AS customer_name,
        c.phone AS customer_phone
     FROM booking b
     INNER JOIN soccer_field f ON f.id = b.id_field
     INNER JOIN customers c ON c.id = b.id_customer
     INNER JOIN schedules h ON h.id = b.time_booking
     WHERE $whereBookingsSql
     ORDER BY h.time",
    'ARRAY_ALL',
    array_merge($bookingParams, $commonParams)
);

$recurringOnly = query(
    "SELECT
        CONCAT('RB-', rb.id) AS id,
        rb.field_id AS id_field,
        :fecha_rec_day_select AS date_booking,
        rb.day_of_week AS day_booking,
        NULL AS time_booking,
        COALESCE(s.time, DATE_FORMAT(rb.start_time, '%H:%i:%s')) AS hour_label,
        1 AS status,
        1 AS is_fixed,
        rb.id AS recurring_booking_id,
        'recurring_planned' AS source,
        COALESCE(
            (
                SELECT pr.price
                FROM price_ranges pr
                WHERE pr.id_field = sf.id
                  AND COALESCE(s.time, rb.start_time) >= pr.start_time
                  AND COALESCE(s.time, rb.start_time) < pr.end_time
                ORDER BY pr.start_time DESC
                LIMIT 1
            ),
            sf.price_hour
        ) AS total_amount,
        0 AS paid_amount,
        sf.threshold AS threshold,
        sf.full_name AS cancha,
        c.full_name AS customer_name,
        c.phone AS customer_phone
     FROM recurring_booking rb
     INNER JOIN soccer_field sf ON sf.id = rb.field_id
     LEFT JOIN customers c ON c.id = rb.customer_id
     LEFT JOIN schedules s ON s.hour = rb.start_time
     WHERE $whereRecurringSql
       AND NOT EXISTS (
            SELECT 1
              FROM recurring_booking_pause p
             WHERE p.recurring_booking_id = rb.id
               AND p.status = 'approved'
               AND :fecha_rec_day_pause BETWEEN p.from_date AND p.to_date
       )
       AND NOT EXISTS (
            SELECT 1
              FROM booking b2
             WHERE b2.recurring_booking_id = rb.id
               AND b2.date_booking = :fecha_rec_day_exists
       )
     ORDER BY rb.start_time",
    'ARRAY_ALL',
    array_merge($recurringParams, $commonParams)
);

$all = array_merge($bookings ?: [], $recurringOnly ?: []);
usort($all, function ($a, $b) {
    return strcmp((string) ($a['hour_label'] ?? ''), (string) ($b['hour_label'] ?? ''));
});

$slotUsage = [];
foreach ($all as $row) {
    $key = (string) ($row['id_field'] ?? '') . '|' . (string) ($row['hour_label'] ?? '');
    if ($key === '|') {
        continue;
    }
    if (!isset($slotUsage[$key])) {
        $slotUsage[$key] = 0;
    }
    $slotUsage[$key]++;
}
$slotIndex = [];

$totalReservas = count($all);
$totalFacturado = 0.0;
$totalPagado = 0.0;
$totalSaldo = 0.0;
$rowsHtml = '';

foreach ($all as $row) {
    $total = (float) ($row['total_amount'] ?? 0);
    $paid = (float) ($row['paid_amount'] ?? 0);
    $saldo = max(0, $total - $paid);
    $estado = ($saldo <= 0) ? 'Pagada' : (($paid > 0) ? 'Parcial' : 'Pendiente');
    $fija = ((int) ($row['is_fixed'] ?? 0) === 1) ? ' (Fija)' : '';

    $slotKey = (string) ($row['id_field'] ?? '') . '|' . (string) ($row['hour_label'] ?? '');
    $slotIndex[$slotKey] = (int) ($slotIndex[$slotKey] ?? 0) + 1;
    $numeroCancha = 'Cancha ' . $slotIndex[$slotKey];

    $totalFacturado += $total;
    $totalPagado += $paid;
    $totalSaldo += $saldo;

    $rowsHtml .= '<tr>'
        . '<td>' . esc($row['hour_label'] ?? '-') . '</td>'
        . '<td>' . esc($numeroCancha) . '</td>'
        . '<td>' . esc(($row['cancha'] ?? '-') . $fija) . '</td>'
        . '<td>' . esc($row['customer_name'] ?? 'Cliente sin nombre') . '</td>'
        . '<td>' . esc($row['customer_phone'] ?? '-') . '</td>'
        . '<td align="right">' . esc(money($total)) . '</td>'
        . '<td align="right">' . esc(money($paid)) . '</td>'
        . '<td align="right">' . esc(money($saldo)) . '</td>'
        . '<td>' . esc($estado) . '</td>'
        . '</tr>';
}

if ($rowsHtml === '') {
    $rowsHtml = '<tr><td colspan="9" align="center">Sin reservas para la fecha seleccionada</td></tr>';
}

$fieldName = 'Todas las canchas';
if ($requestedField > 0) {
    $fieldInfo = query('SELECT full_name FROM soccer_field WHERE id = ?', '', [$requestedField]);
    $fieldName = (string) ($fieldInfo->full_name ?? ('Cancha #' . $requestedField));
}

$html = '
<h2 style="font-size:16px; margin:0;">Planilla de Reservas del Dia</h2>
<p style="font-size:10px; color:#4b5563; margin:4px 0 10px 0;">
Fecha: ' . esc(showDate($fecha)) . ' | Cancha: ' . esc($fieldName) . ' | Generado: ' . esc(date('d/m/Y H:i')) . '
</p>
<table cellpadding="5" cellspacing="0" border="1" style="font-size:9px;">
    <tr style="background-color:#f3f4f6; font-weight:bold;">
        <td width="14%">Reservas</td>
        <td width="22%" align="right">Facturado</td>
        <td width="22%" align="right">Pagado</td>
        <td width="22%" align="right">Saldo</td>
        <td width="20%">Estado general</td>
    </tr>
    <tr>
        <td>' . esc((string) $totalReservas) . '</td>
        <td align="right">' . esc(money($totalFacturado)) . '</td>
        <td align="right">' . esc(money($totalPagado)) . '</td>
        <td align="right">' . esc(money($totalSaldo)) . '</td>
        <td>' . esc($totalSaldo <= 0 ? 'Sin saldos pendientes' : 'Con saldos pendientes') . '</td>
    </tr>
</table>
<br>
<table cellpadding="4" cellspacing="0" border="1" style="font-size:8.5px;">
    <tr style="background-color:#e5e7eb; font-weight:bold;">
        <td width="8%">Hora</td>
        <td width="10%">N° Cancha</td>
        <td width="16%">Cancha</td>
        <td width="20%">Cliente</td>
        <td width="14%">Telefono</td>
        <td width="10%" align="right">Total</td>
        <td width="10%" align="right">Pagado</td>
        <td width="8%" align="right">Saldo</td>
        <td width="4%">Estado</td>
    </tr>
    ' . $rowsHtml . '
</table>
';

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('BotCanchero');
$pdf->SetAuthor(COMPANY);
$pdf->SetTitle('Planilla de Reservas');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 8);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reservas_' . $fecha . '.pdf', 'D');
exit;
