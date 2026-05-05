<?php

$action = $argv[1] ?? '';
$userId = 51;
$fieldId = 67;

if ($action === '') {
    fwrite(STDERR, "Uso: php scripts/probe_web_integration.php [add_booking|add_recurring|edit_profile|view_ingresos|view_mi_ingresos]\n");
    exit(1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['canchero'] = $userId;

if ($action === 'add_booking') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'full_name' => 'QA Web Integracion',
        'phone' => '11' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
        'id_field' => (string) $fieldId,
        'email' => 'qa-web@example.com',
        'date_booking' => date('d/m/Y', strtotime('+1 day')),
        'time_booking' => '17',
    ];

    chdir(__DIR__ . '/../app/lib/request');
    include 'add-booking.php';
}

if ($action === 'add_recurring') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $minute = str_pad((string) random_int(0, 59), 2, '0', STR_PAD_LEFT);
    $_POST = [
        'field_id' => (string) $fieldId,
        'customer_id' => '1',
        'day_of_week' => (string) date('N', strtotime('+1 day')),
        'start_time' => '05:' . $minute,
        'duration_min' => '60',
        'valid_from' => date('Y-m-d', strtotime('+1 day')),
        'valid_until' => '',
    ];

    chdir(__DIR__ . '/../app/lib/request');
    include 'addRecurringBooking.php';
}

if ($action === 'edit_profile') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'id' => (string) $userId,
        'full_name' => 'Matias Luciano Garcia',
        'email' => 'matiasgarcia444@gmail.com',
        'phone' => '3625293513',
    ];

    chdir(__DIR__ . '/../app/lib/request');
    include 'edit-user.php';
}

if ($action === 'view_ingresos' || $action === 'view_mi_ingresos') {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = ['date' => date('d/m/Y')];

    ob_start();
    include __DIR__ . '/../app/' . ($action === 'view_ingresos' ? 'ingresos.php' : 'mi-ingresos.php');
    $html = ob_get_clean();

    $isMiIngresos = ($action === 'view_mi_ingresos');
    $result = [
        'ok' => true,
        'view' => $action,
        'checks' => $isMiIngresos
            ? [
                'has_detalle' => (strpos($html, 'Detalle de Ingresos') !== false),
                'has_total' => (strpos($html, 'Ingreso Total del Día') !== false),
            ]
            : [
                'has_estado_pago' => (strpos($html, 'Estado del Pago') !== false),
                'has_total' => (strpos($html, 'Total:') !== false),
            ],
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit(0);
}

fwrite(STDERR, "Accion no soportada: {$action}\n");
exit(1);
