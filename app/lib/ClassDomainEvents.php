<?php

class DomainEvents
{
    private static $tableExistsCache = [];
    private static $columnExistsCache = [];
    private static $readyCache = null;
    private static $actorPhoneCache = [];

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
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
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
        self::$columnExistsCache[$key] = !empty($row);
        return self::$columnExistsCache[$key];
    }

    public static function isInfrastructureReady()
    {
        if (self::$readyCache !== null) {
            return self::$readyCache;
        }

        self::$readyCache =
            self::tableExists('domain_events')
            && self::columnExists('domain_events', 'entity_type')
            && self::columnExists('domain_events', 'entity_id')
            && self::columnExists('domain_events', 'event_name')
            && self::columnExists('domain_events', 'source')
            && self::columnExists('domain_events', 'performed_by_user_id')
            && self::columnExists('domain_events', 'performed_by_phone')
            && self::columnExists('domain_events', 'payload_json')
            && self::columnExists('domain_events', 'created_at');

        return self::$readyCache;
    }

    private static function currentActorUserId()
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $sessionUserId = (int) ($_SESSION['canchero'] ?? 0);
        if ($sessionUserId > 0) return $sessionUserId;

        if (class_exists('Users')) {
            $userId = (int) (Users::infoUser('id') ?? 0);
            if ($userId > 0) return $userId;
        }
        return null;
    }

    private static function getUserPhone($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) return null;
        if (array_key_exists($userId, self::$actorPhoneCache)) {
            return self::$actorPhoneCache[$userId];
        }

        $row = query(
            "SELECT phone
               FROM users
              WHERE id = ?
              LIMIT 1",
            'ARRAY',
            [$userId]
        );
        self::$actorPhoneCache[$userId] = trim((string) ($row['phone'] ?? '')) ?: null;
        return self::$actorPhoneCache[$userId];
    }

    private static function currentActorPhone($userId = null)
    {
        $userId = $userId !== null ? (int) $userId : (int) (self::currentActorUserId() ?? 0);
        if ($userId > 0) {
            return self::getUserPhone($userId);
        }
        return null;
    }

    private static function normalizePayload($payload)
    {
        if (!is_array($payload)) {
            $payload = $payload ? ['data' => $payload] : [];
        }
        $payload['_request_id'] = getRequestId();
        return $payload;
    }

    public static function record($eventName, $entityType = null, $entityId = null, $payload = [], $options = [])
    {
        if (!self::isInfrastructureReady()) return false;

        $eventName = trim((string) $eventName);
        $entityType = trim((string) $entityType);
        if ($eventName === '' || $entityType === '') return false;

        $payload = self::normalizePayload($payload);
        $source = trim((string) ($options['source'] ?? ($payload['_source'] ?? 'web')));
        if ($source === '') $source = 'web';

        $performedByUserId = array_key_exists('performed_by_user_id', $options)
            ? (int) $options['performed_by_user_id']
            : self::currentActorUserId();
        if ($performedByUserId <= 0) $performedByUserId = null;

        $performedByPhone = array_key_exists('performed_by_phone', $options)
            ? trim((string) $options['performed_by_phone'])
            : self::currentActorPhone($performedByUserId);
        if ($performedByPhone === '') $performedByPhone = null;

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payloadJson === false) {
            $payloadJson = json_encode(['_request_id' => getRequestId(), 'encoding_error' => true]);
        }

        query(
            "INSERT INTO domain_events
                (entity_type, entity_id, event_name, source, performed_by_user_id, performed_by_phone, payload_json, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW())",
            '',
            [
                $entityType,
                $entityId !== null ? (int) $entityId : null,
                $eventName,
                $source,
                $performedByUserId,
                $performedByPhone,
                $payloadJson,
            ]
        );

        return true;
    }
}
