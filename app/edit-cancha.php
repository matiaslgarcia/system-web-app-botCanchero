<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();
    $usuario = '';
    Theme::header([
        'title' => 'Editar Cancha',
        'base'  => URL,
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ],
        'extra_css' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css']
    ]);
    inc('edit-cancha');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle'
        ],
        'dataJS' => ['edit-cancha'],
        'extra_js' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js']
    ]);
