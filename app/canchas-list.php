<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();

    Theme::header([
        'title' => 'Listado de Canchas',
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
