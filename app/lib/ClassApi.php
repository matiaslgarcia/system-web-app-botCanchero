<?php

    class Api{
        public static function start(){
            self::Auth();
            self::Action();
        }
        private static function Action(){
            if(isset($_POST['action'])){
                if(in_array($_POST['action'], self::listAction())){
                    $action = $_POST['action'];
                    self::$action(obj($_POST));
                }else{
                    self::requestFail('accion '. $_POST['action'].' no valida', '0003', 400);
                }
            }else{
                self::requestFail('accion no valida', '0003', 400);
            }
        }
        private static function listAction(){
            $actions = array(
                'get_canchas'       => true,
                'set_reserva'       => true,
                'get_provincias'    => true,
                'get_reservas'      => true,
                'add_customer'      => true,
                'get_provincias'    => true,
                'save_mobbex'       => true,
                'set_checkout'      => true,
                'get_end_operation' => true,
            );

            return $actions;
        }
        private static function Auth(){
            if(isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])){
                $email = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
                $user = query("SELECT * FROM users WHERE email = '$email' AND status = 1 AND rol = 'botCanchero' OR rol = 'superAdmin'");
                if($user){
                    if(!password_verify($password, $user->password)){
                        self::AuthFail();
                    }
                }else{
                    self::AuthFail();
                }
            }else{
                
                self::AuthFail();
            }
        }

        private static function AuthFail(){
            self::requestFail('Autenticación fallida', '0001', 403);
        }
        private static function requestFail($msg, $code, $status = 500){
            JSON(['message' => $msg, 'code' => $code, 'data' => $_POST], $status);
        }

        private static function get_canchas(){
            $canchas = canchas::getAll();

            foreach($canchas AS $cancha){
                $cancha->logo = URL . $cancha->logo;
            }

            JSON($canchas);
        }
        private static function set_reserva(){
            self::validateParam(['phone', 'id_cancha', 'fecha', 'hora', 'name']);
            $_POST['full_name']            = $_POST['name'];
            $_POST['email']                = (isset($_POST['email'])) ? $_POST['email'] : '';
            $_POST['UsuarioPlayerId']      = Customers::checkExitCustomerOCreate(obj($_POST))->id;
            $_POST['CanchaAsignada']       = $_POST['id_cancha'];
            $_POST['UsuarioModificacion']  = 1;
            $_POST['FechaReserva']         = $_POST['fecha'];
            $_POST['HoraReserva']          = $_POST['hora'];
            $_POST['DiaReserva']           = dayName($_POST['fecha']);
            
            self::addReserva(obj($_POST));
        }
        private static function addReserva($data){
            
            $data->id = Booking::getIDNewReserva();
            query("INSERT INTO booking(
                id,
                id_customer ,
                id_field,
                day_booking,
                time_booking,
                date_booking,
                user,
                status
                ) VALUES (
                    '$data->id',
                    '$data->UsuarioPlayerId',
                    '$data->CanchaAsignada',
                    '$data->DiaReserva',
                    '$data->HoraReserva',
                    '$data->FechaReserva',
                    '$data->UsuarioModificacion',
                    '1'
                )"
            );
            Mobex::updateCheckOut($data->data_id, $data->id);
            Booking::getBookingByAPI($data->id);
        }
        private static function get_horas($data){

            self::validateParam(['fecha', 'id_cancha']);
            $_POST['id_day'] = dayName($_POST['fecha']);

            $horas = Schedules::getByFieldLibre($_POST['id_cancha'], $_POST['id_day'], $_POST['fecha']);
            JSON($horas);
        }

        private static function validateParam($params = array()){
            foreach($params as $param){
                if(!isset($_POST[$param]) or empty($_POST[$param])){
                    self::requestFail('parametros no validos', '0004', 400);
                }
            }
        }

        private static function get_customer($data){
            self::validateParam(['phone']);
            $data->phone = str_replace(['+','-'],'',$data->phone);
            $jugador = query("SELECT * FROM customers WHERE phone = '$data->phone'");
            if($jugador){
                JSON($jugador);
            }else{
                JSON([], 403);
            }
        }

        private static function add_customer($data){
            self::validateParam(['name', 'phone']);
            $data['email'] = (isset($data['email']) ? $data['email'] : '');
            Customers::add( (object) array(
                'full_name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email']
            ));
            
            JSON(Customers::getByNumeroTelefono($data['phone']));
        }

        private static function get_provincias(){
            $provincias = Address::getProvincias();

            JSON($provincias);
        }
        private static function get_provincia($data){
            $provincia = Address::getProvincia($data->id_provincia);

            JSON($provincia);
        }
        private static function get_booking($data){
            self::validateParam(['phone']);
            $booking = query("SELECT
                    b.id,
                    d.name AS day,
                    s.hour12 AS time,
                    f.full_name AS cancha,
                    f.latitude AS latitud,
                    f.length AS longitud,
                    f.phone,
                    b.date_booking AS fecha,
                    status.name AS status,
                    v.data_id AS paymentId,
                    f.token_mercadopago AS access_token,
                    (SELECT p.transaction_amount 
                    FROM payment p 
                    INNER JOIN vouchers v2 ON p.payment_id = v2.data_id
                    WHERE v2.data_id = v.data_id
                    LIMIT 1) AS transaction_amount
                FROM booking AS b 
                    INNER JOIN customers AS c 
                        ON b.id_customer = c.id
                    INNER JOIN soccer_field AS f 
                        ON f.id = b.id_field
                    INNER JOIN schedules_day AS d
                        ON d.id = b.day_booking
                    INNER JOIN schedules AS s 
                        ON s.id = b.time_booking
                    INNER JOIN booking_status AS status
                        ON b.status = status.id
                    INNER JOIN vouchers AS v 
                        ON v.id_booking = b.id
                WHERE c.phone = '$data->phone'"
            , 'ALL');
            foreach($booking AS $b){
                $b->fecha = showDate($b->fecha);
            }
            JSON($booking);
        }
        private static function cancel_booking(){
            $_POST['user'] = 1;
            $_POST['id']   = $_POST['id_booking'];
            Booking::cancel(obj($_POST));
        }

        private static function save_mobbex($data){

            query("INSERT INTO mobbex_ops(
                    checkout_currency,
                    checkout_total,
                    checkout_uid, 
                    childs_entity_name, 
                    childs_entity_uid, 
                    payment_id,
                    payment_source_cardholder_identification,
                    payment_source_cardholder_name,
                    payment_expiration_month,
                    payment_expiration_year,
                    payment_installment_amount,
                    payment_installment_description,
                    payment_installment_reference,
                    payment_name,
                    payment_number,
                    payment_reference,
                    payment_transaction_authorizationCode,
                    paymenttransaction_batchNo,
                    paymenttransaction_resultCode,
                    paymenttransaction_retrievalReferenceNo,
                    paymenttransaction_ticketNo,
                    paymenttransaction_transactionId,
                    payment_status_code,
                    payment_status_message,
                    payment_status_resultCode,
                    payment_status_text,
                    payment_status_view,
                    payment_total,
                    customer_email,
                    customer_identification,
                    customer_name,
                    entity_name,
                    entity_uid,
                    created,
                    description,
                    created_id
                    )VALUES (
                    '$data->checkout_currency',
                    '$data->checkout_total',
                    '$data->checkout_uid', '$data->childs_entity_name', '$data->childs_entity_uid', '$data->payment_id', 
                    '$data->payment_source_cardholder_identification', '$data->payment_source_cardholder_name', '$data->payment_expiration_month', '$data->payment_expiration_year',
                    '$data->payment_installment_amount', '$data->payment_installment_description', '$data->payment_installment_reference', '$data->payment_name', '$data->payment_number', '$data->payment_reference',
                    '$data->payment_transaction_authorizationCode', '$data->paymenttransaction_batchNo', '$data->paymenttransaction_resultCode', '$data->paymenttransaction_retrievalReferenceNo',
                    '$data->paymenttransaction_ticketNo', '$data->paymenttransaction_transactionId', '$data->payment_status_code', '$data->payment_status_message', '$data->payment_status_resultCode',
                    '$data->payment_status_text', '$data->payment_status_view', '$data->payment_total', '$data->customer_email', '$data->customer_identification', '$data->customer_name', '$data->entity_name',
                    '$data->entity_uid', '$data->created', '$data->description', '$data->created_id')
            ");
        }
        private static function set_checkout($data){
            $data->date_created = date('Y-m-d H:i:s');
            query("INSERT INTO transacciones (
                data_id, url, descripcion, currency, total, timeout, created, split_entityType, split_tax_id, split_uid, split_reference, split_hold, split_description, split_percentage, split_total, split_fee, split_refundFee, date_create
                )VALUES (
                '$data->data_id', '$data->url', '$data->descripcion', '$data->currency', '$data->total', '$data->timeout', '$data->created',
                '$data->split_entityType', '$data->split_tax_id', '$data->split_uid', '$data->split_reference', '$data->split_hold', '$data->split_description',
                '$data->split_percentage', '$data->split_total', '$data->split_fee', '$data->split_refundFee', '$data->date_created')"
            );
        }
        private static function get_end_operation($data){
            $result = query("SELECT
                    t.data_id AS id,
                    m.payment_status_code AS payment_status
                FROM
                    transacciones AS t
                LEFT JOIN mobbex_ops AS m
                ON
                    m.checkout_uid = t.data_id
                WHERE
                t.data_id = '$data->id'
            ");

            JSON($result);
        }
    }