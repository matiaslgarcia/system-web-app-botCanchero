<?php

    class Customers{
        public static function checkExitCustomerOCreate($data){
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
            query("UPDATE customers SET full_name = ?, phone = ?, email = ? WHERE id = ?", '', [$data->full_name, $data->phone, $data->email, $id]);
        }
        public static function getByNumeroTelefono($phone){
            $customer = query("SELECT * FROM customers WHERE phone = ? LIMIT 1", '', [$phone]);

            return $customer;
        }
        public static function add($data){
            $data->id = self::getIDNewCustomer();
            query("INSERT INTO customers(
                    id, full_name, phone, email
                ) VALUES (
                    ?, ?, ?, ?)", '', [$data->id, $data->full_name, $data->phone, $data->email]);
            return self::getCustomerById($data->id);
        }

        /** Métodos para API v2 **/
        public static function get(){
            $phone = $_GET['phone'] ?? null;
            if (!$phone) {
                Api::ApiError(['error' => 'phone is required'], 400);
            }
            $phone = str_replace(['+','-',' '], '', $phone);
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
            
            $phone = str_replace(['+','-',' '], '', $data->phone);
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