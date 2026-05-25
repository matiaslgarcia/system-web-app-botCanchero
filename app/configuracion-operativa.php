<?php

require 'int.php';

Users::loginCheck();

$redirectUrl = 'account_settings';
$establishmentId = (int) ($_GET['establishment_id'] ?? 0);
if ($establishmentId > 0) {
    $redirectUrl .= '?establishment_id=' . $establishmentId;
}
header('Location: ' . $redirectUrl . '#kt_user_operational_tab');
exit;
