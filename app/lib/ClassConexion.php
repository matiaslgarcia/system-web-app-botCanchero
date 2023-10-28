<?php

function conexion($sql){
    $dbhost = dbhost;
    $dbname = dbname;
    $dbuser = dbuser;
    $dbpass = dbpass;
    try{
        $db = new PDO("mysql:host=$dbhost;dbname=$dbname; charset=utf8", $dbuser, $dbpass);
        return $db->prepare($sql);
    }catch(PDOException $e){
        die('no se puede conectar a la base de datos');
    }
}

function query($sql, $get = ''){
    $db = conexion($sql);
    
    $db->execute();

    $get = strtoupper($get);

    switch($get){
        case 'ALL':
            return $db->fetchAll(PDO::FETCH_OBJ);
        break;
        case 'columns':
            return $db->fetchAll(PDO::FETCH_COLUMN);
        break;
        case 'ERROR':
            var_dump($db->errorInfo());
        break;
        case 'ARRAY':
            return $db->fetch(PDO::FETCH_ASSOC);
        break;
        case 'ARRAY_ALL':
            return $db->fetchAll(PDO::FETCH_ASSOC);
        break;
        default:
            return $db->fetch(PDO::FETCH_OBJ);
        break;
        case 'ERROR_JSON':
            JSON($db->errorInfo(), 400);
        break;
    };

}