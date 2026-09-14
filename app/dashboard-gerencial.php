<?php

require 'int.php';

Users::loginCheck();

Theme::header([
    'title' => 'Dashboard Gerencial',
    'css'   => [
        'plugins.bundle',
        'style.bundle',
        'FontAwesome',
        'theme'
    ]
]);
inc('dashboard-gerencial');
Theme::footer([
    'js' => [
        'plugins.bundle',
        'scripts.bundle',
    ],
    'dataJS' => [
        'dashboard-gerencial',
    ]
]);
