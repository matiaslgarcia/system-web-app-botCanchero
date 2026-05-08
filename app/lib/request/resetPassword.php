<?php

define('SKIP_AUTH', true);
require '../../int.php';

$token = (string) ($_POST['token'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

Users::resetPasswordByToken($token, $password, $passwordConfirm);
