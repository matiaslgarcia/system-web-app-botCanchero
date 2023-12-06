<?php

    class Booking{
        private static function set($data){
            query("INSERT INTO booking(
                id,
                id_customer,
                id_field,
                day_booking,
                time_booking,
                date_booking,
                user,
                status
                ) VALUES (
                    '$data->id',
                    '$data->UsuarioPlayerId',
                    '$data->CanchaAsignada',
                    '$data->DiaReserva',
                    '$data->HoraReserva',
                    '$data->FechaReserva',
                    '$data->UsuarioModificacion',
                    '1'
                )"
            );
        }
        private static function getIDNewReserva(){
            $result = query("SELECT id + 1 AS id FROM `booking`  ORDER BY id DESC LIMIT 1");

            return $result->id;
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
        public static function add(){
            $data = Api::getData();

            $data->full_name            = $data->name;
            $data->email                = (isset($_POST['email'])) ? $_POST['email'] : '';
            $data->UsuarioPlayerId      = Customers::checkExitCustomerOCreate($data)->id;
            $data->CanchaAsignada       = $data->id_cancha;
            $data->UsuarioModificacion  = 1;
            $data->FechaReserva         = $data->fecha;
            $data->HoraReserva          = $data->hora;
            $data->DiaReserva           = dayName($data->fecha);
            $data->id                   = self::getIDNewReserva();

            self::set($data);
            Payment::updateCheckOut($data->data_id, $data->id);
            self::getBookingByAPI($data->id);
        }
    }