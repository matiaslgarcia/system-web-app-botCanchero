<?php

// Plantilla — copiar a config.php y completar (NO commitear config.php).
// PHP-4 + PHP-5: credenciales fuera del repo, errores nunca en pantalla en prod.

require_once __DIR__ . '/lib/DotEnv.php';
try {
    (new DotEnv(__DIR__ . '/../.env'))->load();
} catch (Exception $e) {
    // Sin .env el sitio falla rápido en prod (intencional).
}

$appEnv = $_ENV['APP_ENV'] ?? 'production';
if ($appEnv === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

define('dbhost', $_ENV['DB_HOST'] ?? 'localhost');
define('dbname', $_ENV['DB_NAME'] ?? 'botcanchero');
define('dbuser', $_ENV['DB_USER'] ?? 'root');
define('dbpass', $_ENV['DB_PASS'] ?? '');
define('WEBSITE', $_ENV['WEBSITE_URL'] ?? 'https://botcanchero.com');
define('COMPANY', $_ENV['COMPANY_NAME'] ?? 'BotCanchero');
$detectedBaseUrl = 'https://saas.botcanchero.com/';
if (!empty($_SERVER['HTTP_HOST'])) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    );
    $scheme = $isHttps ? 'https' : 'http';
    $detectedBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/app/';
}
define('URL', $_ENV['BASE_URL'] ?? $detectedBaseUrl);
