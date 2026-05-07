<?php

    require '../../int.php';
    Users::requireSuperAdmin(true);

    $error = [];
    $rol = trim((string) ($_POST['rol'] ?? ''));
    $allowedRoles = ['canchero', 'superAdmin'];

    if (empty($_POST['full_name'])) $error[] = ['key' => 'full_name'];
    if (empty($_POST['phone'])) $error[] = ['key' => 'phone'];
    if (empty($_POST['email'])) $error[] = ['key' => 'email'];
    if (empty($_POST['password'])) $error[] = ['key' => 'password'];
    if (empty($_POST['id_field'])) $error[] = ['key' => 'id_field'];
    if (!in_array($rol, $allowedRoles, true)) $error[] = ['key' => 'rol'];

    if (!empty($error)) {
        JSON(['add_fail' => true, 'error' => $error, 'icon' => 'error', 'msg' => 'Completá los campos requeridos'], 400);
    }
    if (Users::validateByEmail($_POST['email'])) {
        JSON(['add_fail' => true, 'error' => [['key' => 'email']], 'icon' => 'error', 'msg' => 'Este correo ya tiene una cuenta'], 400);
    }

    Users::add(obj($_POST));
    
    
