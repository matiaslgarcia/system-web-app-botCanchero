<?php
    require '/var/www/systemWebBotCanchero/app/int.php';
    date_default_timezone_set('America/Argentina/Buenos_Aires');

    $date = date('Ymd');
    $time = date('His');

    function completar($id){
        $current_user = query("SELECT user FROM booking WHERE id = '$id'", 'ONE');

        if ($current_user == 1) {
            query("UPDATE booking SET user = 1, status = 3 WHERE id = '$id'");
        } elseif ($current_user == 21) {
            query("UPDATE booking SET user = 21, status = 3 WHERE id = '$id'");
        }
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

