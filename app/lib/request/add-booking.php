<?php

    require '../../int.php';

    if (session_status() === PHP_SESSION_NONE) session_start();

    $_POST['phone'] = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
    $_POST['full_name'] = trim((string) ($_POST['full_name'] ?? ''));

    $error = array();
    if(empty($_POST['phone'])){
        array_push($error, array('key' => 'phone'));
    }
    if(empty($_POST['full_name'])){
        array_push($error, array('key' => 'full_name'));
    }
    if(empty($_POST['id_field'])){
        array_push($error, array('key' => 'id_field'));
    }
    // if(empty($_POST['email'])){
    //     array_push($error, array('key' => 'email'));
    // }
    if(empty($_POST['date_booking'])){
        array_push($error, array('key' => 'date_booking'));
    }
    if(empty($_POST['time_booking'])){
        array_push($error, array('key' => 'time_booking'));
    }
    
    if(count($error) > 0){
        JSON(['fail' => true, 'error' => $error, 'icon' => 'error', 'msg' => 'Por Favor! Complete Todos Los Campos'], 403);
    }else{
        $_POST['id_customer']   = Customers::checkExitCustomerOCreate(obj($_POST))->id;
        $_POST['id_field']      = $_POST['id_field'];
        $_POST['user']          = Users::infoUser('id');
        $_POST['date_booking']  = setDate($_POST['date_booking']);
        $_POST['day_booking']   = dayName($_POST['date_booking']);
        Booking::add(obj($_POST));
    }
    
