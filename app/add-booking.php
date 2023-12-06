<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Agregar Reserva',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme',
            'datepicker.min'
        ]
    ]);
    inc('add-booking');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'datepicker.min',
        ],
        'dataJS' => ['add-reserva']
    ]);