<?php

    require 'int.php';
    Users::loginCheck();

    Theme::header([
        'title' => 'Reservas Fijas',
        'css' => ['plugins.bundle','style.bundle','FontAwesome','theme'],
    ]);
    inc('recurring-bookings');
    Theme::footer([
        'js' => ['plugins.bundle','scripts.bundle'],
        'dataJS' => ['recurring-bookings'],
    ]);
