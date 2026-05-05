<?php

    require '../../int.php';
    session_start();

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

    query(
        "UPDATE booking SET status = 3
          WHERE recurring_booking_id = :id AND date_booking >= CURDATE()",
        '',
        [':id' => $id]
    );

    JSON(['ok' => true]);
