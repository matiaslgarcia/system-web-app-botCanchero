<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['error' => 'Unauthorized'], 401);
    }

    // Bug de aislamiento multi-tenant: esta consulta no filtraba por
    // establecimiento, así que un canchero podía buscar y ver nombre y
    // teléfono de clientes que solo reservaron en OTRO establecimiento.
    // customers no tiene establishment_id propio — se llega ahí a través de
    // booking -> soccer_field, igual que en ClassCustomerCRM.
    $establishmentId = (int) (FeatureGate::currentEstablishmentId() ?? 0);
    if ($establishmentId <= 0) {
        JSON([]);
    }

    $notAnonymizedClause = class_exists('Customers') && Customers::isPrivacyInfrastructureReady()
        ? Customers::getNotAnonymizedWhereClause('c', 'sf')
        : '1=1';

    $q = $_GET['q'] ?? '';

    if (empty($q)) {
        // Por defecto traer los 10 más recientes
        $customers = query(
            "SELECT DISTINCT c.id, c.full_name as text, c.full_name, c.phone
               FROM customers c
               INNER JOIN booking b ON b.id_customer = c.id
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE sf.establishment_id = :est
                AND {$notAnonymizedClause}
              ORDER BY c.id DESC
              LIMIT 10",
            'ARRAY_ALL',
            [':est' => $establishmentId]
        );
    } else {
        $customers = query(
            "SELECT DISTINCT c.id, c.full_name as text, c.full_name, c.phone
               FROM customers c
               INNER JOIN booking b ON b.id_customer = c.id
               INNER JOIN soccer_field sf ON sf.id = b.id_field
              WHERE sf.establishment_id = :est
                AND (c.full_name LIKE :q OR c.phone LIKE :q)
                AND {$notAnonymizedClause}
              LIMIT 20",
            'ARRAY_ALL',
            [':est' => $establishmentId, ':q' => "%$q%"]
        );
    }

    // Format for Select2
    foreach($customers as &$c) {
        $c['text'] = $c['text'] . " (" . $c['phone'] . ")";
    }

    JSON($customers ?: []);
