<?php

    
    class Canchas{
        public static function getall(){
            $canchas = query("SELECT id, full_name AS name, phone, latitude, length, logo, price_hour, tax_id FROM soccer_field WHERE status = 1", 'ALL');

            foreach($canchas AS $cancha){
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo; 
            }
            

            JSON($canchas);
        }
    }