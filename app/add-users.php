<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();

    Theme::header([
        'title' => 'Crear Usuario',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('add-user');
    
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['add-user']
    ]);
