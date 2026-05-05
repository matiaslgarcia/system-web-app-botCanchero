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
            $authHeader = $getallheaders['Authorization'] ?? $getallheaders['authorization'] ?? null;
            if (empty($authHeader)) {
                self::failAuth();
            }
            $parts = explode(' ', $authHeader, 2);
            if (count($parts) !== 2 || $parts[0] !== 'Bearer' || empty($parts[1])) {
                self::failAuth();
            }
            if (!self::checkedToken($parts[1])) {
                self::failAuth();
            }
        }

        private static $bound_establishment_id = null;

        public static function getEstablishmentId(){
            return self::$bound_establishment_id;
        }

        private static function checkedToken($token){
            // 'ARRAY' devuelve un único row como array asociativo
            // (PDO::FETCH_ASSOC), no un array de rows. Acá no hay $result[0].
            $result = query(
                "SELECT establishment_id FROM api_token WHERE token = ? AND active = 1 LIMIT 1",
                'ARRAY',
                [$token]
            );
            if (!empty($result) && array_key_exists('establishment_id', $result)) {
                self::$bound_establishment_id = (int) $result['establishment_id'];
                return true;
            }
            return false;
        }
    }
