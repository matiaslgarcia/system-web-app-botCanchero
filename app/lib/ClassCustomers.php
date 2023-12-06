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
            query("UPDATE customers SET full_name = '$data->full_name', phone ='$data->phone', email ='$data->email' WHERE id = '$id'");
        }
        public static function getByNumeroTelefono($phone){
            $customer = query("SELECT * FROM customers WHERE phone = '$phone' LIMIT 1");

            return $customer;
        }
        public static function add($data){
            $data->id = self::getIDNewCustomer();
            query("INSERT INTO customers(
                    id, full_name, phone, email
                ) VALUES (
                    '$data->id', '$data->full_name', '$data->phone', '$data->email')");
            return self::getCustomerById($data->id);
        }
        public static function getCustomerById($id){
            $customer = query("SELECT * FROM customers WHERE id = '$id'");
            return $customer;
        }
        private static function getIDNewCustomer(){
            $result = query("SELECT id + 1 AS id  FROM customers ORDER BY id DESC LIMIT 1");

            return $result->id;
        }
    }