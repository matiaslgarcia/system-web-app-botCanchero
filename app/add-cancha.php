<?php

    require 'int.php';

    Users::loginCheck();
    Users::requireSuperAdmin();

    Theme::header([
        'title' => 'Crear Cancha',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ],
        'extra_css' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css']
    ]);
    inc('add-cancha');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle'
        ],
        'dataJS' => ['add-cancha'],
        'extra_js' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js']
    ]);
