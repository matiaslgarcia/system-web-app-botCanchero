<?php
$dashboard = CustomerCRM::getDashboardData((int) ($_GET['establishment_id'] ?? 0), $_GET);
$customers = $dashboard['customers'] ?? [];
$stats = $dashboard['stats'] ?? [];
$fields = $dashboard['fields'] ?? [];
$establishments = $dashboard['establishments'] ?? [];
$selectedEstablishmentId = (int) ($dashboard['selected_establishment_id'] ?? 0);
$filters = $dashboard['filters'] ?? ['q' => '', 'field_id' => 0, 'activity' => ''];
$selectedFieldId = (int) ($filters['field_id'] ?? 0);
$selectedActivity = (string) ($filters['activity'] ?? '');
$search = (string) ($filters['q'] ?? '');
$isSuperAdmin = Users::isSuperAdmin();
// Item 24: estos totales tienen que salir del set filtrado completo, no de
// la página actual (ya vienen calculados así desde getDashboardData).
$totalBookingsCount = (int) ($stats['total_bookings_sum'] ?? 0);
$totalCancelledCount = (int) ($stats['total_cancelled_sum'] ?? 0);
$totalRevenue = (float) ($stats['total_revenue_sum'] ?? 0);
$pagination = $dashboard['pagination'] ?? ['page' => 1, 'per_page' => 25, 'total' => count($customers), 'total_pages' => 1];
// Reconstruye la query string de filtros activos para los links de página.
$paginationQuery = $_GET;
unset($paginationQuery['page']);
?>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <main id="contenido" tabindex="-1" class="content d-flex flex-column flex-column-fluid">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        <div class="d-flex flex-wrap flex-stack mb-8">
                            <div class="d-flex flex-column">
                                <h1 class="text-dark fw-bolder fs-2 mb-2">Clientes</h1>
                                <span class="text-muted fs-6">Una vista simple para entender quién reserva, quién cancela y cuánto te genera cada cliente.</span>
                            </div>
                        </div>

                        <?php if (!$dashboard['ready']) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-triangle-exclamation fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Infraestructura pendiente</h4>
                                    <span>Para usar este módulo hay que aplicar la migración `migrations/017_crm_customers.sql`.</span>
                                </div>
                            </div>
                        <?php } elseif ($selectedEstablishmentId > 0 && !$dashboard['feature_enabled']) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <i class="fa-solid fa-lock fs-1 text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Módulo no habilitado por plan</h4>
                                    <span>Activá `mod_crm` desde `Planes y Módulos` para exponer la ficha de clientes a este establecimiento.</span>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- SIS-03/SIS-05: eran 5 métricas en tarjetas bg-light-* de a 4 por fila
                             (la 5ta quedaba sola); se pasa al componente .bc-kpi-row que ya usa
                             "Hoy", con su variante de 5 columnas. -->
                        <!-- Auditoría UX/UI (13/09): la tarjeta oscura (.lead) es siempre la
                             métrica de dinero principal y va siempre primera -- en Hoy es "A
                             cobrar en cancha" y va primera, acá "Dinero ingresado" quedaba
                             última, como si el énfasis visual más fuerte no siguiera ninguna
                             regla entre pantallas. -->
                        <div class="bc-kpi-row bc-kpi-row-5 mb-8">
                            <div class="bc-kpi lead">
                                <div class="bc-kpi-label">Dinero ingresado</div>
                                <div class="bc-kpi-num">$<?php echo formatearPeso($totalRevenue); ?></div>
                            </div>
                            <div class="bc-kpi">
                                <div class="bc-kpi-label">Clientes visibles</div>
                                <div class="bc-kpi-num"><?php echo (int) ($stats['total_customers'] ?? 0); ?></div>
                            </div>
                            <div class="bc-kpi">
                                <div class="bc-kpi-label">Reservas registradas</div>
                                <div class="bc-kpi-num"><?php echo $totalBookingsCount; ?></div>
                            </div>
                            <div class="bc-kpi">
                                <div class="bc-kpi-label">Con próxima reserva</div>
                                <div class="bc-kpi-num"><?php echo (int) ($stats['with_upcoming_booking'] ?? 0); ?></div>
                            </div>
                            <div class="bc-kpi">
                                <div class="bc-kpi-label">Cancelaciones</div>
                                <div class="bc-kpi-num"><?php echo $totalCancelledCount; ?></div>
                            </div>
                        </div>

                        <div class="card card-xl-stretch mb-8">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Base de clientes</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Buscá por nombre, teléfono o email y abrí una ficha con reservas, cancelaciones, próximas reservas e historial.</span>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <form method="GET" class="row g-5 align-items-end mb-7">
                                    <?php if ($isSuperAdmin) { ?>
                                        <div class="col-xl-3 col-md-6">
                                            <label class="form-label fw-semibold">Establecimiento</label>
                                            <select name="establishment_id" class="form-select form-select-solid">
                                                <option value="">Todos</option>
                                                <?php foreach ($establishments as $establishment) { ?>
                                                    <option value="<?php echo (int) $establishment->id; ?>" <?php echo $selectedEstablishmentId === (int) $establishment->id ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) $establishment->name); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="form-label fw-semibold">Cancha</label>
                                        <select name="field_id" class="form-select form-select-solid">
                                            <option value="">Todas</option>
                                            <?php foreach ($fields as $field) { ?>
                                                <option value="<?php echo (int) $field->id; ?>" <?php echo $selectedFieldId === (int) $field->id ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $field->name); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="form-label fw-semibold">Actividad</label>
                                        <!-- Item 17 (auditoría UX/UI): "risk" y el segmento por facturación ya
                                             los calculaba el backend pero no estaban en este select -- la
                                             pantalla ya sabía que Pali Alarcón canceló el 100% y no dejaba
                                             filtrar por eso. -->
                                        <select name="activity" class="form-select form-select-solid">
                                            <option value="">Todos</option>
                                            <option value="upcoming" <?php echo $selectedActivity === 'upcoming' ? 'selected' : ''; ?>>Con próxima reserva</option>
                                            <option value="inactive" <?php echo $selectedActivity === 'inactive' ? 'selected' : ''; ?>>No vuelve hace 60+ días</option>
                                            <option value="frequent" <?php echo $selectedActivity === 'frequent' ? 'selected' : ''; ?>>Frecuentes (5+ reservas)</option>
                                            <option value="risk" <?php echo $selectedActivity === 'risk' ? 'selected' : ''; ?>>Cancela más del 30%</option>
                                            <option value="top10" <?php echo $selectedActivity === 'top10' ? 'selected' : ''; ?>>Top 10 por facturación</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-md-12">
                                        <label class="form-label fw-semibold">Buscar</label>
                                        <input type="text" name="q" class="form-control form-control-solid" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, teléfono o email">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-3">
                                        <a href="clientes<?php echo $isSuperAdmin ? '' : ''; ?>" class="btn btn-light-secondary">Limpiar</a>
                                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                                    </div>
                                </form>

                                <?php if (empty($customers)) { ?>
                                    <div class="text-muted py-10 text-center">No se encontraron clientes con el filtro actual.</div>
                                <?php } else { ?>
                                    <!-- Item 17 (auditoría UX/UI): la pantalla ya tenía todos los datos
                                         para segmentar, pero no había forma de accionar sobre un grupo --
                                         seleccionar clientes acá y mandarlos directo a Enviar Mensaje. -->
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-light-secondary bg-opacity-50 rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk-seleccionar-todos-clientes">
                                            <label class="form-check-label fw-semibold" for="chk-seleccionar-todos-clientes">
                                                Seleccionar todos (esta página)
                                            </label>
                                        </div>
                                        <button type="button" id="btn-whatsapp-seleccionados" class="btn btn-sm btn-success" disabled>
                                            <i class="fa-brands fa-whatsapp me-2"></i>Enviar WhatsApp a seleccionados (<span id="count-seleccionados-clientes">0</span>)
                                        </button>
                                    </div>
                                    <div id="clientes-mobile-list" class="d-flex flex-column gap-3">
                                        <?php foreach ($customers as $customer) {
                                            $phoneDisplay = trim((string) ($customer['phone'] ?? ''));
                                            $phoneWaLink = preg_replace('/\D+/', '', $phoneDisplay);
                                        ?>
                                            <div class="bc-mobile-card">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <?php if ($phoneWaLink !== '') { ?>
                                                            <input type="checkbox" class="form-check-input chk-cliente-wa mt-1" value="<?php echo htmlspecialchars($phoneWaLink); ?>">
                                                        <?php } ?>
                                                        <div class="d-flex flex-column">
                                                        <span class="text-dark fw-bolder fs-6"><?php echo htmlspecialchars((string) $customer['full_name']); ?></span>
                                                        <span class="text-muted fs-7">
                                                            <?php if ($phoneWaLink !== '') { ?>
                                                                <a href="https://wa.me/<?php echo htmlspecialchars($phoneWaLink); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($phoneDisplay); ?></a>
                                                            <?php } else { ?>
                                                                Sin telefono
                                                            <?php } ?>
                                                        </span>
                                                        <?php if ($isSuperAdmin && $selectedEstablishmentId === 0) { ?>
                                                            <span class="text-muted fs-8"><?php echo htmlspecialchars((string) $customer['establishment_name']); ?></span>
                                                        <?php } ?>
                                                        </div>
                                                    </div>
                                                    <a class="btn btn-sm btn-light-primary" href="cliente?id=<?php echo (int) $customer['customer_id']; ?>&establishment_id=<?php echo (int) $customer['establishment_id']; ?>">Ver ficha</a>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                                    <span class="text-muted fs-8">Reservas</span>
                                                    <span class="fw-semibold text-gray-800"><?php echo (int) ($customer['total_bookings'] ?? 0); ?> (canceladas: <?php echo (int) ($customer['cancelled_bookings'] ?? 0); ?>)</span>
                                                </div>
                                                <?php $cancelRateCard = (int) ($customer['total_bookings'] ?? 0) > 0 ? round(((int) ($customer['cancelled_bookings'] ?? 0) / (int) $customer['total_bookings']) * 100, 2) : 0.0; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-8">Tasa de cancelación</span>
                                                    <span class="fw-semibold <?php echo $cancelRateCard >= 30 ? 'text-danger' : 'text-gray-800'; ?>"><?php echo formatearPeso($cancelRateCard); ?>%</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-8">Dinero generado</span>
                                                    <span class="fw-bolder text-success">$<?php echo formatearPeso((float) ($customer['paid_total'] ?? 0)); ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-8">Próxima reserva</span>
                                                    <span class="text-gray-800 fs-8"><?php echo !empty($customer['next_booking_date']) ? htmlspecialchars(showDate((string) $customer['next_booking_date'])) : 'Sin próxima reserva'; ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div id="clientes-table-wrapper" class="table-responsive">
                                        <table id="tabla-clientes" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bolder text-muted">
                                                    <th class="no-sort w-25px"></th>
                                                    <th>Cliente</th>
                                                    <?php if ($isSuperAdmin && $selectedEstablishmentId === 0) { ?>
                                                        <th>Establecimiento</th>
                                                    <?php } ?>
                                                    <th>Reservas</th>
                                                    <th>Dinero generado</th>
                                                    <th>Próxima reserva</th>
                                                    <th class="text-end no-sort">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($customers as $customer) {
                                                    $phoneDisplay = trim((string) ($customer['phone'] ?? ''));
                                                    $phoneWaLink = preg_replace('/\D+/', '', $phoneDisplay);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($phoneWaLink !== '') { ?>
                                                                <input type="checkbox" class="form-check-input chk-cliente-wa" value="<?php echo htmlspecialchars($phoneWaLink); ?>">
                                                            <?php } ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="text-dark fw-bolder fs-6"><?php echo htmlspecialchars((string) $customer['full_name']); ?></span>
                                                                <span class="text-muted fs-7">
                                                                    <?php if ($phoneWaLink !== '') { ?>
                                                                        <a href="https://wa.me/<?php echo htmlspecialchars($phoneWaLink); ?>" target="_blank" rel="noopener noreferrer">
                                                                            <?php echo htmlspecialchars($phoneDisplay); ?>
                                                                        </a>
                                                                    <?php } else { ?>
                                                                        Sin telefono
                                                                    <?php } ?>
                                                                </span>
                                                                <?php if ((string) ($customer['email'] ?? '') !== '') { ?>
                                                                    <span class="text-muted fs-8"><?php echo htmlspecialchars((string) $customer['email']); ?></span>
                                                                <?php } ?>
                                                            </div>
                                                        </td>
                                                        <?php if ($isSuperAdmin && $selectedEstablishmentId === 0) { ?>
                                                            <td><?php echo htmlspecialchars((string) $customer['establishment_name']); ?></td>
                                                        <?php } ?>
                                                        <td data-order="<?php echo (int) ($customer['total_bookings'] ?? 0); ?>">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold text-gray-800"><?php echo plural((int) ($customer['total_bookings'] ?? 0), 'reserva'); ?></span>
                                                                <?php $cancelRateRow = (int) ($customer['total_bookings'] ?? 0) > 0 ? round(((int) ($customer['cancelled_bookings'] ?? 0) / (int) $customer['total_bookings']) * 100, 2) : 0.0; ?>
                                                                <span class="text-muted fs-7">Canceladas: <?php echo (int) ($customer['cancelled_bookings'] ?? 0); ?> <span class="<?php echo $cancelRateRow >= 30 ? 'text-danger fw-bold' : ''; ?>">(<?php echo formatearPeso($cancelRateRow); ?>%)</span></span>
                                                                <span class="text-muted fs-8">Última reserva: <?php echo !empty($customer['last_booking_date']) ? htmlspecialchars(showDate((string) $customer['last_booking_date'])) : 'Sin historial'; ?></span>
                                                            </div>
                                                        </td>
                                                        <td data-order="<?php echo (float) ($customer['paid_total'] ?? 0); ?>">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bolder text-success">$<?php echo formatearPeso((float) ($customer['paid_total'] ?? 0)); ?></span>
                                                                <span class="text-muted fs-8">Promedio por reserva: $<?php echo formatearPeso((int) ($customer['total_bookings'] ?? 0) > 0 ? ((float) ($customer['paid_total'] ?? 0) / (int) ($customer['total_bookings'] ?? 0)) : 0); ?></span>
                                                            </div>
                                                        </td>
                                                        <td data-order="<?php echo !empty($customer['next_booking_date']) ? strtotime((string) $customer['next_booking_date']) : 0; ?>">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold text-gray-800"><?php echo plural((int) ($customer['upcoming_bookings'] ?? 0), 'pendiente'); ?></span>
                                                                <span class="text-muted fs-8">
                                                                    <?php echo !empty($customer['next_booking_date']) ? htmlspecialchars(showDate((string) $customer['next_booking_date'])) : 'Sin próxima reserva'; ?>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <a class="btn btn-sm btn-light-primary" href="cliente?id=<?php echo (int) $customer['customer_id']; ?>&establishment_id=<?php echo (int) $customer['establishment_id']; ?>">
                                                                Ver ficha
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                                <?php if ($pagination['total_pages'] > 1) : ?>
                                    <!-- Item 24 (auditoría UX/UI): sin esto los clientes se renderizaban
                                         todos de una, sin paginar ni contar -- con una base grande la
                                         pantalla se vuelve un scroll interminable. -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 gap-3">
                                        <div class="text-muted fs-7">
                                            Mostrando <?php echo count($customers) ? (($pagination['page'] - 1) * $pagination['per_page'] + 1) : 0; ?>–<?php echo min($pagination['page'] * $pagination['per_page'], $pagination['total']); ?> de <?php echo $pagination['total']; ?> <?php echo plural($pagination['total'], 'cliente'); ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($pagination['page'] > 1) : ?>
                                                <a class="btn btn-sm btn-light-primary" href="clientes?<?php echo htmlspecialchars(http_build_query(array_merge($paginationQuery, ['page' => $pagination['page'] - 1]))); ?>">← Anterior</a>
                                            <?php else : ?>
                                                <span class="btn btn-sm btn-light disabled">← Anterior</span>
                                            <?php endif; ?>
                                            <span class="btn btn-sm btn-light-secondary disabled">Página <?php echo $pagination['page']; ?> de <?php echo $pagination['total_pages']; ?></span>
                                            <?php if ($pagination['page'] < $pagination['total_pages']) : ?>
                                                <a class="btn btn-sm btn-light-primary" href="clientes?<?php echo htmlspecialchars(http_build_query(array_merge($paginationQuery, ['page' => $pagination['page'] + 1]))); ?>">Siguiente →</a>
                                            <?php else : ?>
                                                <span class="btn btn-sm btn-light disabled">Siguiente →</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
