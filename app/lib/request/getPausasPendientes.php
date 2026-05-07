<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    $user = Users::getById($_SESSION['canchero']);
    $status = $_POST['status'] ?? 'paused';
    $fieldId = (int) ($_POST['field_id'] ?? 0);

    $where = ['1=1'];
    $params = [];
    if ($user->rol !== 'superAdmin') {
        $myField = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
        $estId = (int) ($myField->establishment_id ?? 0);
        if ($estId > 0) {
            $where[] = 'sf.establishment_id = :est';
            $params[':est'] = $estId;
        } else {
            $where[] = 'sf.id = :my_field';
            $params[':my_field'] = (int) $user->id_field;
        }
    }

    if (in_array($status, ['paused', 'active', 'cancelled'], true)) {
        $where[] = 'rb.status = :st';
        $params[':st'] = $status;
    }
    if ($fieldId > 0) {
        $where[] = 'rb.field_id = :field';
        $params[':field'] = $fieldId;
    }

    $whereSql = implode(' AND ', $where);

    $countWhere = [];
    foreach ($where as $cond) {
        if ($cond !== 'rb.status = :st') $countWhere[] = $cond;
    }
    $countParams = $params;
    unset($countParams[':st']);

    $countRows = query(
        "SELECT
            SUM(CASE WHEN rb.status = 'paused' THEN 1 ELSE 0 END) AS paused_count,
            SUM(CASE WHEN rb.status = 'active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN rb.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
         FROM recurring_booking rb
         INNER JOIN soccer_field sf ON sf.id = rb.field_id
         WHERE " . implode(' AND ', $countWhere),
        'ARRAY',
        $countParams
    );

    $rows = query(
        "SELECT
            rb.id,
            rb.field_id,
            rb.day_of_week,
            rb.start_time,
            rb.duration_min,
            rb.valid_from,
            rb.valid_until,
            rb.status,
            rb.cancelled_at,
            rb.cancelled_reason,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            sf.full_name AS field_name
         FROM recurring_booking rb
         INNER JOIN soccer_field sf ON sf.id = rb.field_id
         LEFT JOIN customers c ON c.id = rb.customer_id
         WHERE $whereSql
         ORDER BY rb.updated_at DESC, rb.id DESC",
        'ARRAY_ALL',
        $params
    );

    JSON([
        'items' => $rows ?: [],
        'counts' => [
            'paused' => (int) ($countRows['paused_count'] ?? 0),
            'active' => (int) ($countRows['active_count'] ?? 0),
            'cancelled' => (int) ($countRows['cancelled_count'] ?? 0),
        ]
    ]);
