<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Hoy',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('dia');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['dia']
    ]);
