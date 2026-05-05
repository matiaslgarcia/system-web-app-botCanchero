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
                // Canchas
                'canchas_getAll',
                'canchas_getByHora',

                // Booking
                'booking_add',
                'booking_cancel',
                'booking_move',
                'booking_confirm',
                'booking_checkAvailability',
                'booking_getBalance',
                'booking_listAvailable',
                'booking_listByPhone',

                // Customers (API v2 - Migrados de v1)
                'customers_get',
                'customers_register',
                'customers_listAll',

                // Schedules
                'schedules_getFreeByField',

                // Payment
                'payment_created',
                'payment_setPreferencia',
                'payment_get',
                'payment_getCredentials',
                'payment_getVaucher',
                'payment_result',
                'payment_registerCash',

                // Services
                'services_getAllServicesCancha',

                // Recurring (Fase 2)
                'recurring_addBooking',
                'recurring_listBookings',
                'recurring_moveBooking',
                'recurring_cancelBooking',
                'recurring_pauseRequest',
                'recurring_pauseReview',
                'recurring_listPauses',
                'recurring_generateWeek',
                'recurring_updatePayment',

                // Calendario
                'calendar_view',
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
