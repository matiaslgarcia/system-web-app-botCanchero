<?php

    require 'int.php';

    Users::loginCheck();

    Theme::header([
        'title' => 'Configuración de Cuenta',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
            'FontAwesome',
            'theme'
        ]
    ]);
    inc('account_settings');
    Theme::footer([
        'js' => [
            'plugins.bundle',
            'scripts.bundle',
        ]
    ]);
?>
<script type="module" src="lib/dataJS/account_settings.js?ver=<?php echo VERSION ?>"></script>
<script type="module" src="lib/dataJS/configuracion-operativa.js?ver=<?php echo VERSION ?>"></script>
