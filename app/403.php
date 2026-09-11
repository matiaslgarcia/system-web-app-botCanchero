<?php
// Jugada 17 — ErrorDocument 403 del panel. Standalone a propósito (no
// depende de int.php/sesión).
http_response_code(403);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso no disponible — BotCanchero</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Poppins, Helvetica, Arial, sans-serif;
            background: #F5F8FA;
            color: #181C32;
            padding: 24px;
            text-align: center;
        }
        .wrap { max-width: 420px; }
        .code {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .08em;
            color: #A1A5B7;
            margin-bottom: 12px;
        }
        h1 { font-size: 26px; font-weight: 700; margin: 0 0 10px; }
        p { color: #5E6278; margin: 0 0 24px; font-size: 15px; line-height: 1.5; }
        a.btn {
            display: inline-block;
            background: #009EF7;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 11px 22px;
            border-radius: 8px;
        }
        a.btn:hover { background: #0086d1; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code">ERROR 403</div>
        <h1>No tenés acceso a esta página</h1>
        <p>Puede que tu sesión haya vencido o que este recurso no sea para tu usuario. Volvé a iniciar sesión para seguir.</p>
        <a class="btn" href="/app/">Ir al panel</a>
    </div>
</body>
</html>
