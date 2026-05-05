<?php
require 'int.php';
$columns = query("SHOW COLUMNS FROM booking_logs", "ALL");
header('Content-Type: application/json');
echo json_encode($columns, JSON_PRETTY_PRINT);
