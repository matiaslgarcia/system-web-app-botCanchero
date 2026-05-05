<?php

    require '../../int.php';

    if(!empty($_POST['id_field']) && !empty($_POST['date_booking'])){
        $_POST['day_booking'] = dayName(setDate($_POST['date_booking']));
        $_POST['date_booking'] = setDate($_POST['date_booking']);
        $excludeId = $_POST['id'] ?? null;
        Schedules::getHorasBooking($_POST['day_booking'], $_POST['id_field'], $_POST['date_booking'], $excludeId);
    }