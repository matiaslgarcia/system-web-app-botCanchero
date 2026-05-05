<?php

/**
 * Vista de calendario para el panel del canchero.
 *
 * GET /api/v2/?action=calendar_view&establishment_id=X&from=YYYY-MM-DD&to=YYYY-MM-DD&field_id=optional
 *
 * Devuelve dos colecciones:
 *  - bookings        : reservas reales (sueltas + instancias generadas de fijas) en el rango
 *  - recurringSlots  : slots fijos activos del establecimiento (para que la UI los pinte
 *                      como recurrentes incluso si todavía no se generó el booking real)
 *  - pausesApproved  : pausas aprobadas en el rango (para tachar el slot de fija ese día)
 *
 * El frontend combina las tres listas para pintar:
 *  - azul    → booking source='one_off'
 *  - naranja → booking source='recurring' o slot de recurringSlots sin booking generado todavía
 *  - gris    → fechas dentro de pausesApproved
 */
class Calendar {

    public static function view() {
        $boundEst = Auth::getEstablishmentId();
        $requestedEst = isset($_GET['establishment_id']) ? (int) $_GET['establishment_id'] : 0;
        if ($boundEst && $requestedEst && $requestedEst !== (int) $boundEst) {
            Api::ApiError(['error' => 'Forbidden'], 403);
        }
        $est = $boundEst ?: $requestedEst;
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $fieldId = isset($_GET['field_id']) ? (int) $_GET['field_id'] : null;

        if (!$est || !$from || !$to) {
            Api::ApiError(['error' => 'establishment_id, from y to son obligatorios'], 400);
        }

        $fieldFilter = $fieldId ? ' AND b.id_field = :field' : '';
        $params = [
            ':est'  => $est,
            ':from' => $from,
            ':to'   => $to,
        ];
        if ($fieldId) $params[':field'] = $fieldId;

        $bookings = query(
            "SELECT b.id, b.id_field, b.date_booking, b.day_booking, b.time_booking,
                    s.hour AS hour_label,
                    b.status, b.is_fixed, b.recurring_booking_id, b.source,
                    b.total_amount, b.paid_amount, b.payment_status,
                    c.full_name AS customer_name, c.phone AS customer_phone,
                    sf.full_name AS field_name
               FROM booking b
               LEFT JOIN schedules s ON s.id = b.time_booking
               LEFT JOIN customers c ON c.id = b.id_customer
               LEFT JOIN soccer_field sf ON sf.id = b.id_field
              WHERE sf.establishment_id = :est
                AND b.date_booking BETWEEN :from AND :to
                $fieldFilter
              ORDER BY b.date_booking, b.time_booking",
            'ARRAY_ALL',
            $params
        );

        $recurringParams = [':est' => $est];
        $fieldFilterRec = '';
        if ($fieldId) {
            $fieldFilterRec = ' AND rb.field_id = :field';
            $recurringParams[':field'] = $fieldId;
        }
        $recurring = query(
            "SELECT rb.id, rb.field_id, rb.customer_id, rb.day_of_week,
                    rb.start_time, rb.duration_min,
                    rb.valid_from, rb.valid_until, rb.status,
                    c.full_name AS customer_name, c.phone AS customer_phone,
                    sf.full_name AS field_name
               FROM recurring_booking rb
               LEFT JOIN customers c ON c.id = rb.customer_id
               LEFT JOIN soccer_field sf ON sf.id = rb.field_id
              WHERE rb.establishment_id = :est
                AND rb.status = 'active'
                $fieldFilterRec",
            'ARRAY_ALL',
            $recurringParams
        );

        $pauses = query(
            "SELECT p.id, p.recurring_booking_id, p.from_date, p.to_date, p.reason
               FROM recurring_booking_pause p
               JOIN recurring_booking rb ON rb.id = p.recurring_booking_id
              WHERE rb.establishment_id = :est
                AND p.status = 'approved'
                AND (
                    (p.from_date BETWEEN :from AND :to)
                 OR (p.to_date   BETWEEN :from AND :to)
                 OR (:from BETWEEN p.from_date AND p.to_date)
                )",
            'ARRAY_ALL',
            [':est' => $est, ':from' => $from, ':to' => $to]
        );

        JSON([
            'bookings'        => $bookings ?: [],
            'recurringSlots'  => $recurring ?: [],
            'pausesApproved'  => $pauses ?: [],
        ]);
    }
}
