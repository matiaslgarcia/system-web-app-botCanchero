<?php

class FeatureGate {
    private static $tableExistsCache = [];
    private static $readyCache = null;
    private static $enabledCache = [];

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

    public static function missingTables() {
        $required = [
            'establishment',
            'plan_catalog',
            'feature_catalog',
            'plan_feature_map',
            'establishment_subscription',
            'establishment_feature_override',
        ];
        return array_values(array_filter($required, function ($table) {
            return !self::tableExists($table);
        }));
    }

    public static function isInfrastructureReady() {
        if (self::$readyCache !== null) {
            return self::$readyCache;
        }
        self::$readyCache = count(self::missingTables()) === 0;
        return self::$readyCache;
    }

    public static function currentEstablishmentId() {
        if (class_exists('Auth') && method_exists('Auth', 'getEstablishmentId')) {
            $apiEst = (int) Auth::getEstablishmentId();
            if ($apiEst > 0) return $apiEst;
        }

        if (!class_exists('Users') || !method_exists('Users', 'infoUser')) {
            return null;
        }

        $fieldId = (int) Users::infoUser('id_field');
        if ($fieldId <= 0) return null;

        $row = query(
            "SELECT establishment_id
               FROM soccer_field
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$fieldId]
        );
        $establishmentId = (int) ($row['establishment_id'] ?? 0);
        return $establishmentId > 0 ? $establishmentId : null;
    }

    public static function listEnabled($establishmentId) {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0 || !self::isInfrastructureReady()) {
            return [];
        }
        if (isset(self::$enabledCache[$establishmentId])) {
            return self::$enabledCache[$establishmentId];
        }

        $rows = query(
            "SELECT f.code,
                    CASE
                        WHEN o.id IS NOT NULL
                             AND (o.expires_at IS NULL OR o.expires_at >= NOW())
                        THEN o.enabled
                        ELSE COALESCE(pfm.enabled, 0)
                    END AS enabled
               FROM feature_catalog f
               LEFT JOIN establishment_subscription es
                      ON es.establishment_id = :est
               LEFT JOIN plan_feature_map pfm
                      ON pfm.plan_id = es.plan_id
                     AND pfm.feature_id = f.id
               LEFT JOIN establishment_feature_override o
                      ON o.establishment_id = :est2
                     AND o.feature_id = f.id
              WHERE f.status = 1",
            'ALL',
            [
                ':est' => $establishmentId,
                ':est2' => $establishmentId,
            ]
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row->code] = (int) ($row->enabled ?? 0) === 1;
        }
        self::$enabledCache[$establishmentId] = $result;
        return $result;
    }

    public static function isEnabled($establishmentId, $featureCode, $defaultWhenUnavailable = true) {
        $featureCode = trim((string) $featureCode);
        $establishmentId = (int) $establishmentId;
        if ($featureCode === '') return false;
        if ($establishmentId <= 0) return (bool) $defaultWhenUnavailable;
        if (!self::isInfrastructureReady()) return (bool) $defaultWhenUnavailable;

        $enabled = self::listEnabled($establishmentId);
        if (!array_key_exists($featureCode, $enabled)) {
            return false;
        }
        return $enabled[$featureCode];
    }

    public static function requireCurrent($featureCode, $asJson = false) {
        $establishmentId = self::currentEstablishmentId();
        if (self::isEnabled($establishmentId, $featureCode)) {
            return;
        }

        if ($asJson) {
            JSON([
                'error' => 'Módulo no habilitado para este establecimiento',
                'feature_code' => (string) $featureCode,
            ], 403, true);
        }

        header('Location: ' . URL . '?feature_locked=' . urlencode((string) $featureCode));
        die();
    }

    public static function requireApi($featureCode) {
        $establishmentId = self::currentEstablishmentId();
        if (self::isEnabled($establishmentId, $featureCode)) {
            return;
        }

        Api::ApiError([
            'error' => 'Feature not enabled for this establishment',
            'feature_code' => (string) $featureCode,
        ], 403);
    }

    public static function getPlans() {
        if (!self::isInfrastructureReady()) return [];
        return query(
            "SELECT p.id, p.code, p.name, p.description, p.status, p.sort_order,
                    COUNT(DISTINCT pfm.feature_id) AS features_count
               FROM plan_catalog p
               LEFT JOIN plan_feature_map pfm ON pfm.plan_id = p.id AND pfm.enabled = 1
              GROUP BY p.id
              ORDER BY p.sort_order ASC, p.name ASC",
            'ALL'
        ) ?: [];
    }

    public static function listEstablishmentsOverview() {
        if (!self::isInfrastructureReady()) return [];
        return query(
            "SELECT e.id,
                    e.name,
                    e.active,
                    e.owner_user_id,
                    u.full_name AS owner_name,
                    u.email AS owner_email,
                    es.plan_id,
                    es.status AS subscription_status,
                    es.billing_mode,
                    es.price_amount,
                    es.currency,
                    es.trial_ends_at,
                    es.ends_at,
                    p.code AS plan_code,
                    p.name AS plan_name,
                    (
                        SELECT COUNT(*)
                          FROM establishment_feature_override o
                         WHERE o.establishment_id = e.id
                           AND (o.expires_at IS NULL OR o.expires_at >= NOW())
                    ) AS override_count
               FROM establishment e
               LEFT JOIN users u ON u.id = e.owner_user_id
               LEFT JOIN establishment_subscription es ON es.establishment_id = e.id
               LEFT JOIN plan_catalog p ON p.id = es.plan_id
              ORDER BY e.name ASC, e.id ASC",
            'ALL'
        ) ?: [];
    }

    public static function getSubscription($establishmentId) {
        if (!self::isInfrastructureReady()) return null;
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return null;

        return query(
            "SELECT es.*, p.code AS plan_code, p.name AS plan_name
               FROM establishment_subscription es
               INNER JOIN plan_catalog p ON p.id = es.plan_id
              WHERE es.establishment_id = ?
              LIMIT 1",
            '',
            [$establishmentId]
        );
    }

    public static function getFeatureMatrix($establishmentId) {
        if (!self::isInfrastructureReady()) return [];
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) return [];

        return query(
            "SELECT f.id,
                    f.code,
                    f.name,
                    f.description,
                    f.scope,
                    f.is_premium,
                    COALESCE(pfm.enabled, 0) AS plan_enabled,
                    o.id AS override_id,
                    o.enabled AS override_enabled,
                    o.reason,
                    o.expires_at,
                    CASE
                        WHEN o.id IS NULL THEN 'inherit'
                        WHEN o.enabled = 1 THEN 'enabled'
                        ELSE 'disabled'
                    END AS override_mode,
                    CASE
                        WHEN o.id IS NOT NULL
                             AND (o.expires_at IS NULL OR o.expires_at >= NOW())
                        THEN o.enabled
                        ELSE COALESCE(pfm.enabled, 0)
                    END AS effective_enabled
               FROM feature_catalog f
               LEFT JOIN establishment_subscription es
                      ON es.establishment_id = :est
               LEFT JOIN plan_feature_map pfm
                      ON pfm.plan_id = es.plan_id
                     AND pfm.feature_id = f.id
               LEFT JOIN establishment_feature_override o
                      ON o.establishment_id = :est2
                     AND o.feature_id = f.id
              WHERE f.status = 1
              ORDER BY f.scope ASC, f.sort_order ASC, f.name ASC",
            'ALL',
            [
                ':est' => $establishmentId,
                ':est2' => $establishmentId,
            ]
        ) ?: [];
    }

    public static function getDashboardData($selectedEstablishmentId = null) {
        $data = [
            'ready' => self::isInfrastructureReady(),
            'missing_tables' => self::missingTables(),
            'plans' => [],
            'establishments' => [],
            'selected_establishment_id' => null,
            'selected_subscription' => null,
            'selected_features' => [],
        ];

        if (!$data['ready']) return $data;

        $data['plans'] = self::getPlans();
        $data['establishments'] = self::listEstablishmentsOverview();
        $selectedEstablishmentId = (int) $selectedEstablishmentId;

        if ($selectedEstablishmentId <= 0 && !empty($data['establishments'])) {
            $selectedEstablishmentId = (int) $data['establishments'][0]->id;
        }
        $data['selected_establishment_id'] = $selectedEstablishmentId > 0 ? $selectedEstablishmentId : null;

        if ($selectedEstablishmentId > 0) {
            $data['selected_subscription'] = self::getSubscription($selectedEstablishmentId);
            $data['selected_features'] = self::getFeatureMatrix($selectedEstablishmentId);
        }

        return $data;
    }

    private static function validateInfrastructureForWrite() {
        if (!self::isInfrastructureReady()) {
            JSON([
                'error' => 'La infraestructura comercial no está disponible',
                'missing_tables' => self::missingTables(),
            ], 409, true);
        }
    }

    private static function clearCache($establishmentId = null) {
        if ($establishmentId === null) {
            self::$enabledCache = [];
            return;
        }
        unset(self::$enabledCache[(int) $establishmentId]);
    }

    public static function saveSubscription($data) {
        self::validateInfrastructureForWrite();
        $establishmentId = (int) ($data->establishment_id ?? 0);
        $planId = (int) ($data->plan_id ?? 0);
        $status = trim((string) ($data->status ?? 'trial'));
        $billingMode = trim((string) ($data->billing_mode ?? 'manual'));
        $priceAmount = trim((string) ($data->price_amount ?? ''));
        $currency = strtoupper(trim((string) ($data->currency ?? 'ARS')));
        $trialEndsAt = trim((string) ($data->trial_ends_at ?? ''));
        $endsAt = trim((string) ($data->ends_at ?? ''));

        $allowedStatus = ['trial', 'active', 'past_due', 'suspended', 'cancelled'];
        $allowedBilling = ['manual', 'subscription', 'fee', 'none'];

        if ($establishmentId <= 0 || $planId <= 0) {
            JSON(['error' => 'Establecimiento o plan inválido'], 400, true);
        }
        if (!in_array($status, $allowedStatus, true)) {
            JSON(['error' => 'Estado de suscripción inválido'], 400, true);
        }
        if (!in_array($billingMode, $allowedBilling, true)) {
            JSON(['error' => 'Modo de facturación inválido'], 400, true);
        }
        if ($currency === '') $currency = 'ARS';

        $establishment = query("SELECT id FROM establishment WHERE id = ? LIMIT 1", 'ARRAY', [$establishmentId]);
        if (!$establishment) {
            JSON(['error' => 'Establecimiento no encontrado'], 404, true);
        }

        $plan = query("SELECT id, name FROM plan_catalog WHERE id = ? LIMIT 1", 'ARRAY', [$planId]);
        if (!$plan) {
            JSON(['error' => 'Plan no encontrado'], 404, true);
        }

        $priceValue = $priceAmount === '' ? null : (float) $priceAmount;
        $trialValue = $trialEndsAt !== '' ? date('Y-m-d H:i:s', strtotime($trialEndsAt)) : null;
        $endsValue = $endsAt !== '' ? date('Y-m-d H:i:s', strtotime($endsAt)) : null;

        query(
            "INSERT INTO establishment_subscription
                (establishment_id, plan_id, status, billing_mode, price_amount, currency, trial_ends_at, ends_at, starts_at, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                plan_id = VALUES(plan_id),
                status = VALUES(status),
                billing_mode = VALUES(billing_mode),
                price_amount = VALUES(price_amount),
                currency = VALUES(currency),
                trial_ends_at = VALUES(trial_ends_at),
                ends_at = VALUES(ends_at),
                updated_at = NOW()",
            '',
            [
                $establishmentId,
                $planId,
                $status,
                $billingMode,
                $priceValue,
                $currency,
                $trialValue,
                $endsValue,
            ]
        );

        self::clearCache($establishmentId);
        audit('subscription_update', 'establishment', $establishmentId, [
            'plan_id' => $planId,
            'plan_name' => $plan['name'],
            'status' => $status,
            'billing_mode' => $billingMode,
            'price_amount' => $priceValue,
            'currency' => $currency,
            'trial_ends_at' => $trialValue,
            'ends_at' => $endsValue,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Suscripción actualizada correctamente',
            'establishment_id' => $establishmentId,
        ]);
    }

    public static function saveOverride($data) {
        self::validateInfrastructureForWrite();
        $establishmentId = (int) ($data->establishment_id ?? 0);
        $featureId = (int) ($data->feature_id ?? 0);
        $mode = trim((string) ($data->override_mode ?? 'inherit'));
        $reason = trim((string) ($data->reason ?? ''));
        $expiresAt = trim((string) ($data->expires_at ?? ''));
        $createdBy = (int) ($_SESSION['canchero'] ?? 0);

        if ($establishmentId <= 0 || $featureId <= 0) {
            JSON(['error' => 'Establecimiento o módulo inválido'], 400, true);
        }
        if (!in_array($mode, ['inherit', 'enabled', 'disabled'], true)) {
            JSON(['error' => 'Modo de override inválido'], 400, true);
        }

        $feature = query(
            "SELECT id, code, name
               FROM feature_catalog
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$featureId]
        );
        if (!$feature) {
            JSON(['error' => 'Módulo no encontrado'], 404, true);
        }

        if ($mode === 'inherit') {
            query(
                "DELETE FROM establishment_feature_override
                  WHERE establishment_id = ?
                    AND feature_id = ?",
                '',
                [$establishmentId, $featureId]
            );
        } else {
            $enabled = $mode === 'enabled' ? 1 : 0;
            $expiresValue = $expiresAt !== '' ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null;
            query(
                "INSERT INTO establishment_feature_override
                    (establishment_id, feature_id, enabled, reason, expires_at, created_by_user_id, created_at, updated_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    enabled = VALUES(enabled),
                    reason = VALUES(reason),
                    expires_at = VALUES(expires_at),
                    created_by_user_id = VALUES(created_by_user_id),
                    updated_at = NOW()",
                '',
                [
                    $establishmentId,
                    $featureId,
                    $enabled,
                    $reason !== '' ? $reason : null,
                    $expiresValue,
                    $createdBy > 0 ? $createdBy : null,
                ]
            );
        }

        self::clearCache($establishmentId);
        audit('feature_override_update', 'establishment', $establishmentId, [
            'feature_id' => $featureId,
            'feature_code' => $feature['code'],
            'override_mode' => $mode,
            'reason' => $reason,
            'expires_at' => $expiresAt !== '' ? $expiresAt : null,
        ]);

        JSON([
            'success' => true,
            'msg' => 'Override actualizado correctamente',
            'establishment_id' => $establishmentId,
            'feature_code' => $feature['code'],
        ]);
    }
}
