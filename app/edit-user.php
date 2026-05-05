<?php

    require 'int.php';

    Users::loginCheck([
        'base' => '../'
    ]);
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
    inc('edit-user');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['edit-user']
    ]);