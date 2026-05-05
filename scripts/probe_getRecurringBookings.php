<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
$_SESSION['canchero'] = 51;
$_POST = ['status' => (string) ($argv[1] ?? 'active')];

include 'getRecurringBookings.php';
