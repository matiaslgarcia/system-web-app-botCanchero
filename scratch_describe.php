<?php
require 'app/config.php';
require 'app/lib/ClassConexion.php';

$res = query("DESCRIBE soccer_field", 'ALL');
print_r($res);
