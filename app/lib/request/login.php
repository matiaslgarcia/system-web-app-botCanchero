<?php

    define('SKIP_AUTH', true);
    require '../../int.php';

    Users::login($_POST['email'], $_POST['password']);