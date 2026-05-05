<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user = Users::getById($_SESSION['canchero']);
    $status = $_POST['status'] ?? '';
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
    if ($status) {
        $where[] = 'rb.status = :st';
        $params[':st'] = $status;
    }
    if ($fieldId > 0) {
        $where[] = 'sf.id = :field_id';
        $params[':field_id'] = $fieldId;
    }
    $whereSql = implode(' AND ', $where);

    $rows = query(
        "SELECT rb.*, sf.full_name AS field_name,
                c.full_name AS customer_name, c.phone AS customer_phone
           FROM recurring_booking rb
           INNER JOIN soccer_field sf ON sf.id = rb.field_id
           LEFT JOIN customers c ON c.id = rb.customer_id
          WHERE $whereSql
          ORDER BY rb.day_of_week, rb.start_time",
        'ARRAY_ALL',
        $params
    );

    JSON(['items' => $rows ?: []]);
