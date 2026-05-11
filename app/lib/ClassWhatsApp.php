<?php

class WhatsApp {

    /**
     * Envía un mensaje de texto vía Meta Cloud API.
     * Retorna ['success' => bool, 'error' => string|null].
     */
    public static function sendText(string $to, string $message): array {
        $token    = defined('META_JWTOKEN')       ? META_JWTOKEN       : '';
        $numberId = defined('META_NUMBER_ID')     ? META_NUMBER_ID     : '';
        $version  = defined('META_GRAPH_VERSION') ? META_GRAPH_VERSION : 'v25.0';

        if (!$token || !$numberId) {
            return ['success' => false, 'error' => 'Credenciales Meta no configuradas.'];
        }

        $url     = "https://graph.facebook.com/{$version}/{$numberId}/messages";
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => 'Error de red: ' . $curlErr];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['messages'])) {
            return ['success' => true, 'error' => null];
        }

        $errorMsg = $decoded['error']['message'] ?? ('HTTP ' . $httpCode);
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Retorna clientes (id, full_name, phone) con al menos una reserva.
     * Si $idField es null → todos los clientes del establecimiento (superAdmin).
     * Si $idField es int  → solo clientes de esa cancha.
     */
    public static function getClientes(?int $idField = null): array {
        if ($idField !== null) {
            $rows = query(
                "SELECT DISTINCT c.id, c.full_name, c.phone
                   FROM customers c
                   INNER JOIN booking b ON b.id_customer = c.id
                  WHERE b.id_field = ?
                  ORDER BY c.full_name ASC",
                'ALL',
                [$idField]
            );
        } else {
            $rows = query(
                "SELECT DISTINCT c.id, c.full_name, c.phone
                   FROM customers c
                   INNER JOIN booking b ON b.id_customer = c.id
                  ORDER BY c.full_name ASC",
                'ALL'
            );
        }
        return $rows ?: [];
    }
}
