<?php

    class Customers{
        // Canonical E.164-ish format for Argentine WhatsApp numbers, so the
        // same real phone always maps to the same stored string regardless of
        // whether it came in with a leading "+", the "54" country code, the
        // "9" mobile marker, a trunk "0", spaces or dashes. Every path that
        // writes or looks up customers.phone must go through this first —
        // otherwise the same person can get re-created as a "new" customer
        // just because the digits arrived shaped differently.
        public static function normalizePhone($raw){
            $digits = preg_replace('/\D+/', '', (string) $raw);
            if ($digits === '') return '';
            if (strpos($digits, '54') === 0 && strlen($digits) > 10) {
                $digits = substr($digits, 2);
            }
            if (strpos($digits, '0') === 0 && strlen($digits) > 10) {
                $digits = substr($digits, 1);
            }
            if (strpos($digits, '9') === 0 && strlen($digits) === 11) {
                $digits = substr($digits, 1);
            }
            return '+549' . $digits;
        }
        public static function checkExitCustomerOCreate($data){
            $data->phone = self::normalizePhone($data->phone);
            $result = self::getByNumeroTelefono($data->phone);

            if($result){
                self::update($data, $result->id);
                $customer = $result;
            }else{
                $customer = self::add($data);
            }

            return $customer;
        }
        private static function update($data, $id){
            $email = $data->email ?? '';
            $phone = self::normalizePhone($data->phone);
            query("UPDATE customers SET full_name = ?, phone = ?, email = ? WHERE id = ?", '', [$data->full_name, $phone, $email, $id]);
        }
        public static function getByNumeroTelefono($phone){
            $phone = self::normalizePhone($phone);
            $customer = query("SELECT * FROM customers WHERE phone = ? LIMIT 1", '', [$phone]);

            return $customer;
        }
        public static function add($data){
            $data->id = self::getIDNewCustomer();
            $email = $data->email ?? '';
            $phone = self::normalizePhone($data->phone);
            query("INSERT INTO customers(
                    id, full_name, phone, email
                ) VALUES (
                    ?, ?, ?, ?)", '', [$data->id, $data->full_name, $phone, $email]);
            return self::getCustomerById($data->id);
        }

        /** Métodos para API v2 **/
        public static function get(){
            $phone = $_GET['phone'] ?? null;
            if (!$phone) {
                Api::ApiError(['error' => 'phone is required'], 400);
            }
            $customer = self::getByNumeroTelefono($phone);
            if ($customer) {
                JSON($customer);
            } else {
                JSON([], 404);
            }
        }

        public static function register(){
            $data = Api::getData();
            if (!isset($data->name) || !isset($data->phone)) {
                Api::ApiError(['error' => 'name and phone are required'], 400);
            }

            $phone = self::normalizePhone($data->phone);
            $email = $data->email ?? '';

            $newCustomer = self::add((object) [
                'full_name' => $data->name,
                'phone'     => $phone,
                'email'     => $email
            ]);
            
            JSON($newCustomer);
        }

        public static function getCustomerById($id){
            $customer = query("SELECT * FROM customers WHERE id = ?", '', [$id]);
            return $customer;
        }

        private static function getIDNewCustomer(){
            $result = query("SELECT id + 1 AS id FROM customers ORDER BY id DESC LIMIT 1");
            return ($result && isset($result->id)) ? $result->id : 1;
        }

        /**
         * GET /api/v2/?action=customers_listAll
         * Devuelve customers DISTINCT que tienen al menos una reserva en el
         * establishment del token bound. Usado por el cron de notificaciones.
         */
        public static function listAll() {
            $boundEst = class_exists('Auth') ? Auth::getEstablishmentId() : null;
            $params = [];
            $estFilter = '';
            if ($boundEst) {
                $estFilter = ' AND sf.establishment_id = :est ';
                $params[':est'] = (int) $boundEst;
            }

            $rows = query(
                "SELECT DISTINCT c.id, c.full_name, c.phone, c.email
                   FROM customers c
                   INNER JOIN booking b ON b.id_customer = c.id
                   INNER JOIN soccer_field sf ON sf.id = b.id_field
                  WHERE 1=1
                  $estFilter
                  ORDER BY c.id DESC",
                'ARRAY_ALL',
                $params
            );
            JSON($rows ?: []);
        }
    }