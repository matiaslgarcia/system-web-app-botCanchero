<?php

    require_once 'int.php';
    Api::checkdateAction();
    Auth::Verific();
    Api::requireFeatureForCurrentAction();

    $data = Api::getDataRequeste();
    $method = $data->method;
    $data->action::$method();
