<?php

    

    Class MercadoPago{
        const client_id = "4739156168928171";
        const url_auth  = "https://auth.mercadopago.com/authorization";
        const uri_auth  = "https://saas.botcanchero.com/mercadopago_OAuth";
        public static function getUrlOAuth(){
            $state = date('ymdhis') . rand(100000, 999999);
            $url = self::url_auth ."?client_id=" . self::client_id . "&response_type=code&platform_id=mp&state=".$state."&redirect_uri=" . self::uri_auth;

            return $url;
        }
    }