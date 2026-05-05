<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
$_SESSION['canchero'] = 51;
$_POST = [
    'fecha' => $argv[1] ?? date('Y-m-d'),
    'field_id' => $argv[2] ?? '',
];

include 'getDia.php';
