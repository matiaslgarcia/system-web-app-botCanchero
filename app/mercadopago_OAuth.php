<?php

    require 'int.php';

    session_start();

    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;
    if (empty($code)) {
        header('Location: ' . MercadoPago::appUrl('account_settings?error=mp_oauth_code_missing'));
        die();
    }

    MercadoPago::createRefreshToken($code, $state);
