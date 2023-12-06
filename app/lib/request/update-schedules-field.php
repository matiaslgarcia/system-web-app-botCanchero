<?php

    require '../../int.php';


    Schedules::deleteAllSchedulesField($_POST['id_day'], $_POST['id_field']);


    foreach($_POST['horario'] AS $val){
        Schedules::addField($_POST['id_day'], $_POST['id_field'], $val);
    }