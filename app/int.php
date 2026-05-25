<?php
	date_default_timezone_set('America/Argentina/Buenos_Aires');

    define('VERSION', '1.0.36');
    
    require 'config.php';
    require 'lib/function.php';
    initRequestContext();
    require 'lib/ClassConexion.php';
    require 'lib/ClassDomainEvents.php';
    require 'lib/ClassTheme.php';
    require 'lib/ClassUsers.php';
    require 'lib/ClassFeatureGate.php';
    require 'lib/ClassBusinessRules.php';
    require 'lib/ClassCanchas.php';
    require 'lib/ClassBooking.php';
    require 'lib/ClassPayment.php';
    require 'lib/ClassCustomers.php';
    require 'lib/ClassCustomerCRM.php';
    require 'lib/ClassApi.php';
    require 'lib/ClassAddress.php';
    require 'lib/ClassSchedules.php';
    require 'lib/ClassInvoices.php';
    require 'lib/ClassMercadoPago.php';
    require 'lib/ClassServices.php';
    require 'lib/ClassWhatsApp.php';

    if (!defined('SKIP_AUTH')) {
        Users::loginCheck();
        
        // PHP-B16/17: Security Headers
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("X-XSS-Protection: 1; mode=block");
        header("Content-Security-Policy: default-src 'self' http://localhost:8080; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://unpkg.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; " .
               "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: blob: https://*.tile.openstreetmap.org https://unpkg.com https://logospng.org https://cdn-icons-png.flaticon.com https://*.mlstatic.com https://img.icons8.com; " .
               "connect-src 'self' http://localhost:8080 https://cdnjs.cloudflare.com https://unpkg.com https://cdn.jsdelivr.net https://nominatim.openstreetmap.org;");
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }

        // Verificación CSRF en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if ($token !== Users::getCsrfToken()) {
                JSON(['error' => 'Invalid CSRF token'], 403, true);
            }
        }
    }
