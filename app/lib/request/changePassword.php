<?php

    require '../../int.php';

    Users::changePasword($_POST['password'], $_POST['id']);