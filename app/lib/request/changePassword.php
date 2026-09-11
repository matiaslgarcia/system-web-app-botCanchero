<?php

    require '../../int.php';

    $id = (int) ($_POST['id'] ?? 0);
    $password = (string) ($_POST['password'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    if ($id <= 0 || $password === '') {
        JSON(['error' => 'Datos inválidos para cambiar contraseña'], 400, true);
    }

    Users::changePassword($password, $id, $currentPassword);
