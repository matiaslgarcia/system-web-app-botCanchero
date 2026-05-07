<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $_POST['user'] = Users::infoUser('id');
    Booking::cancel(obj($_POST));