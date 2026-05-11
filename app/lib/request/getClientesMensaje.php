<?php
require '../../int.php';

Users::loginCheck();

header('Content-Type: application/json');

$rol     = Users::infoUser('rol');
$idField = ($rol === 'superAdmin') ? null : (int) Users::infoUser('id_field');

$clientes = WhatsApp::getClientes($idField);

$result = array_map(function ($c) {
    return [
        'id'        => $c->id,
        'full_name' => $c->full_name,
        'phone'     => preg_replace('/\D/', '', (string) $c->phone), // normalizado sin +
    ];
}, $clientes);

echo json_encode(array_values($result));
