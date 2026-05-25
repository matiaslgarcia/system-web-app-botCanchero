<?php

class BusinessRules {
    private static $tableExistsCache = [];
    private static $rulesCache = [];

    private static function isSuperAdminContext() {
        return class_exists('Users')
            && method_exists('Users', 'isSuperAdmin')
            && Users::isSuperAdmin();
    }

    public static function defaults() {
        return [
            'establishment_id' => null,
            'allow_customer_self_service' => 1,
            'allow_customer_view_balance' => 1,
            'allow_customer_cancel' => 1,
            'customer_cancel_min_hours' => 6,
            'allow_customer_reschedule' => 1,
            'customer_reschedule_min_hours' => 6,
            'allow_customer_pause_request' => 1,
            'customer_pause_min_hours' => 6,
            'pause_requires_approval' => 1,
            'allow_customer_transfer' => 0,
            'allow_waitlist' => 0,
            'allow_shared_payments' => 0,
            'refund_policy' => 'manual',
            'no_show_policy' => 'charge_full',
            'max_future_booking_days' => 30,
            'hold_expiration_minutes' => 15,
            'reminder_lead_minutes_csv' => '720,360',
            'public_policy_text' => '',
        ];
    }

    private static function tableExists($tableName) {
        $tableName = trim((string) $tableName);
        if ($tableName === '') return false;
        if (array_key_exists($tableName, self::$tableExistsCache)) {
            return self::$tableExistsCache[$tableName];
        }
        $row = query(
            "SELECT 1
               FROM information_schema.tables
              WHERE table_schema = DATABASE()
                AND table_name = ?
              LIMIT 1",
            'ARRAY',
            [$tableName]
        );
        self::$tableExistsCache[$tableName] = !empty($row);
        return self::$tableExistsCache[$tableName];
    }

    public static function isInfrastructureReady() {
        return self::tableExists('business_rules') && self::tableExists('establishment');
    }

    public static function currentEstablishmentId() {
        return FeatureGate::currentEstablishmentId();
    }

    public static function resolveSelectedEstablishmentId($requestedId = null) {
        $requestedId = (int) $requestedId;
        if (self::isSuperAdminContext()) {
            if ($requestedId > 0) return $requestedId;
            $overview = FeatureGate::listEstablishmentsOverview();
            if (!empty($overview)) {
                return (int) ($overview[0]->id ?? 0);
            }
            return 0;
        }
        return (int) self::currentEstablishmentId();
    }

    public static function canManage($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return false;
        if (self::isSuperAdminContext()) return true;
        return $establishmentId === (int) self::currentEstablishmentId();
    }

    public static function isFeatureEnabled($establishmentId) {
        $establishmentId = (int) $establishmentId;
        return FeatureGate::isEnabled($establishmentId, 'mod_operational_rules', true);
    }

    public static function normalizeReminderCsv($value) {
        $parts = preg_split('/[\s,;]+/', (string) $value);
        $parts = array_filter(array_map(function ($item) {
            $minutes = (int) $item;
            return $minutes > 0 ? (string) $minutes : null;
        }, $parts));
        $parts = array_values(array_unique($parts));
        rsort($parts, SORT_NUMERIC);
        return !empty($parts) ? implode(',', $parts) : '720,360';
    }

    private static function formatReminderMinutesLabel($minutes) {
        $minutes = (int) $minutes;
        if ($minutes <= 0) return null;

        if ($minutes % 1440 === 0) {
            $days = (int) ($minutes / 1440);
            return $days === 1 ? '1 dia antes' : $days . ' dias antes';
        }

        if ($minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);
            return $hours === 1 ? '1 hora antes' : $hours . ' horas antes';
        }

        return $minutes === 1 ? '1 minuto antes' : $minutes . ' minutos antes';
    }

    public static function describeReminderLeadMinutes($value) {
        $normalized = self::normalizeReminderCsv($value);
        $parts = array_values(array_filter(array_map(function ($item) {
            return self::formatReminderMinutesLabel($item);
        }, explode(',', $normalized))));

        if (empty($parts)) {
            return '12 horas y 6 horas antes';
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        $last = array_pop($parts);
        return implode(', ', $parts) . ' y ' . $last;
    }

    private static function mergeDefaults($row) {
        $defaults = self::defaults();
        if (is_object($row)) {
            $row = (array) $row;
        }
        if (!is_array($row)) {
            return $defaults;
        }
        return array_merge($defaults, $row);
    }

    public static function getByEstablishment($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) {
            return self::mergeDefaults([]);
        }
        if (isset(self::$rulesCache[$establishmentId])) {
            return self::$rulesCache[$establishmentId];
        }

        $rules = self::defaults();
        $rules['establishment_id'] = $establishmentId;

        if (!self::isInfrastructureReady()) {
            self::$rulesCache[$establishmentId] = $rules;
            return $rules;
        }

        $row = query(
            "SELECT *
               FROM business_rules
              WHERE establishment_id = ?
              LIMIT 1",
            'ARRAY',
            [$establishmentId]
        );

        if ($row) {
            $rules = self::mergeDefaults($row);
            $rules['establishment_id'] = $establishmentId;
        }

        self::$rulesCache[$establishmentId] = $rules;
        return $rules;
    }

    public static function summaryItems($rules) {
        $rules = self::mergeDefaults($rules);
        $summary = [];
        $summary[] = (int) ($rules['allow_customer_self_service'] ?? 1) === 1
            ? 'Autoservicio habilitado'
            : 'Autoservicio deshabilitado';
        $summary[] = $rules['allow_customer_cancel']
            ? 'Cancelación por cliente hasta ' . (int) $rules['customer_cancel_min_hours'] . ' h antes'
            : 'Cancelación por cliente deshabilitada';
        $summary[] = $rules['allow_customer_reschedule']
            ? 'Re-agenda por cliente hasta ' . (int) $rules['customer_reschedule_min_hours'] . ' h antes'
            : 'Re-agenda por cliente deshabilitada';
        $summary[] = $rules['allow_customer_pause_request']
            ? 'Pausa puntual de fija hasta ' . (int) $rules['customer_pause_min_hours'] . ' h antes'
            : 'Pausa puntual de fija deshabilitada';
        $summary[] = 'Fechas futuras permitidas hasta ' . (int) ($rules['max_future_booking_days'] ?? 30) . ' días';
        return $summary;
    }

    public static function canCustomerCancelAt($establishmentId, $dateTime) {
        $rules = self::getByEstablishment($establishmentId);
        if ((int) $rules['allow_customer_self_service'] !== 1) return false;
        if ((int) $rules['allow_customer_cancel'] !== 1) return false;
        return self::hoursUntil($dateTime) >= (int) $rules['customer_cancel_min_hours'];
    }

    public static function canCustomerRescheduleAt($establishmentId, $dateTime) {
        $rules = self::getByEstablishment($establishmentId);
        if ((int) $rules['allow_customer_self_service'] !== 1) return false;
        if ((int) $rules['allow_customer_reschedule'] !== 1) return false;
        return self::hoursUntil($dateTime) >= (int) $rules['customer_reschedule_min_hours'];
    }

    public static function canCustomerPauseAt($establishmentId, $dateTime) {
        $rules = self::getByEstablishment($establishmentId);
        if ((int) $rules['allow_customer_self_service'] !== 1) return false;
        if ((int) $rules['allow_customer_pause_request'] !== 1) return false;
        return self::hoursUntil($dateTime) >= (int) $rules['customer_pause_min_hours'];
    }

    public static function canScheduleDate($establishmentId, $date) {
        $rules = self::getByEstablishment($establishmentId);
        $maxDays = max(1, (int) ($rules['max_future_booking_days'] ?? 30));
        $date = date('Y-m-d', strtotime((string) $date));
        if (!$date) return false;

        $today = date('Y-m-d');
        $maxDate = date('Y-m-d', strtotime('+' . $maxDays . ' days'));
        return $date >= $today && $date <= $maxDate;
    }

    private static function hoursUntil($dateTime) {
        $ts = strtotime((string) $dateTime);
        if (!$ts) return -1;
        return (int) floor(($ts - time()) / 3600);
    }

    public static function getDashboardData($selectedEstablishmentId = null) {
        $establishmentId = self::resolveSelectedEstablishmentId($selectedEstablishmentId);
        $selectedRules = self::getByEstablishment($establishmentId);
        $selectedEstablishment = null;
        $establishments = [];

        if (self::isSuperAdminContext()) {
            $establishments = FeatureGate::listEstablishmentsOverview();
            foreach ($establishments as $establishment) {
                if ((int) ($establishment->id ?? 0) === $establishmentId) {
                    $selectedEstablishment = $establishment;
                    break;
                }
            }
        } elseif ($establishmentId > 0) {
            $selectedEstablishment = query(
                "SELECT e.id, e.name
                   FROM establishment e
                  WHERE e.id = ?
                  LIMIT 1",
                '',
                [$establishmentId]
            );
        }

        return [
            'ready' => self::isInfrastructureReady(),
            'selected_establishment_id' => $establishmentId,
            'selected_establishment' => $selectedEstablishment,
            'establishments' => $establishments,
            'rules' => $selectedRules,
            'summary_items' => self::summaryItems($selectedRules),
            'feature_enabled' => self::isFeatureEnabled($establishmentId),
        ];
    }

    private static function toBool($value, $default = 0) {
        if ($value === null || $value === '') return (int) $default;
        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
    }

    private static function toPositiveInt($value, $default) {
        $number = (int) $value;
        return $number >= 0 ? $number : (int) $default;
    }

    public static function save($data) {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura de reglas operativas no está disponible'], 409, true);
        }

        $establishmentId = (int) ($data->establishment_id ?? 0);
        if ($establishmentId <= 0) {
            JSON(['error' => 'Establecimiento inválido'], 400, true);
        }
        if (!self::canManage($establishmentId)) {
            JSON(['error' => 'No tenés permiso para modificar estas reglas'], 403, true);
        }
        if (!self::isSuperAdminContext() && !self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'Este módulo no está habilitado para tu establecimiento'], 403, true);
        }

        $refundPolicy = trim((string) ($data->refund_policy ?? 'manual'));
        $noShowPolicy = trim((string) ($data->no_show_policy ?? 'charge_full'));
        $allowedRefundPolicies = ['none', 'credit', 'manual', 'full'];
        $allowedNoShowPolicies = ['charge_full', 'charge_deposit', 'credit', 'manual'];

        if (!in_array($refundPolicy, $allowedRefundPolicies, true)) {
            JSON(['error' => 'Política de reembolso inválida'], 400, true);
        }
        if (!in_array($noShowPolicy, $allowedNoShowPolicies, true)) {
            JSON(['error' => 'Política de no-show inválida'], 400, true);
        }

        $payload = [
            'establishment_id' => $establishmentId,
            'allow_customer_self_service' => self::toBool($data->allow_customer_self_service ?? null, 1),
            'allow_customer_view_balance' => self::toBool($data->allow_customer_view_balance ?? null, 1),
            'allow_customer_cancel' => self::toBool($data->allow_customer_cancel ?? null, 1),
            'customer_cancel_min_hours' => self::toPositiveInt($data->customer_cancel_min_hours ?? 6, 6),
            'allow_customer_reschedule' => self::toBool($data->allow_customer_reschedule ?? null, 1),
            'customer_reschedule_min_hours' => self::toPositiveInt($data->customer_reschedule_min_hours ?? 6, 6),
            'allow_customer_pause_request' => self::toBool($data->allow_customer_pause_request ?? null, 1),
            'customer_pause_min_hours' => self::toPositiveInt($data->customer_pause_min_hours ?? 6, 6),
            'pause_requires_approval' => self::toBool($data->pause_requires_approval ?? null, 1),
            'allow_customer_transfer' => self::toBool($data->allow_customer_transfer ?? null, 0),
            'allow_waitlist' => self::toBool($data->allow_waitlist ?? null, 0),
            'allow_shared_payments' => self::toBool($data->allow_shared_payments ?? null, 0),
            'refund_policy' => $refundPolicy,
            'no_show_policy' => $noShowPolicy,
            'max_future_booking_days' => max(1, self::toPositiveInt($data->max_future_booking_days ?? 30, 30)),
            'hold_expiration_minutes' => max(1, self::toPositiveInt($data->hold_expiration_minutes ?? 15, 15)),
            'reminder_lead_minutes_csv' => self::normalizeReminderCsv($data->reminder_lead_minutes_csv ?? ''),
            'public_policy_text' => trim((string) ($data->public_policy_text ?? '')),
        ];

        query(
            "INSERT INTO business_rules
                (establishment_id, allow_customer_self_service, allow_customer_view_balance,
                 allow_customer_cancel, customer_cancel_min_hours,
                 allow_customer_reschedule, customer_reschedule_min_hours,
                 allow_customer_pause_request, customer_pause_min_hours, pause_requires_approval,
                 allow_customer_transfer, allow_waitlist, allow_shared_payments,
                 refund_policy, no_show_policy, max_future_booking_days,
                 hold_expiration_minutes, reminder_lead_minutes_csv, public_policy_text,
                 created_at, updated_at)
             VALUES
                (:est, :self_service, :view_balance,
                 :cancel, :cancel_hours,
                 :reschedule, :reschedule_hours,
                 :pause_request, :pause_hours, :pause_approval,
                 :transfer, :waitlist, :shared_payments,
                 :refund_policy, :no_show_policy, :max_days,
                 :hold_minutes, :reminders, :public_policy,
                 NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                 allow_customer_self_service = VALUES(allow_customer_self_service),
                 allow_customer_view_balance = VALUES(allow_customer_view_balance),
                 allow_customer_cancel = VALUES(allow_customer_cancel),
                 customer_cancel_min_hours = VALUES(customer_cancel_min_hours),
                 allow_customer_reschedule = VALUES(allow_customer_reschedule),
                 customer_reschedule_min_hours = VALUES(customer_reschedule_min_hours),
                 allow_customer_pause_request = VALUES(allow_customer_pause_request),
                customer_pause_min_hours = VALUES(customer_pause_min_hours),
                 pause_requires_approval = VALUES(pause_requires_approval),
                 allow_customer_transfer = VALUES(allow_customer_transfer),
                 allow_waitlist = VALUES(allow_waitlist),
                 allow_shared_payments = VALUES(allow_shared_payments),
                 refund_policy = VALUES(refund_policy),
                 no_show_policy = VALUES(no_show_policy),
                 max_future_booking_days = VALUES(max_future_booking_days),
                 hold_expiration_minutes = VALUES(hold_expiration_minutes),
                 reminder_lead_minutes_csv = VALUES(reminder_lead_minutes_csv),
                 public_policy_text = VALUES(public_policy_text),
                 updated_at = NOW()",
            '',
            [
                ':est' => $payload['establishment_id'],
                ':self_service' => $payload['allow_customer_self_service'],
                ':view_balance' => $payload['allow_customer_view_balance'],
                ':cancel' => $payload['allow_customer_cancel'],
                ':cancel_hours' => $payload['customer_cancel_min_hours'],
                ':reschedule' => $payload['allow_customer_reschedule'],
                ':reschedule_hours' => $payload['customer_reschedule_min_hours'],
                ':pause_request' => $payload['allow_customer_pause_request'],
                ':pause_hours' => $payload['customer_pause_min_hours'],
                ':pause_approval' => $payload['pause_requires_approval'],
                ':transfer' => $payload['allow_customer_transfer'],
                ':waitlist' => $payload['allow_waitlist'],
                ':shared_payments' => $payload['allow_shared_payments'],
                ':refund_policy' => $payload['refund_policy'],
                ':no_show_policy' => $payload['no_show_policy'],
                ':max_days' => $payload['max_future_booking_days'],
                ':hold_minutes' => $payload['hold_expiration_minutes'],
                ':reminders' => $payload['reminder_lead_minutes_csv'],
                ':public_policy' => $payload['public_policy_text'] !== '' ? $payload['public_policy_text'] : null,
            ]
        );

        self::$rulesCache[$establishmentId] = self::mergeDefaults($payload);

        audit('business_rules_update', 'establishment', $establishmentId, $payload);
        domain_event('business_rules_updated', 'establishment', $establishmentId, $payload, [
            'source' => 'web_admin',
        ]);

        JSON([
            'success' => true,
            'msg' => 'Reglas operativas guardadas correctamente',
            'establishment_id' => $establishmentId,
        ]);
    }
}
