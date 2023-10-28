<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Dashboard',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme',
            'dataTables',
            'datepicker.min'
        ]
    ]);
    inc('ingresos');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'dataTables',
            'datepicker.min'
        ],
        'dataJS' => ['ingresos']
    ]);