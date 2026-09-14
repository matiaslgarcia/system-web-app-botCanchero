<?php

require '../../int.php';
if (session_status() === PHP_SESSION_NONE) session_start();

Users::loginCheck();
header('Content-Type: application/json');

$rol     = Users::infoUser('rol');
$idField = ($rol === 'superAdmin') ? null : (int) Users::infoUser('id_field');

$detailId = isset($_GET['broadcast_id']) ? (int) $_GET['broadcast_id'] : 0;

if ($detailId > 0) {
    $where  = 'WHERE id = ?';
    $params = [$detailId];
    if ($idField !== null) {
        $where .= ' AND id_field = ?';
        $params[] = $idField;
    }
    $broadcast = query(
        "SELECT b.*, u.full_name AS sent_by_name
           FROM whatsapp_broadcast b
           LEFT JOIN users u ON u.id = b.sent_by_user_id
          $where",
        '',
        $params
    );
    if (!$broadcast) {
        echo json_encode(['error' => 'No encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $recipients = query(
        "SELECT full_name, phone, status, error, created_at
           FROM whatsapp_broadcast_recipient
          WHERE broadcast_id = ?
          ORDER BY id ASC",
        'ALL',
        [$detailId]
    );
    echo json_encode(['broadcast' => $broadcast, 'recipients' => $recipients ?: []], JSON_UNESCAPED_UNICODE);
    exit;
}

$where  = '1=1';
$params = [];
if ($idField !== null) {
    $where = 'id_field = ?';
    $params[] = $idField;
}

$broadcasts = query(
    "SELECT b.*, u.full_name AS sent_by_name
       FROM whatsapp_broadcast b
       LEFT JOIN users u ON u.id = b.sent_by_user_id
      WHERE $where
      ORDER BY b.created_at DESC
      LIMIT 50",
    'ALL',
    $params
);

echo json_encode(['broadcasts' => $broadcasts ?: []], JSON_UNESCAPED_UNICODE);
