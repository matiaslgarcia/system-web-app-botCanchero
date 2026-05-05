<?php

    require '../../int.php';

    Users::changePassword($_POST['password'], $_POST['id']);