<?php

    require 'int.php';

    session_start();

    MercadoPago::createRefreshToken($_GET['code']);