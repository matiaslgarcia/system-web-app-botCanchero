<?php

    require '../../int.php';

    session_start();

    $user = Users::getById($_SESSION['canchero']);

    $cancha = ($user->rol == 'superAdmin') ? '%' : $user->id_field;

    $booking = query("SELECT
            b.id AS id,
            b.day_booking AS day,
            b.date_booking AS date,
            h.time AS time,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            f.full_name AS cancha,
            s.id AS id_status,
            CONCAT(c.full_name) AS title,
            CONCAT(b.date_booking, ' ',  h.time) AS start,
            CONCAT('reserva/', b.id) AS url,
            CONCAT(s.color_hex) AS color
            
        FROM
            booking AS b
        INNER JOIN soccer_field AS f
        ON
            f.id = b.id_field
        INNER JOIN customers AS c
        ON
            c.id = b.id_customer
        INNER JOIN booking_status AS s
        ON
            s.id = b.status
        INNER JOIN schedules AS h
        ON h.id = b.time_booking
        WHERE
            f.id LIKE '$cancha';
        ", 'ALL'
    );

    foreach($booking AS $reserva){
        $reserva->start = date("Y-m-d\TH:i:s", strtotime($reserva->start));

        if($reserva->id_status == 1){
            $reserva->droppable  = true;
            $reserva->editable = true;
        }
        
    }

    $data = [
        'reservas' => $booking,
    ];

    JSON($data);