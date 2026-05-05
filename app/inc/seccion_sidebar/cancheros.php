<?php if(Users::infoUser('rol') == 'canchero') : ?>
<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="#kt_aside_menu" data-kt-menu="true" data-kt-menu-expand="false">
    <div class="menu-item">
        <div class="menu-content pt-8 pb-2">
            <span class="menu-section text-muted text-uppercase fs-8 ls-1">PANEL CANCHERO</span>
        </div>
    </div>
    
    <div class="menu-item">
        <a class="menu-link" href="./">
            <span class="menu-icon">
                <i class="fa-solid fa-calendar-days fs-4"></i>
            </span>
            <span class="menu-title">Calendario Reservas</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="dia">
            <span class="menu-icon">
                <i class="fa-solid fa-calendar-day fs-4"></i>
            </span>
            <span class="menu-title">Hoy</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="recurring-bookings">
            <span class="menu-icon">
                <i class="fa-solid fa-arrows-rotate fs-4"></i>
            </span>
            <span class="menu-title">Reservas Fijas</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="pausas-pendientes">
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

    <div class="menu-item">
        <a class="menu-link" href="mi-cancha">
            <span class="menu-icon">
                <i class="fa-solid fa-futbol fs-4"></i>
            </span>
            <span class="menu-title">Mi Cancha</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="./mi-cancha?tab=horarios">
            <span class="menu-icon">
                <i class="fa-solid fa-clock fs-4"></i>
            </span>
            <span class="menu-title">Horarios</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="./mi-cancha?tab=servicios">
            <span class="menu-icon">
                <i class="fa-solid fa-concierge-bell fs-4"></i>
            </span>
            <span class="menu-title">Servicios</span>
        </a>
    </div>

    <div class="menu-item">
        <a class="menu-link" href="mi-ingresos">
            <span class="menu-icon">
                <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
            </span>
            <span class="menu-title">Ingresos</span>
        </a>
    </div>

    <div class="menu-item">
        <div class="menu-content">
            <div class="separator mx-1 my-4"></div>
        </div>
    </div>
</div>
<?php endif ;?>