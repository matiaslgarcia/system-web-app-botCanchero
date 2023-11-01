<?php

function JSON($arr , $status = 200, $die = true){
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($arr);

    if($die == true){
        die();
    }
}
function OBJ($arr){
        
    $obj = new ArrayObject($arr);
    $obj->setFlags(ArrayObject::ARRAY_AS_PROPS);
    
    return $obj;
}