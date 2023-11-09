<?php

    
    class Canchas{
        public static function getall(){
            $canchas = query("SELECT
            sf.id,
            sf.full_name AS name,
            sf.phone,
            sf.latitude,
            sf.length,
            sf.logo,
            sf.price_hour,
            sf.tax_id,
            sf.token_mercadopago,
            p.name as province,
            p.id as id_province,
            c.name as city,
            c.id as id_city
        FROM
            soccer_field sf
            INNER JOIN province p ON p.id = sf.id_province
            INNER JOIN city c on c.id = sf.id_city
        WHERE
            sf.status = 1", 'ALL');

            foreach($canchas AS $cancha){
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo; 
            }

            JSON($canchas);
        }
        public static function getByHora(){
            $data = Api::getData();
            $data->day = dayName($data->fecha);
            $canchas = query("SELECT
                    sf.id,
                    sf.full_name as name,
                    sf.phone as phone,
                    sf.latitude,
                    sf.length,
                    sf.logo,
                    sf.price_hour,
                    sd.name as day,
                    sf.token_mercadopago,
                    h.id_schedule as id_hora,
                    (select hour12 from schedules where id = h.id_schedule) as hora12,
                    sf.threshold
                from soccer_field sf
                inner join schedules_field h on
                    sf.id = h.id_field
                inner join schedules_day sd on
                    h.id_day = sd.id
                where
                    h.id_schedule = '$data->hora'
                    and h.id_day = '$data->day'
                    and (select count(*)  from booking b where b.id_field = h.id_field and b.date_booking = '$data->fecha' and b.time_booking = h.id_schedule  ) < sf.threshold
            ", 'ALL');
            JSON($canchas);
        }
    }