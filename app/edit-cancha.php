<?php

    require 'int.php';

    Users::loginCheck();
    $usuario = '';
    Theme::header([
        'title' => 'Editar Usuario',
        'base'  => '../',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('edit-cancha');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'maps'
        ],
        'dataJS' => ['edit-cancha']
    ]);