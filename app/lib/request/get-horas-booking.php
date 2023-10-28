<?php

    require '../../int.php';

    if(!empty($_POST['id_field']) and empty(!$_POST['date_booking'])){
        $_POST['day_booking'] = dayName(setDate($_POST['date_booking']));
        Schedules::getHorasBooking($_POST['day_booking'], $_POST['id_field'], $_POST['date_booking']);
    }