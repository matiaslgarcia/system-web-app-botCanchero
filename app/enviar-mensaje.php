<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Enviar Mensaje WhatsApp',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme',
        ]
    ]);
    inc('enviar-mensaje');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ],
        'dataJS' => ['enviar-mensaje']
    ]);
