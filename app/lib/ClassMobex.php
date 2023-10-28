<?php

    class Mobex{
        public static function updateCheckOut($data_id, $id_booking){
            query("UPDATE transacciones SET id_booking = '$id_booking' WHERE data_id = '$data_id'");
        }
    }