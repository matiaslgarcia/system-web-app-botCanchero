<?php
define('SKIP_AUTH', true);
require __DIR__ . '/int.php';

// Proxy público para webhook MP cuando no hay túnel directo al bot.
// Recibe en /app/mpay-hook y reenvía al server interno del bot (puerto 2998).
$target = 'http://botcanchero_bot:2998/mpay-hook';
$query = $_SERVER['QUERY_STRING'] ?? '';
if ($query !== '') {
    $target .= '?' . $query;
}

$body = file_get_contents('php://input');
$headers = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') !== 0) continue;
    $name = str_replace('_', '-', substr($key, 5));
    $name = implode('-', array_map('ucfirst', explode('-', strtolower($name))));
    $headers[] = $name . ': ' . $value;
}
$headers[] = 'Content-Type: application/json';

$ctx = stream_context_create([
    'http' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'POST',
        'header' => implode("\r\n", $headers),
        'content' => $body !== false ? $body : '',
        'ignore_errors' => true,
        'timeout' => 15,
    ],
]);

$resp = @file_get_contents($target, false, $ctx);
$status = 502;
if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
    $status = (int) $m[1];
}

http_response_code($status);
if ($resp !== false && $resp !== null) {
    echo $resp;
} else {
    echo 'mp-hook-proxy-error';
}

