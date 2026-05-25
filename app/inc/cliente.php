<?php
$profile = CustomerCRM::getCustomerProfile(
    (int) ($_GET['id'] ?? 0),
    (int) ($_GET['establishment_id'] ?? 0),
    (int) ($_GET['history_page'] ?? 1)
);
$customer = $profile['customer'] ?? null;
$summary = $profile['summary'] ?? [];
$upcomingBookings = $profile['upcoming_bookings'] ?? [];
$recentBookings = $profile['recent_bookings'] ?? [];
$recentBookingsPagination = $profile['recent_bookings_pagination'] ?? [];
$selectedEstablishmentId = (int) ($profile['selected_establishment_id'] ?? 0);
$isFeatureEnabled = (bool) ($profile['feature_enabled'] ?? true);
$isNotFound = (bool) ($profile['not_found'] ?? false);
$privacyScopeStatus = (string) ($profile['privacy_scope_status'] ?? 'active');
$isInactiveScope = $privacyScopeStatus === 'inactive';
$phoneDisplay = trim((string) (($customer['phone'] ?? '') !== '' ? $customer['phone'] : ''));
$phoneWaLink = preg_replace('/\D+/', '', $phoneDisplay);
$lastBookingLabel = !empty($summary['last_booking_date']) ? showDate((string) $summary['last_booking_date']) : 'Sin historial';
$nextBookingLabel = !empty($summary['next_booking_date']) ? showDate((string) $summary['next_booking_date']) : 'Sin próxima reserva';
$historyCurrentPage = max(1, (int) ($recentBookingsPagination['current_page'] ?? 1));
$historyTotalPages = max(1, (int) ($recentBookingsPagination['total_pages'] ?? 1));
$historyTotalItems = max(0, (int) ($recentBookingsPagination['total_items'] ?? 0));
$historyBaseUrl = 'cliente?id=' . (int) ($_GET['id'] ?? 0)
    . ($selectedEstablishmentId > 0 ? '&establishment_id=' . $selectedEstablishmentId : '');
?>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        <div class="d-flex flex-wrap flex-stack mb-8">
                            <div class="d-flex flex-column">
                                <h1 class="text-dark fw-bolder fs-2 mb-2">Ficha de Cliente</h1>
                                <span class="text-muted fs-6">Resumen simple del cliente con sus reservas, cancelaciones, próximas fechas e historial.</span>
                            </div>
                            <a href="clientes<?php echo $selectedEstablishmentId > 0 ? '?establishment_id=' . $selectedEstablishmentId : ''; ?>" class="btn btn-primary btn-sm px-6 py-3 fw-bold">
                                <i class="fa-solid fa-arrow-left me-2"></i>Volver a Clientes
                            </a>
                        </div>

                        <?php if (!$profile['ready']) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-triangle-exclamation fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Infraestructura pendiente</h4>
                                    <span>Para usar este módulo hay que aplicar la migración `migrations/017_crm_customers.sql`.</span>
                                </div>
                            </div>
                        <?php } elseif (!$isFeatureEnabled) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-lock fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Módulo no habilitado por plan</h4>
                                    <span>Activá `mod_crm` desde `Planes y Módulos` para usar la ficha de clientes.</span>
                                </div>
                            </div>
                        <?php } elseif ($isNotFound || !$customer) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-circle-info fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Cliente no encontrado</h4>
                                    <span>No se encontró información del cliente dentro del establecimiento seleccionado.</span>
                                </div>
                            </div>
                        <?php } else { ?>
                            <?php if ($isInactiveScope) { ?>
                                <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                    <i class="fa-solid fa-user-slash fs-1 text-warning me-4"></i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-warning">Cliente dado de baja en esta sede</h4>
                                        <span>La ficha queda en modo solo lectura hasta reactivar al cliente en este establecimiento.</span>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="card mb-8">
                                <div class="card-body p-8">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-6">
                                        <div class="d-flex align-items-center gap-5">
                                            <div class="symbol symbol-75px">
                                                <span class="symbol-label bg-light-primary text-primary fs-2 fw-bolder">
                                                    <?php echo htmlspecialchars(strtoupper(substr((string) $customer['full_name'], 0, 1))); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark fw-bolder fs-2"><?php echo htmlspecialchars((string) $customer['full_name']); ?></span>
                                                <span class="text-muted fs-6"><?php echo htmlspecialchars((string) ($customer['establishment_name'] ?? 'Establecimiento')); ?></span>
                                                <div class="d-flex flex-wrap gap-4 mt-2 text-muted fs-7">
                                                    <span>
                                                        <i class="fa-solid fa-phone me-2"></i>
                                                        <?php if ($phoneWaLink !== '') { ?>
                                                            <a href="https://wa.me/<?php echo htmlspecialchars($phoneWaLink); ?>" target="_blank" rel="noopener noreferrer">
                                                                <?php echo htmlspecialchars($phoneDisplay); ?>
                                                            </a>
                                                        <?php } else { ?>
                                                            Sin telefono
                                                        <?php } ?>
                                                    </span>
                                                    <span><i class="fa-solid fa-envelope me-2"></i><?php echo htmlspecialchars((string) (($customer['email'] ?? '') !== '' ? $customer['email'] : 'Sin email')); ?></span>
                                                    <span><i class="fa-solid fa-calendar-check me-2"></i>Próxima reserva: <?php echo htmlspecialchars((string) $nextBookingLabel); ?></span>
                                                    <span><i class="fa-solid fa-clock-rotate-left me-2"></i>Última reserva: <?php echo htmlspecialchars((string) $lastBookingLabel); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                            <span class="badge badge-light-primary fs-7"><?php echo (int) ($summary['total_bookings'] ?? 0); ?> reservas</span>
                                            <span class="badge badge-light-danger fs-7"><?php echo (int) ($summary['cancelled_bookings'] ?? 0); ?> cancelaciones</span>
                                            <span class="badge badge-light-success fs-7">$<?php echo number_format((float) ($summary['paid_total'] ?? 0), 2); ?> generados</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-5 g-xl-8 mb-8">
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-light-primary h-100">
                                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 112px;">
                                            <div class="text-muted fs-7">Reservas totales</div>
                                            <div class="fw-bold fs-2"><?php echo (int) ($summary['total_bookings'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-light-info h-100">
                                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 112px;">
                                            <div class="text-muted fs-7">Próximas reservas</div>
                                            <div class="fw-bold fs-2"><?php echo (int) ($summary['upcoming_bookings'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-light-danger h-100">
                                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 112px;">
                                            <div class="text-muted fs-7">Cancelaciones</div>
                                            <div class="fw-bold fs-2"><?php echo (int) ($summary['cancelled_bookings'] ?? 0); ?></div>
                                            <div class="text-muted fs-8 mt-1">Tasa: <?php echo number_format((float) ($summary['cancel_rate'] ?? 0), 2); ?>%</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-light-success h-100">
                                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 112px;">
                                            <div class="text-muted fs-7">Dinero generado</div>
                                            <div class="fw-bold fs-2">$<?php echo number_format((float) ($summary['paid_total'] ?? 0), 2); ?></div>
                                            <div class="text-muted fs-8 mt-1">Promedio por reserva: $<?php echo number_format((int) ($summary['total_bookings'] ?? 0) > 0 ? ((float) ($summary['paid_total'] ?? 0) / (int) ($summary['total_bookings'] ?? 0)) : 0, 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-light-warning h-100">
                                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 112px;">
                                            <div class="text-muted fs-7">Última reserva</div>
                                            <div class="fw-bold fs-5"><?php echo htmlspecialchars((string) $lastBookingLabel); ?></div>
                                            <div class="text-muted fs-8 mt-1">Próxima: <?php echo htmlspecialchars((string) $nextBookingLabel); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-5 g-xl-8">
                                <div class="col-xl-5">
                                    <div class="card h-100">
                                        <div class="card-header border-0 pt-5">
                                            <div class="card-title d-flex flex-column">
                                                <span class="card-label fw-bolder fs-3 mb-1">Próximas reservas</span>
                                                <span class="text-muted fw-semibold fs-7">Agenda futura del cliente en el establecimiento.</span>
                                            </div>
                                        </div>
                                        <div class="card-body py-3">
                                            <?php if (empty($upcomingBookings)) { ?>
                                                <div class="text-muted py-10 text-center">No hay reservas futuras para este cliente.</div>
                                            <?php } else { ?>
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                        <thead>
                                                            <tr class="fw-bolder text-muted">
                                                                <th>Cancha</th>
                                                                <th>Fecha</th>
                                                                <th>Hora</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($upcomingBookings as $booking) { ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars((string) ($booking->field_name ?? '')); ?></td>
                                                                    <td><?php echo htmlspecialchars(showDate((string) ($booking->date_booking ?? ''))); ?></td>
                                                                    <td><?php echo htmlspecialchars((string) ($booking->booking_hour ?? '')); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-7">
                                    <div class="card h-100">
                                        <div class="card-header border-0 pt-5">
                                            <div class="card-title d-flex flex-column">
                                                <span class="card-label fw-bolder fs-3 mb-1">Historial reciente</span>
                                                <span class="text-muted fw-semibold fs-7">Últimas 10 reservas por página con navegación para revisar el historial completo.</span>
                                            </div>
                                        </div>
                                        <div class="card-body py-3">
                                            <?php if (empty($recentBookings)) { ?>
                                                <div class="text-muted py-10 text-center">No hay historial de reservas para este cliente.</div>
                                            <?php } else { ?>
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3">
                                                        <thead>
                                                            <tr class="fw-bolder text-muted">
                                                                <th>Reserva</th>
                                                                <th>Cancha</th>
                                                                <th>Estado</th>
                                                                <th class="text-end">Cobrado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($recentBookings as $booking) { ?>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="fw-semibold text-gray-800"><?php echo htmlspecialchars(showDate((string) ($booking['date_booking'] ?? ''))); ?></span>
                                                                            <span class="text-muted fs-8"><?php echo htmlspecialchars((string) ($booking['booking_hour'] ?? '')); ?></span>
                                                                        </div>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars((string) ($booking['field_name'] ?? '')); ?></td>
                                                                    <td><?php echo htmlspecialchars((string) ($booking['status_label'] ?? '')); ?></td>
                                                                    <td class="text-end">$<?php echo number_format((float) ($booking['paid_amount'] ?? 0), 2); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php if ($historyTotalPages > 1) { ?>
                                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4 mt-6">
                                                        <div class="text-muted fs-7">
                                                            Página <?php echo $historyCurrentPage; ?> de <?php echo $historyTotalPages; ?> | <?php echo $historyTotalItems; ?> reservas en total
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <?php if ($historyCurrentPage > 1) { ?>
                                                                <a href="<?php echo htmlspecialchars($historyBaseUrl . '&history_page=' . ($historyCurrentPage - 1)); ?>" class="btn btn-sm btn-light-primary">
                                                                    <i class="fa-solid fa-arrow-left me-1"></i>Anterior
                                                                </a>
                                                            <?php } ?>
                                                            <?php for ($page = 1; $page <= $historyTotalPages; $page++) { ?>
                                                                <a href="<?php echo htmlspecialchars($historyBaseUrl . '&history_page=' . $page); ?>" class="btn btn-sm <?php echo $page === $historyCurrentPage ? 'btn-primary' : 'btn-light'; ?>">
                                                                    <?php echo $page; ?>
                                                                </a>
                                                            <?php } ?>
                                                            <?php if ($historyCurrentPage < $historyTotalPages) { ?>
                                                                <a href="<?php echo htmlspecialchars($historyBaseUrl . '&history_page=' . ($historyCurrentPage + 1)); ?>" class="btn btn-sm btn-light-primary">
                                                                    Siguiente<i class="fa-solid fa-arrow-right ms-1"></i>
                                                                </a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
