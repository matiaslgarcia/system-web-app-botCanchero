<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['canchero'])) {
        JSON(['error' => 'Unauthorized'], 401);
    }

    $establishmentId = 1; // En este modelo legacy el establishment es 1 (botCanchero)
    
    // Validar inputs
    $field_id = (int)($_POST['field_id'] ?? 0);
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $day_of_week = (int)($_POST['day_of_week'] ?? 0);
    $start_time = trim((string)($_POST['start_time'] ?? ''));
    $duration_min = (int)($_POST['duration_min'] ?? 60);
    $valid_from = $_POST['valid_from'] ?? date('Y-m-d');
    $valid_until = $_POST['valid_until'] ?? null;

    if (!$field_id || !$customer_id || !$day_of_week || !$start_time) {
        JSON(['error' => 'Faltan campos obligatorios'], 400);
    }

    // Normalizar start_time:
    // soporta "22:00", "22:00:00", "22:00 - 23:00", "22:00 (1 hora)".
    if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $start_time, $m)) {
        $start_time = $m[1];
    }
    if (preg_match('/^\d{1,2}:\d{2}$/', $start_time)) {
        $start_time .= ":00";
    }
    if (!preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $start_time)) {
        JSON(['error' => 'Formato de horario inválido'], 400);
    }

    // Validar conflicto por cupos (threshold): se permite solapar hasta completar capacidad.
    $capacity = query(
        "SELECT COALESCE(NULLIF(threshold, 0), 1) AS threshold
           FROM soccer_field
          WHERE id = :field
          LIMIT 1",
        'ARRAY',
        [':field' => $field_id]
    );
    $threshold = max(1, (int) ($capacity['threshold'] ?? 1));

    $occupied = query(
        "SELECT COUNT(*) AS total
           FROM recurring_booking
          WHERE establishment_id = :est
            AND field_id = :field
            AND day_of_week = :dow
            AND status IN ('active', 'pending_payment')
            AND (:start < ADDTIME(start_time, SEC_TO_TIME(duration_min*60)))
            AND (ADDTIME(:start_copy, SEC_TO_TIME(:dur*60)) > start_time)
            AND (valid_until IS NULL OR valid_until >= :vfrom)",
        'ARRAY',
        [
            ':est' => $establishmentId,
            ':field' => $field_id,
            ':dow' => $day_of_week,
            ':start' => $start_time,
            ':start_copy' => $start_time,
            ':dur' => $duration_min,
            ':vfrom' => $valid_from,
        ]
    );

    if ((int) ($occupied['total'] ?? 0) >= $threshold) {
        JSON(['error' => 'Ese horario ya completó los cupos simultáneos configurados para la cancha.'], 409);
    }

    $sql = "INSERT INTO recurring_booking (
        establishment_id, field_id, customer_id,
        day_of_week, start_time, duration_min,
        valid_from, valid_until,
        status, created_by
    ) VALUES (
        :est, :field, :cust,
        :dow, :start, :dur,
        :vfrom, :vuntil,
        'active', 'owner_web'
    )";

    query($sql, '', [
        ':est'     => $establishmentId,
        ':field'   => $field_id,
        ':cust'    => $customer_id,
        ':dow'     => $day_of_week,
        ':start'   => $start_time,
        ':dur'     => $duration_min,
        ':vfrom'   => $valid_from,
        ':vuntil'  => $valid_until ? $valid_until : null,
    ]);

    $newId = query("SELECT LAST_INSERT_ID() AS id")->id;

    // Trigger generation for the first week immediately
    // Para simplificar, asumimos que el cron lo hará, pero podriamos llamar a generateWeek aquí.
    
    JSON(['ok' => true, 'id' => $newId]);
