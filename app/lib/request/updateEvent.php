<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $_POST['user'] = Users::infoUser('id');
    $_POST['day_booking'] = dayName($_POST['date_booking']);
    Booking::updateByCalendar(obj($_POST));