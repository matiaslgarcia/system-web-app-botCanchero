<?php

    require '../../int.php';
    session_start();

    if (!isset($_SESSION['canchero'])) {
        JSON(['error' => 'Unauthorized'], 401);
    }

    $q = $_GET['q'] ?? '';
    
    if (empty($q)) {
        // Por defecto traer los 10 más recientes
        $customers = query(
            "SELECT id, full_name as text, phone 
             FROM customers 
             ORDER BY id DESC
             LIMIT 10",
            'ARRAY_ALL'
        );
    } else {
        $customers = query(
            "SELECT id, full_name as text, phone 
             FROM customers 
             WHERE full_name LIKE :q OR phone LIKE :q 
             LIMIT 20",
            'ARRAY_ALL',
            [':q' => "%$q%"]
        );
    }

    // Format for Select2
    foreach($customers as &$c) {
        $c['text'] = $c['text'] . " (" . $c['phone'] . ")";
    }

    JSON($customers ?: []);
