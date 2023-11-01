<?php

    class Auth{

        public static function Verific(){
            $getallheaders = getallheaders();

            self::checkedAut($getallheaders);
        }

        private static function failAuth(){
            Api::ApiError([
                "error" => "Unauthorized",
                "message" => "Unauthorized access. Invalid or missing authentication token."
            ], 403);
        }

        private static function checkedAut($getallheaders){

            if(!empty($getallheaders['Authorization'])){
                $Auth = explode(' ', $getallheaders['Authorization']);
                if(!$Auth[0] == 'Bearer' or !self::checkedToken($Auth[1]))
                    self::failAuth();
            }else{
                self::failAuth();
            }
        }
        private static function checkedToken($token){
            $result = query("SELECT * FROM api_token WHERE token = '$token'");

            if($result)
                return true;
        }
    }