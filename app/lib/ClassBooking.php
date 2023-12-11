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
            s.id AS status_id,
            s.color AS status_color,
            f.price_hour AS precio_cancha,
            w.amount_payment as pagado
            FROM
                booking AS b
            INNER JOIN soccer_field AS f ON b.id_field = f.id
            INNER JOIN customers AS c ON b.id_customer = c.id
            INNER JOIN booking_status AS s ON b.status = s.id
            INNER JOIN schedules AS h ON h.id = b.time_booking
            LEFT JOIN payment_app_web as w ON w.id_booking = b.id
             WHERE b.id = '$id'");
            
            return $reserva;
        }
        public static function getValorCanchaByBooking($id){
            $valor = query("SELECT
                                f.price_hour AS precio_cancha
                            FROM
                                booking AS b
                            INNER JOIN soccer_field AS f ON b.id_field = f.id
                            WHERE b.id = '$id'");
            return $valor;
        }
        public static function getTotalById($id){
            $total = query("SELECT
            p.transaction_amount as cant
            FROM
                booking AS b
            INNER JOIN vouchers AS v ON b.id = v.id_booking
            INNER JOIN payment AS p ON p.payment_id = v.data_id
            WHERE b.id = '$id'");
            
            return $total;
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
        public static function getUsuario($id){
            $usuario = query("SELECT 
            u.id as user
            FROM `users` as u inner join booking as b on b.user = u.id
            where u.id = 1 and b.id = '$id'");

           return $usuario;
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
	                v.data_id,
	                sf.token_mercadopago,
	                (select hour from schedules where id = b.time_booking) as time,
	                b.date_booking as date,
	                sf.full_name as cancha,
	                c.phone as customer_phone,
	                p.transaction_amount as total
                from booking as b
                inner join vouchers v on
	                v.id_booking = b.id
                inner join payment p on
	                p.payment_id = v.data_id
                inner join soccer_field sf on
	                sf.id  = b.id_field
                inner join customers c on
	                c.id = b.id_customer 
                where b.id = '$id'"
            );
            JSON($booking);
        }
        public static function cerrarPago($data){
            query("INSERT INTO payment_app_web (
                        payment_date, 
                        amount_payment, 
                        method_payment, 
                        id_booking
                    ) VALUES (
                        CURRENT_DATE(), 
                        '$data->cantidad_a_pagar', 
                        '$data->metodo_pago', 
                        '$data->id_reserva'
                    )"
                );
            JSON($data);
        }
    }
