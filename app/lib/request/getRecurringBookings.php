<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    $user = Users::getById($_SESSION['canchero']);
    $status = $_POST['status'] ?? '';
    $fieldId = (int) ($_POST['field_id'] ?? 0);

    $baseWhere = ['1=1'];
    $baseParams = [];
    if ($user->rol !== 'superAdmin') {
        $myField = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
        $estId = (int) ($myField->establishment_id ?? 0);
        if ($estId > 0) {
            $baseWhere[] = 'sf.establishment_id = :est';
            $baseParams[':est'] = $estId;
        } else {
            $baseWhere[] = 'sf.id = :my_field';
            $baseParams[':my_field'] = (int) $user->id_field;
        }
    }
    if ($fieldId > 0) {
        $baseWhere[] = 'sf.id = :field_id';
        $baseParams[':field_id'] = $fieldId;
    }
    $baseWhereSql = implode(' AND ', $baseWhere);

    $where = $baseWhere;
    $params = $baseParams;
    if ($status) {
        $where[] = 'rb.status = :st';
        $params[':st'] = $status;
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

    // FIJ-01 (auditoría, cruce entre pantallas): con el filtro en "Activas"
    // y sin ninguna fija activa, el vacío decía "No hay reservas fijas" —
    // literalmente falso si hay canceladas/pausadas. total_all deja que el
    // frente distinga "no tenés" de "no tenés con este filtro".
    $totalAllRow = ($status !== '')
        ? query(
            "SELECT COUNT(*) AS c
               FROM recurring_booking rb
               INNER JOIN soccer_field sf ON sf.id = rb.field_id
              WHERE $baseWhereSql",
            '',
            $baseParams
        )
        : null;
    $totalAll = $totalAllRow ? (int) ($totalAllRow->c ?? 0) : count($rows ?: []);

    JSON(['items' => $rows ?: [], 'total_all' => $totalAll]);
