<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['canchero'])) {
        JSON(['error' => 'Unauthorized'], 401);
    }

    $request = array_merge($_GET ?? [], $_POST ?? []);
    $id_field = (int)($request['id_field'] ?? 0);
    $id_day = (int)($request['id_day'] ?? 0);

    if (!$id_field || !$id_day) {
        JSON([]);
    }

    // Devolver solo horarios disponibles para reserva fija:
    // - configurados para esa cancha/día
    // - sin solapamiento con otras fijas activas/pending_payment
    $schedules = query(
        "SELECT
            s.id,
            s.time,
            s.hour,
            s.hour12,
            COALESCE(NULLIF(f.threshold, 0), 1) AS threshold,
            (
                SELECT COUNT(*)
                  FROM recurring_booking rb
                 WHERE rb.field_id = sf.id_field
                   AND rb.day_of_week = sf.id_day
                   AND rb.status IN ('active', 'pending_payment')
                   AND (s.time < ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)))
                   AND (ADDTIME(s.time, '01:00:00') > rb.start_time)
            ) AS occupied
           FROM schedules s
           INNER JOIN schedules_field sf ON sf.id_schedule = s.id
           INNER JOIN soccer_field f ON f.id = sf.id_field
          WHERE sf.id_field = :field
            AND sf.id_day = :day
          HAVING occupied < threshold
          ORDER BY s.id ASC",
        'ARRAY_ALL',
        [':field' => $id_field, ':day' => $id_day]
    );

    JSON($schedules ?: []);
