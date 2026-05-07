<?php

    class Booking{
        private static function normalizeBookingSource($source, $isFixed = 0) {
            $src = strtolower(trim((string) $source));
            if ((int) $isFixed === 1) return 'recurring';
            return $src === 'recurring' ? 'recurring' : 'one_off';
        }

        private static function addBookingLog($bookingId, $action, $note = null, $userId = null) {
            query(
                "INSERT INTO booking_logs (id_booking, id_user, action, note, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                '',
                [(int) $bookingId, $userId !== null ? (int) $userId : null, (string) $action, $note]
            );
        }

        private static function assertFieldOwnership($fieldId, $boundEst, $errorMessage = 'Forbidden access to field') {
            if (!$boundEst) return;
            $field = query("SELECT establishment_id FROM soccer_field WHERE id = ?", "ARRAY", [(int) $fieldId]);
            if (!$field || (int) ($field['establishment_id'] ?? 0) !== (int) $boundEst) {
                Api::ApiError(['error' => $errorMessage], 403);
            }
        }

        private static function assertBookingOwnership($bookingId, $boundEst, $errorMessage = 'Forbidden') {
            if (!$boundEst) return;
            $check = query(
                "SELECT sf.establishment_id
                   FROM booking b
                   JOIN soccer_field sf ON sf.id = b.id_field
                  WHERE b.id = ?",
                "ARRAY",
                [(int) $bookingId]
            );
            if (!$check || (int) ($check['establishment_id'] ?? 0) !== (int) $boundEst) {
                Api::ApiError(['error' => $errorMessage], 403);
            }
        }

        private static function hasSlotConflict($fieldId, $dateBooking, $timeBooking, $excludeBookingId = null) {
            if (!$fieldId || !$dateBooking || !$timeBooking) return true;

            $excludeSql = $excludeBookingId ? " AND b.id <> :exclude_id" : "";
            $dow = (int) date('N', strtotime($dateBooking));
            $params = [
                ':id_field' => (int) $fieldId,
                ':date_booking' => $dateBooking,
                ':time_booking' => (int) $timeBooking,
                ':dow' => $dow,
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
                            AND b.status NOT IN (2)
                            AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())
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
                    ) AS occupied,
                    (SELECT COALESCE(NULLIF(threshold, 0), 1) FROM soccer_field WHERE id = :id_field_cap LIMIT 1) AS threshold",
                'ARRAY',
                array_merge($params, [
                    ':time_booking_2' => (int) $timeBooking,
                    ':id_field_2' => (int) $fieldId,
                    ':date_booking_2' => $dateBooking,
                    ':date_booking_3' => $dateBooking,
                    ':date_booking_4' => $dateBooking,
                    ':date_booking_5' => $dateBooking,
                    ':id_field_cap' => (int) $fieldId,
                ])
            );

            $occupied = (int) ($row['occupied'] ?? 0);
            $threshold = max(1, (int) ($row['threshold'] ?? 1));
            return $occupied >= $threshold;
        }

        // PHP-12: id ahora se genera con AUTO_INCREMENT (LAST_INSERT_ID).
        // Se eliminó el `id` del INSERT y getIDNewReserva (race condition).
        //
        // BOT-RACE-FIX: INSERT ... SELECT WHERE NOT EXISTS — atómico, sin lock
        // explícito. Si dos requests intentan reservar la misma cancha+fecha+hora
        // simultáneamente, solo uno gana cuando ya no hay cupos (`threshold`).
        // status NOT IN (2) = ignorar canceladas; status 7 con expires_at vencido
        // tampoco bloquea (pre-reservas expiradas se reciclan).
        private static function set($data){
            $sql = "INSERT INTO booking(
                id_customer, id_field, day_booking, time_booking, date_booking,
                user, status, is_fixed, total_amount, deposit_amount, paid_amount,
                payment_status, source, expires_at
            )
            SELECT :id_customer, :id_field, :day_booking, :time_booking, :date_booking,
                   :user, :status, :is_fixed, :total_amount, :deposit_amount, :paid_amount,
                   :payment_status, :source, :expires_at
            FROM dual
            WHERE (
                (
                    SELECT COUNT(*)
                      FROM booking b
                     WHERE b.id_field = :id_field_chk
                       AND b.date_booking = :date_booking_chk
                       AND b.time_booking = :time_booking_chk
                       AND b.status NOT IN (2)
                       AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW())
                ) + (
                    SELECT COUNT(*)
                      FROM recurring_booking rb
                      INNER JOIN schedules s ON s.id = :time_booking_chk_2
                     WHERE rb.field_id = :id_field_chk_2
                       AND rb.day_of_week = :dow_chk
                       AND rb.start_time <= s.hour
                       AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                       AND rb.status = 'active'
                       AND rb.valid_from <= :date_booking_chk_2
                       AND (rb.valid_until IS NULL OR rb.valid_until >= :date_booking_chk_3)
                       AND NOT EXISTS (
                            SELECT 1
                              FROM recurring_booking_pause p
                             WHERE p.recurring_booking_id = rb.id
                               AND p.status = 'approved'
                               AND :date_booking_chk_4 BETWEEN p.from_date AND p.to_date
                       )
                       AND NOT EXISTS (
                            SELECT 1
                              FROM booking b2
                             WHERE b2.recurring_booking_id = rb.id
                               AND b2.date_booking = :date_booking_chk_5
                               AND b2.status <> 2
                       )
                )
            ) < (
                SELECT COALESCE(NULLIF(sf.threshold, 0), 1)
                  FROM soccer_field sf
                 WHERE sf.id = :id_field_cap
                 LIMIT 1
            )";

            $dow = (int) date('N', strtotime($data->FechaReserva));

            $stmt = conexion($sql);
            $stmt->execute([
                ':id_customer'      => $data->UsuarioPlayerId,
                ':id_field'         => $data->CanchaAsignada,
                ':day_booking'      => $data->DiaReserva,
                ':time_booking'     => $data->HoraReserva,
                ':date_booking'     => $data->FechaReserva,
                ':user'             => $data->UsuarioModificacion,
                ':status'           => $data->status ?? 1,
                ':is_fixed'         => $data->is_fixed,
                ':total_amount'     => isset($data->total_amount)   ? (float) $data->total_amount   : 0,
                ':deposit_amount'   => isset($data->deposit_amount) ? (float) $data->deposit_amount : null,
                ':paid_amount'      => isset($data->paid_amount)    ? (float) $data->paid_amount    : 0,
                ':payment_status'   => $data->payment_status ?? 'pending',
                ':source'           => self::normalizeBookingSource($data->source ?? 'one_off', $data->is_fixed ?? 0),
                ':expires_at'       => $data->expires_at ?? null,
                ':id_field_chk'     => $data->CanchaAsignada,
                ':date_booking_chk' => $data->FechaReserva,
                ':time_booking_chk' => $data->HoraReserva,
                ':time_booking_chk_2' => $data->HoraReserva,
                ':id_field_chk_2'     => $data->CanchaAsignada,
                ':dow_chk'            => $dow,
                ':date_booking_chk_2' => $data->FechaReserva,
                ':date_booking_chk_3' => $data->FechaReserva,
                ':date_booking_chk_4' => $data->FechaReserva,
                ':date_booking_chk_5' => $data->FechaReserva,
                ':id_field_cap'       => $data->CanchaAsignada,
            ]);

            if ($stmt->rowCount() === 0) {
                // Conflicto: otro proceso reservó el mismo slot.
                return null;
            }

            $row = query("SELECT LAST_INSERT_ID() AS id");
            return $row ? (int) $row->id : null;
        }
        private static function updateCheckOut($data_id, $id_booking){
            $sql = "UPDATE vouchers SET id_booking = ? WHERE data_id = ?";
            return query($sql, '', [$id_booking, $data_id]);
        }
        public static function getBookingByAPI($id){
            $booking = query("SELECT
	                b.id,
	                v.data_id,
	                COALESCE(e.mp_access_token, sf.token_mercadopago) AS token_mercadopago,
	                (select hour from schedules where id = b.time_booking) as time,
	                b.date_booking as date,
	                sf.full_name as cancha,
	                c.phone as customer_phone,
	                COALESCE(p.transaction_amount, b.total_amount, 0) as total
                from booking as b
                left join vouchers v on
	                v.id_booking = b.id
                left join payment p on
	                p.payment_id = v.data_id
                inner join soccer_field sf on
	                sf.id  = b.id_field
                left join establishment e on
                    e.id = sf.establishment_id
                inner join customers c on
	                c.id = b.id_customer 
                where b.id = ?", '', [$id]
            );

            return JSON($booking);
        }
        public static function add(){
            $data = Api::getData();

            $data->full_name            = $data->name;
            $data->email                = (isset($_POST['email'])) ? $_POST['email'] : '';
            $data->UsuarioPlayerId      = Customers::checkExitCustomerOCreate($data)->id;
            $data->CanchaAsignada       = $data->id_cancha;
            // PHP-B10: aceptar `created_by` desde el bot/web; default 'bot' = user 1.
            $data->UsuarioModificacion  = isset($data->created_by_user_id) ? (int) $data->created_by_user_id : 1;
            $data->FechaReserva         = $data->fecha;
            $data->HoraReserva          = $data->hora;
            $data->DiaReserva           = dayName($data->fecha);
            $data->is_fixed             = (isset($data->is_fixed)) ? $data->is_fixed : 0;

            $boundEst = Auth::getEstablishmentId();
            self::assertFieldOwnership($data->id_cancha, $boundEst, 'Forbidden: Court does not belong to this token');

            // Si vino seña: deposit_amount = paid_amount inicial.
            if (isset($data->deposit_amount) && !isset($data->paid_amount)) {
                $data->paid_amount = $data->deposit_amount;
                $data->payment_status = 'partial';
            }
            if (isset($data->total_amount) && isset($data->paid_amount)
                && (float) $data->paid_amount >= (float) $data->total_amount) {
                $data->payment_status = 'paid';
            }

            $newId = self::set($data);
            if ($newId === null) {
                // Race detectada: el slot ya fue tomado por otra reserva.
                Api::ApiError([
                    'error' => 'Slot already booked',
                    'code'  => 'SLOT_TAKEN',
                ], 409);
                return;
            }
            $data->id = $newId;
            self::updateCheckOut($data->data_id, $newId);
            self::addBookingLog(
                $newId,
                'crear',
                !empty($data->data_id)
                    ? 'Reserva creada desde bot/webhook. payment_id=' . $data->data_id
                    : 'Reserva creada'
            );
            if (!empty($data->payment_status) && $data->payment_status !== 'pending') {
                self::addBookingLog(
                    $newId,
                    'pago_completado',
                    sprintf(
                        'Pago registrado (status=%s, paid_amount=%s, total_amount=%s)',
                        (string) $data->payment_status,
                        (string) ($data->paid_amount ?? 0),
                        (string) ($data->total_amount ?? 0)
                    )
                );
            }
            self::getBookingByAPI($newId);
        }

        /**
         * GET /api/v2/?action=booking_getBalance&booking_id=X
         * Devuelve total / pagado / saldo y status para mostrar en bot y web.
         */
        public static function getBalance() {
            $bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
            if ($bookingId <= 0) {
                Api::ApiError(['error' => 'booking_id es obligatorio'], 400);
            }
            $b = query(
                "SELECT id, total_amount, deposit_amount, paid_amount, payment_status,
                        paid_in_cash_at
                   FROM booking WHERE id = :id",
                'ARRAY',
                [':id' => $bookingId]
            );
            if (!$b) {
                Api::ApiError(['error' => 'Booking not found'], 404);
            }

            $boundEst = Auth::getEstablishmentId();
            self::assertBookingOwnership($bookingId, $boundEst);
            $total = (float) $b['total_amount'];
            $paid = (float) $b['paid_amount'];
            JSON([
                'booking_id'     => (int) $b['id'],
                'total_amount'   => $total,
                'deposit_amount' => $b['deposit_amount'] !== null ? (float) $b['deposit_amount'] : null,
                'paid_amount'    => $paid,
                'balance_due'    => max(0, $total - $paid),
                'payment_status' => $b['payment_status'],
                'paid_in_cash_at'=> $b['paid_in_cash_at'],
            ]);
        }

        /**
         * GET /api/v2/?action=booking_listAvailable&establishment_id=X&field_id=Y&date=YYYY-MM-DD
         */
        public static function listAvailable() {
            $fieldId = isset($_GET['field_id']) ? (int) $_GET['field_id'] : 0;
            $date = $_GET['date'] ?? null;
            if (!$fieldId || !$date) {
                Api::ApiError(['error' => 'field_id y date son obligatorios'], 400);
            }

            $boundEst = Auth::getEstablishmentId();
            self::assertFieldOwnership($fieldId, $boundEst);
            $dow = (int) date('N', strtotime($date));

            $rows = query(
                "SELECT
                    s.id,
                    s.hour,
                    COALESCE(NULLIF(sf.threshold, 0), 1) AS threshold,
                    (
                        (SELECT COUNT(*)
                           FROM booking b
                          WHERE b.id_field = :field
                            AND b.date_booking = :date
                            AND b.time_booking = s.id
                            AND b.status NOT IN (2)
                            AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW()))
                        +
                        (SELECT COUNT(*)
                           FROM recurring_booking rb
                          WHERE rb.field_id = :field2
                            AND rb.day_of_week = :dow
                            AND rb.start_time <= s.hour
                            AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                            AND rb.status = 'active'
                            AND rb.valid_from <= :date2
                            AND (rb.valid_until IS NULL OR rb.valid_until >= :date3)
                            AND NOT EXISTS (
                                SELECT 1 FROM recurring_booking_pause p
                                 WHERE p.recurring_booking_id = rb.id
                                   AND p.status = 'approved'
                                   AND :date4 BETWEEN p.from_date AND p.to_date
                            )
                            AND NOT EXISTS (
                                SELECT 1 FROM booking b2
                                 WHERE b2.recurring_booking_id = rb.id
                                   AND b2.date_booking = :date5
                                   AND b2.status <> 2
                            ))
                    ) AS occupied,
                    GREATEST(0, COALESCE(NULLIF(sf.threshold, 0), 1) - (
                        (SELECT COUNT(*)
                           FROM booking b
                          WHERE b.id_field = :field6
                            AND b.date_booking = :date6
                            AND b.time_booking = s.id
                            AND b.status NOT IN (2)
                            AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW()))
                        +
                        (SELECT COUNT(*)
                           FROM recurring_booking rb
                          WHERE rb.field_id = :field7
                            AND rb.day_of_week = :dow2
                            AND rb.start_time <= s.hour
                            AND ADDTIME(rb.start_time, SEC_TO_TIME(rb.duration_min * 60)) > s.hour
                            AND rb.status = 'active'
                            AND rb.valid_from <= :date7
                            AND (rb.valid_until IS NULL OR rb.valid_until >= :date8)
                            AND NOT EXISTS (
                                SELECT 1 FROM recurring_booking_pause p
                                 WHERE p.recurring_booking_id = rb.id
                                   AND p.status = 'approved'
                                   AND :date9 BETWEEN p.from_date AND p.to_date
                            )
                            AND NOT EXISTS (
                                SELECT 1 FROM booking b2
                                 WHERE b2.recurring_booking_id = rb.id
                                   AND b2.date_booking = :date10
                                   AND b2.status <> 2
                            ))
                    )) AS free_slots
                   FROM schedules s
                   INNER JOIN schedules_field sfh ON sfh.id_schedule = s.id
                   INNER JOIN soccer_field sf ON sf.id = sfh.id_field
                  WHERE sfh.id_field = :field3
                    AND sfh.id_day = :dow_name
                  HAVING occupied < threshold
                  ORDER BY s.hour",
                'ARRAY_ALL',
                [
                    ':field'  => $fieldId,
                    ':date'   => $date,
                    ':field2' => $fieldId,
                    ':dow'    => $dow,
                    ':date2'  => $date,
                    ':date3'  => $date,
                    ':date4'  => $date,
                    ':date5'  => $date,
                    ':field6' => $fieldId,
                    ':date6'  => $date,
                    ':field7' => $fieldId,
                    ':dow2'   => $dow,
                    ':date7'  => $date,
                    ':date8'  => $date,
                    ':date9'  => $date,
                    ':date10' => $date,
                    ':field3' => $fieldId,
                    ':dow_name' => dayName($date),
                ]
            );
            JSON($rows ?: []);
        }

        public static function checkAvailability() {
            $data = Api::getData();
            if (!isset($data->id_field) || !isset($data->date) || !isset($data->hour)) {
                Api::ApiError(['error' => 'id_field, date and hour are required'], 400);
            }

            $boundEst = Auth::getEstablishmentId();
            self::assertFieldOwnership($data->id_field, $boundEst);

            $dow = (int) date('N', strtotime($data->date));
            $row = query(
                "SELECT
                    (
                        (SELECT COUNT(*)
                           FROM booking b
                          WHERE b.id_field = :id_field
                            AND b.date_booking = :date_booking
                            AND b.time_booking = :time_booking
                            AND b.status NOT IN (2)
                            AND (b.status <> 7 OR b.expires_at IS NULL OR b.expires_at > NOW()))
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
                    ) AS occupied,
                    (SELECT COALESCE(NULLIF(threshold, 0), 1) FROM soccer_field WHERE id = :id_field_cap LIMIT 1) AS threshold",
                'ARRAY',
                [
                    ':id_field' => (int) $data->id_field,
                    ':date_booking' => $data->date,
                    ':time_booking' => (int) $data->hour,
                    ':time_booking_2' => (int) $data->hour,
                    ':id_field_2' => (int) $data->id_field,
                    ':dow' => $dow,
                    ':date_booking_2' => $data->date,
                    ':date_booking_3' => $data->date,
                    ':date_booking_4' => $data->date,
                    ':date_booking_5' => $data->date,
                    ':id_field_cap' => (int) $data->id_field,
                ]
            );

            $occupied = (int) ($row['occupied'] ?? 0);
            $threshold = max(1, (int) ($row['threshold'] ?? 1));

            JSON(['isAvailable' => $occupied < $threshold]);
        }

        /** Métodos Migrados v2 **/
        public static function cancel(){
            $data = Api::getData();
            if (!isset($data->id_booking)) {
                Api::ApiError(['error' => 'id_booking is required'], 400);
            }

            $boundEst = Auth::getEstablishmentId();
            self::assertBookingOwnership($data->id_booking, $boundEst);
            
            // Status 2 = Cancelado
            $sql = "UPDATE booking SET user = ?, status = '2' WHERE id = ?";
            query($sql, '', [1, $data->id_booking]);
            self::addBookingLog((int) $data->id_booking, 'cancelar', 'Reserva cancelada desde API v2', 1);
            
            audit('booking_cancel', 'booking', $data->id_booking);
            
            JSON(['status' => 'success', 'message' => 'Booking cancelled', 'id' => $data->id_booking]);
        }

        public static function move() {
            $data = Api::getData();
            $bookingId = (int) ($data->id_booking ?? 0);
            $dateBooking = (string) ($data->date_booking ?? '');
            $timeBooking = (int) ($data->time_booking ?? 0);
            $updatedBy = isset($data->updated_by_user_id) ? (int) $data->updated_by_user_id : 1;

            if (!$bookingId || !$dateBooking || !$timeBooking) {
                Api::ApiError(['error' => 'id_booking, date_booking y time_booking son obligatorios'], 400);
            }

            $boundEst = Auth::getEstablishmentId();
            self::assertBookingOwnership($bookingId, $boundEst);

            $booking = query(
                "SELECT id, id_field, status, is_fixed
                   FROM booking
                  WHERE id = ?",
                'ARRAY',
                [$bookingId]
            );

            if (!$booking) {
                Api::ApiError(['error' => 'Reserva no encontrada'], 404);
            }
            if ((int) ($booking['is_fixed'] ?? 0) === 1) {
                Api::ApiError(['error' => 'Las reservas fijas se re-agendan desde su flujo específico'], 400);
            }
            if ((int) ($booking['status'] ?? 0) === 2) {
                Api::ApiError(['error' => 'No se puede re-agendar una reserva cancelada'], 400);
            }

            $fieldId = (int) ($booking['id_field'] ?? 0);
            $dayBooking = dayName($dateBooking);

            if (self::hasSlotConflict($fieldId, $dateBooking, $timeBooking, $bookingId)) {
                Api::ApiError([
                    'error' => 'El horario seleccionado ya está reservado',
                    'code' => 'SLOT_TAKEN',
                ], 409);
            }

            query(
                "UPDATE booking
                    SET day_booking = ?,
                        date_booking = ?,
                        time_booking = ?,
                        user = ?,
                        status = '6'
                  WHERE id = ?",
                '',
                [$dayBooking, $dateBooking, $timeBooking, $updatedBy, $bookingId]
            );

            self::addBookingLog(
                $bookingId,
                'reagendar',
                sprintf(
                    'Reserva re-agendada desde API v2 a %s (%s)',
                    $dateBooking,
                    $timeBooking
                ),
                $updatedBy
            );

            JSON([
                'ok' => true,
                'status' => 'success',
                'message' => 'Booking moved',
                'id' => $bookingId,
            ]);
        }

        public static function confirm() {
            $d = Api::getData();
            $id = (int) ($d->id ?? 0);
            if (!$id) Api::ApiError(['error' => 'id required'], 400);
            $boundEst = Auth::getEstablishmentId();
            self::assertBookingOwnership($id, $boundEst);
            query("UPDATE booking SET status = 1, paid_amount = total_amount, payment_status = 'paid' WHERE id = ?", '', [$id]);
            if ($d->payment_id ?? null) query("UPDATE vouchers SET id_booking = ? WHERE data_id = ?", '', [$id, $d->payment_id]);
            self::addBookingLog(
                $id,
                'pago_completado',
                !empty($d->payment_id)
                    ? 'Pago aprobado confirmado. payment_id=' . $d->payment_id
                    : 'Pago aprobado confirmado'
            );
            JSON(['ok' => true]);
        }

        /**
         * GET /api/v2/?action=booking_listByPhone&phone=XXX
         * Lista reservas (activas/canceladas/completadas) de un cliente por teléfono.
         * BOT-TENANT: scoped al establishment del token bound.
         */
        public static function listByPhone() {
            $phone = $_GET['phone'] ?? null;
            if (!$phone) Api::ApiError(['error' => 'phone is required'], 400);
            $phoneDigits = preg_replace('/\D+/', '', (string) $phone);
            if (!$phoneDigits) Api::ApiError(['error' => 'phone is invalid'], 400);
            $last10 = strlen($phoneDigits) > 10 ? substr($phoneDigits, -10) : $phoneDigits;

            $boundEst = Auth::getEstablishmentId();
            $estFilter = '';
            $params = [
                ':phone_full' => $phoneDigits,
                ':phone_last10' => $last10,
            ];
            if ($boundEst) {
                $estFilter = ' AND sf.establishment_id = :est ';
                $params[':est'] = (int) $boundEst;
            }

            $phoneExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.phone, '+', ''), '-', ''), ' ', ''), '(', ''), ')', '')";

            $rows = query(
                "SELECT
                    b.id,
                    b.id_field,
                    b.time_booking,
                    CASE b.status
                        WHEN 1 THEN 'Activo'
                        WHEN 6 THEN 'Activo'
                        WHEN 2 THEN 'Cancelado'
                        WHEN 3 THEN 'Completado'
                        WHEN 7 THEN 'Pendiente'
                        ELSE 'Otro'
                    END AS status,
                    b.is_fixed,
                    DATE_FORMAT(b.date_booking, '%d/%m/%Y') AS fecha,
                    b.day_booking AS day,
                    s.hour AS time,
                    sf.full_name AS cancha,
                    sf.latitude AS latitud,
                    sf.length AS longitud,
                    COALESCE(e.mp_access_token, sf.token_mercadopago) AS access_token,
                    p.payment_id AS paymentId,
                    p.transaction_amount
                 FROM booking b
                 INNER JOIN customers c ON c.id = b.id_customer
                 INNER JOIN soccer_field sf ON sf.id = b.id_field
                 LEFT JOIN establishment e ON e.id = sf.establishment_id
                 LEFT JOIN schedules s ON s.id = b.time_booking
                 LEFT JOIN vouchers v ON v.id_booking = b.id
                 LEFT JOIN payment p ON p.payment_id = v.data_id
                 WHERE (
                    $phoneExpr = :phone_full
                    OR RIGHT($phoneExpr, 10) = :phone_last10
                 )
                 $estFilter
                 ORDER BY b.date_booking DESC, b.time_booking DESC",
                'ARRAY_ALL',
                $params
            );
            JSON($rows ?: []);
        }
    }
