<?php

    require '../../int.php';
    session_start();
    
    $_POST['user'] = Users::infoUser('id');
    Booking::cancel(obj($_POST));