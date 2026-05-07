<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();

    Theme::header([
        'title' => 'Ingresos',
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
