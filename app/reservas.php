<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Reservas',
        'fullcalendar' => true,
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('reservas');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'fullcalendar'
        ],
        'dataJS' => ['reservas']
    ]);