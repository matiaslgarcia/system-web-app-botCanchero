<?php
require 'int.php';
$columns = query("SHOW COLUMNS FROM booking_logs", "ALL");
$msg = "COLS: ";
foreach($columns as $c) $msg .= $c->Field . ", ";
throw new Exception($msg);
