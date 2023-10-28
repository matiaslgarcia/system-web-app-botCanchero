<?php

    require 'int.php';

    Users::loginCheck();
    Theme::header([
        'title' => 'Editar Usuario',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('mi-cancha');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['mi-cancha']
    ]);