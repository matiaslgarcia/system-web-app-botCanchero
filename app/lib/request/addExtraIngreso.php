<?php
require '../../int.php';

Users::loginCheck(true);

header('Content-Type: application/json');

$description    = trim((string) ($_POST['description'] ?? ''));
$amount         = (float) ($_POST['amount'] ?? 0);
$method_payment = trim((string) ($_POST['method_payment'] ?? 'efectivo'));
$date_income    = trim((string) ($_POST['date_income'] ?? date('Y-m-d')));
$id_field       = (int) ($_POST['id_field'] ?? Users::infoUser('id_field'));

$allowed_methods = ['efectivo', 'transferencia', 'mercado_pago', 'otro'];

if (!$description || strlen($description) > 255) {
    echo json_encode(['success' => false, 'error' => 'Descripción inválida.']);
    exit;
}
if ($amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'El monto debe ser mayor a cero.']);
    exit;
}
if (!in_array($method_payment, $allowed_methods, true)) {
    echo json_encode(['success' => false, 'error' => 'Método de pago inválido.']);
    exit;
}
$parsedDate = DateTime::createFromFormat('Y-m-d', $date_income) ?: DateTime::createFromFormat('d/m/Y', $date_income);
if (!$parsedDate) {
    echo json_encode(['success' => false, 'error' => 'Fecha inválida.']);
    exit;
}
$date_income = $parsedDate->format('Y-m-d');

$userId = Users::infoUser('id');

query(
    "INSERT INTO extra_income (id_field, date_income, description, amount, method_payment, created_by_user_id)
     VALUES (?, ?, ?, ?, ?, ?)",
    '',
    [$id_field, $date_income, $description, $amount, $method_payment, $userId]
);

echo json_encode(['success' => true]);
