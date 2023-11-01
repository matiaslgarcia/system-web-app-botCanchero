<?php


    class Api{
        public static function getDataRequeste(){
            $action = $_GET['action'];
            $action = explode('_', $action);

            if(count($action) < 2){
                self::ActionInvalid();
            }else{
                return (OBJECT) array('action' => $action[0], 'method' => $action[1]);
            }
        }

        public static function checkdateAction(){
            if(empty($_GET['action'])){
                self::ActionInvalid();
            }else{
                self::ActionList();
            }
        }

        public static function ApiError($arr, $code){
            JSON($arr, $code);
            die();
        }

        private static function ActionList(){
            $action = array(
                'payment_created',
                'payment_getVaucher'
            );
            if(!in_array($_GET['action'], $action)){
                self::ActionInvalid();
            }
        }

        private static function ActionInvalid(){
            self::ApiError([
                "error" => "Invalid Action",
                "message" => "invalid or missing action parameter."
            ], 400);
        }

        public static function getData(){
            $json = file_get_contents("php://input");
            $obj = json_decode($json);
            if ($obj === null && json_last_error() !== JSON_ERROR_NONE) {
                self::ApiError([
                    "error" => "Invalid Action",
                    "message" => "invalid or missing data JSON."
                ], 400);
            }
            return $obj;
        }
    }