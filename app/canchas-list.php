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
    inc('canchas-list');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['canchas-list']
    ]);