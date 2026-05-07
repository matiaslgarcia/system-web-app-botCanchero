<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Reserva',
        'base'  => URL,
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'theme',
            'datepicker.min',
        ]
    ]);
    inc('reserva');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
            'datepicker.min',
        ]
    ]);
?>
    <!-- CSS Global -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script type="module" src="lib/dataJS/reserva.js?ver=<?php echo VERSION ?>"></script>
