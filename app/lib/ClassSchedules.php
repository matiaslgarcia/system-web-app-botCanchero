<?php

    class Schedules{
        public static function getByField(){
            $user = Users::getById(Users::infoUser('id'));
            $field = ($user->rol == 'superAdmin') ? '' : 'WHERE f.id_field = ' . $user->id_field;

            $schedules = query("SELECT s.id, s.hour, s.time, f.id_field, f.id_day, s.hour12 FROM schedules AS s LEFT JOIN schedules_field AS f ON s.id = f.id_schedule $field ORDER BY s.id ASC", 'ALL');

            return $schedules;

        }
        public static function getByfieldID($id_field){

            $schedules = query("SELECT s.id, s.hour, s.time, f.id_field, f.id_day, s.hour12 FROM schedules AS s LEFT JOIN schedules_field AS f ON s.id = f.id_schedule WHERE f.id_field = $id_field ORDER BY S.id ASC", 'ALL');

            return $schedules;

        }
        public static function getDay(){
            $day = query("SELECT * FROM schedules_day ORDER BY id", 'ALL');

            return $day;
        }
        public static function getAllDayCancha($id_day, $id_field){
            $schedules = query("SELECT 
                    s.id,
                    s.hour,
                    s.hour12, 
                    CASE WHEN f.id IS NOT NULL THEN 'checked' ELSE NULL END AS checked
                    FROM schedules AS s 
                    LEFT JOIN schedules_field AS f ON s.id = f.id_schedule
                    AND f.id_day = '$id_day'
                    AND
                    f.id_field = '$id_field'
                    ORDER BY s.id ASC",
            'ALL');

            return $schedules;
        }
        public static function deleteAllSchedulesField($id_day, $id_field){
            query("DELETE FROM schedules_field WHERE id_day = '$id_day' AND id_field = '$id_field'");
        }
        public static function addField($id_day, $id_field, $id_schedule){
            query("INSERT INTO schedules_field(
                id_field,
                id_schedule,
                id_day
                ) VALUES (
                    '$id_field',
                    '$id_schedule',
                    '$id_day'
                )
            ");
        }
        public static function getByFieldLibre($id_field, $id_day, $date){
            $result = query("SELECT
                    s.id,
                    d.name AS dia,
                    d.id AS num_day,
                    s.hour AS time,
                    (SELECT COUNT(*) FROM booking WHERE id_field = sf.id_field AND day_booking = d.id AND time_booking = s.id AND date_booking = '$date' AND status <> 2) AS total,
                    f.threshold as threshold
                 FROM
                    schedules_field AS sf
                INNER JOIN schedules AS s
                ON
                     s.id = sf.id_schedule
                INNER JOIN schedules_day AS d
                ON
                    sf.id_day = d.id
                INNER JOIN soccer_field AS f
                ON
                    f.id = sf.id_field
                WHERE 
                    sf.id_field = '$id_field' AND sf.id_day = '$id_day'
                HAVING COALESCE(total, 0) < threshold
            ", 'ALL');

            return $result;
        }
        public static function getHorasBooking($day, $cancha, $date){
            $result = query("SELECT
                    s.id AS id,
                    s.hour12 AS text,
                    (SELECT COUNT(*) AS total FROM booking WHERE id_field = '$cancha' and date_booking = '$date' AND time_booking = s.id AND status <> 2) AS total,
                    f.threshold
                FROM
                    schedules_field AS sf
                INNER JOIN schedules_day AS d
                    ON sf.id_day = d.id
                INNER JOIN schedules AS s
                    ON sf.id_schedule = s.id
                INNER JOIN soccer_field AS f
                    ON f.id = sf.id_field
                WHERE f.id = '$cancha' AND  d.id = '$day'
                HAVING 
                 COALESCE(total, 0) < threshold",
            'ALL');
            
            JSON($result);
        }
    }