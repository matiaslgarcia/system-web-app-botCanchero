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
                    t.data_id AS data_id,
                    t.total,
                    f.full_name AS cancha,
                    m.payment_number AS card,
                    m.payment_reference AS card_type,
                    m.created AS date,
                    m.payment_status_code AS status
                FROM
                    transacciones AS t
                INNER JOIN booking AS b
                ON
                    t.id_booking = b.id
                INNER JOIN mobbex_ops AS m
                ON
                    m.checkout_uid = t.data_id
                INNER JOIN soccer_field AS f
                ON
                f.id = b.id_field
                WHERE b.id_field LIKE '$cancha' AND m.created LIKE '$date%';", 'ALL');
            return $invoices;
        }
        public static function getMiIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = Users::infoUser('id_field');
            $invoices = query("SELECT
                    t.data_id AS data_id,
                    t.total,
                    f.full_name AS cancha,
                    m.payment_number AS card,
                    m.payment_reference AS card_type,
                    m.created AS date,
                    m.payment_status_code AS status
                FROM
                    transacciones AS t
                INNER JOIN booking AS b
                ON
                    t.id_booking = b.id
                INNER JOIN mobbex_ops AS m
                ON
                    m.checkout_uid = t.data_id
                INNER JOIN soccer_field AS f
                ON
                f.id = b.id_field
                WHERE b.id_field LIKE '$cancha' AND m.created LIKE '$date%';", 'ALL');
            return $invoices;
            return $invoices;
        }

    }