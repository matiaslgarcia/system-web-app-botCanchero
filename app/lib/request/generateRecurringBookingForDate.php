<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['ok' => false, 'error' => 'Unauthorized'], 401);
    }

    $user = Users::getById($_SESSION['canchero']);
    $recurringId = (int) ($_POST['recurring_booking_id'] ?? 0);
    $dateBooking = trim((string) ($_POST['date_booking'] ?? ''));

    if ($recurringId <= 0 || !$dateBooking) {
        JSON(['ok' => false, 'error' => 'Parámetros inválidos'], 400);
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateBooking)) {
        JSON(['ok' => false, 'error' => 'date_booking inválida'], 400);
    }

    $rb = query(
        "SELECT rb.*, sf.establishment_id, sf.threshold
           FROM recurring_booking rb
           INNER JOIN soccer_field sf ON sf.id = rb.field_id
          WHERE rb.id = :id
          LIMIT 1",
        'ARRAY',
        [':id' => $recurringId]
    );
    if (!$rb) {
        JSON(['ok' => false, 'error' => 'Reserva fija no encontrada'], 404);
    }

    if ($user->rol !== 'superAdmin') {
        $myField = query("SELECT establishment_id FROM soccer_field WHERE id = ? LIMIT 1", 'ARRAY', [(int) $user->id_field]);
        $userEstId = (int) ($myField['establishment_id'] ?? 0);
        if ($userEstId > 0) {
            if ((int) ($rb['establishment_id'] ?? 0) !== $userEstId) {
                JSON(['ok' => false, 'error' => 'Sin permiso'], 403);
            }
        } else {
            if ((int) ($rb['field_id'] ?? 0) !== (int) $user->id_field) {
                JSON(['ok' => false, 'error' => 'Sin permiso'], 403);
            }
        }
    }

    $rbStatus = (string) ($rb['status'] ?? '');
    if (!in_array($rbStatus, ['active', 'pending_payment'], true)) {
        JSON(['ok' => false, 'error' => 'La reserva fija no está activa'], 409);
    }

    $dow = (int) date('N', strtotime($dateBooking));
    if ((int) ($rb['day_of_week'] ?? 0) !== $dow) {
        JSON(['ok' => false, 'error' => 'La fecha no coincide con el día de la reserva fija'], 409);
    }

    if (!empty($rb['valid_from']) && $dateBooking < (string) $rb['valid_from']) {
        JSON(['ok' => false, 'error' => 'Fuera de vigencia'], 409);
    }
    if (!empty($rb['valid_until']) && $dateBooking > (string) $rb['valid_until']) {
        JSON(['ok' => false, 'error' => 'Fuera de vigencia'], 409);
    }

    $pause = query(
        "SELECT id
           FROM recurring_booking_pause
          WHERE recurring_booking_id = :rb
            AND status = 'approved'
            AND :date BETWEEN from_date AND to_date
          LIMIT 1",
        'ARRAY',
        [':rb' => $recurringId, ':date' => $dateBooking]
    );
    if ($pause) {
        JSON(['ok' => false, 'error' => 'La reserva fija está pausada para esta fecha'], 409);
    }

    $exists = query(
        "SELECT id
           FROM booking
          WHERE recurring_booking_id = :rb
            AND date_booking = :date
          LIMIT 1",
        'ARRAY',
        [':rb' => $recurringId, ':date' => $dateBooking]
    );
    if ($exists && (int) ($exists['id'] ?? 0) > 0) {
        JSON(['ok' => true, 'booking_id' => (int) $exists['id']]);
    }

    $sched = query(
        "SELECT id, hour
           FROM schedules
          WHERE hour LIKE :h
          LIMIT 1",
        'ARRAY',
        [':h' => substr((string) ($rb['start_time'] ?? ''), 0, 5) . '%']
    );
    $timeBookingId = (int) ($sched['id'] ?? 0);
    $hour = (string) ($sched['hour'] ?? '');
    if ($timeBookingId <= 0 || !$hour) {
        JSON(['ok' => false, 'error' => 'No se pudo resolver el horario'], 500);
    }

    $threshold = max(1, (int) ($rb['threshold'] ?? 1));

    $occupiedBookings = query(
        "SELECT COUNT(*) AS occupied
           FROM booking b
          WHERE b.id_field = :field
            AND b.date_booking = :date
            AND b.time_booking = :time
            AND b.status <> 2",
        'ARRAY',
        [':field' => (int) $rb['field_id'], ':date' => $dateBooking, ':time' => $timeBookingId]
    );
    $occupiedRecurring = query(
        "SELECT COUNT(*) AS occupied
           FROM recurring_booking rb2
          WHERE rb2.id <> :self
            AND rb2.field_id = :field
            AND rb2.day_of_week = :dow
            AND rb2.start_time <= :hour
            AND ADDTIME(rb2.start_time, SEC_TO_TIME(rb2.duration_min * 60)) > :hour2
            AND rb2.status = 'active'
            AND (rb2.valid_from IS NULL OR rb2.valid_from <= :date_from)
            AND (rb2.valid_until IS NULL OR rb2.valid_until >= :date_until)
            AND NOT EXISTS (
                SELECT 1
                  FROM recurring_booking_pause p
                 WHERE p.recurring_booking_id = rb2.id
                   AND p.status = 'approved'
                   AND :date_pause BETWEEN p.from_date AND p.to_date
            )
            AND NOT EXISTS (
                SELECT 1
                  FROM booking b2
                 WHERE b2.recurring_booking_id = rb2.id
                   AND b2.date_booking = :date_exists
                   AND b2.status <> 2
            )",
        'ARRAY',
        [
            ':self' => $recurringId,
            ':field' => (int) $rb['field_id'],
            ':dow' => $dow,
            ':hour' => $hour,
            ':hour2' => $hour,
            ':date_from' => $dateBooking,
            ':date_until' => $dateBooking,
            ':date_pause' => $dateBooking,
            ':date_exists' => $dateBooking,
        ]
    );
    $occupied = (int) ($occupiedBookings['occupied'] ?? 0) + (int) ($occupiedRecurring['occupied'] ?? 0);
    if ($occupied >= $threshold) {
        JSON(['ok' => false, 'error' => 'El horario no tiene más cupos disponibles'], 409);
    }

    $price = query(
        "SELECT pr.price
           FROM price_ranges pr
          WHERE pr.id_field = :field
            AND :hour >= pr.start_time
            AND :hour2 < pr.end_time
          ORDER BY pr.start_time DESC
          LIMIT 1",
        'ARRAY',
        [':field' => (int) $rb['field_id'], ':hour' => $hour, ':hour2' => $hour]
    );
    $fieldPrice = query(
        "SELECT COALESCE(NULLIF(price_hour, 0), NULLIF(price_per_hour, 0), 0) AS price
           FROM soccer_field
          WHERE id = :id
          LIMIT 1",
        'ARRAY',
        [':id' => (int) $rb['field_id']]
    );
    $totalAmount = (float) (($price['price'] ?? null) !== null ? $price['price'] : ($fieldPrice['price'] ?? 0));

    query(
        "INSERT INTO booking
            (id_customer, id_field, day_booking, time_booking, date_booking,
             user, status, is_fixed, recurring_booking_id, source,
             total_amount, deposit_amount, paid_amount, payment_status)
         VALUES
            (:cust, :field, :dow, :time, :date,
             :user, 1, 1, :rb, 'recurring',
             :total, 0, 0, 'pending')",
        '',
        [
            ':cust'  => (int) ($rb['customer_id'] ?? 0),
            ':field' => (int) $rb['field_id'],
            ':dow'   => $dow,
            ':time'  => $timeBookingId,
            ':date'  => $dateBooking,
            ':user'  => (int) ($user->id ?? 1),
            ':rb'    => $recurringId,
            ':total' => $totalAmount,
        ]
    );

    $lastId = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
    $bookingId = (int) ($lastId['id'] ?? 0);
    if ($bookingId <= 0) {
        JSON(['ok' => false, 'error' => 'No se pudo generar el turno'], 500);
    }

    Booking::addLog($bookingId, 'crear', 'Generado desde reserva fija');
    JSON(['ok' => true, 'booking_id' => $bookingId]);

