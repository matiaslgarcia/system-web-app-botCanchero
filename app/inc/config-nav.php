<?php
/**
 * Item 14 (auditoría UX/UI): "Una sola sección Configuración con seis
 * bloques: Establecimiento · Horarios y precios · Servicios · Cobros
 * (Mercado Pago) · Bot de WhatsApp · Cuenta y seguridad."
 *
 * Unificar Mi Cancha y Configuración de cuenta en una sola pantalla
 * significa reescribir dos formularios grandes e independientes
 * (Mercado Pago con su flujo de OAuth, cambio de contraseña, horarios,
 * servicios, reglas operativas del bot) -- alto riesgo de romper algo sin
 * poder probarlo en un navegador real. Esta barra da la misma sensación
 * de "una sola sección con seis bloques" sin tocar ninguno de los dos
 * formularios: los tres bloques de la pantalla actual son tabs locales
 * (como ya eran), los otros tres son links directos a la solapa exacta
 * de la otra pantalla, en vez de un cartel aparte avisando "eso está en
 * otro lado".
 *
 * Uso: $configNavActive = 'establecimiento'|'horarios'|'servicios'|'cobros'|'bot'|'cuenta';
 *      inc('config-nav');
 */
$configNavItems = [
    'establecimiento' => ['label' => 'Establecimiento',    'href' => 'mi-cancha#config',                     'icon' => 'fa-futbol'],
    'horarios'         => ['label' => 'Horarios y precios', 'href' => 'mi-cancha#horarios',                   'icon' => 'fa-clock'],
    'servicios'        => ['label' => 'Servicios',          'href' => 'mi-cancha#servicios',                  'icon' => 'fa-list-check'],
    'cobros'           => ['label' => 'Cobros (Mercado Pago)', 'href' => 'account_settings#kt_user_payments_tab', 'icon' => 'fa-credit-card'],
    'bot'              => ['label' => 'Bot de WhatsApp',    'href' => 'account_settings#kt_user_operational_tab', 'icon' => 'fa-robot'],
    'cuenta'           => ['label' => 'Cuenta y seguridad', 'href' => 'account_settings#kt_user_profile_tab', 'icon' => 'fa-user-gear'],
];
$configNavActive = $configNavActive ?? '';
?>
<div class="mb-5 config-nav-unified">
    <div class="text-muted fs-8 text-uppercase fw-bold mb-2">Configuración</div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($configNavItems as $key => $item) : ?>
            <a href="<?php echo htmlspecialchars($item['href']); ?>"
               class="btn btn-sm <?php echo $key === $configNavActive ? 'btn-primary' : 'btn-light'; ?>">
                <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?> me-2"></i><?php echo htmlspecialchars($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
