<?php

    require '../../int.php';

    if (session_status() === PHP_SESSION_NONE) {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    $user = Users::getById($_SESSION['canchero']);
    
    // Obtener establishment_id
    $myField = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
    $estId = $myField->establishment_id ?? 0;

    $params = [];
    $where = "WHERE 1=1";

    if ($user->rol != 'superAdmin') {
        if ($estId > 0) {
            $where .= " AND f.establishment_id = :estId";
            $params[':estId'] = $estId;
        } else {
            $where .= " AND f.id = :fieldId";
            $params[':fieldId'] = $user->id_field;
        }
    }

    if (isset($_POST['field_id']) && !empty($_POST['field_id'])) {
        $where .= " AND f.id = :filterFieldId";
        $params[':filterFieldId'] = (int)$_POST['field_id'];
    }

    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+60 days'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $startDate = date('Y-m-d');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        $endDate = date('Y-m-d', strtotime('+60 days'));
    }
    if ($endDate < $startDate) {
        $tmp = $startDate;
        $startDate = $endDate;
        $endDate = $tmp;
    }
    $rangeDays = (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400);
    if ($rangeDays > 180) {
        $endDate = date('Y-m-d', strtotime($startDate . ' +180 days'));
    }

    $metaOnly = isset($_POST['meta_only']) && (int) $_POST['meta_only'] === 1;
    $metaRow = query(
        "SELECT
            COUNT(*) AS total,
            COALESCE(MAX(b.id), 0) AS max_id,
            COALESCE(SUM(CRC32(CONCAT_WS('|',
                b.id,
                b.status,
                b.date_booking,
                b.time_booking,
                COALESCE(b.paid_amount, 0),
                COALESCE(b.total_amount, 0),
                COALESCE(b.source, ''),
                COALESCE(v.data_id, '')
            ))), 0) AS checksum
        FROM booking AS b
        INNER JOIN soccer_field AS f
            ON f.id = b.id_field
        LEFT JOIN vouchers AS v
            ON v.id_booking = b.id
        $where",
        '',
        $params
    );
    if (!$metaRow) {
        JSON(['error' => 'No se pudo calcular estado de reservas'], 500);
    }
    $total = (int) ($metaRow->total ?? 0);
    $maxId = (int) ($metaRow->max_id ?? 0);
    $checksum = (string) ($metaRow->checksum ?? '0');

    $recMetaParams = $params;
    $recMetaParams[':rec_start'] = $startDate;
    $recMetaParams[':rec_end'] = $endDate;
    $recMeta = query(
        "SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CRC32(CONCAT_WS('|',
                rb.id,
                rb.field_id,
                rb.customer_id,
                rb.day_of_week,
                rb.start_time,
                rb.duration_min,
                COALESCE(rb.valid_from, ''),
                COALESCE(rb.valid_until, ''),
                rb.status,
                COALESCE(rb.updated_at, '')
            ))), 0) AS checksum
         FROM recurring_booking rb
         INNER JOIN soccer_field f ON f.id = rb.field_id
         $where
           AND rb.status IN ('active', 'pending_payment')
           AND rb.valid_from <= :rec_end
           AND (rb.valid_until IS NULL OR rb.valid_until >= :rec_start)",
        '',
        $recMetaParams
    );
    $recTotal = (int) ($recMeta->total ?? 0);
    $recChecksum = (string) ($recMeta->checksum ?? '0');
    $signature = sha1($total . '|' . $maxId . '|' . $checksum . '|' . $recTotal . '|' . $recChecksum . '|' . $startDate . '|' . $endDate);

    if ($metaOnly) {
        $clientSignature = isset($_POST['signature']) ? (string) $_POST['signature'] : '';
        JSON([
            'meta' => [
                'signature' => $signature,
                'total' => $total,
                'max_id' => $maxId,
                'changed' => ($clientSignature !== $signature)
            ]
        ]);
    }

    $booking = query(
        "SELECT
            b.id AS id,
            b.id_field,
            b.day_booking AS day,
            b.date_booking AS date,
            h.time AS time,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            f.full_name AS cancha,
            COALESCE(NULLIF(f.threshold, 0), 1) AS threshold,
            s.id AS id_status,
            CONCAT(c.full_name) AS title,
            CONCAT(b.date_booking, ' ',  h.time) AS start,
            CONCAT('reserva/', b.id) AS url,
            CONCAT(s.color_hex) AS color,
            CASE
                WHEN b.is_fixed = 1 THEN 1
                WHEN COALESCE(b.recurring_booking_id, 0) > 0 THEN 1
                WHEN COALESCE(b.source, '') IN ('recurring', 'recurring_planned') THEN 1
                WHEN EXISTS (
                    SELECT 1
                      FROM recurring_booking rbx
                     WHERE rbx.field_id = b.id_field
                       AND rbx.customer_id = b.id_customer
                       AND rbx.day_of_week = (WEEKDAY(b.date_booking) + 1)
                       AND rbx.start_time = h.time
                       AND rbx.status IN ('active', 'pending_payment')
                       AND b.date_booking >= rbx.valid_from
                       AND (rbx.valid_until IS NULL OR b.date_booking <= rbx.valid_until)
                ) THEN 1
                ELSE 0
            END AS is_fixed,
            b.recurring_booking_id,
            CASE
                WHEN COALESCE(b.source, '') IN ('bot', 'customer_bot', 'whatsapp', 'bot_whatsapp') THEN 'bot'
                WHEN EXISTS (
                    SELECT 1
                      FROM booking_logs bl
                     WHERE bl.id_booking = b.id
                       AND bl.action = 'crear'
                       AND bl.note LIKE '%bot/webhook%'
                     LIMIT 1
                ) THEN 'bot'
                WHEN COALESCE(b.source, '') = '' THEN 'web'
                ELSE b.source
            END AS source,
            b.total_amount,
            b.paid_amount,
            b.payment_status
        FROM
            booking AS b
        INNER JOIN soccer_field AS f
        ON f.id = b.id_field
        INNER JOIN customers AS c
        ON c.id = b.id_customer
        INNER JOIN booking_status AS s
        ON s.id = b.status
        INNER JOIN schedules AS h
        ON h.id = b.time_booking
        $where
        ", 'ALL', $params
    );

    $recurringParams = $params;
    $recurringParams[':range_start'] = $startDate;
    $recurringParams[':range_start_2'] = $startDate;
    $recurringParams[':range_end'] = $endDate;
    $recurringOnly = query(
        "SELECT
            CONCAT('RB-', rb.id, '-', DATE_FORMAT(d.d, '%Y%m%d')) AS id,
            rb.field_id AS id_field,
            DATE_FORMAT(d.d, '%W') AS day,
            DATE_FORMAT(d.d, '%Y-%m-%d') AS date,
            h.time AS time,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            f.full_name AS cancha,
            COALESCE(NULLIF(f.threshold, 0), 1) AS threshold,
            1 AS id_status,
            CONCAT(c.full_name) AS title,
            CONCAT(DATE_FORMAT(d.d, '%Y-%m-%d'), ' ', COALESCE(h.time, rb.start_time)) AS start,
            'recurring-bookings' AS url,
            '#fd7e14' AS color,
            1 AS is_fixed,
            rb.id AS recurring_booking_id,
            'recurring_planned' AS source,
            0 AS total_amount,
            0 AS paid_amount,
            'pending' AS payment_status
        FROM recurring_booking rb
        INNER JOIN soccer_field f ON f.id = rb.field_id
        LEFT JOIN customers c ON c.id = rb.customer_id
        LEFT JOIN schedules h ON h.time = rb.start_time
        INNER JOIN (
            SELECT DATE_ADD(:range_start, INTERVAL n DAY) AS d
              FROM (
                    SELECT (ones.i + tens.i * 10 + hundreds.i * 100) AS n
                      FROM (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
                      CROSS JOIN (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
                      CROSS JOIN (SELECT 0 i UNION ALL SELECT 1) hundreds
              ) nums
             WHERE DATE_ADD(:range_start_2, INTERVAL n DAY) <= :range_end
        ) d ON 1=1
        $where
          AND rb.status IN ('active', 'pending_payment')
          AND rb.day_of_week = (WEEKDAY(d.d) + 1)
          AND d.d >= rb.valid_from
          AND (rb.valid_until IS NULL OR d.d <= rb.valid_until)
          AND NOT EXISTS (
                SELECT 1
                  FROM booking b2
                 WHERE b2.recurring_booking_id = rb.id
                   AND b2.date_booking = DATE_FORMAT(d.d, '%Y-%m-%d')
                   AND b2.status <> 2
          )
        ORDER BY d.d, COALESCE(h.time, rb.start_time)",
        'ALL',
        $recurringParams
    );

    $allBookings = array_merge($booking ?: [], $recurringOnly ?: []);
    foreach ($allBookings as $idx => $row) {
        $reserva = is_array($row) ? (object) $row : $row;
        $reserva->start = date("Y-m-d\TH:i:s", strtotime($reserva->start));

        // Tag visual fija + estado de pago
        $tags = [];
        if ($reserva->is_fixed == 1) $tags[] = '♻️';
        $reserva->title = (count($tags) ? implode(' ', $tags) . ' ' : '') . $reserva->title;

        // Pintar fijas en naranja, sueltas con color del status (mantener original)
        $isRecurringBooking = !empty($reserva->recurring_booking_id) && (int)$reserva->recurring_booking_id > 0;
        if ($reserva->source === 'recurring' || $reserva->source === 'recurring_planned' || $reserva->is_fixed == 1 || $isRecurringBooking) {
            $reserva->color = '#fd7e14'; // naranja
        }

        // Saldo pendiente coloreado
        $totalAmount = (float) ($reserva->total_amount ?? 0);
        $paidAmount = (float) ($reserva->paid_amount ?? 0);
        $reserva->balance_due = max(0, $totalAmount - $paidAmount);

        if (($reserva->source ?? '') === 'recurring_planned') {
            $reserva->droppable = false;
            $reserva->editable = false;
        } else if ($reserva->id_status == 1) {
            $reserva->droppable = true;
            $reserva->editable = true;
        }
        $allBookings[$idx] = $reserva;
    }

    $data = [
        'reservas' => $allBookings,
        'meta' => [
            'signature' => $signature,
            'total' => $total,
            'max_id' => $maxId
        ]
    ];
    JSON($data);
