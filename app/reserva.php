<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Reserva',
        'base'  => '../',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme',
            'datepicker.min',
        ]
    ]);
    inc('reserva');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'datepicker.min',
        ],
        'dataJS' => ['reserva']
    ]);