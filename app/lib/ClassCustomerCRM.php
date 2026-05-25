<?php

class CustomerCRM
{
    private static $tableExistsCache = [];
    private static $columnExistsCache = [];
    private static $readyCache = null;

    private static function isSuperAdminContext()
    {
        return class_exists('Users')
            && method_exists('Users', 'isSuperAdmin')
            && Users::isSuperAdmin();
    }

    private static function boundApiEstablishmentId()
    {
        return (class_exists('Auth') && method_exists('Auth', 'getEstablishmentId'))
            ? (int) Auth::getEstablishmentId()
            : 0;
    }

    private static function currentActorUserId()
    {
        if (class_exists('Users') && method_exists('Users', 'infoUser')) {
            $userId = (int) (Users::infoUser('id') ?? 0);
            if ($userId > 0) {
                return $userId;
            }
        }

        return 1;
    }

    private static function tableExists($tableName)
    {
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

    private static function columnExists($tableName, $columnName)
    {
        $tableName = trim((string) $tableName);
        $columnName = trim((string) $columnName);
        if ($tableName === '' || $columnName === '') return false;
        $cacheKey = $tableName . '.' . $columnName;
        if (array_key_exists($cacheKey, self::$columnExistsCache)) {
            return self::$columnExistsCache[$cacheKey];
        }

        $row = query(
            "SELECT 1
               FROM information_schema.columns
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND column_name = ?
              LIMIT 1",
            'ARRAY',
            [$tableName, $columnName]
        );
        self::$columnExistsCache[$cacheKey] = !empty($row);
        return self::$columnExistsCache[$cacheKey];
    }

    public static function missingTables()
    {
        $required = [
            'customers',
            'booking',
            'soccer_field',
            'customer_tag',
            'customer_tag_map',
            'customer_note',
        ];
        return array_values(array_filter($required, function ($table) {
            return !self::tableExists($table);
        }));
    }

    public static function isInfrastructureReady()
    {
        if (self::$readyCache !== null) {
            return self::$readyCache;
        }
        self::$readyCache = count(self::missingTables()) === 0;
        return self::$readyCache;
    }

    public static function isPrivacyInfrastructureReady()
    {
        return class_exists('Customers') && Customers::isPrivacyInfrastructureReady();
    }

    public static function currentEstablishmentId()
    {
        $boundApiEstablishmentId = self::boundApiEstablishmentId();
        if ($boundApiEstablishmentId > 0) {
            return $boundApiEstablishmentId;
        }
        return (int) (FeatureGate::currentEstablishmentId() ?? 0);
    }

    private static function normalizeText($value)
    {
        $value = trim((string) $value);
        if ($value === '') return '';
        $value = preg_replace('/\s+/', ' ', $value);
        return trim((string) $value);
    }

    private static function normalizeTagColor($color)
    {
        $allowed = ['primary', 'success', 'warning', 'danger', 'info', 'dark', 'secondary'];
        $color = strtolower(trim((string) $color));
        return in_array($color, $allowed, true) ? $color : 'primary';
    }

    private static function resolveSelectedEstablishmentId($selectedEstablishmentId = 0)
    {
        if (self::isSuperAdminContext()) {
            return max(0, (int) $selectedEstablishmentId);
        }
        return self::currentEstablishmentId();
    }

    private static function isFeatureEnabled($establishmentId)
    {
        return FeatureGate::isEnabled((int) $establishmentId, 'mod_crm', true);
    }

    private static function getSelectableEstablishments()
    {
        return self::isSuperAdminContext() ? (FeatureGate::listEstablishmentsOverview() ?: []) : [];
    }

    private static function listFields($selectedEstablishmentId = 0)
    {
        $selectedEstablishmentId = (int) $selectedEstablishmentId;
        if ($selectedEstablishmentId > 0) {
            return query(
                "SELECT id, full_name AS name
                   FROM soccer_field
                  WHERE establishment_id = ?
                  ORDER BY full_name ASC",
                'ALL',
                [$selectedEstablishmentId]
            ) ?: [];
        }

        if (!self::isSuperAdminContext()) {
            $currentEstablishmentId = self::currentEstablishmentId();
            if ($currentEstablishmentId <= 0) return [];
            return query(
                "SELECT id, full_name AS name
                   FROM soccer_field
                  WHERE establishment_id = ?
                  ORDER BY full_name ASC",
                'ALL',
                [$currentEstablishmentId]
            ) ?: [];
        }

        return query(
            "SELECT id, full_name AS name
               FROM soccer_field
              ORDER BY full_name ASC",
            'ALL'
        ) ?: [];
    }

    private static function buildListWhere($selectedEstablishmentId, $filters = [])
    {
        $where = ['1=1'];
        $params = [];

        if (self::isPrivacyInfrastructureReady()) {
            $where[] = Customers::getActiveWhereClause('c', 'sf');
        }

        if ((int) $selectedEstablishmentId > 0) {
            $where[] = 'sf.establishment_id = :establishment_id';
            $params[':establishment_id'] = (int) $selectedEstablishmentId;
        } elseif (!self::isSuperAdminContext()) {
            $currentEstablishmentId = self::currentEstablishmentId();
            if ($currentEstablishmentId > 0) {
                $where[] = 'sf.establishment_id = :establishment_id';
                $params[':establishment_id'] = $currentEstablishmentId;
            }
        }

        $fieldId = (int) ($filters['field_id'] ?? 0);
        if ($fieldId > 0) {
            $where[] = 'b.id_field = :field_id';
            $params[':field_id'] = $fieldId;
        }

        $search = self::normalizeText($filters['q'] ?? '');
        if ($search !== '') {
            $where[] = '(c.full_name LIKE :search OR c.phone LIKE :search OR COALESCE(c.email, \'\') LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        return [$where, $params];
    }

    private static function ensureCustomerIsMutable($customerId, $establishmentId)
    {
        $customerId = (int) $customerId;
        $establishmentId = (int) $establishmentId;
        if ($customerId <= 0 || $establishmentId <= 0) {
            JSON(['error' => 'Cliente o establecimiento inválido'], 400, true);
        }
        if (!self::customerHasScope($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente no pertenece al scope del establecimiento seleccionado'], 403, true);
        }
        if (self::isPrivacyInfrastructureReady() && Customers::isInactive($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente está dado de baja en este establecimiento y debe reactivarse antes de editarlo'], 409, true);
        }
        if (self::isPrivacyInfrastructureReady() && Customers::isAnonymized($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente ya fue anonimizado y no admite nuevas acciones'], 409, true);
        }
    }

    private static function customerHasScope($customerId, $establishmentId)
    {
        $customerId = (int) $customerId;
        $establishmentId = (int) $establishmentId;
        if ($customerId <= 0 || $establishmentId <= 0) return false;

        $bookingRow = query(
            "SELECT 1
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id_customer = ?
                AND sf.establishment_id = ?
              LIMIT 1",
            'ARRAY',
            [$customerId, $establishmentId]
        );
        if (!empty($bookingRow)) return true;

        if (class_exists('Waitlist') && Waitlist::isInfrastructureReady()) {
            $waitlistRow = query(
                "SELECT 1
                   FROM booking_waitlist
                  WHERE customer_id = ?
                    AND establishment_id = ?
                  LIMIT 1",
                'ARRAY',
                [$customerId, $establishmentId]
            );
            if (!empty($waitlistRow)) return true;
        }

        return false;
    }

    private static function getCustomerEstablishmentIds($customerId)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) return [];

        $establishments = [];

        $bookingRows = query(
            "SELECT DISTINCT sf.establishment_id
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id_customer = ?
                AND sf.establishment_id IS NOT NULL
                AND sf.establishment_id > 0",
            'ALL',
            [$customerId]
        ) ?: [];

        foreach ($bookingRows as $row) {
            $estId = (int) ($row->establishment_id ?? 0);
            if ($estId > 0) {
                $establishments[$estId] = $estId;
            }
        }

        if (class_exists('Waitlist') && Waitlist::isInfrastructureReady()) {
            $waitlistRows = query(
                "SELECT DISTINCT establishment_id
                   FROM booking_waitlist
                  WHERE customer_id = ?
                    AND establishment_id > 0",
                'ALL',
                [$customerId]
            ) ?: [];

            foreach ($waitlistRows as $row) {
                $estId = (int) ($row->establishment_id ?? 0);
                if ($estId > 0) {
                    $establishments[$estId] = $estId;
                }
            }
        }

        return array_values($establishments);
    }

    private static function getCustomerPrivacyContext($customerId, $selectedEstablishmentId)
    {
        $customerId = (int) $customerId;
        $selectedEstablishmentId = (int) $selectedEstablishmentId;

        $context = [
            'selected_establishment_id' => $selectedEstablishmentId,
            'establishment_ids' => [],
            'other_establishment_ids' => [],
            'local_bookings' => 0,
            'other_bookings' => 0,
            'local_waitlist_entries' => 0,
            'other_waitlist_entries' => 0,
            'other_establishments_count' => 0,
        ];

        if ($customerId <= 0 || $selectedEstablishmentId <= 0) {
            return $context;
        }

        $bookingRows = query(
            "SELECT sf.establishment_id, COUNT(*) AS qty
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id_customer = ?
                AND sf.establishment_id IS NOT NULL
                AND sf.establishment_id > 0
              GROUP BY sf.establishment_id",
            'ALL',
            [$customerId]
        ) ?: [];

        foreach ($bookingRows as $row) {
            $estId = (int) ($row->establishment_id ?? 0);
            $qty = (int) ($row->qty ?? 0);
            if ($estId <= 0) continue;

            $context['establishment_ids'][$estId] = $estId;
            if ($estId === $selectedEstablishmentId) {
                $context['local_bookings'] += $qty;
            } else {
                $context['other_bookings'] += $qty;
                $context['other_establishment_ids'][$estId] = $estId;
            }
        }

        if (class_exists('Waitlist') && Waitlist::isInfrastructureReady()) {
            $waitlistRows = query(
                "SELECT establishment_id, COUNT(*) AS qty
                   FROM booking_waitlist
                  WHERE customer_id = ?
                    AND establishment_id > 0
                  GROUP BY establishment_id",
                'ALL',
                [$customerId]
            ) ?: [];

            foreach ($waitlistRows as $row) {
                $estId = (int) ($row->establishment_id ?? 0);
                $qty = (int) ($row->qty ?? 0);
                if ($estId <= 0) continue;

                $context['establishment_ids'][$estId] = $estId;
                if ($estId === $selectedEstablishmentId) {
                    $context['local_waitlist_entries'] += $qty;
                } else {
                    $context['other_waitlist_entries'] += $qty;
                    $context['other_establishment_ids'][$estId] = $estId;
                }
            }
        }

        $context['establishment_ids'] = array_values($context['establishment_ids']);
        $context['other_establishment_ids'] = array_values($context['other_establishment_ids']);
        $context['other_establishments_count'] = count($context['other_establishment_ids']);

        return $context;
    }

    private static function buildPrivacyPolicySummary($privacyScopeStatus, $privacyContext)
    {
        $privacyScopeStatus = trim((string) $privacyScopeStatus);
        $otherCount = (int) ($privacyContext['other_establishments_count'] ?? 0);

        $lines = [
            'Los historicos visibles en esta ficha quedan limitados al establecimiento actual.',
            'Notas, tags y consentimientos siempre se administran por establecimiento.',
        ];

        if ($otherCount > 0) {
            $lines[] = 'Existen historicos del cliente en otras sedes y no se muestran ni se alteran desde esta ficha.';
        } else {
            $lines[] = 'No hay historicos detectados en otras sedes para este cliente.';
        }

        if ($privacyScopeStatus === 'inactive') {
            $lines[] = 'La baja lógica oculta al cliente en esta sede, pero conserva reservas, caja y trazabilidad histórica.';
        } else {
            $lines[] = 'El cliente permanece operativo en esta sede mientras no se aplique baja lógica o anonimización.';
        }

        $lines[] = 'La anonimización elimina datos personales operativos y mantiene solo la referencia histórica mínima para auditoría.';

        return $lines;
    }

    private static function cleanupScopedPrivacyData($customerId, $establishmentId)
    {
        $customerId = (int) $customerId;
        $establishmentId = (int) $establishmentId;

        if ($customerId <= 0 || $establishmentId <= 0) {
            return ['notes_deleted' => false, 'tags_deleted' => false, 'consents_revoked' => false];
        }

        $notesDeleted = false;
        $tagsDeleted = false;
        $consentsRevoked = false;

        if (self::tableExists('customer_note')) {
            query(
                "DELETE FROM customer_note
                  WHERE customer_id = ?
                    AND establishment_id = ?",
                '',
                [$customerId, $establishmentId]
            );
            $notesDeleted = true;
        }

        if (self::tableExists('customer_tag_map') && self::tableExists('customer_tag')) {
            query(
                "DELETE FROM customer_tag_map
                  WHERE customer_id = ?
                    AND tag_id IN (
                        SELECT id
                          FROM customer_tag
                         WHERE establishment_id = ?
                    )",
                '',
                [$customerId, $establishmentId]
            );
            $tagsDeleted = true;
        }

        if (self::isConsentInfrastructureReady()) {
            foreach (['communications', 'waitlist'] as $consentType) {
                query(
                    "INSERT INTO customer_consent
                        (customer_id, establishment_id, consent_type, status, source, created_by_user_id, created_at, updated_at)
                     VALUES (?, ?, ?, 'revoked', 'privacy_anonymization', ?, NOW(), NOW())",
                    '',
                    [$customerId, $establishmentId, $consentType, self::currentActorUserId()]
                );
            }
            $consentsRevoked = true;
        }

        return [
            'notes_deleted' => $notesDeleted,
            'tags_deleted' => $tagsDeleted,
            'consents_revoked' => $consentsRevoked,
        ];
    }

    private static function anonymizeCustomerGlobally($customerId, $reason, $userId)
    {
        $customerId = (int) $customerId;
        $userId = (int) $userId;
        if ($customerId <= 0 || !self::isPrivacyInfrastructureReady()) {
            return false;
        }

        $identity = Customers::buildAnonymizedIdentity($customerId);
        query(
            "UPDATE customers
                SET full_name = ?,
                    phone = ?,
                    email = ?,
                    privacy_status = 'anonymized',
                    anonymized_at = NOW(),
                    anonymized_by_user_id = ?,
                    anonymization_reason = ?
              WHERE id = ?",
            '',
            [
                $identity['full_name'],
                $identity['phone'],
                $identity['email'],
                $userId,
                $reason !== '' ? $reason : null,
                $customerId,
            ]
        );

        return true;
    }

    private static function getTagsMap($customerEstablishmentPairs)
    {
        if (empty($customerEstablishmentPairs)) return [];

        $conditions = [];
        $params = [];
        foreach (array_values($customerEstablishmentPairs) as $index => $pair) {
            $conditions[] = '(ctm.customer_id = :customer_' . $index . ' AND ct.establishment_id = :est_' . $index . ')';
            $params[':customer_' . $index] = (int) ($pair['customer_id'] ?? 0);
            $params[':est_' . $index] = (int) ($pair['establishment_id'] ?? 0);
        }

        $rows = query(
            "SELECT ctm.id AS tag_map_id,
                    ctm.customer_id,
                    ct.establishment_id,
                    ct.id AS tag_id,
                    ct.name,
                    ct.color
               FROM customer_tag_map ctm
               INNER JOIN customer_tag ct ON ct.id = ctm.tag_id
              WHERE ct.status = 1
                AND (" . implode(' OR ', $conditions) . ")
              ORDER BY ct.name ASC",
            'ALL',
            $params
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $key = (int) ($row->customer_id ?? 0) . ':' . (int) ($row->establishment_id ?? 0);
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = [
                'tag_map_id' => (int) ($row->tag_map_id ?? 0),
                'tag_id' => (int) ($row->tag_id ?? 0),
                'name' => (string) ($row->name ?? ''),
                'color' => (string) ($row->color ?? 'primary'),
            ];
        }
        return $result;
    }

    private static function bookingStatusLabel($status)
    {
        $status = (int) $status;
        $map = [
            1 => 'Pendiente',
            2 => 'Cancelada',
            3 => 'Completada',
            4 => 'Actualizada',
            5 => 'Creada',
            6 => 'Re-agendada',
            7 => 'Expirada',
        ];
        return $map[$status] ?? 'Estado ' . $status;
    }

    public static function isConsentInfrastructureReady()
    {
        return self::tableExists('customer_consent')
            && self::columnExists('customer_consent', 'id')
            && self::columnExists('customer_consent', 'customer_id')
            && self::columnExists('customer_consent', 'establishment_id')
            && self::columnExists('customer_consent', 'consent_type')
            && self::columnExists('customer_consent', 'status')
            && self::columnExists('customer_consent', 'source')
            && self::columnExists('customer_consent', 'created_at')
            && self::columnExists('customer_consent', 'updated_at');
    }

    private static function consentStatusMeta($status)
    {
        $status = strtolower(trim((string) $status));
        $map = [
            'granted' => ['label' => 'Otorgado', 'color' => 'success'],
            'revoked' => ['label' => 'Revocado', 'color' => 'danger'],
            'pending' => ['label' => 'Pendiente', 'color' => 'warning'],
            'unknown' => ['label' => 'Sin registrar', 'color' => 'secondary'],
        ];
        return $map[$status] ?? $map['unknown'];
    }

    private static function getConsentMap($customerEstablishmentPairs)
    {
        if (!self::isConsentInfrastructureReady() || empty($customerEstablishmentPairs)) {
            return [];
        }

        $conditions = [];
        $params = [];
        foreach (array_values($customerEstablishmentPairs) as $index => $pair) {
            $conditions[] = '(cc.customer_id = :cons_customer_' . $index . ' AND cc.establishment_id = :cons_est_' . $index . ')';
            $params[':cons_customer_' . $index] = (int) ($pair['customer_id'] ?? 0);
            $params[':cons_est_' . $index] = (int) ($pair['establishment_id'] ?? 0);
        }

        $rows = query(
            "SELECT cc.customer_id,
                    cc.establishment_id,
                    cc.consent_type,
                    cc.status,
                    cc.source,
                    cc.created_at
               FROM customer_consent cc
               INNER JOIN (
                    SELECT customer_id, establishment_id, consent_type, MAX(id) AS latest_id
                      FROM customer_consent
                     GROUP BY customer_id, establishment_id, consent_type
               ) latest
                       ON latest.latest_id = cc.id
              WHERE " . implode(' OR ', $conditions),
            'ALL',
            $params
        ) ?: [];

        $map = [];
        foreach ($rows as $row) {
            $key = (int) ($row->customer_id ?? 0) . ':' . (int) ($row->establishment_id ?? 0);
            if (!isset($map[$key])) {
                $map[$key] = [];
            }
            $status = (string) ($row->status ?? 'unknown');
            $meta = self::consentStatusMeta($status);
            $map[$key][(string) ($row->consent_type ?? 'general')] = [
                'consent_type' => (string) ($row->consent_type ?? 'general'),
                'status' => $status,
                'status_label' => $meta['label'],
                'status_color' => $meta['color'],
                'source' => (string) ($row->source ?? ''),
                'updated_at' => (string) ($row->created_at ?? ''),
            ];
        }
        return $map;
    }

    private static function defaultConsentSet()
    {
        $defaults = [];
        foreach (['communications', 'waitlist'] as $type) {
            $meta = self::consentStatusMeta('unknown');
            $defaults[$type] = [
                'consent_type' => $type,
                'status' => 'unknown',
                'status_label' => $meta['label'],
                'status_color' => $meta['color'],
                'source' => '',
                'updated_at' => '',
            ];
        }
        return $defaults;
    }

    private static function buildCustomerScore($metrics, $consents = [])
    {
        $score = 50;
        $totalBookings = (int) ($metrics['total_bookings'] ?? 0);
        $bookingsLast30 = (int) ($metrics['bookings_last_30_days'] ?? 0);
        $upcomingBookings = (int) ($metrics['upcoming_bookings'] ?? 0);
        $cancelRate = (float) ($metrics['cancel_rate'] ?? 0);
        $partialCount = (int) ($metrics['partial_count'] ?? 0);
        $paidTotal = (float) ($metrics['paid_total'] ?? 0);

        if ($bookingsLast30 > 0) $score += 20;
        if ($totalBookings >= 5) $score += 10;
        if ($upcomingBookings > 0) $score += 10;
        if ($paidTotal > 0) $score += 5;

        if ($cancelRate >= 40) {
            $score -= 25;
        } elseif ($cancelRate >= 20) {
            $score -= 15;
        }

        if ($partialCount > 0) $score -= 8;

        $communicationsConsent = $consents['communications']['status'] ?? 'unknown';
        $waitlistConsent = $consents['waitlist']['status'] ?? 'unknown';
        if ($communicationsConsent === 'granted') $score += 5;
        if ($waitlistConsent === 'granted') $score += 5;
        if ($communicationsConsent === 'revoked') $score -= 8;

        $score = max(0, min(100, $score));
        if ($score >= 80) {
            $tier = ['label' => 'VIP', 'color' => 'success'];
        } elseif ($score >= 60) {
            $tier = ['label' => 'Activo', 'color' => 'primary'];
        } elseif ($score >= 40) {
            $tier = ['label' => 'Seguimiento', 'color' => 'warning'];
        } else {
            $tier = ['label' => 'Riesgo', 'color' => 'danger'];
        }

        $alerts = [];
        if ($cancelRate >= 30) $alerts[] = 'Cancelaciones altas';
        if ($bookingsLast30 <= 0) $alerts[] = 'Cliente inactivo';
        if ($partialCount > 0) $alerts[] = 'Saldo pendiente';
        if ($communicationsConsent === 'revoked') $alerts[] = 'Sin consentimiento de comunicación';
        if ($upcomingBookings > 0) $alerts[] = 'Con próxima reserva';

        return [
            'value' => $score,
            'label' => $tier['label'],
            'color' => $tier['color'],
            'alerts' => array_slice(array_values(array_unique($alerts)), 0, 3),
        ];
    }

    public static function getDashboardData($selectedEstablishmentId = 0, $filters = [])
    {
        $selectedEstablishmentId = self::resolveSelectedEstablishmentId($selectedEstablishmentId);
        $filters = [
            'q' => self::normalizeText($filters['q'] ?? ''),
            'field_id' => (int) ($filters['field_id'] ?? 0),
            'activity' => trim((string) ($filters['activity'] ?? '')),
        ];

        $payload = [
            'ready' => self::isInfrastructureReady(),
            'consent_ready' => self::isConsentInfrastructureReady(),
            'privacy_ready' => self::isPrivacyInfrastructureReady(),
            'feature_enabled' => true,
            'selected_establishment_id' => $selectedEstablishmentId,
            'filters' => $filters,
            'establishments' => self::getSelectableEstablishments(),
            'fields' => self::listFields($selectedEstablishmentId),
            'customers' => [],
            'stats' => [
                'total_customers' => 0,
                'active_last_30' => 0,
                'with_upcoming_booking' => 0,
                'high_cancellation_risk' => 0,
                'frequent_customers' => 0,
                'vip_customers' => 0,
                'without_communications_consent' => 0,
            ],
        ];

        if (!$payload['ready']) {
            return $payload;
        }

        $featureTarget = $selectedEstablishmentId > 0 ? $selectedEstablishmentId : self::currentEstablishmentId();
        if ($featureTarget > 0) {
            $payload['feature_enabled'] = self::isFeatureEnabled($featureTarget);
        }

        [$where, $params] = self::buildListWhere($selectedEstablishmentId, $filters);
        $paidAmountExpr = self::columnExists('booking', 'paid_amount') ? 'COALESCE(b.paid_amount, 0)' : '0';

        $rows = query(
            "SELECT c.id AS customer_id,
                    c.full_name,
                    c.phone,
                    c.email,
                    sf.establishment_id,
                    e.name AS establishment_name,
                    COUNT(DISTINCT b.id) AS total_bookings,
                    SUM(CASE WHEN b.status = 2 THEN 1 ELSE 0 END) AS cancelled_bookings,
                    SUM(CASE WHEN b.status <> 2 AND b.date_booking >= CURDATE() THEN 1 ELSE 0 END) AS upcoming_bookings,
                    SUM(CASE WHEN b.date_booking >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS bookings_last_30_days,
                    MAX(b.date_booking) AS last_booking_date,
                    MIN(CASE WHEN b.status <> 2 AND b.date_booking >= CURDATE() THEN b.date_booking END) AS next_booking_date,
                    ROUND(SUM(" . $paidAmountExpr . "), 2) AS paid_total,
                    ROUND(SUM(CASE WHEN " . (self::columnExists('booking', 'payment_status') ? "COALESCE(b.payment_status, '')" : "''") . " = 'partial' THEN 1 ELSE 0 END), 0) AS partial_count
               FROM customers c
               INNER JOIN booking b ON b.id_customer = c.id
               INNER JOIN soccer_field sf ON sf.id = b.id_field
               LEFT JOIN establishment e ON e.id = sf.establishment_id
              WHERE " . implode(' AND ', $where) . "
              GROUP BY c.id, c.full_name, c.phone, c.email, sf.establishment_id, e.name
              ORDER BY MAX(b.date_booking) DESC, c.full_name ASC",
            'ALL',
            $params
        ) ?: [];

        $pairs = [];
        $customers = [];
        foreach ($rows as $row) {
            $totalBookings = (int) ($row->total_bookings ?? 0);
            $cancelledBookings = (int) ($row->cancelled_bookings ?? 0);
            $upcomingBookings = (int) ($row->upcoming_bookings ?? 0);
            $bookingsLast30 = (int) ($row->bookings_last_30_days ?? 0);
            $cancelRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 2) : 0.0;

            $customer = [
                'customer_id' => (int) ($row->customer_id ?? 0),
                'full_name' => (string) ($row->full_name ?? ''),
                'phone' => (string) ($row->phone ?? ''),
                'email' => (string) ($row->email ?? ''),
                'establishment_id' => (int) ($row->establishment_id ?? 0),
                'establishment_name' => (string) ($row->establishment_name ?? 'Establecimiento'),
                'total_bookings' => $totalBookings,
                'cancelled_bookings' => $cancelledBookings,
                'upcoming_bookings' => $upcomingBookings,
                'bookings_last_30_days' => $bookingsLast30,
                'last_booking_date' => (string) ($row->last_booking_date ?? ''),
                'next_booking_date' => (string) ($row->next_booking_date ?? ''),
                'paid_total' => (float) ($row->paid_total ?? 0),
                'partial_count' => (int) ($row->partial_count ?? 0),
                'cancel_rate' => $cancelRate,
                'tags' => [],
                'consents' => self::defaultConsentSet(),
                'score' => ['value' => 0, 'label' => 'Seguimiento', 'color' => 'warning', 'alerts' => []],
            ];

            $activity = $filters['activity'];
            if ($activity === 'upcoming' && $upcomingBookings <= 0) continue;
            if ($activity === 'inactive' && $bookingsLast30 > 0) continue;
            if ($activity === 'frequent' && $totalBookings < 5) continue;
            if ($activity === 'risk' && $cancelRate < 30) continue;

            $customers[] = $customer;
            $pairs[] = [
                'customer_id' => $customer['customer_id'],
                'establishment_id' => $customer['establishment_id'],
            ];
        }

        $tagsMap = self::getTagsMap($pairs);
        $consentMap = self::getConsentMap($pairs);
        foreach ($customers as &$customer) {
            $key = (int) $customer['customer_id'] . ':' . (int) $customer['establishment_id'];
            $customer['tags'] = $tagsMap[$key] ?? [];
            $customer['consents'] = array_merge(self::defaultConsentSet(), $consentMap[$key] ?? []);
            $customer['score'] = self::buildCustomerScore($customer, $customer['consents']);
        }
        unset($customer);

        $payload['customers'] = $customers;
        $payload['stats'] = [
            'total_customers' => count($customers),
            'active_last_30' => count(array_filter($customers, function ($row) {
                return (int) ($row['bookings_last_30_days'] ?? 0) > 0;
            })),
            'with_upcoming_booking' => count(array_filter($customers, function ($row) {
                return (int) ($row['upcoming_bookings'] ?? 0) > 0;
            })),
            'high_cancellation_risk' => count(array_filter($customers, function ($row) {
                return (float) ($row['cancel_rate'] ?? 0) >= 30;
            })),
            'frequent_customers' => count(array_filter($customers, function ($row) {
                return (int) ($row['total_bookings'] ?? 0) >= 5;
            })),
            'vip_customers' => count(array_filter($customers, function ($row) {
                return (int) (($row['score']['value'] ?? 0)) >= 80;
            })),
            'without_communications_consent' => count(array_filter($customers, function ($row) {
                return (($row['consents']['communications']['status'] ?? 'unknown') === 'revoked');
            })),
        ];

        return $payload;
    }

    private static function getDominantChannel($customerId, $establishmentId)
    {
        $customerId = (int) $customerId;
        $establishmentId = (int) $establishmentId;
        if ($customerId <= 0 || $establishmentId <= 0) return '-';

        if (self::columnExists('booking', 'source')) {
            $row = query(
                "SELECT COALESCE(NULLIF(TRIM(b.source), ''), 'web') AS channel,
                        COUNT(*) AS qty
                   FROM booking b
                   INNER JOIN soccer_field sf ON sf.id = b.id_field
                  WHERE b.id_customer = ?
                    AND sf.establishment_id = ?
                  GROUP BY COALESCE(NULLIF(TRIM(b.source), ''), 'web')
                  ORDER BY qty DESC, channel ASC
                  LIMIT 1",
                'ARRAY',
                [$customerId, $establishmentId]
            );
            if (!empty($row['channel'])) {
                return (string) $row['channel'];
            }
        }

        if (class_exists('Waitlist') && Waitlist::isInfrastructureReady()) {
            $row = query(
                "SELECT requested_by_channel AS channel, COUNT(*) AS qty
                   FROM booking_waitlist
                  WHERE customer_id = ?
                    AND establishment_id = ?
                  GROUP BY requested_by_channel
                  ORDER BY qty DESC, requested_by_channel ASC
                  LIMIT 1",
                'ARRAY',
                [$customerId, $establishmentId]
            );
            if (!empty($row['channel'])) {
                return (string) $row['channel'];
            }
        }

        return 'web';
    }

    private static function getTagCatalog($establishmentId)
    {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return [];

        return query(
            "SELECT id, name, color
               FROM customer_tag
              WHERE establishment_id = ?
                AND status = 1
              ORDER BY name ASC",
            'ALL',
            [$establishmentId]
        ) ?: [];
    }

    public static function getCustomerProfile($customerId, $selectedEstablishmentId = 0, $recentBookingsPage = 1)
    {
        $customerId = (int) $customerId;
        $selectedEstablishmentId = self::resolveSelectedEstablishmentId($selectedEstablishmentId);
        $recentBookingsPerPage = 10;
        $recentBookingsPage = max(1, (int) $recentBookingsPage);
        $featureEnabled = $selectedEstablishmentId > 0 ? self::isFeatureEnabled($selectedEstablishmentId) : true;

        $payload = [
            'ready' => self::isInfrastructureReady(),
            'consent_ready' => self::isConsentInfrastructureReady(),
            'privacy_ready' => self::isPrivacyInfrastructureReady(),
            'privacy_scope_ready' => class_exists('Customers') && Customers::isPrivacyScopeInfrastructureReady(),
            'feature_enabled' => $featureEnabled,
            'selected_establishment_id' => $selectedEstablishmentId,
            'establishments' => self::getSelectableEstablishments(),
            'fields' => self::listFields($selectedEstablishmentId),
            'customer' => null,
            'summary' => [],
            'upcoming_bookings' => [],
            'recent_bookings' => [],
            'recent_bookings_pagination' => [
                'current_page' => 1,
                'per_page' => $recentBookingsPerPage,
                'total_items' => 0,
                'total_pages' => 1,
                'offset' => 0,
            ],
            'notes' => [],
            'tags' => [],
            'tag_catalog' => [],
            'consents' => self::defaultConsentSet(),
            'score' => ['value' => 0, 'label' => 'Seguimiento', 'color' => 'warning', 'alerts' => []],
            'privacy_scope_status' => 'active',
            'privacy_context' => [],
            'privacy_policy' => [],
            'not_found' => false,
        ];

        if (!$payload['ready'] || $customerId <= 0 || $selectedEstablishmentId <= 0) {
            $payload['not_found'] = true;
            return $payload;
        }

        if (!self::customerHasScope($customerId, $selectedEstablishmentId)) {
            $payload['not_found'] = true;
            return $payload;
        }

        $paidAmountExpr = self::columnExists('booking', 'paid_amount') ? 'COALESCE(b.paid_amount, 0)' : '0';
        $paymentStatusExpr = self::columnExists('booking', 'payment_status') ? "COALESCE(b.payment_status, '')" : "''";

        $customer = query(
            "SELECT c.id,
                    c.full_name,
                    c.phone,
                    c.email,
                    e.name AS establishment_name,
                    COUNT(DISTINCT b.id) AS total_bookings,
                    SUM(CASE WHEN b.status = 2 THEN 1 ELSE 0 END) AS cancelled_bookings,
                    SUM(CASE WHEN b.status <> 2 AND b.date_booking >= CURDATE() THEN 1 ELSE 0 END) AS upcoming_bookings,
                    ROUND(SUM(" . $paidAmountExpr . "), 2) AS paid_total,
                    ROUND(SUM(CASE WHEN " . $paymentStatusExpr . " = 'partial' THEN 1 ELSE 0 END), 0) AS partial_count,
                    MAX(b.date_booking) AS last_booking_date,
                    MIN(CASE WHEN b.status <> 2 AND b.date_booking >= CURDATE() THEN b.date_booking END) AS next_booking_date
               FROM customers c
               INNER JOIN booking b ON b.id_customer = c.id
               INNER JOIN soccer_field sf ON sf.id = b.id_field
               LEFT JOIN establishment e ON e.id = sf.establishment_id
              WHERE c.id = ?
                AND sf.establishment_id = ?
                AND " . (self::isPrivacyInfrastructureReady() ? Customers::getNotAnonymizedWhereClause('c', 'sf') : '1=1') . "
              GROUP BY c.id, c.full_name, c.phone, c.email, e.name
              LIMIT 1",
            'ARRAY',
            [$customerId, $selectedEstablishmentId]
        );

        if (!$customer) {
            $payload['not_found'] = true;
            return $payload;
        }

        $payload['privacy_scope_status'] = self::isPrivacyInfrastructureReady()
            ? Customers::getScopeStatus($customerId, $selectedEstablishmentId)
            : 'active';
        $payload['privacy_context'] = self::getCustomerPrivacyContext($customerId, $selectedEstablishmentId);
        $payload['privacy_policy'] = self::buildPrivacyPolicySummary(
            $payload['privacy_scope_status'],
            $payload['privacy_context']
        );

        $payload['customer'] = [
            'id' => (int) ($customer['id'] ?? 0),
            'full_name' => (string) ($customer['full_name'] ?? ''),
            'phone' => (string) ($customer['phone'] ?? ''),
            'email' => (string) ($customer['email'] ?? ''),
            'establishment_name' => (string) ($customer['establishment_name'] ?? 'Establecimiento'),
            'dominant_channel' => self::getDominantChannel($customerId, $selectedEstablishmentId),
        ];
        $payload['summary'] = [
            'total_bookings' => (int) ($customer['total_bookings'] ?? 0),
            'cancelled_bookings' => (int) ($customer['cancelled_bookings'] ?? 0),
            'upcoming_bookings' => (int) ($customer['upcoming_bookings'] ?? 0),
            'bookings_last_30_days' => 0,
            'paid_total' => (float) ($customer['paid_total'] ?? 0),
            'partial_count' => (int) ($customer['partial_count'] ?? 0),
            'last_booking_date' => (string) ($customer['last_booking_date'] ?? ''),
            'next_booking_date' => (string) ($customer['next_booking_date'] ?? ''),
            'cancel_rate' => (int) ($customer['total_bookings'] ?? 0) > 0
                ? round(((int) ($customer['cancelled_bookings'] ?? 0) / (int) ($customer['total_bookings'] ?? 0)) * 100, 2)
                : 0.0,
        ];

        $payload['upcoming_bookings'] = query(
            "SELECT b.id,
                    b.date_booking,
                    b.status,
                    sf.full_name AS field_name,
                    s.hour12 AS booking_hour,
                    ROUND(" . $paidAmountExpr . ", 2) AS paid_amount
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
               INNER JOIN schedules s ON s.id = b.time_booking
              WHERE b.id_customer = ?
                AND sf.establishment_id = ?
                AND b.status <> 2
                AND b.date_booking >= CURDATE()
              ORDER BY b.date_booking ASC, b.time_booking ASC
              LIMIT 20",
            'ALL',
            [$customerId, $selectedEstablishmentId]
        ) ?: [];

        $recentBookingsCountRow = query(
            "SELECT COUNT(*) AS qty
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id_customer = ?
                AND sf.establishment_id = ?",
            'ARRAY',
            [$customerId, $selectedEstablishmentId]
        );
        $recentBookingsTotal = (int) ($recentBookingsCountRow['qty'] ?? 0);
        $recentBookingsTotalPages = max(1, (int) ceil($recentBookingsTotal / $recentBookingsPerPage));
        $recentBookingsPage = min($recentBookingsPage, $recentBookingsTotalPages);
        $recentBookingsOffset = ($recentBookingsPage - 1) * $recentBookingsPerPage;
        $payload['recent_bookings_pagination'] = [
            'current_page' => $recentBookingsPage,
            'per_page' => $recentBookingsPerPage,
            'total_items' => $recentBookingsTotal,
            'total_pages' => $recentBookingsTotalPages,
            'offset' => $recentBookingsOffset,
        ];

        $recentBookings = query(
            "SELECT b.id,
                    b.date_booking,
                    b.status,
                    sf.full_name AS field_name,
                    s.hour12 AS booking_hour,
                    ROUND(" . $paidAmountExpr . ", 2) AS paid_amount
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
               INNER JOIN schedules s ON s.id = b.time_booking
              WHERE b.id_customer = ?
                AND sf.establishment_id = ?
              ORDER BY b.date_booking DESC, b.time_booking DESC
              LIMIT " . $recentBookingsPerPage . " OFFSET " . $recentBookingsOffset,
            'ALL',
            [$customerId, $selectedEstablishmentId]
        ) ?: [];

        $payload['recent_bookings'] = array_map(function ($row) {
            return [
                'id' => (int) ($row->id ?? 0),
                'date_booking' => (string) ($row->date_booking ?? ''),
                'status' => (int) ($row->status ?? 0),
                'status_label' => self::bookingStatusLabel((int) ($row->status ?? 0)),
                'field_name' => (string) ($row->field_name ?? ''),
                'booking_hour' => (string) ($row->booking_hour ?? ''),
                'paid_amount' => (float) ($row->paid_amount ?? 0),
            ];
        }, $recentBookings);

        $noteRows = query(
            "SELECT cn.id,
                    cn.note,
                    cn.created_at,
                    u.full_name AS user_name
               FROM customer_note cn
               LEFT JOIN users u ON u.id = cn.user_id
              WHERE cn.customer_id = ?
                AND cn.establishment_id = ?
              ORDER BY cn.created_at DESC, cn.id DESC
              LIMIT 30",
            'ALL',
            [$customerId, $selectedEstablishmentId]
        ) ?: [];
        $payload['notes'] = array_map(function ($row) {
            return [
                'id' => (int) ($row->id ?? 0),
                'note' => (string) ($row->note ?? ''),
                'created_at' => (string) ($row->created_at ?? ''),
                'user_name' => (string) ($row->user_name ?? 'Sistema'),
            ];
        }, $noteRows);

        $tagsMap = self::getTagsMap([[
            'customer_id' => $customerId,
            'establishment_id' => $selectedEstablishmentId,
        ]]);
        $payload['tags'] = $tagsMap[$customerId . ':' . $selectedEstablishmentId] ?? [];
        $payload['tag_catalog'] = $payload['feature_enabled'] ? self::getTagCatalog($selectedEstablishmentId) : [];
        $consentMap = self::getConsentMap([[
            'customer_id' => $customerId,
            'establishment_id' => $selectedEstablishmentId,
        ]]);
        $payload['consents'] = array_merge(self::defaultConsentSet(), $consentMap[$customerId . ':' . $selectedEstablishmentId] ?? []);

        $last30Row = query(
            "SELECT COUNT(*) AS qty
               FROM booking b
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE b.id_customer = ?
                AND sf.establishment_id = ?
                AND b.date_booking >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            'ARRAY',
            [$customerId, $selectedEstablishmentId]
        );
        $payload['summary']['bookings_last_30_days'] = (int) ($last30Row['qty'] ?? 0);
        $payload['score'] = self::buildCustomerScore($payload['summary'], $payload['consents']);

        return $payload;
    }

    public static function saveNote($data)
    {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura del CRM todavía no está disponible'], 409, true);
        }

        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Seleccioná un establecimiento válido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }

        $customerId = (int) ($data->customer_id ?? 0);
        $note = self::normalizeText($data->note ?? '');
        if ($customerId <= 0 || $note === '') {
            JSON(['error' => 'Completá cliente y nota para guardar el seguimiento'], 400, true);
        }
        if (!self::customerHasScope($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente no pertenece al scope del establecimiento seleccionado'], 403, true);
        }
        if (self::isPrivacyInfrastructureReady() && Customers::isAnonymized($customerId)) {
            JSON(['error' => 'El cliente fue anonimizado y ya no admite seguimiento'], 409, true);
        }

        query(
            "INSERT INTO customer_note (establishment_id, customer_id, user_id, note, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            '',
            [$establishmentId, $customerId, self::currentActorUserId(), $note]
        );
        $noteId = (int) Conexion::conectar()->lastInsertId();

        audit('customer_note_add', 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'note_id' => $noteId,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Nota guardada correctamente',
            'note_id' => $noteId,
        ]);
    }

    public static function saveTag($data)
    {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura del CRM todavía no está disponible'], 409, true);
        }

        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Seleccioná un establecimiento válido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }

        $customerId = (int) ($data->customer_id ?? 0);
        self::ensureCustomerIsMutable($customerId, $establishmentId);

        $tagName = self::normalizeText($data->tag_name ?? '');
        $tagId = (int) ($data->tag_id ?? 0);
        $tagColor = self::normalizeTagColor($data->color ?? 'primary');
        if ($tagId <= 0 && $tagName === '') {
            JSON(['error' => 'Seleccioná o escribí un tag para asociar al cliente'], 400, true);
        }

        if ($tagId <= 0) {
            query(
                "INSERT INTO customer_tag (establishment_id, name, color, status, created_by_user_id, created_at, updated_at)
                 VALUES (?, ?, ?, 1, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    color = VALUES(color),
                    status = 1,
                    updated_at = VALUES(updated_at)",
                '',
                [$establishmentId, $tagName, $tagColor, self::currentActorUserId()]
            );
            $tagRow = query(
                "SELECT id
                   FROM customer_tag
                  WHERE establishment_id = ?
                    AND name = ?
                  LIMIT 1",
                'ARRAY',
                [$establishmentId, $tagName]
            );
            $tagId = (int) ($tagRow['id'] ?? 0);
        } else {
            $tagRow = query(
                "SELECT id
                   FROM customer_tag
                  WHERE id = ?
                    AND establishment_id = ?
                    AND status = 1
                  LIMIT 1",
                'ARRAY',
                [$tagId, $establishmentId]
            );
            if (!$tagRow) {
                JSON(['error' => 'El tag seleccionado no pertenece al establecimiento'], 404, true);
            }
        }

        query(
            "INSERT INTO customer_tag_map (customer_id, tag_id, created_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)",
            '',
            [$customerId, $tagId]
        );

        $tagMap = query(
            "SELECT ctm.id, ct.name, ct.color
               FROM customer_tag_map ctm
               INNER JOIN customer_tag ct ON ct.id = ctm.tag_id
              WHERE ctm.customer_id = ?
                AND ctm.tag_id = ?
              LIMIT 1",
            'ARRAY',
            [$customerId, $tagId]
        );

        audit('customer_tag_add', 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'tag_id' => $tagId,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Tag asociado correctamente',
            'tag' => [
                'tag_map_id' => (int) ($tagMap['id'] ?? 0),
                'tag_id' => $tagId,
                'name' => (string) ($tagMap['name'] ?? $tagName),
                'color' => (string) ($tagMap['color'] ?? $tagColor),
            ],
        ]);
    }

    public static function saveConsent($data)
    {
        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Seleccioná un establecimiento válido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }
        if (!self::isConsentInfrastructureReady()) {
            JSON(['error' => 'La infraestructura de consentimientos todavía no está disponible'], 409, true);
        }

        $customerId = (int) ($data->customer_id ?? 0);
        $consentType = strtolower(trim((string) ($data->consent_type ?? '')));
        $status = strtolower(trim((string) ($data->status ?? '')));
        $source = self::normalizeText($data->source ?? 'web_admin');

        self::ensureCustomerIsMutable($customerId, $establishmentId);
        if (!in_array($status, ['granted', 'revoked', 'pending'], true)) {
            JSON(['error' => 'Estado de consentimiento no válido'], 400, true);
        }
        if ($source === '') $source = 'web_admin';

        query(
            "INSERT INTO customer_consent (customer_id, establishment_id, consent_type, status, source, created_by_user_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            '',
            [$customerId, $establishmentId, $consentType, $status, $source, self::currentActorUserId()]
        );

        audit('customer_consent_update', 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'consent_type' => $consentType,
            'status' => $status,
            'source' => $source,
        ]);

        $meta = self::consentStatusMeta($status);
        JSON([
            'success' => true,
            'msg' => 'Consentimiento guardado correctamente',
            'consent' => [
                'consent_type' => $consentType,
                'status' => $status,
                'status_label' => $meta['label'],
                'status_color' => $meta['color'],
                'source' => $source,
            ],
        ]);
    }

    public static function deleteTagMap($data)
    {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura del CRM todavía no está disponible'], 409, true);
        }

        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Seleccioná un establecimiento válido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }

        $tagMapId = (int) ($data->tag_map_id ?? 0);
        if ($tagMapId <= 0) {
            JSON(['error' => 'Tag no válido'], 400, true);
        }

        $tagMap = query(
            "SELECT ctm.id, ctm.customer_id, ct.establishment_id
               FROM customer_tag_map ctm
               INNER JOIN customer_tag ct ON ct.id = ctm.tag_id
              WHERE ctm.id = ?
              LIMIT 1",
            'ARRAY',
            [$tagMapId]
        );
        if (!$tagMap || (int) ($tagMap['establishment_id'] ?? 0) !== $establishmentId) {
            JSON(['error' => 'El tag seleccionado no pertenece al establecimiento'], 404, true);
        }

        query("DELETE FROM customer_tag_map WHERE id = ?", '', [$tagMapId]);

        audit('customer_tag_remove', 'customer', (int) ($tagMap['customer_id'] ?? 0), [
            'establishment_id' => $establishmentId,
            'tag_map_id' => $tagMapId,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Tag removido correctamente',
        ]);
    }

    public static function anonymizeCustomer($data)
    {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura del CRM todavía no está disponible'], 409, true);
        }
        if (!self::isPrivacyInfrastructureReady()) {
            JSON(['error' => 'La infraestructura de privacidad todavía no está disponible'], 409, true);
        }

        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        if ($establishmentId <= 0) {
            JSON(['error' => 'Seleccioná un establecimiento válido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }

        $customerId = (int) ($data->customer_id ?? 0);
        $reason = self::normalizeText($data->reason ?? '');
        if ($customerId <= 0) {
            JSON(['error' => 'Cliente no válido'], 400, true);
        }
        if (!self::customerHasScope($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente no pertenece al scope del establecimiento seleccionado'], 403, true);
        }
        if (Customers::isAnonymized($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente ya fue anonimizado previamente'], 409, true);
        }

        $customer = query(
            "SELECT id, full_name, phone, email
               FROM customers
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$customerId]
        );
        if (!$customer) {
            JSON(['error' => 'Cliente no encontrado'], 404, true);
        }

        $userId = self::currentActorUserId();
        $hasScopedPrivacy = class_exists('Customers') && Customers::isPrivacyScopeInfrastructureReady();
        $establishmentIds = self::getCustomerEstablishmentIds($customerId);
        $otherEstablishmentIds = array_values(array_filter($establishmentIds, function ($value) use ($establishmentId) {
            return (int) $value !== $establishmentId;
        }));
        $hasOtherEstablishments = !empty($otherEstablishmentIds);

        $cleanup = [
            'notes_deleted' => false,
            'tags_deleted' => false,
            'consents_revoked' => false,
        ];
        $scopeMode = 'global_fallback';
        $globalAnonymized = false;

        if ($hasScopedPrivacy) {
            query(
                "INSERT INTO customer_privacy_scope
                    (customer_id, establishment_id, status, reason, anonymized_at, anonymized_by_user_id, created_at, updated_at)
                 VALUES (?, ?, 'anonymized', ?, NOW(), ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    reason = VALUES(reason),
                    anonymized_at = VALUES(anonymized_at),
                    anonymized_by_user_id = VALUES(anonymized_by_user_id),
                    updated_at = NOW()",
                '',
                [$customerId, $establishmentId, $reason !== '' ? $reason : null, $userId]
            );

            $cleanup = self::cleanupScopedPrivacyData($customerId, $establishmentId);

            if (!$hasOtherEstablishments) {
                $globalAnonymized = self::anonymizeCustomerGlobally($customerId, $reason, $userId);
                $scopeMode = 'global';
            } else {
                $scopeMode = 'establishment';
            }
        } else {
            $globalAnonymized = self::anonymizeCustomerGlobally($customerId, $reason, $userId);
            if (self::tableExists('customer_note')) {
                query("DELETE FROM customer_note WHERE customer_id = ?", '', [$customerId]);
                $cleanup['notes_deleted'] = true;
            }
            if (self::tableExists('customer_tag_map')) {
                query("DELETE FROM customer_tag_map WHERE customer_id = ?", '', [$customerId]);
                $cleanup['tags_deleted'] = true;
            }
            if (self::isConsentInfrastructureReady()) {
                foreach (['communications', 'waitlist'] as $consentType) {
                    query(
                        "INSERT INTO customer_consent
                            (customer_id, establishment_id, consent_type, status, source, created_by_user_id, created_at, updated_at)
                         VALUES (?, ?, ?, 'revoked', 'privacy_anonymization', ?, NOW(), NOW())",
                        '',
                        [$customerId, $establishmentId, $consentType, $userId]
                    );
                }
                $cleanup['consents_revoked'] = true;
            }
        }

        audit('customer_anonymized', 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'reason' => $reason,
            'scope_mode' => $scopeMode,
            'global_anonymized' => $globalAnonymized,
            'other_establishments_count' => count($otherEstablishmentIds),
            'previous_phone' => (string) ($customer['phone'] ?? ''),
            'previous_email' => (string) ($customer['email'] ?? ''),
        ]);

        domain_event('customer_anonymized', 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'reason' => $reason,
            'scope_mode' => $scopeMode,
            'global_anonymized' => $globalAnonymized,
            'other_establishment_ids' => $otherEstablishmentIds,
            'notes_deleted' => $cleanup['notes_deleted'],
            'tags_deleted' => $cleanup['tags_deleted'],
            'consents_revoked' => $cleanup['consents_revoked'],
            'previous_name' => (string) ($customer['full_name'] ?? ''),
        ], [
            'source' => 'web_admin',
        ]);

        JSON([
            'success' => true,
            'msg' => $scopeMode === 'establishment'
                ? 'Cliente anonimizado para este establecimiento sin afectar otras sedes'
                : 'Cliente anonimizado correctamente',
        ]);
    }

    public static function setCustomerVisibility($data)
    {
        if (!self::isInfrastructureReady()) {
            JSON(['error' => 'La infraestructura del CRM todavía no está disponible'], 409, true);
        }
        if (!self::isPrivacyInfrastructureReady() || !class_exists('Customers') || !Customers::isPrivacyScopeInfrastructureReady()) {
            JSON(['error' => 'La infraestructura de baja lógica todavía no está disponible'], 409, true);
        }

        $establishmentId = self::resolveSelectedEstablishmentId((int) ($data->establishment_id ?? 0));
        $customerId = (int) ($data->customer_id ?? 0);
        $status = strtolower(trim((string) ($data->status ?? '')));
        $reason = self::normalizeText($data->reason ?? '');

        if ($establishmentId <= 0 || $customerId <= 0) {
            JSON(['error' => 'Cliente o establecimiento inválido'], 400, true);
        }
        if (!self::isFeatureEnabled($establishmentId)) {
            JSON(['error' => 'El módulo CRM no está habilitado para este establecimiento'], 403, true);
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            JSON(['error' => 'Estado de visibilidad inválido'], 400, true);
        }
        if (!self::customerHasScope($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente no pertenece al scope del establecimiento seleccionado'], 403, true);
        }
        if (Customers::isAnonymized($customerId, $establishmentId)) {
            JSON(['error' => 'El cliente ya fue anonimizado y no admite cambios de visibilidad'], 409, true);
        }

        query(
            "INSERT INTO customer_privacy_scope
                (customer_id, establishment_id, status, reason, anonymized_at, anonymized_by_user_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, NULL, NULL, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                reason = VALUES(reason),
                anonymized_at = NULL,
                anonymized_by_user_id = NULL,
                updated_at = NOW()",
            '',
            [$customerId, $establishmentId, $status, $reason !== '' ? $reason : null]
        );

        $eventName = $status === 'inactive' ? 'customer_deactivated' : 'customer_reactivated';
        audit($eventName, 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'reason' => $reason,
            'status' => $status,
        ]);
        domain_event($eventName, 'customer', $customerId, [
            'establishment_id' => $establishmentId,
            'reason' => $reason,
            'status' => $status,
        ], [
            'source' => 'web_admin',
        ]);

        JSON([
            'success' => true,
            'msg' => $status === 'inactive'
                ? 'Cliente dado de baja en este establecimiento'
                : 'Cliente reactivado en este establecimiento',
        ]);
    }
}
