<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Dashboard',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('add-cancha');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'maps'
        ],
        'dataJS' => ['add-cancha']
    ]);