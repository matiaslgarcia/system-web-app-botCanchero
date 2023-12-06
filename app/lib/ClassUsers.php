<?php

    class Users{
        public static function loginCheck($arr = []){
            session_start();
            if(!isset($_SESSION['canchero'])){
                self::showLogin($arr);
                die();
            }
        }
        public static function login($email, $password){
            $user = query("SELECT * FROM users WHERE email = '$email' LIMIT 1;");
            if($user){
                if($user->status == 1){
                    if(password_verify($password, $user->password)){
                        self::openSession($user->id);
                        JSON(['success' => true]);
                    }else{
                        self::loginFail();
                    }
                }else{
                    JSON(['login_fail' => true, 'msg' => 'Su cuenta se encuentra inactiva favor contacte a soporte', 'icon' => 'error'], 400);
                }
                
            }else{
                self::loginFail();
            }
        }
        public static function out(){
            session_start();
            session_destroy();
            header('Location: ./');

        }
        public static function infoUser($param){
            $id = $_SESSION['canchero'];
            $user = query("SELECT u.id, u.full_name AS name, u.email, u.avatar, u.rol, u.id_field AS id_field, RIGHT(s.token_mercadopago, 5) AS token_id FROM users u LEFT JOIN soccer_field s ON u.id_field = s.id WHERE u.id = '$id';", 'ARRAY');
            $user['token_id'] = (!empty($user['token_id'])) ? '******' . $user['token_id']  : '';
            $user['avatar'] = (!empty($user->avatar)) ? 'uploads/avatar/' .  $user->avatar : 'assets/img/avatars/avatar.png';
            return $user[$param];
        }
        public static function edit($data){
            self::uploadAvatarUser($data->id);

            foreach($data AS $key => $value){
                if($key != 'id'){
                    query("UPDATE users SET $key = '$value' WHERE id = '$data->id' ");
                }
                
            }
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'usuario actualizado']);
        }
        public static function getById($id){
            $user = query("SELECT id, full_name AS name, email, avatar, rol, phone, id_field FROM users WHERE id = '$id';");
            $user->avatar = (!empty($user->avatar)) ? 'upload/avatar/' .  $user->avatar : 'assets/img/avatars/blank.png';
            return $user;
        }
        public static function add($user){
            $user->CanchaAsignada = (empty($user->CanchaAsignada)) ? '0' : $user->CanchaAsignada;
            $user->password = self::emcrytePassword($user->password);

            query("INSERT INTO users
            (full_name, phone, id_field, password, email, rol)
                VALUES
            ('$user->full_name','$user->phone', '$user->id_field', '$user->password','$user->email','$user->rol')");
            self::uploadAvatarUser($user->id);

            JSON(['succes' => true, 'user_id' => $user->id]);
        }
        public static function getAll(){
            $users = query("SELECT u.id, u.full_name AS name, u.avatar, f.full_name AS cancha, f.id as id_cancha FROM users as u INNER JOIN soccer_field AS f ON f.id = u.id_field WHERE u.full_name != 'botCanchero' ", 'all');

            foreach($users as $user){
                $user->avatar = (!empty($user->avatar)) ? 'upload/avatar/' .  $user->avatar : 'assets/img/avatars/avatar.png';
            }
            
            return $users;
        }
        public static function delete($data){
            query("DELETE FROM users WHERE id = '$data->id'");
        }
        private static function showLogin($arr = []){
            $base = (isset($arr['base'])) ? $arr['base'] : '';
            Theme::header([
                'title' => 'Login',
                'base' => $base,
                'css'   => [
                    'plugins.bundle',
                    'style.bundle',
                    'FontAwesome',
                    'theme'
                ]
            ]);
            require 'inc/login.php';
            Theme::footer([
                'js' => [
                    'plugins.bundle',
                    'scripts.bundle',
                ],
                'dataJS' => ['login']
            ]);
            
        }

        private static function loginFail(){
            JSON(['login_fail' => true, 'msg' => 'Usuario o contraseña son incorrectos', 'icon' => 'error'], 400);
        }
        private static function openSession($id){
            session_start();
            $_SESSION['canchero'] = $id;
        }
        private static function getIdNewUser(){
            $result = query("SELECT  AUTO_INCREMENT AS value FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'users'");
            return $result->value;
        }
        private static function emcrytePassword($password){
            $password = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12));

            return $password;
        }

        private static function uploadAvatarUser($id){
            if(!empty( $_FILES['avatar']['name'])){
                $file = $_FILES['avatar'];
                move_uploaded_file($file['tmp_name'], '../../upload/avatar/'. $file['name']);
                $name = $file['name'];
                query("UPDATE users SET avatar = '$name' WHERE id = '$id'");
                
            }  
        }
        public static function changePasword($password, $id){
            $password = self::emcrytePassword($password);
            query("UPDATE users SET password = '$password' WHERE id = '$id'");
            JSON(['success' => true]);
        }
        public static function validateByEmail($email){
            $result = query("SELECT * FROM users WHERE email = '$email'");
            
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }