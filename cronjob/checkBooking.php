<?php
    require '/var/www/systemWebBotCanchero/app/int.php';

    $date = date('Ymd');
    $time = date('His');

    function completar($id){
        query("UPDATE booking SET user = 1, status = 3 WHERE id = '$id'");
    }

    $booking = query("SELECT
            b.id
        FROM booking AS b
        INNER JOIN schedules AS s
            ON s.id = b.time_booking
        WHERE
            DATE_FORMAT(b.date_booking, '%Y%m%d') <= '$date'
            AND
            DATE_FORMAT(DATE_ADD(s.time, INTERVAL 1 HOUR),'%H%i%s%') < '$time'
            AND
            b.status IN (1,6) "
    ,'ALL');

    foreach($booking AS $b){
        completar($b->id);
    }
