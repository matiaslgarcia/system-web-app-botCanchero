<?php

    require '../../int.php';
    session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['ok' => false, 'error' => 'Sesión inválida'], 401);
        exit;
    }

    $user = Users::getById($_SESSION['canchero']);
    $id = (int) ($_POST['id'] ?? 0);
    $dow = (int) ($_POST['day_of_week'] ?? 0);
    $start = $_POST['start_time'] ?? null;
    $dur = (int) ($_POST['duration_min'] ?? 60);

    if (!$id || !$dow || !$start) {
        JSON(['ok' => false, 'error' => 'Faltan parámetros'], 400);
        exit;
    }

    $rb = query(
        "SELECT establishment_id, field_id FROM recurring_booking WHERE id = :id",
        'ARRAY',
        [':id' => $id]
    );
    if (!$rb) { JSON(['ok' => false, 'error' => 'No encontrada'], 404); exit; }

    if ($user->rol !== 'superAdmin' && (int) $rb['field_id'] !== (int) $user->id_field) {
        JSON(['ok' => false, 'error' => 'Sin permiso'], 403);
        exit;
    }

    // Validar conflicto
    $conflict = query(
        "SELECT id FROM recurring_booking
          WHERE id <> :id
            AND establishment_id = :est
            AND field_id = :field
            AND day_of_week = :dow
            AND status IN ('active','pending_payment')
            AND (:start < ADDTIME(start_time, SEC_TO_TIME(duration_min*60)))
            AND (ADDTIME(:start2, SEC_TO_TIME(:dur*60)) > start_time)",
        'ARRAY',
        [
            ':id'    => $id,
            ':est'   => (int) $rb['establishment_id'],
            ':field' => (int) $rb['field_id'],
            ':dow'   => $dow,
            ':start' => $start,
            ':start2'=> $start,
            ':dur'   => $dur,
        ]
    );
    if ($conflict) {
        JSON(['ok' => false, 'error' => 'Conflicto con otra fija en ese horario'], 409);
        exit;
    }

    query(
        "UPDATE recurring_booking
            SET day_of_week = :dow,
                start_time = :start,
                duration_min = :dur
          WHERE id = :id",
        '',
        [':id' => $id, ':dow' => $dow, ':start' => $start, ':dur' => $dur]
    );

    // Cancelar bookings futuros NO pagados
    query(
        "UPDATE booking
            SET status = 3
          WHERE recurring_booking_id = :id
            AND date_booking >= CURDATE()
            AND (paid_amount IS NULL OR paid_amount = 0)",
        '',
        [':id' => $id]
    );

    JSON(['ok' => true]);
