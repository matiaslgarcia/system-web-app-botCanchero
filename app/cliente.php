<?php

require 'int.php';

Users::loginCheck();

Theme::header([
    'title' => 'Ficha de Cliente',
    'css'   => [
        'plugins.bundle',
        'style.bundle',
        'FontAwesome',
        'theme'
    ]
]);
inc('cliente');
Theme::footer([
    'js' => [
        'plugins.bundle',
        'scripts.bundle',
    ],
    'dataJS' => [
        'cliente',
    ]
]);
