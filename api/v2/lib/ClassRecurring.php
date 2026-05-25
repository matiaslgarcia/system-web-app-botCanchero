<?php

/**
 * Endpoints de reserva fija (recurring_booking).
 *
 * Convención URL: ?action=recurring_<method>
 *   recurring_addBooking      POST   crear nueva fija + asociar setup_fee_payment
 *   recurring_listBookings    GET    ?customer_id=... | ?establishment_id=...
 *   recurring_moveBooking     POST   cambiar día/hora; regenera bookings futuros sin tocar pagados
 *   recurring_cancelBooking   POST   cancela; libera slot
 *   recurring_pauseRequest    POST   cliente pide pausa con motivo (queda pending)
 *   recurring_pauseReview     POST   canchero aprueba/rechaza la pausa
 *   recurring_listPauses      GET    ?establishment_id=...&status=pending  bandeja del canchero
 *   recurring_generateWeek    POST   cron semanal genera bookings para próximos 7 días
 */
class Recurring {
    private static function assertRecurringOwnership($recurringId, $boundEst, $error = 'Forbidden') {
        if (!$boundEst) return;
        $owner = query("SELECT establishment_id FROM recurring_booking WHERE id = ?", "ARRAY", [(int) $recurringId]);
        if (!$owner || (int) ($owner['establishment_id'] ?? 0) !== (int) $boundEst) {
            Api::ApiError(['error' => $error], 403);
        }
    }

    private static function getRecurringById($recurringId) {
        return query(
            "SELECT rb.*,
                    sf.full_name AS field_name,
                    c.full_name AS customer_name,
                    c.phone AS customer_phone
               FROM recurring_booking rb
               LEFT JOIN soccer_field sf ON sf.id = rb.field_id
               LEFT JOIN customers c ON c.id = rb.customer_id
              WHERE rb.id = ?
              LIMIT 1",
            'ARRAY',
            [(int) $recurringId]
        ) ?: null;
    }

    private static function validateOccurrenceDateForRecurring($recurring, $date) {
        $date = (string) $date;
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Api::ApiError(['error' => 'occurrence_date must be YYYY-MM-DD'], 400);
        }
        if ($date < (string) ($recurring['valid_from'] ?? '')) {
            Api::ApiError(['error' => 'La fecha está fuera de vigencia de la reserva fija'], 409);
        }
        if (!empty($recurring['valid_until']) && $date > (string) $recurring['valid_until']) {
            Api::ApiError(['error' => 'La fecha está fuera de vigencia de la reserva fija'], 409);
        }
        if ((int) date('N', strtotime($date)) !== (int) ($recurring['day_of_week'] ?? 0)) {
            Api::ApiError(['error' => 'La fecha no coincide con el día semanal de la reserva fija'], 409);
        }
    }

    private static function buildRecurringPayload($recurring, $extra = []) {
        return array_merge([
            'establishment_id' => (int) ($recurring['establishment_id'] ?? 0),
            'field_id' => (int) ($recurring['field_id'] ?? 0),
            'customer_id' => (int) ($recurring['customer_id'] ?? 0),
            'customer_phone' => (string) ($recurring['customer_phone'] ?? ''),
            'day_of_week' => (int) ($recurring['day_of_week'] ?? 0),
            'start_time' => (string) ($recurring['start_time'] ?? ''),
            'duration_min' => (int) ($recurring['duration_min'] ?? 0),
            'valid_from' => (string) ($recurring['valid_from'] ?? ''),
            'valid_until' => (string) ($recurring['valid_until'] ?? ''),
            'status' => (string) ($recurring['status'] ?? ''),
            'field_name' => (string) ($recurring['field_name'] ?? ''),
            'customer_name' => (string) ($recurring['customer_name'] ?? ''),
        ], $extra);
    }

    private static function buildOccurrenceDateTime($date, $startTime) {
        $date = trim((string) $date);
        $time = substr(trim((string) $startTime), 0, 5);
        if ($date === '' || $time === '') return null;
        $ts = strtotime($date . ' ' . $time . ':00');
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    /**
     * POST /api/v2/?action=recurring_addBooking
     * Body JSON: {
     *   establishment_id, field_id, customer_id (o customer_phone),
     *   day_of_week (1-7 ISO), start_time ("HH:MM:SS"), duration_min,
     *   valid_from ("YYYY-MM-DD"), valid_until ("YYYY-MM-DD" | null),
     *   setup_fee_amount, setup_fee_payment_id (MP payment id si ya pagó),
     *   created_by ("customer_bot"|"owner_web")
     * }
     */
    public static function addBooking() {
        $d = Api::getData();
        $boundEst = Auth::getEstablishmentId();
        $establishmentId = $boundEst ?? (int)($d->establishment_id ?? 0);
        
        if (!$establishmentId) {
            Api::ApiError(['error' => 'establishment_id is required'], 400);
        }

        // Resolver customer_id si vino solo phone
        $customerId = isset($d->customer_id)
            ? (int) $d->customer_id
            : self::resolveCustomerByPhone($d->customer_phone ?? null);

        if (!$customerId) {
            Api::ApiError(['error' => 'Customer not found'], 404);
        }

        // Validar conflicto: ¿hay otra fija activa pisando ese slot?
        $conflict = query(
            "SELECT id FROM recurring_booking
              WHERE establishment_id = :est
                AND field_id = :field
                AND day_of_week = :dow
                AND status IN ('active','pending_payment')
                AND (:start < ADDTIME(start_time, SEC_TO_TIME(duration_min*60)))
                AND (ADDTIME(:start2, SEC_TO_TIME(:dur*60)) > start_time)
                AND (valid_until IS NULL OR valid_until >= :vfrom)",
            'ARRAY',
            [
                ':est'   => $establishmentId,
                ':field' => (int) $d->field_id,
                ':dow'   => (int) $d->day_of_week,
                ':start' => $d->start_time,
                ':start2'=> $d->start_time,
                ':dur'   => (int) $d->duration_min,
                ':vfrom' => $d->valid_from,
            ]
        );
        if ($conflict) {
            Api::ApiError(['error' => 'Slot ya ocupado por otra reserva fija', 'conflict_id' => $conflict['id']], 409);
        }

        $hasPayment = !empty($d->setup_fee_payment_id);

        $sql = "INSERT INTO recurring_booking (
            establishment_id, field_id, customer_id,
            day_of_week, start_time, duration_min,
            valid_from, valid_until,
            setup_fee_amount, setup_fee_payment_id, setup_paid_at,
            status, created_by
        ) VALUES (
            :est, :field, :cust,
            :dow, :start, :dur,
            :vfrom, :vuntil,
            :fee, :payid, :paid_at,
            :status, :cby
        )";

        query($sql, '', [
            ':est'     => $establishmentId,
            ':field'   => (int) $d->field_id,
            ':cust'    => $customerId,
            ':dow'     => (int) $d->day_of_week,
            ':start'   => $d->start_time,
            ':dur'     => (int) $d->duration_min,
            ':vfrom'   => $d->valid_from,
            ':vuntil'  => $d->valid_until ?? null,
            ':fee'     => (float) ($d->setup_fee_amount ?? 0),
            ':payid'   => $d->setup_fee_payment_id ?? null,
            ':paid_at' => $hasPayment ? date('Y-m-d H:i:s') : null,
            ':status'  => $hasPayment ? 'active' : 'pending_payment',
            ':cby'     => $d->created_by ?? 'customer_bot',
        ]);

        $newId = query("SELECT LAST_INSERT_ID() AS id")->id;
        JSON(['id' => (int) $newId, 'status' => $hasPayment ? 'active' : 'pending_payment']);
    }

    /**
     * GET /api/v2/?action=recurring_listBookings&customer_id=X
     * o &establishment_id=Y
     */
    public static function listBookings() {
        $where = [];
        $params = [];
        if (isset($_GET['customer_id'])) {
            $where[] = 'rb.customer_id = :cust';
            $params[':cust'] = (int) $_GET['customer_id'];
        }
        if (isset($_GET['establishment_id']) || Auth::getEstablishmentId()) {
            $where[] = 'rb.establishment_id = :est';
            $params[':est'] = Auth::getEstablishmentId() ?? (int) $_GET['establishment_id'];
        }
        if (isset($_GET['field_id'])) {
            $where[] = 'rb.field_id = :field';
            $params[':field'] = (int) $_GET['field_id'];
        }
        if (isset($_GET['status'])) {
            $where[] = 'rb.status = :st';
            $params[':st'] = $_GET['status'];
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $rows = query(
            "SELECT rb.*, sf.full_name AS field_name, c.full_name AS customer_name, c.phone AS customer_phone
               FROM recurring_booking rb
               LEFT JOIN soccer_field sf ON sf.id = rb.field_id
               LEFT JOIN customers c ON c.id = rb.customer_id
              $whereSql
              ORDER BY rb.day_of_week, rb.start_time",
            'ARRAY_ALL',
            $params
        );
        JSON($rows ?: []);
    }

    /**
     * POST /api/v2/?action=recurring_moveBooking
     * Body: { id, day_of_week, start_time, duration_min }
     * Cancela bookings futuros NO pagados generados por esta fija y los regenera al nuevo horario.
     */
    public static function moveBooking() {
        $d = Api::getData();
        $id = (int) $d->id;
        $boundEst = Auth::getEstablishmentId();

        // Validar que el booking pertenece al establecimiento del token
        if ($boundEst) {
            self::assertRecurringOwnership($id, $boundEst);
        }

        // Validar conflicto en el nuevo slot
        $conflict = query(
            "SELECT id FROM recurring_booking
              WHERE id <> :id
                AND establishment_id = (SELECT establishment_id FROM recurring_booking WHERE id = :id2)
                AND field_id = (SELECT field_id FROM recurring_booking WHERE id = :id3)
                AND day_of_week = :dow
                AND status IN ('active','pending_payment')
                AND (:start < ADDTIME(start_time, SEC_TO_TIME(duration_min*60)))
                AND (ADDTIME(:start2, SEC_TO_TIME(:dur*60)) > start_time)",
            'ARRAY',
            [
                ':id'    => $id,
                ':id2'   => $id,
                ':id3'   => $id,
                ':dow'   => (int) $d->day_of_week,
                ':start' => $d->start_time,
                ':start2'=> $d->start_time,
                ':dur'   => (int) $d->duration_min,
            ]
        );
        if ($conflict) {
            Api::ApiError(['error' => 'Conflicto con otra fija en el nuevo horario', 'conflict_id' => $conflict['id']], 409);
        }

        query(
            "UPDATE recurring_booking
                SET day_of_week = :dow,
                    start_time = :start,
                    duration_min = :dur
              WHERE id = :id",
            '',
            [
                ':id'    => $id,
                ':dow'   => (int) $d->day_of_week,
                ':start' => $d->start_time,
                ':dur'   => (int) $d->duration_min,
            ]
        );

        // Cancelar bookings futuros NO pagados generados por esta fija
        query(
            "UPDATE booking
                SET status = 2
              WHERE recurring_booking_id = :id
                AND date_booking >= CURDATE()
                AND (paid_amount IS NULL OR paid_amount = 0)",
            '',
            [':id' => $id]
        );

        JSON(['ok' => true]);
    }

    /**
     * POST /api/v2/?action=recurring_cancelBooking
     * Body: { id, reason }
     */
    public static function cancelBooking() {
        $d = Api::getData();
        $id = (int) ($d->id ?? 0);
        if (!$id) Api::ApiError(['error' => 'id is required'], 400);
        $boundEst = Auth::getEstablishmentId();
        self::assertRecurringOwnership($id, $boundEst);
        query(
            "UPDATE recurring_booking
                SET status = 'cancelled',
                    cancelled_at = NOW(),
                    cancelled_reason = :reason
              WHERE id = :id",
            '',
            [':id' => $id, ':reason' => $d->reason ?? null]
        );
        // Cancelar también bookings futuros generados, pagados o no — el cliente decidió cortar.
        query(
            "UPDATE booking
                SET status = 2
              WHERE recurring_booking_id = :id
                AND date_booking >= CURDATE()",
            '',
            [':id' => $id]
        );
        JSON(['ok' => true]);
    }

    /**
     * POST /api/v2/?action=recurring_pauseRequest
     * Body: { recurring_booking_id, from_date, to_date, reason, requested_by }
     */
    public static function pauseRequest() {
        $d = Api::getData();
        $rbId = (int) ($d->recurring_booking_id ?? 0);
        if (!$rbId) Api::ApiError(['error' => 'recurring_booking_id is required'], 400);
        $boundEst = Auth::getEstablishmentId();
        self::assertRecurringOwnership($rbId, $boundEst);
        $recurring = self::getRecurringById($rbId);
        if (!$recurring) {
            Api::ApiError(['error' => 'Reserva fija no encontrada'], 404);
        }
        $rules = BusinessRules::getByEstablishment((int) ($recurring['establishment_id'] ?? 0));
        if ((int) ($rules['allow_customer_pause_request'] ?? 0) !== 1) {
            Api::ApiError(['error' => 'Las pausas de reservas fijas están deshabilitadas'], 403);
        }
        $fromDate = trim((string) ($d->from_date ?? ''));
        $toDate = trim((string) ($d->to_date ?? ''));
        if ($fromDate === '' || $toDate === '') {
            Api::ApiError(['error' => 'from_date y to_date son obligatorios'], 400);
        }
        self::validateOccurrenceDateForRecurring($recurring, $fromDate);
        self::validateOccurrenceDateForRecurring($recurring, $toDate);
        if ($toDate < $fromDate) {
            Api::ApiError(['error' => 'to_date no puede ser menor a from_date'], 409);
        }
        $bookingDateTime = self::buildOccurrenceDateTime($fromDate, $recurring['start_time'] ?? '');
        if (!BusinessRules::canCustomerPauseAt((int) ($recurring['establishment_id'] ?? 0), $bookingDateTime)) {
            Api::ApiError([
                'error' => 'La política del establecimiento solo permite pausar una reserva fija con al menos ' . (int) ($rules['customer_pause_min_hours'] ?? 0) . ' horas de anticipación.'
            ], 409);
        }
        $existingPause = query(
            "SELECT id, status
               FROM recurring_booking_pause
              WHERE recurring_booking_id = :rb
                AND (
                    :from BETWEEN from_date AND to_date
                    OR :to BETWEEN from_date AND to_date
                    OR from_date BETWEEN :from AND :to
                    OR to_date BETWEEN :from AND :to
                )
              ORDER BY id DESC
              LIMIT 1",
            'ARRAY',
            [':rb' => $rbId, ':from' => $fromDate, ':to' => $toDate]
        );
        if ($existingPause && in_array((string) ($existingPause['status'] ?? ''), ['pending', 'approved'], true)) {
            Api::ApiError(['error' => 'Ya existe una pausa para ese rango solicitado'], 409);
        }
        query(
            "INSERT INTO recurring_booking_pause
                (recurring_booking_id, from_date, to_date, reason, status, requested_by, requested_min_hours)
             VALUES (:rb, :from, :to, :reason, 'pending', :rby, :min_hours)",
            '',
            [
                ':rb'     => $rbId,
                ':from'   => $fromDate,
                ':to'     => $toDate,
                ':reason' => $d->reason ?? null,
                ':rby'    => $d->requested_by ?? 'customer_bot',
                ':min_hours' => (int) ($rules['customer_pause_min_hours'] ?? 0),
            ]
        );
        $newId = query("SELECT LAST_INSERT_ID() AS id")->id;
        audit('recurring_pause_request', 'recurring_booking_pause', (int) $newId, [
            'recurring_booking_id' => $rbId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'requested_by' => (string) ($d->requested_by ?? 'customer_bot'),
            'requested_min_hours' => (int) ($rules['customer_pause_min_hours'] ?? 0),
        ]);
        domain_event('recurring_pause_requested', 'recurring_booking_pause', (int) $newId, self::buildRecurringPayload($recurring, [
            'pause_id' => (int) $newId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'requested_by' => (string) ($d->requested_by ?? 'customer_bot'),
            'requested_min_hours' => (int) ($rules['customer_pause_min_hours'] ?? 0),
        ]), [
            'source' => 'api_v2',
        ]);
        JSON(['id' => (int) $newId, 'status' => 'pending']);
    }

    /**
     * POST /api/v2/?action=recurring_pauseReview
     * Body: { pause_id, decision ("approved"|"rejected"), reviewed_by_user_id, review_note }
     */
    public static function pauseReview() {
        $d = Api::getData();
        $decision = in_array($d->decision, ['approved', 'rejected']) ? $d->decision : null;
        if (!$decision) Api::ApiError(['error' => 'decision must be approved|rejected'], 400);

        $pauseId = (int) ($d->pause_id ?? 0);
        $reviewerId = isset($d->reviewed_by_user_id) ? (int) $d->reviewed_by_user_id : 0;
        if (!$pauseId || !$reviewerId) {
            Api::ApiError(['error' => 'pause_id y reviewed_by_user_id son obligatorios'], 400);
        }

        // PHP-13: ownership — validar que el reviewer pertenezca al mismo establishment
        // que el recurring_booking de la pausa. Bloquea aprobaciones cross-tenant.
        $row = query(
            "SELECT rb.establishment_id AS pause_est, u.id_field AS reviewer_field, u.rol AS rol,
                    sf.establishment_id AS reviewer_est
               FROM recurring_booking_pause p
               JOIN recurring_booking rb ON rb.id = p.recurring_booking_id
               JOIN users u ON u.id = :uid
               LEFT JOIN soccer_field sf ON sf.id = u.id_field
              WHERE p.id = :pid",
            'ARRAY',
            [':pid' => $pauseId, ':uid' => $reviewerId]
        );
        if (!$row) Api::ApiError(['error' => 'Pausa o reviewer inexistente'], 404);
        $boundEst = Auth::getEstablishmentId();
        if ($boundEst && (int) ($row['pause_est'] ?? 0) !== (int) $boundEst) {
            Api::ApiError(['error' => 'Forbidden'], 403);
        }
        $isSuperAdmin = ($row['rol'] ?? '') === 'superAdmin';
        if (!$isSuperAdmin && (int) $row['pause_est'] !== (int) ($row['reviewer_est'] ?? 0)) {
            Api::ApiError(['error' => 'Sin permiso para revisar esta pausa'], 403);
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
                ':rev'    => $reviewerId,
                ':note'   => $d->review_note ?? null,
            ]
        );

        // Si se aprueba, cancelar bookings ya generados que caen dentro de la pausa
        if ($decision === 'approved') {
            query(
                "UPDATE booking b
                   JOIN recurring_booking_pause p ON p.id = :id
                    SET b.status = 2
                  WHERE b.recurring_booking_id = p.recurring_booking_id
                    AND b.date_booking BETWEEN p.from_date AND p.to_date
                    AND (b.paid_amount IS NULL OR b.paid_amount = 0)",
                '',
                [':id' => $pauseId]
            );
        }

        audit('recurring_pause_review', 'recurring_booking_pause', $pauseId, [
            'decision' => $decision,
            'reviewed_by_user_id' => $reviewerId,
            'review_note' => (string) ($d->review_note ?? ''),
        ]);

        JSON(['ok' => true, 'status' => $decision]);
    }

    /**
     * POST /api/v2/?action=recurring_skipOccurrence
     * Body: { recurring_booking_id, occurrence_date, reason, requested_by }
     */
    public static function skipOccurrence() {
        $d = Api::getData();
        $id = (int) ($d->id ?? $d->recurring_booking_id ?? 0);
        $occurrenceDate = trim((string) ($d->occurrence_date ?? $d->date_booking ?? ''));
        if (!$id || $occurrenceDate === '') {
            Api::ApiError(['error' => 'recurring_booking_id y occurrence_date son obligatorios'], 400);
        }

        $boundEst = Auth::getEstablishmentId();
        self::assertRecurringOwnership($id, $boundEst);

        $recurring = self::getRecurringById($id);
        if (!$recurring) {
            Api::ApiError(['error' => 'Reserva fija no encontrada'], 404);
        }
        if ((string) ($recurring['status'] ?? '') !== 'active') {
            Api::ApiError(['error' => 'La reserva fija debe estar activa para pausar una fecha puntual'], 409);
        }

        self::validateOccurrenceDateForRecurring($recurring, $occurrenceDate);

        $rules = BusinessRules::getByEstablishment((int) ($recurring['establishment_id'] ?? 0));
        if ((int) ($rules['allow_customer_pause_request'] ?? 0) !== 1) {
            Api::ApiError(['error' => 'Las pausas de reservas fijas están deshabilitadas'], 403);
        }

        $bookingDateTime = self::buildOccurrenceDateTime($occurrenceDate, $recurring['start_time'] ?? '');
        if (!BusinessRules::canCustomerPauseAt((int) ($recurring['establishment_id'] ?? 0), $bookingDateTime)) {
            Api::ApiError([
                'error' => 'La política del establecimiento solo permite pausar una reserva fija con al menos ' . (int) ($rules['customer_pause_min_hours'] ?? 0) . ' horas de anticipación.'
            ], 409);
        }

        $existingPause = query(
            "SELECT id, status
               FROM recurring_booking_pause
              WHERE recurring_booking_id = ?
                AND ? BETWEEN from_date AND to_date
              ORDER BY id DESC
              LIMIT 1",
            'ARRAY',
            [$id, $occurrenceDate]
        );
        if ($existingPause && in_array((string) ($existingPause['status'] ?? ''), ['pending', 'approved'], true)) {
            Api::ApiError(['error' => 'Ya existe una pausa para esa fecha'], 409);
        }

        query(
            "INSERT INTO recurring_booking_pause
                (recurring_booking_id, from_date, to_date, reason, status, requested_by, requested_min_hours, reviewed_at, review_note)
             VALUES (?, ?, ?, ?, 'approved', ?, ?, NOW(), ?)",
            '',
            [
                $id,
                $occurrenceDate,
                $occurrenceDate,
                trim((string) ($d->reason ?? 'customer_bot_skip_occurrence')),
                $d->requested_by ?? 'customer_bot',
                (int) ($rules['customer_pause_min_hours'] ?? 0),
                trim((string) ($d->review_note ?? 'Pausa puntual autoaprobada desde bot/API')),
            ]
        );
        $pauseId = (int) (query("SELECT LAST_INSERT_ID() AS id")->id ?? 0);

        $affected = query(
            "SELECT COUNT(*) AS total
               FROM booking
              WHERE recurring_booking_id = ?
                AND date_booking = ?
                AND (paid_amount IS NULL OR paid_amount = 0)",
            'ARRAY',
            [$id, $occurrenceDate]
        );
        $releasedBookings = (int) ($affected['total'] ?? 0);

        query(
            "UPDATE booking
                SET status = 2
              WHERE recurring_booking_id = ?
                AND date_booking = ?
                AND (paid_amount IS NULL OR paid_amount = 0)",
            '',
            [$id, $occurrenceDate]
        );

        audit('recurring_occurrence_paused', 'recurring_booking_pause', $pauseId, [
            'recurring_booking_id' => $id,
            'occurrence_date' => $occurrenceDate,
            'requested_by' => (string) ($d->requested_by ?? 'customer_bot'),
            'requested_min_hours' => (int) ($rules['customer_pause_min_hours'] ?? 0),
            'released_bookings' => $releasedBookings,
        ]);
        domain_event('recurring_occurrence_paused', 'recurring_booking_pause', $pauseId, self::buildRecurringPayload($recurring, [
            'pause_id' => $pauseId,
            'occurrence_date' => $occurrenceDate,
            'from_date' => $occurrenceDate,
            'to_date' => $occurrenceDate,
            'reason' => trim((string) ($d->reason ?? '')),
            'requested_by' => (string) ($d->requested_by ?? 'customer_bot'),
            'requested_min_hours' => (int) ($rules['customer_pause_min_hours'] ?? 0),
            'released_bookings' => $releasedBookings,
        ]), [
            'source' => 'api_v2',
        ]);

        JSON([
            'ok' => true,
            'pause_id' => $pauseId,
            'status' => 'approved',
            'occurrence_date' => $occurrenceDate,
            'released_bookings' => $releasedBookings,
        ]);
    }

    /**
     * GET /api/v2/?action=recurring_listPauses&establishment_id=X&status=pending
     */
    public static function listPauses() {
        $where = ['1=1'];
        $params = [];
        if (isset($_GET['establishment_id']) || Auth::getEstablishmentId()) {
            $where[] = 'rb.establishment_id = :est';
            $params[':est'] = Auth::getEstablishmentId() ?? (int) $_GET['establishment_id'];
        }
        if (isset($_GET['status'])) {
            $where[] = 'p.status = :st';
            $params[':st'] = $_GET['status'];
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $rows = query(
            "SELECT p.*, rb.field_id, rb.day_of_week, rb.start_time,
                    c.full_name AS customer_name, c.phone AS customer_phone,
                    sf.full_name AS field_name
               FROM recurring_booking_pause p
               JOIN recurring_booking rb ON rb.id = p.recurring_booking_id
               LEFT JOIN customers c ON c.id = rb.customer_id
               LEFT JOIN soccer_field sf ON sf.id = rb.field_id
              $whereSql
              ORDER BY p.requested_at DESC",
            'ARRAY_ALL',
            $params
        );
        JSON($rows ?: []);
    }

    /**
     * POST /api/v2/?action=recurring_generateWeek
     * Body: { establishment_id, days_ahead (default 7) }
     * Para cada recurring_booking activo, genera bookings reales en las próximas N fechas
     * que matcheen el day_of_week, evitando duplicados y respetando pausas aprobadas.
     */
    public static function generateWeek() {
        $d = Api::getData();
        $boundEst = Auth::getEstablishmentId();
        $establishmentId = $boundEst ?? (int) ($d->establishment_id ?? 0);

        if (!$establishmentId) {
            Api::ApiError(['error' => 'establishment_id is required'], 400);
        }
        $daysAhead = (int) ($d->days_ahead ?? 7);

        $recurring = query(
            "SELECT * FROM recurring_booking
              WHERE establishment_id = :est
                AND status = 'active'
                AND (valid_until IS NULL OR valid_until >= CURDATE())",
            'ARRAY_ALL',
            [':est' => $establishmentId]
        );

        $generated = 0;
        $skipped = 0;
        foreach ($recurring ?: [] as $rb) {
            for ($offset = 0; $offset < $daysAhead; $offset++) {
                $date = date('Y-m-d', strtotime("+$offset days"));
                $dow = date('N', strtotime($date)); // 1=Lun ... 7=Dom

                if ((int) $dow !== (int) $rb['day_of_week']) continue;
                if ((int) $rb['valid_from'] && $date < $rb['valid_from']) continue;

                // Skip si hay pausa aprobada cubriendo este día
                $pause = query(
                    "SELECT id FROM recurring_booking_pause
                      WHERE recurring_booking_id = :rb
                        AND status = 'approved'
                        AND :date BETWEEN from_date AND to_date",
                    'ARRAY',
                    [':rb' => $rb['id'], ':date' => $date]
                );
                if ($pause) { $skipped++; continue; }

                // Skip si ya existe el booking para ese día/hora
                $exists = query(
                    "SELECT id FROM booking
                      WHERE recurring_booking_id = :rb
                        AND date_booking = :date",
                    'ARRAY',
                    [':rb' => $rb['id'], ':date' => $date]
                );
                if ($exists) continue;

                // Resolver `time_booking` (id en tabla schedules) buscando por hora
                $sched = query(
                    "SELECT id FROM schedules WHERE hour LIKE :h LIMIT 1",
                    'ARRAY',
                    [':h' => substr($rb['start_time'], 0, 5) . '%']
                );
                $timeBookingId = $sched ? (int) $sched['id'] : 0;

                // Precio de la cancha
                $field = query(
                    "SELECT price_hour FROM soccer_field WHERE id = :id",
                    'ARRAY',
                    [':id' => $rb['field_id']]
                );
                $price = $field ? (float) ($field['price_hour'] ?? 0) : 0;

                query(
                    "INSERT INTO booking
                        (id_customer, id_field, day_booking, time_booking, date_booking,
                         user, status, is_fixed, recurring_booking_id, source,
                         total_amount, paid_amount, payment_status)
                     VALUES
                        (:cust, :field, :dow, :time, :date,
                         1, 1, 1, :rb, 'recurring',
                         :total, 0, 'pending')",
                    '',
                    [
                        ':cust'  => $rb['customer_id'],
                        ':field' => $rb['field_id'],
                        ':dow'   => $rb['day_of_week'],
                        ':time'  => $timeBookingId,
                        ':date'  => $date,
                        ':rb'    => $rb['id'],
                        ':total' => $price,
                    ]
                );
                $generated++;
            }
        }

        JSON(['ok' => true, 'generated' => $generated, 'skipped' => $skipped]);
    }

    private static function resolveCustomerByPhone($phone) {
        if (!$phone) return null;
        // PHP-B9: normalizar phone a sólo dígitos para matchear E.164 vs local.
        $normalized = preg_replace('/\D/', '', $phone);
        if (!$normalized) return null;
        $row = query(
            "SELECT id FROM customers WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), '-', ''), ' ', '') = :phone LIMIT 1",
            'ARRAY',
            [':phone' => $normalized]
        );
        return $row ? (int) $row['id'] : null;
    }

    public static function updatePayment() {
        $d = Api::getData();
        $id = (int) ($d->id ?? 0);
        $payId = $d->payment_id ?? null;
        if (!$id) Api::ApiError(['error' => 'id required'], 400);
        $boundEst = Auth::getEstablishmentId();
        self::assertRecurringOwnership($id, $boundEst);
        query(
            "UPDATE recurring_booking SET status = 'active', setup_fee_payment_id = ?, setup_paid_at = NOW() WHERE id = ?",
            '',
            [$payId, $id]
        );
        JSON(['ok' => true]);
    }
}
