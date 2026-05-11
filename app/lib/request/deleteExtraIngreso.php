<?php
require '../../int.php';

Users::loginCheck(true);

header('Content-Type: application/json');

$id       = (int) ($_POST['id'] ?? 0);
$id_field = (int) Users::infoUser('id_field');

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido.']);
    exit;
}

// SuperAdmin puede borrar cualquiera; usuario solo los de su cancha.
if (Users::isSuperAdmin()) {
    query("DELETE FROM extra_income WHERE id = ?", '', [$id]);
} else {
    query("DELETE FROM extra_income WHERE id = ? AND id_field = ?", '', [$id, $id_field]);
}

echo json_encode(['success' => true]);
