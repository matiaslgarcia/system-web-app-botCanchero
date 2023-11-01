<?php

    class Payment{
        public static function created(){
           $data = Api::getData();
           $data->date_created = date("Y-m-d H:i:s");
           query("INSERT INTO vouchers (
            data_id, id_notification, id_canchero, method_name,  date_create
            ) VALUES (
            '$data->data_id', '$data->id_notification', '$data->id_canchero', '$data->method_name', '$data->date_created')");

            JSON(self::getVaucherByDataID($data->data_id));
        }

        private static function  getVaucherByDataID($data_id){
            $vaucher = query("SELECT * FROM `vouchers` WHERE data_id = '$data_id' ORDER BY id DESC LIMIT 1");
            
            return $vaucher;
        }
    }