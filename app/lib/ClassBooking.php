<?php

    class Booking{
        public static function getById($id){
            $reserva = query("SELECT
                b.id,
                b.id_field,
                f.full_name AS cancha,
                b.time_booking,
                b.date_booking AS fecha,
                h.hour12 AS hora,
                c.full_name AS customer_name,
                c.phone AS customer_phone,
                s.name AS status_name,
                s.id   AS status_id,
                s.color AS status_color
            FROM
                booking AS b
            INNER JOIN soccer_field AS f
            ON
                b.id_field = f.id
            INNER JOIN customers AS c
            ON
                b.id_customer = c.id
            INNER JOIN booking_status AS s
            ON
                b.status = s.id
            INNER JOIN schedules AS h ON h.id = b.time_booking
            WHERE b.id = '$id'");
            
            return $reserva;
        }
        public static function getLogs($Id){
            $logs = query("SELECT
                fecha,
                hora,
                u.full_name AS user,
                s.name AS logs_name,
                s.color AS logs_color,
                l.id
                FROM booking_logs AS l 
                INNER JOIN booking_status AS s 
                    ON l.log = s.id 
                INNER JOIN users AS u
                    ON u.id = l.users WHERE l.id_reserva = '$Id' ORDER BY fecha DESC",'ALL');

           return $logs;
        }
        public static function update($data) {
            query("UPDATE booking SET
                id_customer  = '$data->id_customer',
                day_booking  = '$data->day_booking',
                time_booking = '$data->time_booking',
                date_booking = '$data->date_booking',
                user         = '$data->user',
                payment      = '$data->payment',
                status       = '$data->status'
            WHERE b.id = '$data->id'");
        }
        public static function updateByCalendar($data) {
            query("UPDATE booking AS b INNER JOIN schedules AS s ON s.time = '$data->time_booking' SET
                day_booking  = '$data->day_booking',
                time_booking = s.id,
                date_booking = '$data->date_booking',
                user         = '$data->user'
            WHERE b.id = '$data->id'", 'ERROR');
        }
        public static function getIDNewReserva(){
            $result = query("SELECT id + 1 AS id FROM `booking`  ORDER BY id DESC LIMIT 1");

            return $result->id;
        }
        public static function add($data){
            $data->id = self::getIDNewReserva();
            query("INSERT INTO booking(
                id_customer,
                id_field,
                day_booking,
                time_booking,
                date_booking,
                user,
                status
                ) VALUES (
                    '$data->id_customer',
                    '$data->id_field',
                    '$data->day_booking',
                    '$data->time_booking',
                    '$data->date_booking',
                    '$data->user',
                    '1'
                )"
            );
            JSON($data);
        }
        public static function reagendar($data) {
            query("UPDATE booking SET
                day_booking  = '$data->day_booking',
                time_booking = '$data->time_booking',
                date_booking = '$data->date_booking',
                user         = '$data->user',
                status       = '6'
            WHERE id = '$data->id'");
            JSON(self::getById($data->id));
        }
        public static function cancel($data){
            query("UPDATE booking SET
                user = $data->user,
                status       = '2'
            WHERE id = '$data->id'");
            JSON(self::getById($data->id));
        }
        public static function getBookingByAPI($id){
            $booking = query("SELECT
                    b.id,
                    t.data_id,
                    s.tax_id,
                    (SELECT HOUR FROM schedules WHERE id = b.time_booking) AS time,
                    b.date_booking AS date,
                    s.full_name AS cancha,
                    c.phone AS customer_phone,
                    t.total
                FROM
                    booking AS b
                INNER JOIN transacciones AS t
                INNER JOIN mobbex_ops AS m
                ON
                t.data_id = m.checkout_uid AND t.id_booking = b.id
                INNER JOIN soccer_field AS s
                ON
                    s.id = b.id_field
                INNER JOIN customers AS c
                ON
                    c.id = b.id_customer
                WHERE b.id = '$id'");

            JSON($booking);
        }
    }
