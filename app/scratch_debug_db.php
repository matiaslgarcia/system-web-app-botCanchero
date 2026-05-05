<?php
require 'int.php';
echo "--- STATUS ---\n";
echo json_encode(query("SELECT * FROM booking_status", "ALL"), JSON_PRETTY_PRINT);
echo "\n--- LOGS ---\n";
echo json_encode(query("SELECT * FROM booking_logs LIMIT 5", "ALL"), JSON_PRETTY_PRINT);
