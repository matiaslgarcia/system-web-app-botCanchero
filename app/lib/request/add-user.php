<?php

    require '../../int.php';

    $error = [];
    if(!Users::validateByEmail($_POST['email'])){
        if(empty($_POST['full_name'])){
            array_push($error, ['key' => 'full_name']);
        }
        if(empty($_POST['phone'])){
            array_push($error, ['key' => 'phone']);
        }
        if(empty($_POST['email'])){
            array_push($error, ['key' => 'email']);
        }
        if(empty($_POST['password'])){
            array_push($error, ['key' => 'password']);
        }
    

        if(empty($error)){
            Users::add(obj($_POST));
        }else{
            JSON(['add_fail' => true, 'error' => $error, 'icon' => 'error', 'msg' => 'Complete todo los campos'], 400);
        }
    }else{
        JSON(['add_fail' => true, 'error' => [], 'icon' => 'error', 'msg' => 'Este correo ya tiene un cuenta'], 400);
    }
    
    