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
?>

<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>

            <div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        
                        <!-- Header de la Reserva -->
                        <div class="d-flex flex-wrap flex-stack mb-6">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fa-solid fa-calendar-check fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-1">
                                        <h1 class="text-dark fw-bolder fs-2 mb-0 me-3">Reserva #<?php echo $idReserva ?></h1>
                                        <span class="badge badge-light-primary fs-7 fw-bold me-2">
                                            <?php echo 'Cancha ' . (int) ($reserva->slot_number ?? 1) . '/' . (int) ($reserva->threshold ?? 1) ?>
                                        </span>
                                        <?php $origenReserva = in_array(strtolower((string)($reserva->source ?? 'web')), ['bot','customer_bot','whatsapp','bot_whatsapp'], true) ? 'Bot' : 'Web'; ?>
                                        <span class="badge fs-7 fw-bold me-2 <?php echo $origenReserva === 'Bot' ? 'badge-light-info' : 'badge-light-dark' ?>">
                                            <?php echo $origenReserva === 'Bot' ? '🤖 Desde Bot' : '🖥 Desde Web' ?>
                                        </span>
                                        <span class="badge badge-light-<?php echo $reserva->status_color ?> fs-7 fw-bold"><?php echo $reserva->status_name ?></span>
                                    </div>
                                    <span class="text-muted fw-bold fs-6">Gestiona los detalles y el pago de esta reserva</span>
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
                                    <div class="card-body d-flex align-items-center py-8">
                                        <div class="symbol symbol-60px symbol-circle me-5">
                                            <span class="symbol-label bg-light-warning text-warning fs-1 fw-bold"><?php echo strtoupper(substr($reserva->customer_name, 0, 1)) ?></span>
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <span class="text-gray-800 fs-4 fw-bolder"><?php echo $reserva->customer_name ?></span>
                                            <a href="https://wa.me/<?php echo $reserva->customer_phone ?>" target="_blank" class="text-muted fw-bold text-hover-primary d-flex align-items-center">
                                                <i class="fa-brands fa-whatsapp text-success me-2"></i><?php echo $reserva->customer_phone ?>
                                            </a>
                                        </div>
                                        <div class="text-end">
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
                                        <div class="row g-5">
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
                                        <div class="timeline-label">
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
