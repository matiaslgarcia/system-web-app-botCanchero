<?php
    $idReserva = $_GET['reserva'] ?? '';
    if (preg_match('/^RB-(\d+)$/', (string) $idReserva, $m)) {
        $rbId = (int) $m[1];
        $rb = query(
            "SELECT rb.*, sf.full_name AS field_name,
                    c.full_name AS customer_name, c.phone AS customer_phone
               FROM recurring_booking rb
               INNER JOIN soccer_field sf ON sf.id = rb.field_id
               LEFT JOIN customers c ON c.id = rb.customer_id
              WHERE rb.id = ?
              LIMIT 1",
            'ARRAY',
            [$rbId]
        );
        if (!$rb) {
            ?>
            <div class="d-flex flex-column flex-root">
                <div class="page d-flex flex-row flex-column-fluid">
                    <?php inc('sidebar') ?>
                    <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                        <?php inc('header') ?>
                        <div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
                            <div class="post d-flex flex-column-fluid" id="kt_post">
                                <div id="kt_content_container" class="container-xxl">
                                    <div class="alert alert-danger">Reserva fija no encontrada</div>
                                </div>
                            </div>
                        </div>
                        <?php inc('footer') ?>
                    </div>
                </div>
            </div>
            <?php
            return;
        }

        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
        $statusMap = [
            'active' => ['success', 'Activa'],
            'pending_payment' => ['warning', 'Pendiente de pago'],
            'paused' => ['info', 'Pausada'],
            'cancelled' => ['danger', 'Cancelada'],
        ];
        $st = (string) ($rb['status'] ?? '');
        [$stColor, $stLabel] = $statusMap[$st] ?? ['secondary', $st ?: '—'];
        $from = !empty($rb['valid_from']) ? implode('/', array_reverse(explode('-', (string) $rb['valid_from']))) : '';
        $until = !empty($rb['valid_until']) ? implode('/', array_reverse(explode('-', (string) $rb['valid_until']))) : 'Indefinido';
        $vigencia = trim($from) ? ($from . ' → ' . $until) : ('— → ' . $until);
        $customerName = (string) ($rb['customer_name'] ?? '');
        $customerPhone = (string) ($rb['customer_phone'] ?? '');
        ?>

        <div class="d-flex flex-column flex-root reserva-mobile-page">
            <div class="page d-flex flex-row flex-column-fluid">
                <?php inc('sidebar') ?>
                <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                    <?php inc('header') ?>

                    <div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
                        <div class="post d-flex flex-column-fluid" id="kt_post">
                            <div id="kt_content_container" class="container-xxl">

                                <div class="d-flex flex-wrap flex-stack mb-6 reserva-page-header">
                                    <div class="d-flex align-items-center reserva-header-main">
                                        <div class="symbol symbol-45px me-5">
                                            <span class="symbol-label bg-light-info">
                                                <i class="fa-solid fa-arrows-rotate fs-2x text-info"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-1 reserva-badges-wrap">
                                                <h1 class="text-dark fw-bolder fs-2 mb-0 me-3 reserva-page-title">Reserva fija #RB-<?php echo $rbId ?></h1>
                                                <span class="badge badge-light-<?php echo $stColor ?> fs-7 fw-bold"><?php echo $stLabel ?></span>
                                            </div>
                                            <span class="text-muted fw-bold fs-6 reserva-page-subtitle">Gestiona el compromiso semanal y sus acciones</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-5 g-xl-10">
                                    <div class="col-xl-8">

                                        <div class="row g-5 mb-5">
                                            <div class="col-md-4">
                                                <div class="card card-flush h-md-100">
                                                    <div class="card-header pt-5">
                                                        <div class="card-title d-flex flex-column">
                                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?php echo $days[(int) ($rb['day_of_week'] ?? 0)] ?? '—' ?></span>
                                                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Día</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-flush h-md-100">
                                                    <div class="card-header pt-5">
                                                        <div class="card-title d-flex flex-column">
                                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?php echo substr((string) ($rb['start_time'] ?? ''), 0, 5) ?: '—' ?></span>
                                                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Hora</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-flush h-md-100">
                                                    <div class="card-header pt-5">
                                                        <div class="card-title d-flex flex-column">
                                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?php echo (int) ($rb['duration_min'] ?? 0) ?> min</span>
                                                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Duración</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-flush mb-5">
                                            <div class="card-body d-flex align-items-center py-8 reserva-customer-card">
                                                <div class="symbol symbol-60px symbol-circle me-5">
                                                    <span class="symbol-label bg-light-primary text-primary fs-1 fw-bold"><?php echo strtoupper(substr($customerName ?: 'C', 0, 1)) ?></span>
                                                </div>
                                                <div class="d-flex flex-column flex-grow-1">
                                                    <span class="text-gray-800 fs-4 fw-bolder"><?php echo $customerName ?: 'Cliente sin nombre' ?></span>
                                                    <?php if ($customerPhone): ?>
                                                        <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $customerPhone) ?>" target="_blank" class="text-muted fw-bold text-hover-primary d-flex align-items-center">
                                                            <i class="fa-brands fa-whatsapp text-success me-2"></i><?php echo $customerPhone ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end reserva-customer-meta">
                                                    <span class="text-gray-800 fs-5 fw-bolder d-block"><?php echo $rb['field_name'] ?? '—' ?></span>
                                                    <span class="text-gray-400 fw-bold"><?php echo $vigencia ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-flush">
                                            <div class="card-header pt-7">
                                                <h3 class="card-title align-items-start flex-column">
                                                    <span class="card-label fw-bolder text-gray-800">Acciones</span>
                                                    <span class="text-gray-400 mt-1 fw-bold fs-7">Mover, pausar, reactivar o cancelar</span>
                                                </h3>
                                            </div>
                                            <div class="card-body pt-5">
                                                <div class="row g-5 reserva-actions-grid">
                                                    <?php if (in_array($st, ['active', 'pending_payment'], true)) : ?>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-flex btn-light-primary px-6 w-100 h-100px flex-column justify-content-center btn-rb-mover"
                                                                data-id="<?php echo $rbId ?>"
                                                                data-dow="<?php echo (int) ($rb['day_of_week'] ?? 0) ?>"
                                                                data-start="<?php echo substr((string) ($rb['start_time'] ?? ''), 0, 5) ?>"
                                                                data-dur="<?php echo (int) ($rb['duration_min'] ?? 60) ?>">
                                                                <i class="fa-solid fa-arrow-right-arrow-left fs-2x mb-3"></i>
                                                                <span class="fw-bolder fs-6">Mover Horario</span>
                                                            </button>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-flex btn-light-warning px-6 w-100 h-100px flex-column justify-content-center btn-rb-pausar"
                                                                data-id="<?php echo $rbId ?>">
                                                                <i class="fa-solid fa-pause fs-2x mb-3"></i>
                                                                <span class="fw-bolder fs-6">Pausar</span>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($st === 'paused') : ?>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-flex btn-light-success px-6 w-100 h-100px flex-column justify-content-center btn-rb-reactivar"
                                                                data-id="<?php echo $rbId ?>">
                                                                <i class="fa-solid fa-play fs-2x mb-3"></i>
                                                                <span class="fw-bolder fs-6">Reactivar</span>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($st !== 'cancelled') : ?>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-flex btn-light-danger px-6 w-100 h-100px flex-column justify-content-center btn-rb-cancelar"
                                                                data-id="<?php echo $rbId ?>">
                                                                <i class="fa-solid fa-trash fs-2x mb-3"></i>
                                                                <span class="fw-bolder fs-6">Cancelar</span>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($st === 'cancelled') : ?>
                                                        <div class="col-md-12">
                                                            <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                                                                <i class="fa-solid fa-ban fs-2tx text-danger me-4"></i>
                                                                <div class="d-flex flex-stack flex-grow-1 ">
                                                                    <div class="fw-semibold">
                                                                        <h4 class="text-gray-900 fw-bold">Reserva fija cancelada</h4>
                                                                        <div class="fs-6 text-gray-700">No admite más acciones.</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-xl-4">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-header pt-7">
                                                <h3 class="card-title align-items-start flex-column">
                                                    <span class="card-label fw-bolder text-gray-800">Detalles</span>
                                                </h3>
                                            </div>
                                            <div class="card-body pt-5">
                                                <div class="d-flex flex-column gap-3 reserva-details-list">
                                                    <div class="d-flex justify-content-between reserva-detail-row">
                                                        <span class="text-muted fw-bold">Cancha</span>
                                                        <span class="fw-bold text-gray-800"><?php echo $rb['field_name'] ?? '—' ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between reserva-detail-row">
                                                        <span class="text-muted fw-bold">Vigencia</span>
                                                        <span class="fw-bold text-gray-800"><?php echo $vigencia ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between reserva-detail-row">
                                                        <span class="text-muted fw-bold">Estado</span>
                                                        <span class="fw-bold text-gray-800"><?php echo $stLabel ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php inc('footer') ?>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalMover" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-arrow-right-arrow-left me-2"></i>Mover horario fijo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning fs-7">
                            Cancela bookings futuros NO pagados generados por esta fija y los regenera al nuevo horario.
                            Bookings ya pagados quedan con el horario viejo.
                        </div>
                        <input type="hidden" id="mover-id" />
                        <div class="mb-3">
                            <label class="form-label">Día de la semana</label>
                            <select id="mover-dow" class="form-select">
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miércoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sábado</option>
                                <option value="7">Domingo</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora inicio</label>
                                <input type="time" id="mover-start" class="form-control" />
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Duración (min)</label>
                                <input type="number" id="mover-dur" class="form-control" min="30" step="30" value="60" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="mover-confirmar" class="btn btn-primary">Mover</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-xmark me-2"></i>Cancelar reserva fija</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger fs-7">
                            El compromiso semanal queda dado de baja y los bookings futuros se cancelan.
                        </div>
                        <input type="hidden" id="cancelar-id" />
                        <label class="form-label">Motivo (opcional)</label>
                        <textarea id="cancelar-reason" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" id="cancelar-confirmar" class="btn btn-danger">Confirmar baja</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        return;
    }
?>
<?php 
    $idReserva = $_GET['reserva'];
    $reserva = Booking::getById($idReserva);
    $total = Booking::getTotalById($idReserva);
    $usuario = Booking::getUsuario($idReserva);
    $logs = Booking::getLogs($idReserva);

    // Calcular Falta Pagar
    $precioCancha = (float)$reserva->precio_cancha;
    $pagado = (float)$reserva->pagado;
    $faltaPagar = max(0, $precioCancha - $pagado);

    // Lógica de Reserva Pasada
    $fechaReserva = $reserva->fecha; 
    $horaInicio = explode(' - ', $reserva->hora)[0]; 
    $timestampReserva = strtotime("$fechaReserva $horaInicio");
    $esPasada = ($timestampReserva < time());
    $esReservaFija = ((int) ($reserva->is_fixed ?? 0) === 1) || ((int) ($reserva->recurring_booking_id ?? 0) > 0);
?>

<div class="d-flex flex-column flex-root reserva-mobile-page">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>

            <div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        
                        <!-- Header de la Reserva -->
                        <div class="d-flex flex-wrap flex-stack mb-6 reserva-page-header">
                            <div class="d-flex align-items-center reserva-header-main">
                                <div class="symbol symbol-45px me-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fa-solid fa-calendar-check fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-1 reserva-badges-wrap">
                                        <h1 class="text-dark fw-bolder fs-2 mb-0 me-3 reserva-page-title">Reserva #<?php echo $idReserva ?></h1>
                                        <span class="badge badge-light-primary fs-7 fw-bold me-2">
                                            <?php echo 'Cancha ' . (int) ($reserva->slot_number ?? 1) . '/' . (int) ($reserva->threshold ?? 1) ?>
                                        </span>
                                        <?php if ($esReservaFija): ?>
                                            <span class="badge badge-light-info fs-7 fw-bold me-2">
                                                ♻️ Reserva Fija
                                            </span>
                                        <?php endif; ?>
                                        <?php $origenReserva = in_array(strtolower((string)($reserva->source ?? 'web')), ['bot','customer_bot','whatsapp','bot_whatsapp'], true) ? 'Bot' : 'Web'; ?>
                                        <span class="badge fs-7 fw-bold me-2 <?php echo $origenReserva === 'Bot' ? 'badge-light-info' : 'badge-light-dark' ?>">
                                            <?php echo $origenReserva === 'Bot' ? '🤖 Desde Bot' : '🖥 Desde Web' ?>
                                        </span>
                                        <span class="badge badge-light-<?php echo $reserva->status_color ?> fs-7 fw-bold"><?php echo $reserva->status_name ?></span>
                                    </div>
                                    <span class="text-muted fw-bold fs-6 reserva-page-subtitle">Gestiona los detalles y el pago de esta reserva</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-10">
                            <!-- Columna Izquierda: Información y Acciones -->
                            <div class="col-xl-8">
                                
                                <!-- Cards de Resumen -->
                                <div class="row g-5 mb-5">
                                    <div class="col-md-4">
                                        <div class="card card-flush h-md-100">
                                            <div class="card-header pt-5">
                                                <div class="card-title d-flex flex-column">
                                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">$<?php echo number_format($precioCancha, 0, ',', '.') ?></span>
                                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Valor Cancha</span>
                                                </div>
                                            </div>
                                            <div class="card-body d-flex flex-column justify-content-end pe-0">
                                                <span class="badge badge-light-dark fs-8 fw-bold w-fit"><?php echo $reserva->cancha ?></span>
                                                <span class="badge badge-light-primary fs-8 fw-bold w-fit mt-2">
                                                    N° Cancha: <?php echo 'Cancha ' . (int) ($reserva->slot_number ?? 1) . '/' . (int) ($reserva->threshold ?? 1) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card card-flush h-md-100">
                                            <div class="card-header pt-5">
                                                <div class="card-title d-flex flex-column">
                                                    <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">$<?php echo number_format($pagado, 0, ',', '.') ?></span>
                                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Total Pagado</span>
                                                </div>
                                            </div>
                                            <div class="card-body d-flex flex-column justify-content-end pe-0">
                                                <span class="text-gray-800 fw-bold fs-7">Registrado en sistema</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card card-flush h-md-100">
                                            <div class="card-header pt-5">
                                                <div class="card-title d-flex flex-column">
                                                    <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2">$<?php echo number_format($faltaPagar, 0, ',', '.') ?></span>
                                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Falta Pagar</span>
                                                </div>
                                            </div>
                                            <div class="card-body d-flex flex-column justify-content-end pe-0">
                                                <?php if($faltaPagar > 0): ?>
                                                    <span class="badge badge-light-danger fs-8 fw-bold w-fit">Saldo Pendiente</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-success fs-8 fw-bold w-fit">Pagado Total</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detalles del Cliente -->
                                <div class="card card-flush mb-5">
                                    <div class="card-body d-flex align-items-center py-8 reserva-customer-card">
                                        <div class="symbol symbol-60px symbol-circle me-5">
                                            <span class="symbol-label bg-light-warning text-warning fs-1 fw-bold"><?php echo strtoupper(substr($reserva->customer_name, 0, 1)) ?></span>
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <span class="text-gray-800 fs-4 fw-bolder"><?php echo $reserva->customer_name ?></span>
                                            <a href="https://wa.me/<?php echo $reserva->customer_phone ?>" target="_blank" class="text-muted fw-bold text-hover-primary d-flex align-items-center">
                                                <i class="fa-brands fa-whatsapp text-success me-2"></i><?php echo $reserva->customer_phone ?>
                                            </a>
                                        </div>
                                        <div class="text-end reserva-customer-meta">
                                            <span class="text-gray-800 fs-5 fw-bolder d-block"><?php echo showDate($reserva->fecha) ?></span>
                                            <span class="text-gray-400 fw-bold"><?php echo $reserva->hora ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Acciones Principales -->
                                <div class="card card-flush">
                                    <div class="card-header pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bolder text-gray-800">Acciones de Reserva</span>
                                            <span class="text-gray-400 mt-1 fw-bold fs-7">Gestiona el estado y cobro</span>
                                        </h3>
                                    </div>
                                    <div class="card-body pt-5">
                                        <div class="row g-5 reserva-actions-grid">
                                            <?php if ($reserva->status_id != 2 && $reserva->status_id != 3 && $faltaPagar > 0) : ?>
                                            <div class="col-md-4">
                                                <button class="btn btn-flex btn-light-success px-6 w-100 h-100px flex-column justify-content-center" data-bs-toggle="modal" data-bs-target="#cerrarpago-reserva">
                                                    <i class="fa-solid fa-money-bill-wave fs-2x mb-3"></i>
                                                    <span class="fw-bolder fs-6">Cerrar Pago</span>
                                                </button>
                                            </div>
                                            <?php endif; ?>

                                            <?php if($reserva->status_id != 2 && $reserva->status_id != 3 && !$esPasada) : ?>
                                            <div class="col-md-4">
                                                <button class="btn btn-flex btn-light-primary px-6 w-100 h-100px flex-column justify-content-center" data-bs-toggle="modal" data-bs-target="#reagendar-reserva">
                                                    <i class="fa-solid fa-calendar-day fs-2x mb-3"></i>
                                                    <span class="fw-bolder fs-6">Re-Agendar</span>
                                                </button>
                                            </div>
                                            <?php endif; ?>

                                            <?php if($reserva->status_id != 3 && $reserva->status_id != 2 && !$esPasada) : ?>
                                            <div class="col-md-4">
                                                <button id="btn-action-cancelar-reserva" data-id-reserva="<?php echo $reserva->id ?>" class="btn btn-flex btn-light-danger px-6 w-100 h-100px flex-column justify-content-center">
                                                    <i class="fa-solid fa-ban fs-2x mb-3"></i>
                                                    <span class="fw-bolder fs-6">Cancelar Reserva</span>
                                                </button>
                                            </div>
                                            <?php endif; ?>

                                            <?php if($esReservaFija && $reserva->status_id != 2 && $reserva->status_id != 3 && (int)($reserva->recurring_booking_id ?? 0) > 0) : ?>
                                            <div class="col-md-4">
                                                <button id="btn-action-pausar-fija" data-recurring-id="<?php echo (int) $reserva->recurring_booking_id ?>" class="btn btn-flex btn-light-warning px-6 w-100 h-100px flex-column justify-content-center">
                                                    <i class="fa-solid fa-pause fs-2x mb-3"></i>
                                                    <span class="fw-bolder fs-6">Pausar Fija</span>
                                                </button>
                                            </div>
                                            <?php endif; ?>

                                            <?php if($reserva->status_id == 2): ?>
                                            <div class="col-md-12">
                                                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                                                    <i class="fa-solid fa-ban fs-2tx text-danger me-4"></i>
                                                    <div class="d-flex flex-stack flex-grow-1 ">
                                                        <div class=" fw-semibold">
                                                            <h4 class="text-gray-900 fw-bold">Reserva Cancelada</h4>
                                                            <div class="fs-6 text-gray-700 ">Esta reserva ha sido cancelada y no admite más acciones ni cobros.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php elseif($esPasada): ?>
                                            <div class="col-md-12">
                                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                                    <i class="fa-solid fa-circle-info fs-2tx text-warning me-4"></i>
                                                    <div class="d-flex flex-stack flex-grow-1 ">
                                                        <div class=" fw-semibold">
                                                            <h4 class="text-gray-900 fw-bold">Reserva Finalizada</h4>
                                                            <div class="fs-6 text-gray-700 ">Esta reserva ya ha pasado su horario de juego y no admite modificaciones.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Columna Derecha: Historial -->
                            <div class="col-xl-4">
                                <div class="card card-flush h-lg-100">
                                    <div class="card-header pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bolder text-gray-800">Historial de Cambios</span>
                                        </h3>
                                    </div>
                                    <div class="card-body pt-5">
                                        <div class="timeline-label reserva-timeline">
                                            <?php if(empty($logs)): ?>
                                                <div class="text-center py-10">
                                                    <i class="fa-solid fa-clock-rotate-left fs-3x text-gray-200 mb-3"></i>
                                                    <p class="text-gray-400 fw-bold">No hay movimientos registrados</p>
                                                </div>
                                            <?php else: ?>
                                                <?php if($esPasada && $reserva->status_id != 2): ?>
                                                    <div class="timeline-item">
                                                        <div class="timeline-label fw-bolder text-gray-800 fs-6">
                                                            <?php 
                                                                $horaFinRaw = explode(' - ', $reserva->hora)[1] ?? '--:--';
                                                                echo date('H:i', strtotime($horaFinRaw));
                                                            ?>
                                                        </div>
                                                        <div class="timeline-badge">
                                                            <i class="fa fa-genderless text-success fs-1"></i>
                                                        </div>
                                                        <div class="fw-mormal timeline-content text-muted ps-3">
                                                            <span class="text-gray-800 fw-bolder">Completada</span>
                                                            <span class="d-block fs-8 text-gray-400"><?php echo showDate($reserva->fecha) ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php foreach ($logs as $log) { ?>
                                                    <div class="timeline-item">
                                                        <div class="timeline-label fw-bolder text-gray-800 fs-6"><?php echo $log->hora ?></div>
                                                        <div class="timeline-badge">
                                                            <i class="fa fa-genderless text-<?php echo $log->logs_color ?> fs-1"></i>
                                                        </div>
                                                        <div class="fw-mormal timeline-content text-muted ps-3">
                                                            <span class="text-gray-800 fw-bolder"><?php echo $log->logs_name ?></span> por <?php echo $log->user ?>
                                                            <span class="d-block fs-8 text-gray-400"><?php echo $log->fecha ?></span>
                                                            <?php if (!empty($log->note)): ?>
                                                                <span class="d-block fs-8 text-gray-600"><?php echo htmlspecialchars($log->note) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
            <?php modal('re-agendar') ?>
            <?php modal('cerrar-pago') ?>
        </div>
    </div>
</div>

<style>
.w-fit { width: fit-content; }
.timeline-label:before { left: 47px !important; }
.timeline-item { margin-bottom: 1.5rem; }
</style>
