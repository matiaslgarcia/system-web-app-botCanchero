<?php

    

    Class MercadoPago{

        const client_id     = "4739156168928171";
        const client_secret = "co1OrmarXoTTfgyR4YxyGHhed819ELXG";
        const url_auth      = "https://auth.mercadopago.com/authorization";
        const uri_auth      = "https://saas.botcanchero.com/mercadopago_OAuth";
        const url_token     = "https://api.mercadopago.com/oauth/token";
        public static function getUrlOAuth(){
            $state = date('ymdhis') . rand(100000, 999999);
            $url = self::url_auth ."?client_id=" . self::client_id . "&response_type=code&platform_id=mp&state=".$state."&redirect_uri=" . self::uri_auth;

            return $url;
        }
        public static function createRefreshToken($code){
            $data = array(
                'client_secret' => self::client_secret,
                'client_id'     => self::client_id,
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => self::uri_auth
            );
            $ch = curl_init(self::url_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
        }
    }