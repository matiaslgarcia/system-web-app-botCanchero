<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();

    Theme::header([
        'title' => 'Usuarios Cancheros',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('users-list');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['users-list']
    ]);
