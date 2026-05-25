<?php

    function generateRequestId(){
        try {
            return bin2hex(random_bytes(8));
        } catch (Throwable $e) {
            return uniqid('rid_', true);
        }
    }

    function sanitizeRequestId($rid){
        $rid = (string) $rid;
        if ($rid === '') return '';
        $rid = preg_replace('/[^a-zA-Z0-9._:-]/', '', $rid);
        return substr($rid, 0, 64);
    }

    function initRequestContext(){
        static $initialized = false;
        if ($initialized) return $GLOBALS['REQUEST_ID'] ?? null;
        $initialized = true;

        $incoming = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
        $requestId = sanitizeRequestId($incoming);
        if ($requestId === '') $requestId = generateRequestId();

        $GLOBALS['REQUEST_ID'] = $requestId;
        $_SERVER['HTTP_X_REQUEST_ID'] = $requestId;
        header('X-Request-ID: ' . $requestId);
        return $requestId;
    }

    function getRequestId(){
        return $GLOBALS['REQUEST_ID'] ?? initRequestContext();
    }

    function logWithRequestId($message, $context = []){
        $prefix = '[request_id=' . getRequestId() . '] ';
        if (!empty($context)) {
            $ctx = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            error_log($prefix . $message . ' | context=' . $ctx);
            return;
        }
        error_log($prefix . $message);
    }

    function inc($file, $ruta = '', $extencion = '.php'){
        $dir = __DIR__ . '/../inc/' . $ruta . $file . $extencion;
        require $dir;
    }
    // PHP-15: por defecto termina el script — evita doble JSON / headers ya enviados.
    function JSON($arr, $status = 200, $die = true) {
        $requestId = getRequestId();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Request-ID: ' . $requestId);
        print json_encode($arr);

        if ($die) {
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
    function showLogoPaymetMethod($type){
        $type = str_replace(' ', '_', strtolower($type));
        $map = [
            'online' => 'mercado_pago',
            'mixto' => 'mercado_pago',
            'cash' => 'efectivo',
            'cash_payment' => 'efectivo',
        ];
        $type = $map[$type] ?? $type;
        $relative = 'assets/img/payment_type/'.$type.'.png';
        $absolute = dirname(__DIR__) . '/' . $relative;
        if (file_exists($absolute)) return $relative;

        return 'assets/img/payment_type/mercado_pago.png';
    }

function audit($action, $target_type = null, $target_id = null, $payload = null) {
    $actor_id = $_SESSION['canchero'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!is_array($payload)) $payload = $payload ? ['data' => $payload] : [];
    $payload['_request_id'] = getRequestId();
    $payload_json = json_encode($payload);
    query('INSERT INTO audit_log (actor_user_id, actor_ip, action, target_type, target_id, payload) VALUES (?, ?, ?, ?, ?, ?)', '', [$actor_id, $ip, $action, $target_type, $target_id, $payload_json]);
}

function domain_event($event_name, $entity_type = null, $entity_id = null, $payload = null, $options = []) {
    if (!class_exists('DomainEvents')) return false;
    return DomainEvents::record($event_name, $entity_type, $entity_id, $payload, $options);
}
