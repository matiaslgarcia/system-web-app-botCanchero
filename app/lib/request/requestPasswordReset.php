<?php

define('SKIP_AUTH', true);
require '../../int.php';

$email = (string) ($_POST['email'] ?? '');
Users::requestPasswordReset($email);
