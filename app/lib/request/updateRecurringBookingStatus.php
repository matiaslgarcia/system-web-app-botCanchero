<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['error' => 'Unauthorized'], 401);
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? ''; // active, paused, cancelled
    $reason = trim((string) ($_POST['reason'] ?? ''));

    if (!$id || !in_array($status, ['active', 'paused', 'cancelled'])) {
        JSON(['error' => 'Parámetros inválidos'], 400);
    }

    // Si es cancelled, ponemos fecha de cancelación
    if ($status == 'cancelled') {
        query(
            "UPDATE recurring_booking SET status = ?, cancelled_at = NOW(), cancelled_reason = ? WHERE id = ?",
            '',
            [$status, $reason, $id]
        );
        // También cancelamos bookings futuros de esta fija
        query(
            "UPDATE booking SET status = 3 WHERE recurring_booking_id = ? AND date_booking >= CURDATE()",
            '',
            [$id]
        );
    } else {
        query(
            "UPDATE recurring_booking
                SET status = ?,
                    cancelled_at = CASE WHEN ? = 'active' THEN NULL ELSE cancelled_at END,
                    cancelled_reason = CASE WHEN ? = 'active' THEN NULL ELSE cancelled_reason END
              WHERE id = ?",
            '',
            [$status, $status, $status, $id]
        );
        
        // Si se pausa, cancelamos bookings futuros NO pagados
        if ($status == 'paused') {
            query(
                "UPDATE booking SET status = 3 
                 WHERE recurring_booking_id = ? 
                   AND date_booking >= CURDATE() 
                   AND (paid_amount IS NULL OR paid_amount = 0)",
                '',
                [$id]
            );
        }
    }

    JSON(['ok' => true]);
