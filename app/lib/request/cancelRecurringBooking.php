<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['ok' => false, 'error' => 'Sesión inválida'], 401);
        exit;
    }

    $user = Users::getById($_SESSION['canchero']);
    $id = (int) ($_POST['id'] ?? 0);
    $reason = $_POST['reason'] ?? null;

    if (!$id) {
        JSON(['ok' => false, 'error' => 'id requerido'], 400);
        exit;
    }

    $rb = query(
        "SELECT field_id FROM recurring_booking WHERE id = :id",
        'ARRAY',
        [':id' => $id]
    );
    if (!$rb) { JSON(['ok' => false, 'error' => 'No encontrada'], 404); exit; }
    if ($user->rol !== 'superAdmin' && (int) $rb['field_id'] !== (int) $user->id_field) {
        JSON(['ok' => false, 'error' => 'Sin permiso'], 403);
        exit;
    }

    query(
        "UPDATE recurring_booking
            SET status = 'cancelled',
                cancelled_at = NOW(),
                cancelled_reason = :reason
          WHERE id = :id",
        '',
        [':id' => $id, ':reason' => $reason]
    );

    // FIJ-02 (auditoría, cruce entre pantallas): esto ponía status=3
    // ("Completado"), no 2 ("Cancelado") -- una reserva futura que se cancela
    // junto con su fija no "se completó". El bug real es semántico: dos fijas
    // canceladas en la cuenta de prueba seguían mostrando bloques naranjas
    // activos los jueves/viernes siguientes porque sus bookings ya
    // materializados nunca quedaron marcados como cancelados.
    query(
        "UPDATE booking SET status = 2
          WHERE recurring_booking_id = :id AND date_booking >= CURDATE()",
        '',
        [':id' => $id]
    );

    JSON(['ok' => true]);
