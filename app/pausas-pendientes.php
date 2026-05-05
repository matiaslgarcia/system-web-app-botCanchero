<?php

    require 'int.php';
    Users::loginCheck();

    Theme::header([
        'title' => 'Pausas Pendientes',
        'css' => ['plugins.bundle','style.bundle','FontAwesome','theme'],
    ]);
    inc('pausas-pendientes');
    Theme::footer([
        'js' => ['plugins.bundle','scripts.bundle'],
        'dataJS' => ['pausas-pendientes'],
    ]);
