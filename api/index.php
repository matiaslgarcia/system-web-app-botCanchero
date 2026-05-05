<?php

    // PHP-B3: el int.php web ahora aplica loginCheck()/CSRF automático.
    // V1 API maneja su propia auth (basic-auth) — saltar el middleware web.
    define('SKIP_AUTH', true);
    require '../app/int.php';

    Api::start();
