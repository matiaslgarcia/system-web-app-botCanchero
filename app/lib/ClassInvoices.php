<?php

    class Invoices{

        public static function add($data){
            $data->fechaInvoice = date('Y-m-d');
            $data->total = Canchas::getById($data->id_cancha)->price_hour;
            query("INSERT INTO invoices(
                id_booking, date, id_status, total
                ) VALUES (
                    '$data->id',
                    '$data->fechaInvoice',
                    '3',
                    '$data->total')");   
        }

        public static function getStatusAll(){
            $status = query("SELECT * FROM invoices_status", 'ALL');

            return $status;
        }

        public static function getIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = (isset($_GET['cancha'])) ? $_GET['cancha'] : '%'; 
            $invoices = query("SELECT
                    v.date_create as date,
                    b.id_field,
                    bs.name as status,
                    bs.color as color,
                    v.method_name as paymet_method,
                    p.transaction_amount as total
                from
                booking b
                inner join vouchers v on
                    b.id = v.id_booking
                inner join booking_status bs on
                    bs.id = b.status
                inner join payment p on
                    p.payment_id = v.data_id 
                    where v.date_create like '$date%' and b.id_field  LIKE '$cancha'", 'ALL');
            return $invoices;
        }
        public static function getMiIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = Users::infoUser('id_field');
            $invoices = query("SELECT
                    v.date_create as date,
                    b.id_field,
                    bs.name as status,
                    bs.color as color,
                    v.method_name as paymet_method,
                    p.transaction_amount as total
                from
                    booking b
                inner join vouchers v on
                    b.id = v.id_booking
                inner join booking_status bs on
                    bs.id = b.status
                inner join payment p on
                    p.payment_id = v.data_id 
                where v.date_create like '$date%' and b.id_field  LIKE '$cancha'", 'ALL');
            return $invoices;
        }
        public static function getTotalMiIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = Users::infoUser('id_field');
            
            $condition = "DATE(v.date_create) = '$date'";

            $total = query("SELECT
                        Round(sum(CASE WHEN bs.name = 'Cancelado' THEN -p.transaction_amount ELSE p.transaction_amount END),2 ) as totalDia
                    FROM
                        booking b
                    INNER JOIN vouchers v ON
                        b.id = v.id_booking
                    INNER JOIN booking_status bs ON
                        bs.id = b.status
                    INNER JOIN payment p ON
                        p.payment_id = v.data_id 
                    WHERE
                        $condition;
                   ",
            'ALL');
            return $total;
        }
        public static function getTotalIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = (isset($_GET['cancha'])) ? $_GET['cancha'] : '%';
        
            $condition = "DATE(v.date_create) = '$date'";
            if ($cancha !== '%') {
                $condition .= " AND b.id_field = $cancha";
            }
        
            $total = query("SELECT
                        Round(sum(CASE WHEN bs.name = 'Cancelado' THEN -p.transaction_amount ELSE p.transaction_amount END),2 ) as totalDia
                    FROM
                        booking b
                    INNER JOIN vouchers v ON
                        b.id = v.id_booking
                    INNER JOIN booking_status bs ON
                        bs.id = b.status
                    INNER JOIN payment p ON
                        p.payment_id = v.data_id 
                    WHERE
                        $condition;
                       ",
            'ALL');
        
            return $total;
        }
    }