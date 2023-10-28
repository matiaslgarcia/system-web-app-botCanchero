<?php

    require '../../int.php';
    session_start();
    $_POST['date_booking'] = setDate($_POST['date_booking']);
    $_POST['user'] = Users::infoUser('id');
    $_POST['day_booking'] = dayName($_POST['date_booking']);
    Booking::reAgendar(obj($_POST));