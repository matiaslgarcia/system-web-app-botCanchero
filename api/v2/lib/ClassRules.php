<?php

class Rules
{
    private static function boundEstablishmentId()
    {
        return (int) (Auth::getEstablishmentId() ?? 0);
    }

    private static function resolveEstablishmentId($requestedId = 0)
    {
        $boundEstablishmentId = self::boundEstablishmentId();
        if ($boundEstablishmentId > 0) {
            return $boundEstablishmentId;
        }

        return (int) $requestedId;
    }

    private static function requireEstablishmentId($requestedId = 0)
    {
        $establishmentId = self::resolveEstablishmentId($requestedId);
        if ($establishmentId <= 0) {
            Api::ApiError(['error' => 'establishment_id is required'], 400);
        }

        return $establishmentId;
    }

    private static function getBookingContext($bookingId)
    {
        $bookingId = (int) $bookingId;
        if ($bookingId <= 0) {
            Api::ApiError(['error' => 'booking_id is required'], 400);
        }

        $row = query(
            "SELECT b.id,
                    b.id_field,
                    b.status,
                    b.date_booking,
                    b.is_fixed,
                    b.recurring_booking_id,
                    s.hour,
                    sf.establishment_id,
                    sf.full_name AS field_name
               FROM booking b
               INNER JOIN schedules s ON s.id = b.time_booking
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id = ?
              LIMIT 1",
            'ARRAY',
            [$bookingId]
        );

        if (!$row) {
            Api::ApiError(['error' => 'Booking not found'], 404);
        }

        $boundEstablishmentId = self::boundEstablishmentId();
        if ($boundEstablishmentId > 0 && (int) ($row['establishment_id'] ?? 0) !== $boundEstablishmentId) {
            Api::ApiError(['error' => 'Forbidden'], 403);
        }

        return $row;
    }

    private static function buildBookingDateTime($context)
    {
        if (!$context) return null;
        $date = (string) ($context['date_booking'] ?? '');
        $hour = substr((string) ($context['hour'] ?? ''), 0, 5);
        if ($date === '' || $hour === '') return null;
        $dateTime = strtotime($date . ' ' . $hour . ':00');
        return $dateTime ? date('Y-m-d H:i:s', $dateTime) : null;
    }

    private static function getActionPolicy($bookingId)
    {
        $context = self::getBookingContext($bookingId);
        $establishmentId = (int) ($context['establishment_id'] ?? 0);
        $bookingDateTime = self::buildBookingDateTime($context);
        $isPast = $bookingDateTime ? strtotime($bookingDateTime) < time() : false;
        $statusId = (int) ($context['status'] ?? 0);
        $rules = BusinessRules::getByEstablishment($establishmentId);

        $policy = [
            'can_cancel' => true,
            'cancel_reason' => '',
            'can_reschedule' => true,
            'reschedule_reason' => '',
            'can_transfer' => true,
            'transfer_reason' => '',
            'can_pause_request' => true,
            'pause_reason' => '',
            'can_view_balance' => true,
            'view_balance_reason' => '',
            'booking_datetime' => $bookingDateTime,
            'establishment_id' => $establishmentId,
            'rules' => $rules,
            'is_past' => $isPast,
            'booking' => $context,
        ];

        if ($statusId === 2) {
            $policy['can_cancel'] = false;
            $policy['can_reschedule'] = false;
            $policy['can_transfer'] = false;
            $policy['can_pause_request'] = false;
            $policy['cancel_reason'] = 'La reserva ya está cancelada.';
            $policy['reschedule_reason'] = 'La reserva ya está cancelada.';
            $policy['transfer_reason'] = 'La reserva ya está cancelada.';
            $policy['pause_reason'] = 'La reserva ya está cancelada.';
            return $policy;
        }
        if ($statusId === 3) {
            $policy['can_cancel'] = false;
            $policy['can_reschedule'] = false;
            $policy['can_transfer'] = false;
            $policy['can_pause_request'] = false;
            $policy['cancel_reason'] = 'La reserva ya fue completada.';
            $policy['reschedule_reason'] = 'La reserva ya fue completada.';
            $policy['transfer_reason'] = 'La reserva ya fue completada.';
            $policy['pause_reason'] = 'La reserva ya fue completada.';
            return $policy;
        }
        if ($isPast) {
            $policy['can_cancel'] = false;
            $policy['can_reschedule'] = false;
            $policy['can_transfer'] = false;
            $policy['can_pause_request'] = false;
            $policy['cancel_reason'] = 'La reserva ya pasó su horario de juego.';
            $policy['reschedule_reason'] = 'La reserva ya pasó su horario de juego.';
            $policy['transfer_reason'] = 'La reserva ya pasó su horario de juego.';
            $policy['pause_reason'] = 'La reserva ya pasó su horario de juego.';
            return $policy;
        }

        if ((int) ($rules['allow_customer_self_service'] ?? 1) !== 1) {
            $reason = 'El autoservicio está deshabilitado para este establecimiento.';
            $policy['can_cancel'] = false;
            $policy['can_reschedule'] = false;
            $policy['can_transfer'] = false;
            $policy['can_pause_request'] = false;
            $policy['can_view_balance'] = false;
            $policy['cancel_reason'] = $reason;
            $policy['reschedule_reason'] = $reason;
            $policy['transfer_reason'] = $reason;
            $policy['pause_reason'] = $reason;
            $policy['view_balance_reason'] = $reason;
            return $policy;
        }

        if (!BusinessRules::canCustomerCancelAt($establishmentId, $bookingDateTime)) {
            $policy['can_cancel'] = false;
            $policy['cancel_reason'] = 'La política del establecimiento solo permite cancelar con al menos ' . (int) ($rules['customer_cancel_min_hours'] ?? 0) . ' horas de anticipación.';
        }
        if (!BusinessRules::canCustomerRescheduleAt($establishmentId, $bookingDateTime)) {
            $policy['can_reschedule'] = false;
            $policy['reschedule_reason'] = 'La política del establecimiento solo permite re-agendar con al menos ' . (int) ($rules['customer_reschedule_min_hours'] ?? 0) . ' horas de anticipación.';
        }
        if ((int) ($rules['allow_customer_transfer'] ?? 0) !== 1) {
            $policy['can_transfer'] = false;
            $policy['transfer_reason'] = 'La transferencia de reservas no está habilitada para este establecimiento.';
        }
        if ((int) ($rules['allow_customer_pause_request'] ?? 0) !== 1) {
            $policy['can_pause_request'] = false;
            $policy['pause_reason'] = 'La solicitud de pausa no está habilitada para este establecimiento.';
        } elseif (!BusinessRules::canCustomerPauseAt($establishmentId, $bookingDateTime)) {
            $policy['can_pause_request'] = false;
            $policy['pause_reason'] = 'La política del establecimiento solo permite pausar una reserva fija con al menos ' . (int) ($rules['customer_pause_min_hours'] ?? 0) . ' horas de anticipación.';
        }
        if ((int) ($rules['allow_customer_view_balance'] ?? 1) !== 1) {
            $policy['can_view_balance'] = false;
            $policy['view_balance_reason'] = 'La consulta de saldo no está habilitada para este establecimiento.';
        }

        return $policy;
    }

    public static function get()
    {
        $establishmentId = self::requireEstablishmentId((int) ($_GET['establishment_id'] ?? 0));
        $rules = BusinessRules::getByEstablishment($establishmentId);

        JSON([
            'establishment_id' => $establishmentId,
            'feature_enabled' => BusinessRules::isFeatureEnabled($establishmentId),
            'rules' => $rules,
            'summary_items' => BusinessRules::summaryItems($rules),
        ]);
    }

    public static function getBookingActionPolicy($bookingId)
    {
        return self::getActionPolicy($bookingId);
    }

    public static function update()
    {
        $data = Api::getData();
        $data->establishment_id = self::requireEstablishmentId((int) ($data->establishment_id ?? 0));
        BusinessRules::save($data);
    }

    public static function validateAction()
    {
        $data = Api::getData();
        $action = trim((string) ($data->action ?? ''));
        if ($action === '') {
            Api::ApiError(['error' => 'action is required'], 400);
        }

        if ($action === 'schedule_date') {
            $establishmentId = self::requireEstablishmentId((int) ($data->establishment_id ?? 0));
            $date = trim((string) ($data->date_booking ?? $data->date ?? ''));
            if ($date === '') {
                Api::ApiError(['error' => 'date or date_booking is required'], 400);
            }

            $allowed = BusinessRules::canScheduleDate($establishmentId, $date);
            $rules = BusinessRules::getByEstablishment($establishmentId);
            JSON([
                'action' => $action,
                'allowed' => $allowed,
                'reason' => $allowed ? '' : 'La fecha solicitada supera el máximo permitido de ' . (int) ($rules['max_future_booking_days'] ?? 30) . ' días.',
                'establishment_id' => $establishmentId,
                'rules' => $rules,
            ]);
        }

        $bookingId = (int) ($data->booking_id ?? 0);
        $policy = self::getActionPolicy($bookingId);

        $map = [
            'cancel' => ['can_cancel', 'cancel_reason'],
            'reschedule' => ['can_reschedule', 'reschedule_reason'],
            'transfer' => ['can_transfer', 'transfer_reason'],
            'pause_request' => ['can_pause_request', 'pause_reason'],
            'view_balance' => ['can_view_balance', 'view_balance_reason'],
        ];

        if (!array_key_exists($action, $map)) {
            Api::ApiError(['error' => 'Unsupported action'], 400);
        }

        [$flagKey, $reasonKey] = $map[$action];
        JSON([
            'action' => $action,
            'allowed' => (bool) ($policy[$flagKey] ?? false),
            'reason' => (string) ($policy[$reasonKey] ?? ''),
            'booking_id' => $bookingId,
            'booking_datetime' => $policy['booking_datetime'] ?? null,
            'is_past' => (bool) ($policy['is_past'] ?? false),
            'establishment_id' => (int) ($policy['establishment_id'] ?? 0),
            'rules' => $policy['rules'] ?? BusinessRules::defaults(),
        ]);
    }
}
