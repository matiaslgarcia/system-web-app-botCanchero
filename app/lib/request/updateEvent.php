<?php

    require '../../int.php';
    session_start();
    
    $_POST['user'] = Users::infoUser('id');
    $_POST['day_booking'] = dayName($_POST['date_booking']);
    Booking::updateByCalendar(obj($_POST));