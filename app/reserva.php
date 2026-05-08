<?php

    require 'int.php';

    Users::loginCheck();

    function resolveRecurringToBookingId(int $rbId): int {
        $existing = query(
            "SELECT id
               FROM booking
              WHERE recurring_booking_id = :rb
                AND status <> 2
              ORDER BY ABS(DATEDIFF(date_booking, CURDATE())) ASC, date_booking ASC
              LIMIT 1",
            'ARRAY',
            [':rb' => $rbId]
        );
        if ($existing && (int) ($existing['id'] ?? 0) > 0) {
            return (int) $existing['id'];
        }

        $rb = query(
            "SELECT rb.id, rb.field_id, rb.customer_id, rb.day_of_week, rb.start_time,
                    rb.status, rb.valid_from, rb.valid_until,
                    sf.threshold
               FROM recurring_booking rb
               INNER JOIN soccer_field sf ON sf.id = rb.field_id
              WHERE rb.id = :id
              LIMIT 1",
            'ARRAY',
            [':id' => $rbId]
        );
        if (!$rb) return 0;
        if (!in_array((string) ($rb['status'] ?? ''), ['active', 'pending_payment'], true)) return 0;
        if ((int) ($rb['customer_id'] ?? 0) <= 0) return 0;

        for ($i = 0; $i <= 14; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $dow = (int) date('N', strtotime($date));
            if ($dow !== (int) ($rb['day_of_week'] ?? 0)) continue;
            if (!empty($rb['valid_from']) && $date < (string) $rb['valid_from']) continue;
            if (!empty($rb['valid_until']) && $date > (string) $rb['valid_until']) continue;

            $pause = query(
                "SELECT id
                   FROM recurring_booking_pause
                  WHERE recurring_booking_id = :rb
                    AND status = 'approved'
                    AND :date BETWEEN from_date AND to_date
                  LIMIT 1",
                'ARRAY',
                [':rb' => $rbId, ':date' => $date]
            );
            if ($pause) continue;

            $sameDate = query(
                "SELECT id
                   FROM booking
                  WHERE recurring_booking_id = :rb
                    AND date_booking = :date
                  LIMIT 1",
                'ARRAY',
                [':rb' => $rbId, ':date' => $date]
            );
            if ($sameDate && (int) ($sameDate['id'] ?? 0) > 0) {
                return (int) $sameDate['id'];
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
            if ($timeBookingId <= 0 || !$hour) continue;

            $threshold = max(1, (int) ($rb['threshold'] ?? 1));
            $occupiedBookings = query(
                "SELECT COUNT(*) AS occupied
                   FROM booking b
                  WHERE b.id_field = :field
                    AND b.date_booking = :date
                    AND b.time_booking = :time
                    AND b.status <> 2",
                'ARRAY',
                [':field' => (int) $rb['field_id'], ':date' => $date, ':time' => $timeBookingId]
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
                    ':self' => $rbId,
                    ':field' => (int) $rb['field_id'],
                    ':dow' => $dow,
                    ':hour' => $hour,
                    ':hour2' => $hour,
                    ':date_from' => $date,
                    ':date_until' => $date,
                    ':date_pause' => $date,
                    ':date_exists' => $date,
                ]
            );
            $occupied = (int) ($occupiedBookings['occupied'] ?? 0) + (int) ($occupiedRecurring['occupied'] ?? 0);
            if ($occupied >= $threshold) continue;

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
                    ':cust'  => (int) $rb['customer_id'],
                    ':field' => (int) $rb['field_id'],
                    ':dow'   => $dow,
                    ':time'  => $timeBookingId,
                    ':date'  => $date,
                    ':user'  => (int) ($_SESSION['canchero'] ?? 1),
                    ':rb'    => $rbId,
                    ':total' => $totalAmount,
                ]
            );

            $lastId = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
            $newId = (int) ($lastId['id'] ?? 0);
            if ($newId > 0) return $newId;
        }

        return 0;
    }

    $reservaParam = (string) ($_GET['reserva'] ?? '');
    if (preg_match('/^RB-(\d+)$/', $reservaParam, $m)) {
        $resolved = resolveRecurringToBookingId((int) $m[1]);
        if ($resolved > 0) {
            header('Location: reserva/' . $resolved);
            exit;
        }
        header('Location: recurring-bookings');
        exit;
    }

    Theme::header([
        'title' => 'Reserva',
        'base'  => URL,
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'theme',
            'datepicker.min',
            'reserva-detail-mobile',
        ]
    ]);
    inc('reserva');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'datepicker.min',
        ]
    ]);
?>
    <!-- CSS Global -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script type="module" src="lib/dataJS/reserva.js?ver=<?php echo VERSION ?>"></script>
