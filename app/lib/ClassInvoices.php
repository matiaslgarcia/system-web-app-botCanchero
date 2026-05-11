<?php

    class Invoices{

        public static function add($data){
            $data->fechaInvoice = date('Y-m-d');
            $bookingPrice = Booking::getValorCanchaByBooking($data->id);
            $data->total = (float) ($bookingPrice->precio_cancha ?? Canchas::getById($data->id_cancha)->price_hour ?? 0);
            query("INSERT INTO invoices(
                id_booking, date, id_status, total
                ) VALUES (?, ?, '3', ?)", '', [$data->id, $data->fechaInvoice, $data->total]);   
        }

        public static function getStatusAll(){
            $status = query("SELECT * FROM invoices_status", 'ALL');

            return $status;
        }

        public static function getIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = (isset($_GET['cancha'])) ? $_GET['cancha'] : '%'; 
            $invoices = query(
                "SELECT
                    b.id AS nroReserva,
                    DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) AS date,
                    b.id_field,
                    bs.name AS status,
                    bs.color AS color,
                    CASE
                        WHEN COALESCE(pw.cash_amount, 0) > 0 AND COALESCE(v.method_name, '') <> '' THEN 'mixto'
                        WHEN COALESCE(pw.cash_amount, 0) > 0 THEN 'efectivo'
                        WHEN COALESCE(v.method_name, '') <> '' THEN v.method_name
                        ELSE 'online'
                    END AS paymet_method,
                    ROUND(ABS(COALESCE(b.paid_amount, 0)), 2) AS total,
                    ROUND(
                        CASE
                            WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                            ELSE ABS(COALESCE(b.paid_amount, 0))
                        END,
                        2
                    ) AS signed_total,
                    CASE
                        WHEN b.payment_status = 'refunded' THEN 'refunded'
                        WHEN COALESCE(b.paid_amount, 0) > 0 THEN 'approved'
                        ELSE 'pending'
                    END AS estado
                FROM booking b
                INNER JOIN booking_status bs ON bs.id = b.status
                LEFT JOIN (
                    SELECT
                        id_booking,
                        MAX(date_create) AS last_voucher_date,
                        SUBSTRING_INDEX(GROUP_CONCAT(method_name ORDER BY date_create DESC), ',', 1) AS method_name
                    FROM vouchers
                    GROUP BY id_booking
                ) v ON v.id_booking = b.id
                LEFT JOIN (
                    SELECT
                        id_booking,
                        SUM(amount_payment) AS cash_amount
                    FROM payment_app_web
                    GROUP BY id_booking
                ) pw ON pw.id_booking = b.id
                WHERE DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) = ?
                  AND b.id_field LIKE ?
                  AND (
                    COALESCE(b.paid_amount, 0) > 0
                    OR b.payment_status = 'refunded'
                  )
                ORDER BY b.id DESC",
                'ALL',
                [$date, $cancha]
            );
            return $invoices;
        }
        public static function getMiIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = Users::infoUser('id_field');
            $invoices = query(
                "SELECT
                    b.id AS nroReserva,
                    DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) AS date,
                    b.id_field,
                    bs.name AS status,
                    bs.color AS color,
                    CASE
                        WHEN COALESCE(pw.cash_amount, 0) > 0 AND COALESCE(v.method_name, '') <> '' THEN 'mixto'
                        WHEN COALESCE(pw.cash_amount, 0) > 0 THEN 'efectivo'
                        WHEN COALESCE(v.method_name, '') <> '' THEN v.method_name
                        ELSE 'online'
                    END AS paymet_method,
                    ROUND(ABS(COALESCE(b.paid_amount, 0)), 2) AS total,
                    ROUND(
                        CASE
                            WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                            ELSE ABS(COALESCE(b.paid_amount, 0))
                        END,
                        2
                    ) AS signed_total,
                    CASE
                        WHEN b.payment_status = 'refunded' THEN 'refunded'
                        WHEN COALESCE(b.paid_amount, 0) > 0 THEN 'approved'
                        ELSE 'pending'
                    END AS estado
                FROM booking b
                INNER JOIN booking_status bs ON bs.id = b.status
                LEFT JOIN (
                    SELECT
                        id_booking,
                        MAX(date_create) AS last_voucher_date,
                        SUBSTRING_INDEX(GROUP_CONCAT(method_name ORDER BY date_create DESC), ',', 1) AS method_name
                    FROM vouchers
                    GROUP BY id_booking
                ) v ON v.id_booking = b.id
                LEFT JOIN (
                    SELECT
                        id_booking,
                        SUM(amount_payment) AS cash_amount
                    FROM payment_app_web
                    GROUP BY id_booking
                ) pw ON pw.id_booking = b.id
                WHERE DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) = ?
                  AND b.id_field = ?
                  AND (
                    COALESCE(b.paid_amount, 0) > 0
                    OR b.payment_status = 'refunded'
                  )
                ORDER BY b.id DESC",
                'ALL',
                [$date, $cancha]
            );
                return $invoices;
        }
        public static function getTotalMiIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = Users::infoUser('id_field');
            
            $total = query(
                "SELECT ROUND(COALESCE(SUM(
                    CASE
                        WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                        ELSE COALESCE(b.paid_amount, 0)
                    END
                ), 0), 2) AS totalDia
                FROM booking b
                LEFT JOIN (
                    SELECT id_booking, MAX(date_create) AS last_voucher_date
                    FROM vouchers
                    GROUP BY id_booking
                ) v ON v.id_booking = b.id
                WHERE DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) = ?
                  AND b.id_field = ?
                  AND (
                    COALESCE(b.paid_amount, 0) > 0
                    OR b.payment_status = 'refunded'
                  )",
                'ARRAY',
                [$date, $cancha]
            );
            return (float) ($total['totalDia'] ?? 0);
        }
        public static function getExtraIngresos($date = null, $idField = null) {
            $date = $date ?: ((isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d'));
            if ($idField === null) {
                $idField = (isset($_GET['cancha']) && $_GET['cancha'] !== '%') ? (int) $_GET['cancha'] : null;
            }
            if ($idField) {
                return query("SELECT * FROM extra_income WHERE date_income = ? AND id_field = ? ORDER BY id DESC", 'ALL', [$date, $idField]);
            }
            return query("SELECT * FROM extra_income WHERE date_income = ? ORDER BY id DESC", 'ALL', [$date]);
        }

        public static function getTotalExtraIngresos($date = null, $idField = null) {
            $date = $date ?: ((isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d'));
            if ($idField === null) {
                $idField = (isset($_GET['cancha']) && $_GET['cancha'] !== '%') ? (int) $_GET['cancha'] : null;
            }
            if ($idField) {
                $row = query("SELECT COALESCE(SUM(amount), 0) AS total FROM extra_income WHERE date_income = ? AND id_field = ?", 'ARRAY', [$date, $idField]);
            } else {
                $row = query("SELECT COALESCE(SUM(amount), 0) AS total FROM extra_income WHERE date_income = ?", 'ARRAY', [$date]);
            }
            return (float) ($row['total'] ?? 0);
        }

        public static function getTotalIngresos(){
            $date   = (isset($_GET['date'])) ? setDate($_GET['date']) : date('Y-m-d');
            $cancha = (isset($_GET['cancha'])) ? $_GET['cancha'] : '%';
        
            $total = query(
                "SELECT ROUND(COALESCE(SUM(
                    CASE
                        WHEN b.payment_status = 'refunded' THEN -ABS(COALESCE(b.paid_amount, 0))
                        ELSE COALESCE(b.paid_amount, 0)
                    END
                ), 0), 2) AS totalDia
                FROM booking b
                LEFT JOIN (
                    SELECT id_booking, MAX(date_create) AS last_voucher_date
                    FROM vouchers
                    GROUP BY id_booking
                ) v ON v.id_booking = b.id
                WHERE DATE(COALESCE(b.paid_in_cash_at, v.last_voucher_date, b.date_booking)) = ?
                  AND b.id_field LIKE ?
                  AND (
                    COALESCE(b.paid_amount, 0) > 0
                    OR b.payment_status = 'refunded'
                  )",
                'ARRAY',
                [$date, $cancha]
            );
            return (float) ($total['totalDia'] ?? 0);
        }
    }
