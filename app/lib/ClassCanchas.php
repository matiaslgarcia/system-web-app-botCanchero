<?php

    class Canchas{
        public static function getAll(){
            $canchas = query("SELECT id, full_name AS name, phone, latitude, length, logo, price_hour, tax_id FROM soccer_field WHERE status = 1", 'ALL');

            foreach($canchas AS $cancha){
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo; 
            }
            

            return $canchas;
        }
        public static function add($data){
            $id = self::getIdNewCancha();
            query("INSERT INTO soccer_field
                (full_name, phone, address, latitude, length, logo, price_hour, threshold, tax_id)
                    VALUES
                ('$data->full_name',  '$data->phone',  '$data->address', '$data->latitude', '$data->length', '$data->logo', '$data->price_hour', '$data->limit', '$data->tax_id')"
            );
            
            $data->logo = self::setLogo($id);
            JSON(['icon' => 'success', 'msg' => 'Cancha Agregada Correctamente']);
        }
        private static function getIdNewCancha(){
            $result = query("SELECT  AUTO_INCREMENT AS value FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'soccer_field'");
            
            return $result->value;
        }
        public static function getById($id){
            $cancha = query("SELECT id, full_name AS name, latitude, length, logo,  phone, address, price_hour, threshold, tax_id  FROM soccer_field AS f WHERE id = '$id'");
               $cancha->logo = (empty($cancha->logo)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo; 

            return $cancha;
        }
        public static function edit($data){
            self::setLogo($data->id);
            foreach($data AS $key => $value){
                if($key != 'id'){
                   query("UPDATE soccer_field SET $key = '$value' WHERE id = '$data->id'"); 
                }
            }
            self::setLogo($data->id);
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'guardado correctamente']);
        }

        private static function setLogo($id){
            if(!empty($_FILES['logo']['name'])){
                $file = $_FILES['logo'];

                $logo = $file['name'];
                $tmp  = $file['tmp_name'];
                $dir  = '../../upload/cancha/';

                move_uploaded_file($tmp, $dir . $logo);
                query("UPDATE soccer_field SET logo = '$logo' WHERE id = '$id'"); 
            }
        }
        public static function getMyCancha(){
            $id = $_SESSION['canchero'];

            $cancha = query("SELECT 
                    f.id,
                    f.logo,
                    f.full_name AS name,
                    f.price_hour,
                    f.phone,
                    f.latitude,
                    f.length,
                    f.address,
                    f.threshold
                FROM users AS u
                INNER JOIN soccer_field AS f 
                    ON u.id_field = f.id
                WHERE u.id = '$id' LIMIT 1");
            $cancha->logo = (empty($cancha->log)) ? 'assets/img/cancha.png' : 'upload/cancha/' . $cancha->logo;
            return $cancha;
        }
        public static function deleteCancha($data){
            query("UPDATE soccer_field SET status = 0 WHERE id = '$data->id'");
        }
        public static function getByIdUser(){
            $user = Users::getById($_SESSION['canchero']);
            
            $id = ($user->rol == 'superAdmin') ? '%' : $user->id_field;

            $result = query("SELECT id, full_name AS name FROM soccer_field WHERE id LIKE '$id' and status = 1", 'ALL');

            return $result;
        }
    }