<?php if(Users::infoUser('rol') == 'canchero') : ?>
<?php
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $currentRoute = trim((string) basename((string) $requestPath), '/');
    $currentRoute = preg_replace('/\.php$/', '', $currentRoute);
    if ($currentRoute === '' || $currentRoute === 'app') {
        $currentRoute = 'index';
    }
    $currentEstablishmentId = (int) (FeatureGate::currentEstablishmentId() ?? 0);
    $showCRM = FeatureGate::isEnabled($currentEstablishmentId, 'mod_crm', true);
    // Item 16 (auditoría UX/UI): "Dashboard Gerencial" ya existía -- período
    // mensual, comparación contra el mes anterior, gráficos de ingresos,
    // ocupación y medios de pago -- pero no estaba linkeado desde ningún
    // lado del menú, así que nadie llegaba a usarlo.
    $showAnalytics = FeatureGate::isEnabled($currentEstablishmentId, 'mod_analytics', true);
?>
<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="#kt_aside_menu" data-kt-menu="true" data-kt-menu-expand="false">
    <div class="menu-item">
        <div class="menu-content pt-8 pb-2">
            <span class="menu-section text-muted text-uppercase fs-8 ls-1">PANEL CANCHERO</span>
        </div>
    </div>
    
    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'index' ? 'active' : ''; ?>" href="./">
            <span class="menu-icon">
                <i class="fa-solid fa-calendar-days fs-4"></i>
            </span>
            <span class="menu-title">Calendario Reservas</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link <?php echo in_array($currentRoute, ['dia', 'hoy'], true) ? 'active' : ''; ?>" href="hoy">
            <span class="menu-icon">
                <i class="fa-solid fa-calendar-day fs-4"></i>
            </span>
            <span class="menu-title">Hoy</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link <?php echo in_array($currentRoute, ['recurring-bookings', 'reservas-fijas'], true) ? 'active' : ''; ?>" href="reservas-fijas">
            <span class="menu-icon">
                <i class="fa-solid fa-arrows-rotate fs-4"></i>
            </span>
            <span class="menu-title">Reservas Fijas</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'pausas-pendientes' ? 'active' : ''; ?>" href="pausas-pendientes">
            <span class="menu-icon">
                <i class="fa-solid fa-clock-rotate-left fs-4"></i>
            </span>
            <span class="menu-title">Pausas Pendientes
                <span id="badge-pausas" class="badge badge-circle badge-danger ms-2 d-none"></span>
            </span>
        </a>
    </div>

    <div class="menu-item">
        <div class="menu-content pt-8 pb-2">
            <span class="menu-section text-muted text-uppercase fs-8 ls-1">MI ESTABLECIMIENTO</span>
        </div>
    </div>

    <!-- NAV-01: Horarios y Servicios eran links a esta misma pantalla
         (mi-cancha?tab=horarios / ?tab=servicios) duplicados como ítems de
         sidebar aparte, sin reflejar cuál solapa estaba activa. Un solo
         ítem, la pantalla ya tiene las tres solapas adentro. -->
    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'mi-cancha' ? 'active' : ''; ?>" href="mi-cancha">
            <span class="menu-icon">
                <i class="fa-solid fa-futbol fs-4"></i>
            </span>
            <span class="menu-title">Mi Cancha</span>
        </a>
    </div>

    <?php if ($showCRM) : ?>
    <div class="menu-item">
        <a class="menu-link <?php echo in_array($currentRoute, ['clientes', 'cliente'], true) ? 'active' : ''; ?>" href="clientes">
            <span class="menu-icon">
                <i class="fa-solid fa-address-book fs-4"></i>
            </span>
            <span class="menu-title">Clientes</span>
        </a>
    </div>
    <?php endif; ?>

    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'mi-ingresos' ? 'active' : ''; ?>" href="mi-ingresos">
            <span class="menu-icon">
                <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
            </span>
            <span class="menu-title">Ingresos</span>
        </a>
    </div>

    <?php if ($showAnalytics) : ?>
    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'dashboard-gerencial' ? 'active' : ''; ?>" href="dashboard-gerencial">
            <span class="menu-icon">
                <i class="fa-solid fa-chart-line fs-4"></i>
            </span>
            <span class="menu-title">Dashboard Gerencial</span>
        </a>
    </div>
    <?php endif; ?>

    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'enviar-mensaje' ? 'active' : ''; ?>" href="enviar-mensaje">
            <span class="menu-icon">
                <i class="fa-brands fa-whatsapp fs-4"></i>
            </span>
            <span class="menu-title">Enviar Mensaje</span>
        </a>
    </div>

    <div class="menu-item">
        <div class="menu-content">
            <div class="separator mx-1 my-4"></div>
        </div>
    </div>

    <!-- Item 15 (auditoría UX/UI): "Configuración de cuenta" sólo se llegaba
         desde el avatar arriba a la derecha -- no había forma de saber
         dónde estabas parado desde el sidebar. -->
    <div class="menu-item">
        <a class="menu-link <?php echo $currentRoute === 'account_settings' ? 'active' : ''; ?>" href="account_settings">
            <span class="menu-icon">
                <i class="fa-solid fa-gear fs-4"></i>
            </span>
            <span class="menu-title">Configuración de Cuenta</span>
        </a>
    </div>
</div>
<?php endif ;?>
