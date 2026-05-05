<?php

chdir(__DIR__ . '/../app/lib/request');
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
$_SESSION['canchero'] = 1;
session_write_close();

$_POST = ['meta_only' => 1];
if (!empty($argv[1])) {
    $_POST['signature'] = (string) $argv[1];
}

include 'getReservas.php';
