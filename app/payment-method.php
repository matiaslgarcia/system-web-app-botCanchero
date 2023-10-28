<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Reservas',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme',
        ]
    ]);
    inc('payment-method');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'fullcalendar',
            'jquery.min',
            'datepicker.min'
        ],
        'dataJS' => []
    ]);