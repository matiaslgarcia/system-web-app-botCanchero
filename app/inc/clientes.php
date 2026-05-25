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
$totalBookingsCount = 0;
$totalCancelledCount = 0;
$totalRevenue = 0.0;
foreach ($customers as $customer) {
    $totalBookingsCount += (int) ($customer['total_bookings'] ?? 0);
    $totalCancelledCount += (int) ($customer['cancelled_bookings'] ?? 0);
    $totalRevenue += (float) ($customer['paid_total'] ?? 0);
}
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

                        <div class="row g-5 g-xl-8 mb-8">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-primary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Clientes visibles</div>
                                        <div class="fw-bold fs-2"><?php echo (int) ($stats['total_customers'] ?? 0); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Reservas registradas</div>
                                        <div class="fw-bold fs-2"><?php echo $totalBookingsCount; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Con próxima reserva</div>
                                        <div class="fw-bold fs-2"><?php echo (int) ($stats['with_upcoming_booking'] ?? 0); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-danger">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Cancelaciones</div>
                                        <div class="fw-bold fs-2"><?php echo $totalCancelledCount; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Dinero ingresado</div>
                                        <div class="fw-bold fs-2">$<?php echo number_format($totalRevenue, 2); ?></div>
                                    </div>
                                </div>
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
                                        <select name="activity" class="form-select form-select-solid">
                                            <option value="">Todos</option>
                                            <option value="upcoming" <?php echo $selectedActivity === 'upcoming' ? 'selected' : ''; ?>>Con próxima reserva</option>
                                            <option value="inactive" <?php echo $selectedActivity === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                                            <option value="frequent" <?php echo $selectedActivity === 'frequent' ? 'selected' : ''; ?>>Frecuentes</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
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
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bolder text-muted">
                                                    <th>Cliente</th>
                                                    <?php if ($isSuperAdmin && $selectedEstablishmentId === 0) { ?>
                                                        <th>Establecimiento</th>
                                                    <?php } ?>
                                                    <th>Reservas</th>
                                                    <th>Dinero generado</th>
                                                    <th>Próxima reserva</th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($customers as $customer) { ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <?php
                                                                $phoneDisplay = trim((string) ($customer['phone'] ?? ''));
                                                                $phoneWaLink = preg_replace('/\D+/', '', $phoneDisplay);
                                                                ?>
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
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold text-gray-800"><?php echo (int) ($customer['total_bookings'] ?? 0); ?> reservas</span>
                                                                <span class="text-muted fs-7">Canceladas: <?php echo (int) ($customer['cancelled_bookings'] ?? 0); ?></span>
                                                                <span class="text-muted fs-8">Ultima reserva: <?php echo !empty($customer['last_booking_date']) ? htmlspecialchars(showDate((string) $customer['last_booking_date'])) : 'Sin historial'; ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bolder text-success">$<?php echo number_format((float) ($customer['paid_total'] ?? 0), 2); ?></span>
                                                                <span class="text-muted fs-8">Promedio por reserva: $<?php echo number_format((int) ($customer['total_bookings'] ?? 0) > 0 ? ((float) ($customer['paid_total'] ?? 0) / (int) ($customer['total_bookings'] ?? 0)) : 0, 2); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold text-gray-800"><?php echo (int) ($customer['upcoming_bookings'] ?? 0); ?> pendiente/s</span>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
