<?php

require 'int.php';

Users::loginCheck();

Theme::header([
    'title' => 'Clientes',
    'css'   => [
        'plugins.bundle',
        'style.bundle',
        'FontAwesome',
        'theme'
    ]
]);
inc('clientes');
Theme::footer([
    'js' => [
        'plugins.bundle',
        'scripts.bundle',
    ]
]);
