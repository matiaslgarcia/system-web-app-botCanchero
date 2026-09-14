<?php
ob_start();
require '../../int.php';

Users::loginCheck();

header('Content-Type: application/json');

$message     = trim((string) ($_POST['message'] ?? ''));
$recipients  = $_POST['recipients'] ?? [];
$templateKey = trim((string) ($_POST['template_key'] ?? '')) ?: null;

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
$porTelefono   = [];
foreach ($clientesOk as $c) {
    $porTelefono[$normalizePhone($c->phone)] = $c;
}

$sent   = 0;
$failed = 0;
$errors = [];

// Item 11 (auditoria UX/UI): sin esto, un envio masivo no dejaba ningun
// rastro de a quien le llego el mensaje y a quien no -- se registra un
// envio (whatsapp_broadcast) con el resultado por destinatario
// (whatsapp_broadcast_recipient), visible despues en un historial.
query(
    "INSERT INTO whatsapp_broadcast (id_field, sent_by_user_id, template_key, message, total_recipients)
     VALUES (?, ?, ?, ?, ?)",
    '',
    [$idField, (int) ($_SESSION['canchero'] ?? 0), $templateKey, $message, count($recipients)]
);
$broadcastRow = query("SELECT LAST_INSERT_ID() AS id", 'ARRAY');
$broadcastId  = $broadcastRow ? (int) $broadcastRow['id'] : 0;

foreach ($recipients as $phone) {
    $phone   = $normalizePhone($phone);
    $cliente = $porTelefono[$phone] ?? null;

    if (!$phone || !$cliente) {
        $failed++;
        $errorMsg = "Teléfono no autorizado: {$phone}";
        $errors[] = $errorMsg;
        if ($broadcastId) {
            query(
                "INSERT INTO whatsapp_broadcast_recipient (broadcast_id, customer_id, full_name, phone, status, error)
                 VALUES (?, NULL, NULL, ?, 'failed', ?)",
                '',
                [$broadcastId, $phone, $errorMsg]
            );
        }
        continue;
    }

    // Item 11: variable {nombre} -- mismo mensaje, personalizado por
    // destinatario en vez de un texto genérico para toda la base.
    $personalizedMessage = str_ireplace('{nombre}', $cliente->full_name ?? '', $message);

    $result = WhatsApp::sendText($phone, $personalizedMessage);
    $status = $result['success'] ? 'sent' : 'failed';
    if ($result['success']) {
        $sent++;
    } else {
        $failed++;
        $errors[] = "{$phone}: " . ($result['error'] ?? 'Error desconocido');
    }

    if ($broadcastId) {
        query(
            "INSERT INTO whatsapp_broadcast_recipient (broadcast_id, customer_id, full_name, phone, status, error)
             VALUES (?, ?, ?, ?, ?, ?)",
            '',
            [$broadcastId, $cliente->id ?? null, $cliente->full_name ?? null, $phone, $status, $result['error'] ?? null]
        );
    }

    // Delay entre mensajes para respetar rate limits de Meta
    if ($sent + $failed < count($recipients)) {
        usleep(300000); // 300ms
    }
}

if ($broadcastId) {
    query(
        "UPDATE whatsapp_broadcast SET total_sent = ?, total_failed = ? WHERE id = ?",
        '',
        [$sent, $failed, $broadcastId]
    );
}

ob_end_clean();
echo json_encode([
    'success'      => $sent > 0,
    'sent'         => $sent,
    'failed'       => $failed,
    'errors'       => $errors,
    'broadcast_id' => $broadcastId,
]);
