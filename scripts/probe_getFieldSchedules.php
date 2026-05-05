<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'GET';
session_start();
$_SESSION['canchero'] = 51;
$_GET = [
    'id_field' => (int) ($argv[1] ?? 67),
    'id_day' => (int) ($argv[2] ?? 3),
];

include 'getFieldSchedules.php';
