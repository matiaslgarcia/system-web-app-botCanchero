<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
$_SESSION['canchero'] = 51;
$_POST = [
    'status' => $argv[1] ?? 'paused',
    'field_id' => $argv[2] ?? '',
];

include 'getPausasPendientes.php';
