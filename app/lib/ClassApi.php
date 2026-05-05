<?php

    /**
     * API V1 (basic-auth) — legacy. La gran mayoría de endpoints migró a V2 (Bearer).
     * Esta versión:
     *  - Usa SOLO sentencias preparadas (PHP-1 parche masivo: cierra los SQLi del V1).
     *  - Elimina endpoints muertos (Mobbex, set_checkout, get_end_operation, save_mobbex).
     *  - Mantiene lo que el bot todavía usa por V1 mientras se completa la migración a V2.
     */
    class Api{
        public static function start(){
            self::Auth();
            self::Action();
        }

        private static function Action(){
            if (empty($_POST['action'])) {
                self::requestFail('accion no valida', '0003', 400);
            }
            $action = $_POST['action'];
            if (!array_key_exists($action, self::listAction())) {
                self::requestFail("accion $action no valida", '0003', 400);
            }
            self::$action(obj($_POST));
        }

        private static function listAction(){
            // Mobbex / checkout / get_end_operation eliminados — código muerto.
            return [
                'get_canchas'        => true,
                'set_reserva'        => true,
                'get_provincias'     => true,
                'get_reservas'       => true,
                'add_customer'       => true,
                'add_jugador'        => true,   // alias (set_jugador del bot)
                'check_availability' => true,
                'get_all_customers'  => true,
                'cancel_booking'     => true,
            ];
        }

        private static function Auth(){
            // PHP-1: usuario y password de basic-auth ahora vienen por sentencia preparada.
            if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                self::AuthFail();
            }
            $email = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

            $user = query(
                "SELECT id, password, status, rol FROM users
                  WHERE email = ? AND status = 1 AND rol IN ('botCanchero','superAdmin')
                  LIMIT 1",
                '',
                [$email]
            );
            if (!$user || !password_verify($password, $user->password)) {
                self::AuthFail();
            }
        }

        private static function AuthFail(){
            self::requestFail('Autenticación fallida', '0001', 403);
        }

        private static function requestFail($msg, $code, $status = 500){
            // No filtrar $_POST entero (puede contener PII / contraseñas).
            JSON(['message' => $msg, 'code' => $code], $status);
        }

        private static function get_canchas(){
            $canchas = canchas::getAll();
            foreach ($canchas as $cancha) {
                $cancha->logo = URL . $cancha->logo;
            }
            JSON($canchas);
        }

        private static function set_reserva(){
            self::validateParam(['phone', 'id_cancha', 'fecha', 'hora', 'name']);
            $_POST['full_name']           = $_POST['name'];
            $_POST['email']               = $_POST['email'] ?? '';
            $_POST['UsuarioPlayerId']     = Customers::checkExitCustomerOCreate(obj($_POST))->id;
            $_POST['CanchaAsignada']      = $_POST['id_cancha'];
            $_POST['UsuarioModificacion'] = 1;
            $_POST['FechaReserva']        = $_POST['fecha'];
            $_POST['HoraReserva']         = $_POST['hora'];
            $_POST['DiaReserva']          = dayName($_POST['fecha']);

            self::addReserva(obj($_POST));
        }

        private static function addReserva($data){
            // PHP-12: AUTO_INCREMENT, no más MAX(id)+1.
            query(
                "INSERT INTO booking(
                    id_customer, id_field, day_booking, time_booking, date_booking, user, status
                 ) VALUES (?, ?, ?, ?, ?, ?, 1)",
                '',
                [
                    $data->UsuarioPlayerId,
                    $data->CanchaAsignada,
                    $data->DiaReserva,
                    $data->HoraReserva,
                    $data->FechaReserva,
                    $data->UsuarioModificacion,
                ]
            );
            $row = query("SELECT LAST_INSERT_ID() AS id");
            $newId = $row ? (int) $row->id : null;
            if ($newId && !empty($data->data_id)) {
                query("UPDATE vouchers SET id_booking = ? WHERE data_id = ?", '', [$newId, $data->data_id]);
            }
            if ($newId) Booking::getBookingByAPI($newId);
        }

        private static function validateParam($params = array()){
            foreach ($params as $param) {
                if (!isset($_POST[$param]) || $_POST[$param] === '') {
                    self::requestFail('parametros no validos', '0004', 400);
                }
            }
        }

        private static function get_customer($data){
            self::validateParam(['phone']);
            $phone = str_replace(['+','-'], '', $data->phone);
            $jugador = query("SELECT * FROM customers WHERE phone = ? LIMIT 1", '', [$phone]);
            if ($jugador) {
                JSON($jugador);
            } else {
                JSON([], 403);
            }
        }

        private static function add_customer($data){
            self::validateParam(['name', 'phone']);
            $email = $_POST['email'] ?? '';
            Customers::add((object) [
                'full_name' => $_POST['name'],
                'phone'     => $_POST['phone'],
                'email'     => $email,
            ]);
            JSON(Customers::getByNumeroTelefono($_POST['phone']));
        }

        private static function add_jugador($data){
            // Alias del set_jugador del bot.
            self::add_customer($data);
        }

        private static function get_provincias(){
            JSON(Address::getProvincias());
        }

        private static function get_reservas($data){
            self::validateParam(['phone']);
            $phone = $_POST['phone'];
            $booking = query(
                "SELECT
                    b.id,
                    d.name AS day,
                    s.hour12 AS time,
                    f.full_name AS cancha,
                    f.latitude AS latitud,
                    f.length AS longitud,
                    f.phone,
                    b.date_booking AS fecha,
                    bs.name AS status,
                    v.data_id AS paymentId,
                    (SELECT p.transaction_amount
                       FROM payment p
                      WHERE p.payment_id = v.data_id
                      LIMIT 1) AS transaction_amount
                FROM booking AS b
                INNER JOIN customers AS c ON b.id_customer = c.id
                INNER JOIN soccer_field AS f ON f.id = b.id_field
                INNER JOIN schedules_day AS d ON d.id = b.day_booking
                INNER JOIN schedules AS s ON s.id = b.time_booking
                INNER JOIN booking_status AS bs ON b.status = bs.id
                LEFT JOIN vouchers AS v ON v.id_booking = b.id
                WHERE c.phone = ?",
                'ALL',
                [$phone]
            );
            foreach ($booking as $b) {
                $b->fecha = showDate($b->fecha);
            }
            JSON($booking);
        }

        private static function cancel_booking(){
            self::validateParam(['id_booking']);
            $obj = (object) [
                'user' => 1,
                'id'   => $_POST['id_booking'],
            ];
            Booking::cancel($obj);
        }

        private static function check_availability($data) {
            self::validateParam(['id_cancha', 'fecha', 'hora']);
            $booking = query(
                "SELECT 1
                   FROM booking
                  WHERE id_field     = ?
                    AND date_booking = ?
                    AND time_booking = ?
                    AND status NOT IN (2)
                  LIMIT 1",
                '',
                [$_POST['id_cancha'], $_POST['fecha'], $_POST['hora']]
            );
            JSON(['isAvailable' => $booking ? false : true]);
        }

        private static function get_all_customers(){
            $customers = query("SELECT full_name, phone FROM customers", "ALL");
            JSON($customers);
        }
    }
