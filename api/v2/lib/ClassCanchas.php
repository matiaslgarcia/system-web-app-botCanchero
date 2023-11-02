<?php

    
    class Canchas{
        public static function getall(){
            $canchas = query("SELECT id, full_name AS name, phone, latitude, length, logo, price_hour, tax_id, token_mercadopago FROM soccer_field WHERE status = 1", 'ALL');

            foreach($canchas AS $cancha){
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo; 
            }

            JSON($canchas);
        }
        public static function getByHora(){
            $data = Api::getData();
            $canchas = query("SELECT
                    sf.id,
                    sf.full_name as name,
                    sf.phone as phone,
                    sf.latitude,
                    sf.length,
                    sf.logo,
                    sf.price_hour,
                    sf.token_mercadopago,
                    h.id_schedule as id_hora,
                    (select hour12 from schedules  where id = h.id_schedule) as hora12
                from soccer_field sf
                inner join schedules_field h on
                    sf.id = h.id_field
                where h.id_schedule = '$data->hora'"
            , 'all');
            JSON($canchas);
        }
    }