<?php
    require 'int.php';
    Users::loginCheck();

    Theme::header([
        'title' => 'Mi Cancha',
        'css'   => ['plugins.bundle', 'style.bundle', 'FontAwesome', 'theme'],
        'extra_css' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css']
    ]);

    inc('mi-cancha');

    Theme::footer([
        'js' => ['plugins.bundle', 'scripts.bundle'],
        'dataJS' => ['mi-cancha'],
        'extra_js' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.js']
    ]);