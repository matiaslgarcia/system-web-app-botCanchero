<?php

    class Paymet{
        public static function getMethodPayment(){
            $methodPayment = query("SELECT * FROM payment_method", 'ALL');
            
            foreach($methodPayment AS $method){
                $method->tag = str_replace(' ', '_', $method->name);
            }
            return $methodPayment;
        }
    }