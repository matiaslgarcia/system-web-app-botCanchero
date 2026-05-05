<?php
require 'int.php';
echo "--- LOGS COLUMNS ---\n";
echo json_encode(query("SHOW COLUMNS FROM booking_logs", "ALL"), JSON_PRETTY_PRINT);
echo "\n--- USERS COLUMNS ---\n";
echo json_encode(query("SHOW COLUMNS FROM users", "ALL"), JSON_PRETTY_PRINT);
