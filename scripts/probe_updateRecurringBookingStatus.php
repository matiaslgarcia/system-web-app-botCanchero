<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
$_SESSION['canchero'] = 51;
$_POST = [
    'id' => (int) ($argv[1] ?? 3),
    'status' => (string) ($argv[2] ?? 'paused'),
];

include 'updateRecurringBookingStatus.php';
