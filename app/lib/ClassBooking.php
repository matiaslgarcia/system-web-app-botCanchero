<?php

    class Booking{
        private static function resolveScheduleId($timeBooking) {
            if ($timeBooking === null || $timeBooking === '') return 0;
            if (is_numeric($timeBooking)) return (int) $timeBooking;

            $raw = trim((string) $timeBooking);
            if ($raw === '') return 0;

            $start = null;
            if (preg_match('/^(\d{1,2}):(\d{2})/', $raw, $m)) {
                $start = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            } elseif (preg_match('/(\d{1,2}):(\d{2})/', $raw, $m)) {
                $start = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            }

            if (!$start) return 0;

            $row = query(
                "SELECT id
                   FROM schedules
                  WHERE time = ?
                     OR hour LIKE ?
                  LIMIT 1",
                'ARRAY',
                [$start . ':00', $start . '%']
            );

            return (int) ($row['id'] ?? 0);
        }

        private static function humanizeLogNote($action, $note) {
            $note = trim((string) $note);
            if ($note === '') return '';
            $action = strtolower(trim((string) $action));

            if ($note === 'Reserva actualizada') {
                if ($action === 'cancelar' || $action === '2') return 'Reserva cancelada.';
                if ($action === 'reagendar' || $action === '6') return 'Reserva re-agendada.';
                if ($action === 'crear' || $action === '1') return 'Reserva creada.';
            }

            if (preg_match('/Reserva creada desde bot\/webhook/i', $note)) {
                return 'Reserva ingresada automáticamente desde WhatsApp.';
            }
            if (preg_match('/Pago aprobado confirmado\.\s*payment_id=\d+/i', $note)) {
                return 'Pago aprobado correctamente por Mercado Pago.';
            }
            if (preg_match('/Pago registrado \(status=([a-z_]+),\s*paid_amount=([0-9.,]+),\s*total_amount=([0-9.,]+)\)/i', $note, $m)) {
                $status = strtolower((string) ($m[1] ?? ''));
                $paid = (float) str_replace(',', '.', (string) ($m[2] ?? '0'));
                $total = (float) str_replace(',', '.', (string) ($m[3] ?? '0'));
                if ($status === 'partial' && $total > 0) {
                    $saldo = max(0, $total - $paid);
                    return 'Se registró una seña de $' . number_format($paid, 0, ',', '.') . '. Saldo pendiente: $' . number_format($saldo, 0, ',', '.') . '.';
                }
                return 'Pago registrado por $' . number_format($paid, 0, ',', '.') . '.';
            }
            if (preg_match('/Cash payment:\s*\$([0-9.]+)\s*\(running total\s*\$([0-9.]+)\s*\/\s*\$([0-9.]+)\)/i', $note, $m)) {
                $monto = (float) ($m[1] ?? 0);
                $acum = (float) ($m[2] ?? 0);
                $total = (float) ($m[3] ?? 0);
                $saldo = max(0, $total - $acum);
                return 'Pago en efectivo: $' . number_format($monto, 0, ',', '.') . '. Saldo pendiente: $' . number_format($saldo, 0, ',', '.') . '.';
            }

            return $note;
        }

        private static function getFieldByBookingId($bookingId) {
            $row = query(
                "SELECT id_field FROM booking WHERE id = ? LIMIT 1",
                'ARRAY',
                [$bookingId]
            );
            return $row ? (int) $row['id_field'] : 0;
        }

        private static function getFieldThreshold($idField) {
            $row = query(
                "SELECT COALESCE(NULLIF(threshold, 0), 1) AS threshold
                   FROM soccer_field
                  WHERE id = ?
                  LIMIT 1",
                'ARRAY',
                [(int) $idField]
            );
            return max(1, (int) ($row['threshold'] ?? 1));
        }

        private static function getSlotNumberForBooking($bookingId, $idField, $dateBooking, $timeBooking) {
            $row = query(
                "SELECT COUNT(*) AS slot_number
                   FROM booking
                  WHERE id_field = ?
                    AND date_booking = ?
                    AND time_booking = ?
                    AND id <= ?
                    AND (status <> 2 OR id = ?)",
                'ARRAY',
                [(int) $idField, $dateBooking, (int) $timeBooking, (int) $bookingId, (int) $bookingId]
            );
            return max(1, (int) ($row['slot_number'] ?? 1));
        }

        private static function hasSlotConflict($idField, $dateBooking, $timeBooking, $excludeBookingId = null) {
            if (!$idField || !$dateBooking || !$timeBooking) return true;
            $excludeSql = $excludeBookingId ? " AND b.id <> :exclude_id" : "";
            $dow = (int) date('N', strtotime($dateBooking));
            $params = [
                ':id_field' => (int) $idField,
                ':date_booking' => $dateBooking,
                ':time_booking' => (int) $timeBooking,
                ':dow' => $dow,
                ':time_booking_2' => (int) $timeBooking,
                ':id_field_2' => (int) $idField,
                ':date_booking_2' => $dateBooking,
                ':date_booking_3' => $dateBooking,
                ':date_booking_4' => $dateBooking,
                ':date_booking_5' => $dateBooking,
            ];
            if ($excludeBookingId) $params[':exclude_id'] = (int) $excludeBookingId;

            $row = query(
                "SELECT
                    (
                        (SELECT COUNT(*)
                           FROM booking b
                          WHERE b.id_field = :id_field
                            AND b.date_booking = :date_booking
                            AND b.time_booking = :time_booking
                            AND b.status <> 2
                            $excludeSql)
                        +
                        (SELECT COUNT(*)
                           FROM recurring_booking rb
                           INNER JOIN schedules s ON s.id = :time_booking_2
                          WHERE rb.field_id = :id_field_2
                            AND rb.day_of_week = :dow
                            AND rb.start_time <= s.hour
                            AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                            AND rb.status = 'active'
                            AND rb.valid_from <= :date_booking_2
                            AND (rb.valid_until IS NULL OR rb.valid_until >= :date_booking_3)
                            AND NOT EXISTS (
                                SELECT 1
                                  FROM recurring_booking_pause p
                                 WHERE p.recurring_booking_id = rb.id
                                   AND p.status = 'approved'
                                   AND :date_booking_4 BETWEEN p.from_date AND p.to_date
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                  FROM booking b2
                                 WHERE b2.recurring_booking_id = rb.id
                                   AND b2.date_booking = :date_booking_5
                                   AND b2.status <> 2
                            ))
                    ) AS occupied",
                'ARRAY',
                $params
            );
            $occupied = (int) ($row['occupied'] ?? 0);
            $threshold = self::getFieldThreshold((int) $idField);
            return $occupied >= $threshold;
        }

        public static function getById($id){
            Canchas::ensurePriceRangesStorage();
            $reserva = query("SELECT
            b.id,
            b.id_field,
            COALESCE(b.is_fixed, 0) AS is_fixed,
            b.recurring_booking_id,
            f.full_name AS cancha,
            b.time_booking,
            b.date_booking AS fecha,
            h.hour12 AS hora,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            CASE
                WHEN COALESCE(b.source, '') IN ('bot', 'customer_bot', 'whatsapp', 'bot_whatsapp') THEN 'bot'
                WHEN EXISTS (
                    SELECT 1
                      FROM booking_logs bl
                     WHERE bl.id_booking = b.id
                       AND bl.action = 'crear'
                       AND bl.note LIKE '%bot/webhook%'
                     LIMIT 1
                ) THEN 'bot'
                WHEN COALESCE(b.source, '') = '' THEN 'web'
                ELSE b.source
            END AS source,
            s.name AS status_name,
            s.id AS status_id,
            s.color AS status_color,
            COALESCE(NULLIF(f.threshold, 0), 1) AS threshold,
            COALESCE(
                (
                    SELECT pr.price
                    FROM price_ranges pr
                    WHERE pr.id_field = f.id
                      AND h.hour >= pr.start_time
                      AND h.hour < pr.end_time
                    ORDER BY pr.start_time DESC
                    LIMIT 1
                ),
                f.price_hour
            ) AS precio_cancha,
            b.paid_amount as pagado
            FROM
                booking AS b
            INNER JOIN soccer_field AS f ON b.id_field = f.id
            INNER JOIN customers AS c ON b.id_customer = c.id
            INNER JOIN booking_status AS s ON b.status = s.id
            INNER JOIN schedules AS h ON h.id = b.time_booking
             WHERE b.id = ?", '', [$id]);

            if ($reserva) {
                $reserva->slot_number = self::getSlotNumberForBooking(
                    (int) $reserva->id,
                    (int) $reserva->id_field,
                    (string) $reserva->fecha,
                    (int) $reserva->time_booking
                );
                $reserva->threshold = max(1, (int) ($reserva->threshold ?? 1));
            }

            return $reserva;
        }
        public static function getValorCanchaByBooking($id){
            Canchas::ensurePriceRangesStorage();
            $valor = query("SELECT
                                COALESCE(
                                    (
                                        SELECT pr.price
                                        FROM price_ranges pr
                                        INNER JOIN schedules hs ON hs.id = b.time_booking
                                        WHERE pr.id_field = f.id
                                          AND hs.hour >= pr.start_time
                                          AND hs.hour < pr.end_time
                                        ORDER BY pr.start_time DESC
                                        LIMIT 1
                                    ),
                                    f.price_hour
                                ) AS precio_cancha
                             FROM
                                 booking AS b
                             INNER JOIN soccer_field AS f ON b.id_field = f.id
                             WHERE b.id = ?", '', [$id]);
            return $valor;
        }
        public static function getTotalById($id){
            $total = query("SELECT
            p.transaction_amount as cant
            FROM
                booking AS b
            INNER JOIN vouchers AS v ON b.id = v.id_booking
            INNER JOIN payment AS p ON p.payment_id = v.data_id
            WHERE b.id = ?", '', [$id]);
            
            return $total;
        }
        public static function getLogs($Id){
            // Usamos las columnas reales confirmadas: id_booking, id_user, action, note, created_at
            $result = query("SELECT l.*, u.full_name as user_name 
                            FROM booking_logs l 
                            LEFT JOIN users u ON u.id = l.id_user 
                            WHERE l.id_booking = ? 
                            ORDER BY l.created_at DESC", "ALL", [$Id]);
            
            $formatted = [];
            $recentActions = [];
            foreach($result as $l) {
                // Evita duplicados gemelos (trigger + capa de aplicación) creados casi al mismo tiempo.
                $action = strtolower((string) ($l->action ?? ''));
                $keyUser = (int) ($l->id_user ?? 0);
                if (in_array($action, ['cancelar', 'reagendar', 'crear'], true)) {
                    $keyUser = -1;
                }
                $actionKey = $action . '|' . $keyUser;
                $createdTs = strtotime((string) ($l->created_at ?? ''));
                if ($createdTs !== false) {
                    $lastTs = $recentActions[$actionKey] ?? null;
                    if ($lastTs !== null && abs($lastTs - $createdTs) <= 3) {
                        continue;
                    }
                    $recentActions[$actionKey] = $createdTs;
                }

                // booking_logs.created_at se persiste en UTC; lo mostramos en hora local AR (UTC-03).
                try {
                    $dt = new DateTime((string) $l->created_at, new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
                    $f_fecha = $dt->format('d/m/Y');
                    $f_hora = $dt->format('H:i');
                } catch (Exception $e) {
                    $f_fecha = date('d/m/Y');
                    $f_hora = '--:--';
                }
                
                $row = (object)[
                    'fecha' => $f_fecha,
                    'hora' => $f_hora,
                    'user' => $l->user_name ?? 'Sistema',
                    'action' => $l->action,
                    'logs_name' => '',
                    'logs_color' => '',
                    'note' => self::humanizeLogNote((string) ($l->action ?? ''), (string) ($l->note ?? ''))
                ];
                
                // Mapeo de estilos Metronic basado en 'action'
                switch($l->action) {
                    case 'reagendar': case '6': 
                        $row->logs_name = 'Re-agendada'; $row->logs_color = 'warning'; break;
                    case 'cancelar': case '2': 
                        $row->logs_name = 'Cancelada'; $row->logs_color = 'danger'; break;
                    case 'pago_completado': case '3': case 'cash_payment':
                        $row->logs_name = 'Pago Registrado'; $row->logs_color = 'success'; break;
                    case 'crear': case '1':
                        $row->logs_name = 'Reserva Creada'; $row->logs_color = 'primary'; break;
                    default: 
                        $row->logs_name = ucfirst(str_replace('_', ' ', (string) $l->action)); 
                        $row->logs_color = 'info'; break;
                }
                $formatted[] = $row;
            }
           return $formatted;
        }
        public static function getUsuario($id){
            $usuario = query("SELECT 
            u.id as user
            FROM `users` as u inner join booking as b on b.user = u.id
            where u.id = 1 and b.id = ?", '', [$id]);

           return $usuario;
        }
        public static function update($data) {
            query("UPDATE booking SET
                payment = ?,
                status  = ?
              WHERE id = ?", '', [$data->payment, $data->status, $data->id]);
        }
        public static function updateByCalendar($data) {
            $idField = self::getFieldByBookingId((int) $data->id);
            if (!$idField) {
                JSON(['ok' => false, 'error' => 'Reserva no encontrada'], 404);
            }
            $timeBooking = self::resolveScheduleId($data->time_booking ?? null);
            if ($timeBooking <= 0) {
                JSON(['ok' => false, 'error' => 'Horario inválido'], 400);
            }
            if (self::hasSlotConflict($idField, $data->date_booking, $timeBooking, (int) $data->id)) {
                JSON([
                    'ok' => false,
                    'error' => 'El horario seleccionado ya está reservado',
                    'code' => 'SLOT_TAKEN'
                ], 409);
            }

            query("UPDATE booking
                      SET day_booking  = ?,
                          time_booking = ?,
                          date_booking = ?,
                          user         = ?
                    WHERE id = ?", '', [$data->day_booking, $timeBooking, $data->date_booking, $data->user, $data->id]);
            JSON(['ok' => true]);
        }
        public static function add($data){
            $timeBooking = self::resolveScheduleId($data->time_booking ?? null);
            if ($timeBooking <= 0) {
                JSON(['ok' => false, 'error' => 'Horario inválido'], 400);
            }
            $data->time_booking = $timeBooking;
            if (self::hasSlotConflict((int) $data->id_field, $data->date_booking, $timeBooking)) {
                JSON([
                    'ok' => false,
                    'error' => 'El horario seleccionado no tiene más cupos disponibles',
                    'code' => 'SLOT_TAKEN'
                ], 409);
            }
            query("INSERT INTO booking(
                id_customer,
                id_field,
                day_booking,
                time_booking,
                date_booking,
                user,
                status
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    '1'
                )", '', [$data->id_customer, $data->id_field, $data->day_booking, $data->time_booking, $data->date_booking, $data->user]
            );
            $lastId = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
            $data->id = $lastId ? (int) $lastId['id'] : null;
            if (!$data->id) {
                JSON(['ok' => false, 'error' => 'No se pudo crear la reserva'], 500);
            }
            self::addLog($data->id, 'crear'); // Registro inicial
            JSON($data);
        }
        public static function reagendar($data) {
            $idField = self::getFieldByBookingId((int) $data->id);
            if (!$idField) {
                JSON(['ok' => false, 'error' => 'Reserva no encontrada'], 404);
            }
            $timeBooking = self::resolveScheduleId($data->time_booking ?? null);
            if ($timeBooking <= 0) {
                JSON(['ok' => false, 'error' => 'Horario inválido'], 400);
            }
            if (self::hasSlotConflict($idField, $data->date_booking, $timeBooking, (int) $data->id)) {
                JSON([
                    'ok' => false,
                    'error' => 'El horario seleccionado ya está reservado',
                    'code' => 'SLOT_TAKEN'
                ], 409);
            }

            query("UPDATE booking SET
                day_booking  = ?,
                time_booking = ?,
                date_booking = ?,
                user         = ?,
                status       = '6'
            WHERE id = ?", '', [$data->day_booking, $timeBooking, $data->date_booking, $data->user, $data->id]);
            self::addLog($data->id, 'reagendar'); // 6 = Reagendada
            JSON(self::getById($data->id));
        }
        public static function cancel($data){
            query("UPDATE booking SET
                user = ?,
                status       = '2'
            WHERE id = ?", '', [$data->user, $data->id]);
            self::addLog($data->id, 'cancelar'); // 2 = Cancelada
            JSON(self::getById($data->id));
        }
        public static function getBookingByAPI($id){
            $booking = query("SELECT
	                b.id,
	                v.data_id,
	                sf.token_mercadopago,
	                (select hour from schedules where id = b.time_booking) as time,
	                b.date_booking as date,
	                sf.full_name as cancha,
	                c.phone as customer_phone,
	                p.transaction_amount as total
                from booking as b
                inner join vouchers v on
	                v.id_booking = b.id
                inner join payment p on
	                p.payment_id = v.data_id
                inner join soccer_field sf on
	                sf.id  = b.id_field
                inner join customers c on
	                c.id = b.id_customer 
                where b.id = ?", '', [$id]
            );
            JSON($booking);
        }
        public static function cerrarPago($data){
            $bookingId = (int) ($data->id_reserva ?? 0);
            $amount = (float) ($data->cantidad_a_pagar ?? 0);
            $idField = isset($data->id_field) ? (int) $data->id_field : 0;
            $method = $data->metodo_pago ?? 'Efectivo';
            $userId = (int) ($_SESSION['canchero'] ?? 0);

            if ($bookingId <= 0 || $amount <= 0) {
                JSON(['ok' => false, 'error' => 'id_reserva y cantidad_a_pagar son obligatorios y > 0'], 400);
            }

            $pdo = Db::pdo();
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "SELECT id, id_field, total_amount, paid_amount, payment_status
                       FROM booking
                      WHERE id = :id
                      FOR UPDATE"
                );
                $stmt->execute([':id' => $bookingId]);
                $b = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$b) {
                    $pdo->rollBack();
                    JSON(['ok' => false, 'error' => 'Booking not found'], 404);
                }
                if ($idField > 0 && (int) $b['id_field'] !== $idField) {
                    $pdo->rollBack();
                    JSON(['ok' => false, 'error' => 'Booking no pertenece a la cancha indicada'], 403);
                }

                $insert = $pdo->prepare(
                    "INSERT INTO payment_app_web (
                        payment_date,
                        amount_payment,
                        method_payment,
                        id_booking,
                        id_field
                    ) VALUES (CURRENT_DATE(), :amount, :method, :booking_id, :field_id)"
                );
                $insert->execute([
                    ':amount' => $amount,
                    ':method' => $method,
                    ':booking_id' => $bookingId,
                    ':field_id' => (int) $b['id_field'],
                ]);

                $newPaid = (float) ($b['paid_amount'] ?? 0) + $amount;
                $total = (float) ($b['total_amount'] ?? 0);
                $newStatus = ($total > 0 && $newPaid >= $total) ? 'paid' : 'partial';
                $paidInCashAt = $newStatus === 'paid' ? date('Y-m-d H:i:s') : null;

                $update = $pdo->prepare(
                    "UPDATE booking
                        SET paid_amount = :paid,
                            payment_status = :status,
                            paid_in_cash_at = COALESCE(paid_in_cash_at, :pca),
                            paid_in_cash_by_user_id = :uid
                      WHERE id = :id"
                );
                $update->execute([
                    ':paid' => $newPaid,
                    ':status' => $newStatus,
                    ':pca' => $paidInCashAt,
                    ':uid' => $userId > 0 ? $userId : null,
                    ':id' => $bookingId,
                ]);

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('cerrarPago error: ' . $e->getMessage());
                JSON(['ok' => false, 'error' => 'Internal error'], 500);
            }

            self::addLog($bookingId, 'pago_completado', sprintf('Pago %s: $%.2f', $method, $amount));
            JSON([
                'ok' => true,
                'booking_id' => $bookingId,
                'paid_amount' => $newPaid,
                'balance_due' => max(0, $total - $newPaid),
                'payment_status' => $newStatus,
            ]);
        }

        public static function addLog($id_booking, $action, $note = '') {
            $user = $_SESSION['canchero'];
            query("INSERT INTO booking_logs (id_booking, id_user, action, note, created_at) VALUES (?, ?, ?, ?, NOW())", '', [$id_booking, $user, $action, $note]);
        }
    }
