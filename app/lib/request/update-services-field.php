<?php

    require '../../int.php';

    Services::deleteAllServicesField($_POST['id_field']);
    
    foreach ($_POST['servicio'] as $val) {
        Services::addServiceField($_POST['id_field'],  $val);
    }