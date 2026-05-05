<?php

    require '../../int.php';
    // PHP-B3: int.php ya hace Users::loginCheck() — no duplicar.

    $user = Users::getById($_SESSION['canchero']);
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = $_POST['note'] ?? null;

    if ($bookingId <= 0 || $amount <= 0) {
        JSON(['ok' => false, 'error' => 'booking_id y amount son obligatorios y > 0'], 400);
    }

    if ($user->rol !== 'superAdmin') {
        $owner = query(
            "SELECT b.id
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
               INNER JOIN soccer_field myf ON myf.id = :my_field
              WHERE b.id = :id
                AND (
                    sf.id = :my_field
                    OR (
                        myf.establishment_id IS NOT NULL
                        AND myf.establishment_id > 0
                        AND sf.establishment_id = myf.establishment_id
                    )
                )
              LIMIT 1",
            'ARRAY',
            [':id' => $bookingId, ':my_field' => (int) $user->id_field]
        );
        if (!$owner) {
            JSON(['ok' => false, 'error' => 'Booking no pertenece a esta cancha'], 403);
        }
    }

    // PHP-B6: transacción + SELECT FOR UPDATE para evitar race con dos cobros simultáneos.
    $pdo = Db::pdo();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT id, total_amount, paid_amount, payment_status
               FROM booking WHERE id = :id FOR UPDATE"
        );
        $stmt->execute([':id' => $bookingId]);
        $b = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$b) {
            $pdo->rollBack();
            JSON(['ok' => false, 'error' => 'Booking not found'], 404);
        }
        if ($b['payment_status'] === 'paid') {
            $pdo->rollBack();
            JSON(['ok' => false, 'error' => 'La reserva ya está pagada'], 409);
        }

        $newPaid = (float) $b['paid_amount'] + $amount;
        $total = (float) $b['total_amount'];
        $newStatus = $newPaid >= $total ? 'paid' : 'partial';
        $paidInCashAt = $newStatus === 'paid' ? date('Y-m-d H:i:s') : null;

        $upd = $pdo->prepare(
            "UPDATE booking
                SET paid_amount = :paid,
                    payment_status = :status,
                    paid_in_cash_at = COALESCE(paid_in_cash_at, :pca),
                    paid_in_cash_by_user_id = :emp
              WHERE id = :id"
        );
        $upd->execute([
            ':id'     => $bookingId,
            ':paid'   => $newPaid,
            ':status' => $newStatus,
            ':pca'    => $paidInCashAt,
            ':emp'    => (int) $user->id,
        ]);

        $log = $pdo->prepare(
            "INSERT INTO booking_logs (id_booking, id_user, action, note, created_at)
             VALUES (:bid, :uid, 'cash_payment', :note, NOW())"
        );
        $log->execute([
            ':bid'  => $bookingId,
            ':uid'  => (int) $user->id,
            ':note' => sprintf('Cash $%.2f (running $%.2f / $%.2f)', $amount, $newPaid, $total) . ($note ? ' — ' . $note : ''),
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('cobrarSaldo error: ' . $e->getMessage());
        JSON(['ok' => false, 'error' => 'Internal error'], 500);
    }

    audit('cash_payment', 'booking', $bookingId, [
        'amount'      => $amount,
        'new_paid'    => $newPaid,
        'new_status'  => $newStatus,
        'employee_id' => (int) $user->id,
    ]);

    JSON([
        'ok' => true,
        'booking_id' => $bookingId,
        'paid_amount' => $newPaid,
        'balance_due' => max(0, $total - $newPaid),
        'payment_status' => $newStatus,
    ]);
