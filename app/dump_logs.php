<?php
require 'int.php';
echo "--- LAST 10 LOGS (ALL) ---\n";
echo json_encode(query("SELECT * FROM booking_logs ORDER BY 1 DESC LIMIT 10", "ALL"), JSON_PRETTY_PRINT);
