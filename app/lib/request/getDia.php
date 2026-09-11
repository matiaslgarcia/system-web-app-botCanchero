<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
    Canchas::ensurePriceRangesStorage();

    $user = Users::getById($_SESSION['canchero']);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $requestedField = isset($_POST['field_id']) ? (int) $_POST['field_id'] : 0;

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
        $field = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
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
                JSON(['bookings' => []]);
            }
        }
        $whereBookings[] = "f.id = :field_id";
        $whereRecurring[] = "sf.id = :field_id";
        $commonParams[':field_id'] = $requestedField;
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
            b.deposit_amount,
            COALESCE(b.paid_amount, 0) AS paid_amount,
            b.payment_status,
            b.paid_in_cash_at,
            1 AS can_charge,
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
            0 AS deposit_amount,
            0 AS paid_amount,
            'pending' AS payment_status,
            NULL AS paid_in_cash_at,
            0 AS can_charge,
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

    JSON(['bookings' => $all]);
