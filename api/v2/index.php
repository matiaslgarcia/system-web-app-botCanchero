<?php

    require_once 'int.php';
    Api::checkdateAction();
    Auth::Verific();

    $data = Api::getDataRequeste();
    $method = $data->method;
    $data->action::$method();