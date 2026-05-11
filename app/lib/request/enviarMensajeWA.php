<?php
ob_start();
require '../../int.php';

Users::loginCheck();

header('Content-Type: application/json');

$message    = trim((string) ($_POST['message'] ?? ''));
$recipients = $_POST['recipients'] ?? [];

if (!is_array($recipients)) {
    $recipients = [];
}

if ($message === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'El mensaje no puede estar vacío.']);
    exit;
}
if (mb_strlen($message) > 4096) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'El mensaje supera los 4096 caracteres.']);
    exit;
}
if (empty($recipients)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Seleccioná al menos un destinatario.']);
    exit;
}

// Normalizar phone: quedarse solo con dígitos (algunos registros en DB tienen + o espacios)
$normalizePhone = fn($p) => preg_replace('/\D/', '', (string) $p);

// Validar que los phones recibidos corresponden a clientes del scope del usuario
$rol           = Users::infoUser('rol');
$idField       = ($rol === 'superAdmin') ? null : (int) Users::infoUser('id_field');
$clientesOk    = WhatsApp::getClientes($idField);
$phonesValidos = array_map(fn($c) => $normalizePhone($c->phone), $clientesOk);

$sent   = 0;
$failed = 0;
$errors = [];

foreach ($recipients as $phone) {
    $phone = $normalizePhone($phone);
    if (!$phone || !in_array($phone, $phonesValidos, true)) {
        $failed++;
        $errors[] = "Teléfono no autorizado: {$phone}";
        continue;
    }

    $result = WhatsApp::sendText($phone, $message);
    if ($result['success']) {
        $sent++;
    } else {
        $failed++;
        $errors[] = "{$phone}: " . ($result['error'] ?? 'Error desconocido');
    }

    // Delay entre mensajes para respetar rate limits de Meta
    if ($sent + $failed < count($recipients)) {
        usleep(300000); // 300ms
    }
}

ob_end_clean();
echo json_encode([
    'success' => $sent > 0,
    'sent'    => $sent,
    'failed'  => $failed,
    'errors'  => $errors,
]);
