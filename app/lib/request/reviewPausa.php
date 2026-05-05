<?php

    require '../../int.php';
    session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['ok' => false, 'error' => 'Sesión inválida'], 401);
        exit;
    }

    $user = Users::getById($_SESSION['canchero']);
    $pauseId = (int) ($_POST['pause_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';
    $note = $_POST['review_note'] ?? null;

    if (!$pauseId || !in_array($decision, ['approved', 'rejected'])) {
        JSON(['ok' => false, 'error' => 'Parámetros inválidos'], 400);
        exit;
    }

    // Validar ownership
    $p = query(
        "SELECT rb.field_id FROM recurring_booking_pause p
           INNER JOIN recurring_booking rb ON rb.id = p.recurring_booking_id
          WHERE p.id = :id",
        'ARRAY',
        [':id' => $pauseId]
    );
    if (!$p) { JSON(['ok' => false, 'error' => 'No encontrada'], 404); exit; }
    if ($user->rol !== 'superAdmin' && (int) $p['field_id'] !== (int) $user->id_field) {
        JSON(['ok' => false, 'error' => 'Sin permiso'], 403);
        exit;
    }

    query(
        "UPDATE recurring_booking_pause
            SET status = :status,
                reviewed_by_user_id = :rev,
                reviewed_at = NOW(),
                review_note = :note
          WHERE id = :id",
        '',
        [
            ':id'     => $pauseId,
            ':status' => $decision,
            ':rev'    => (int) $user->id,
            ':note'   => $note,
        ]
    );

    if ($decision === 'approved') {
        // Cancelar bookings ya generados que caen dentro de la pausa
        query(
            "UPDATE booking b
               JOIN recurring_booking_pause p ON p.id = :id
                SET b.status = 3
              WHERE b.recurring_booking_id = p.recurring_booking_id
                AND b.date_booking BETWEEN p.from_date AND p.to_date
                AND (b.paid_amount IS NULL OR b.paid_amount = 0)",
            '',
            [':id' => $pauseId]
        );
    }

    JSON(['ok' => true, 'status' => $decision]);
