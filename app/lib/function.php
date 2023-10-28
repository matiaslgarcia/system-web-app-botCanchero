<?php

    function inc($file, $ruta = '', $extencion = '.php'){
        $dir = 'inc/' . $ruta . $file . $extencion;

        require $dir;
    }
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

    function showSelected($valor1, $valor2){
        if($valor1 == $valor2){
            echo 'selected';
        }
    }
    function showClass($valor1, $valor2, $class){
        if($valor1 == $valor2){
            echo $class;
        }
    }
    function showArgument($valor1, $valor2, $class){
        if($valor1 == $valor2){
            echo $class;
        }
    }
    function modal($file, $extencion = '.php'){
        $dir = 'inc/modal/'. $file . $extencion;

        require $dir;
    }
    function dayName($date){
        $day = date('w', strtotime($date));
        $day = ($day == 0) ? 7 : $day;
        return $day;
    }
    function setDate($date){
        $date = explode('/', $date);

        $date = $date[2] . '-' . $date[1] . '-' . $date[0];

        return $date;
    }
    function dateToName($date){
        if (setlocale(LC_TIME, 'es_ES', 'es_ES.utf8', 'es_ES.utf-8', 'es')) {
            $nombreDia = strftime('%A', strtotime($date));
        } else {
            $nombreDia = date('l', strtotime($date)); // Alternativa en inglés si no se puede cambiar la configuración regional
        }
    
        // Cambiar la primera letra a mayúscula
        $nombreDia = ucfirst($nombreDia);
    
        return $nombreDia;
    }

    function showDate($date){
        $date = explode('-', $date);

        $date = $date[2] . '/' . $date[1] . '/' .$date[0];

        return $date;
    }
    function showCard($card){
        $numero = "45079900****0010";
        $ultimos_4_digitos = substr($numero, -4);
        $asteriscos_al_principio = str_repeat('*', strlen($numero) - 4);

        $numero_oculto = $asteriscos_al_principio . $ultimos_4_digitos;

        return $numero_oculto;
    }
    function showLogoTypeCard($type){

        $type = explode(".", $type);
        $type = $type[0];
        $img  = 'assets/img/'.$type.'.png';

        return $img;
    }