<?php
require 'int.php';
try {
    $pdo = Db::pdo();
    $id_booking = 325; // ID de la reserva que estamos viendo
    $user = $_SESSION['canchero'] ?? 1;
    $action = 'test_debug';
    $note = 'Prueba de insercion manual';
    
    $stmt = $pdo->prepare("INSERT INTO booking_logs (id_booking, id_user, action, note, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$id_booking, $user, $action, $note]);
    
    echo "INSERCION OK. Filas afectadas: " . $stmt->rowCount();
} catch (Exception $e) {
    echo "ERROR DE INSERCION: " . $e->getMessage();
}
