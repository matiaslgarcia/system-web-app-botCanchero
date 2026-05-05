<?php
require 'app/config.php';
require 'app/lib/ClassConexion.php';

$sqlFile = $argv[1] ?? 'migrations/004_mp_webhook_idempotency.sql';
if (!file_exists($sqlFile)) {
    die("Archivo no encontrado: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Separar por ; pero con cuidado de no romper triggers o strings (en este caso es simple)
$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        try {
            query($query);
            echo "Ejecutado: " . substr($query, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "Error en query: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nMigración finalizada.\n";
