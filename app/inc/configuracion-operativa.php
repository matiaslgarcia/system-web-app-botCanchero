<?php
$selectedEstablishmentId = (int) ($_GET['establishment_id'] ?? 0);
$dashboard = BusinessRules::getDashboardData($selectedEstablishmentId);
$selectedEstablishmentId = (int) ($dashboard['selected_establishment_id'] ?? 0);
$selectedEstablishment = $dashboard['selected_establishment'] ?? null;
$establishments = $dashboard['establishments'] ?? [];
$rules = $dashboard['rules'] ?? BusinessRules::defaults();
$summaryItems = $dashboard['summary_items'] ?? [];
$featureEnabled = (bool) ($dashboard['feature_enabled'] ?? true);
$isSuperAdmin = Users::isSuperAdmin();
$canEdit = $selectedEstablishmentId > 0 && ($featureEnabled || $isSuperAdmin);
$reminderCsv = htmlspecialchars((string) ($rules['reminder_lead_minutes_csv'] ?? '720,360'));
$selfServiceEnabled = (int) ($rules['allow_customer_self_service'] ?? 1) === 1;
$cancelEnabled = (int) ($rules['allow_customer_cancel'] ?? 1) === 1;
$rescheduleEnabled = (int) ($rules['allow_customer_reschedule'] ?? 1) === 1;
$maxFutureBookingDays = (int) ($rules['max_future_booking_days'] ?? 30);
$embedded = !empty($configuracionOperativaEmbedded);
$redirectUrl = trim((string) ($configuracionOperativaRedirectUrl ?? ''));
$switcherBaseUrl = $redirectUrl !== '' ? $redirectUrl : 'configuracion-operativa';
$switcherHash = $embedded && $redirectUrl !== '' ? '#kt_user_operational_tab' : '';
?>
<?php if (!$embedded) { ?>
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
                                <h1 class="text-dark fw-bolder fs-2 mb-2">Configuración Operativa</h1>
                                <span class="text-muted fs-6">Definí las reglas que usa hoy el bot de WhatsApp para autoservicio, cancelaciones y re-agendados.</span>
                            </div>
                            <?php if ($selectedEstablishment) { ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-light-primary fs-7">
                                        <?php echo htmlspecialchars((string) ($selectedEstablishment->name ?? 'Establecimiento')); ?>
                                    </span>
                                    <?php if (!$featureEnabled) { ?>
                                        <span class="badge badge-light-warning fs-7">Módulo no habilitado</span>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
<?php } else { ?>
<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0 pt-6">
        <div class="card-title align-items-start flex-column">
            <h3 class="fw-bolder m-0">Configuración Operativa</h3>
            <span class="text-muted mt-2 fw-semibold fs-7">Definí las reglas que usa hoy el bot de WhatsApp para autoservicio, cancelaciones y re-agendados.</span>
        </div>
        <?php if ($selectedEstablishment) { ?>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-light-primary fs-7">
                    <?php echo htmlspecialchars((string) ($selectedEstablishment->name ?? 'Establecimiento')); ?>
                </span>
                <?php if (!$featureEnabled) { ?>
                    <span class="badge badge-light-warning fs-7">Módulo no habilitado</span>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <div class="card-body pt-2">
<?php } ?>

                        <?php if (!$dashboard['ready']) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <span class="svg-icon svg-icon-2hx svg-icon-warning me-4">
                                    <i class="fa-solid fa-triangle-exclamation fs-1"></i>
                                </span>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Infraestructura pendiente</h4>
                                    <span>Para usar esta pantalla hay que aplicar la migración `migrations/013_business_rules.sql`.</span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($dashboard['ready'] && !$featureEnabled && !$isSuperAdmin) { ?>
                            <div class="alert alert-warning d-flex align-items-start p-5 mb-8">
                                <span class="svg-icon svg-icon-2hx svg-icon-warning me-4">
                                    <i class="fa-solid fa-lock fs-1"></i>
                                </span>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Módulo no disponible para tu plan</h4>
                                    <span>Este establecimiento todavía no tiene habilitada la configuración operativa avanzada. Podés activarla desde `superAdmin` con `Planes y Módulos`.</span>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row gy-5 g-xl-8 mb-8">
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch">
                                    <div class="card-body">
                                        <div class="fs-6 fw-semibold text-muted mb-2">Bot habilitado</div>
                                        <div class="fs-2 fw-bolder"><?php echo $selfServiceEnabled ? 'Sí' : 'No'; ?></div>
                                        <div class="text-gray-600 mt-2">Marcá si el cliente puede gestionar su reserva desde el bot de WhatsApp o si todo queda manual.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch">
                                    <div class="card-body">
                                        <div class="fs-6 fw-semibold text-muted mb-2">Cambios por bot</div>
                                        <div class="fs-2 fw-bolder">
                                            <?php if ($cancelEnabled || $rescheduleEnabled) { ?>
                                                <?php echo (int) max((int) ($rules['customer_cancel_min_hours'] ?? 0), (int) ($rules['customer_reschedule_min_hours'] ?? 0)); ?> h
                                            <?php } else { ?>
                                                Solo por admin
                                            <?php } ?>
                                        </div>
                                        <div class="text-gray-600 mt-2">Resume la anticipación que el bot exige para cancelar o re-agendar una reserva.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch">
                                    <div class="card-body">
                                        <div class="fs-6 fw-semibold text-muted mb-2">Límite futuro</div>
                                        <div class="fs-2 fw-bolder"><?php echo $maxFutureBookingDays; ?> d</div>
                                        <div class="text-gray-600 mt-2">Define hasta cuántos días hacia adelante el bot permite mover una reserva.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row gy-5 g-xl-8">
                            <?php if ($isSuperAdmin) { ?>
                                <div class="col-xxl-4">
                                    <div class="card card-xl-stretch mb-5 mb-xl-8">
                                        <div class="card-header border-0 pt-5">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-bolder fs-3 mb-1">Establecimientos</span>
                                                <span class="text-muted mt-1 fw-semibold fs-7">Seleccioná cuál querés configurar</span>
                                            </h3>
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="list-group">
                                                <?php foreach ($establishments as $establishment) { ?>
                                                    <?php $current = (int) ($establishment->id ?? 0) === $selectedEstablishmentId; ?>
                                                    <?php
                                                    $establishmentSwitcherUrl = $switcherBaseUrl
                                                        . (strpos($switcherBaseUrl, '?') === false ? '?' : '&')
                                                        . 'establishment_id=' . (int) $establishment->id
                                                        . $switcherHash;
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($establishmentSwitcherUrl); ?>"
                                                       class="list-group-item list-group-item-action <?php echo $current ? 'active' : ''; ?>">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <h5 class="mb-1"><?php echo htmlspecialchars((string) ($establishment->name ?? 'Establecimiento')); ?></h5>
                                                            <small><?php echo htmlspecialchars((string) ($establishment->plan_name ?? 'Sin plan')); ?></small>
                                                        </div>
                                                        <small><?php echo htmlspecialchars((string) ($establishment->subscription_status ?? 'sin estado')); ?></small>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-xxl-<?php echo $isSuperAdmin ? '8' : '12'; ?>">
                                <div class="card card-xl-stretch mb-5 mb-xl-8">
                                    <div class="card-header border-0 pt-5">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bolder fs-3 mb-1">Políticas del establecimiento</span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">Estas reglas impactan hoy en el bot de WhatsApp productivo y en las validaciones de reservas asociadas.</span>
                                        </h3>
                                    </div>
                                    <div class="card-body py-3">
                                        <?php if ($selectedEstablishmentId <= 0) { ?>
                                            <div class="text-muted py-8 text-center">No hay establecimiento seleccionado.</div>
                                        <?php } else { ?>
                                            <form id="form-business-rules" <?php echo $redirectUrl !== '' ? 'data-redirect-url="' . htmlspecialchars($redirectUrl) . '"' : ''; ?>>
                                                <input type="hidden" name="establishment_id" value="<?php echo $selectedEstablishmentId; ?>">
                                                <input type="hidden" name="reminder_lead_minutes_csv" id="reminder_lead_minutes_csv" value="<?php echo $reminderCsv; ?>">
                                                <input type="hidden" name="allow_customer_view_balance" value="<?php echo (int) ($rules['allow_customer_view_balance'] ?? 1); ?>">
                                                <input type="hidden" name="allow_customer_pause_request" value="<?php echo (int) ($rules['allow_customer_pause_request'] ?? 1); ?>">
                                                <input type="hidden" name="pause_requires_approval" value="<?php echo (int) ($rules['pause_requires_approval'] ?? 1); ?>">
                                                <input type="hidden" name="allow_customer_transfer" value="<?php echo (int) ($rules['allow_customer_transfer'] ?? 0); ?>">
                                                <input type="hidden" name="allow_waitlist" value="<?php echo (int) ($rules['allow_waitlist'] ?? 0); ?>">
                                                <input type="hidden" name="allow_shared_payments" value="<?php echo (int) ($rules['allow_shared_payments'] ?? 0); ?>">
                                                <input type="hidden" name="refund_policy" value="<?php echo htmlspecialchars((string) ($rules['refund_policy'] ?? 'manual')); ?>">
                                                <input type="hidden" name="no_show_policy" value="<?php echo htmlspecialchars((string) ($rules['no_show_policy'] ?? 'charge_full')); ?>">
                                                <input type="hidden" name="hold_expiration_minutes" value="<?php echo (int) ($rules['hold_expiration_minutes'] ?? 15); ?>">
                                                <input type="hidden" name="public_policy_text" value="<?php echo htmlspecialchars((string) ($rules['public_policy_text'] ?? '')); ?>">

                                                <div class="alert alert-primary d-flex align-items-start p-5 mb-8">
                                                    <span class="me-4 fs-2">1</span>
                                                    <div>
                                                        <h4 class="mb-1 text-primary">Opciones que hoy impactan en el bot</h4>
                                                        <div class="text-gray-700 fs-7">
                                                            Esta pantalla muestra solo las reglas que hoy puede configurar el canchero y que el bot de WhatsApp ya respeta en producción.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-5 mb-8">
                                                    <div class="col-lg-7">
                                                        <div class="card border-0 bg-light-primary h-100">
                                                            <div class="card-body p-7">
                                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-4 mb-6">
                                                                    <div>
                                                                        <h3 class="fw-bolder mb-2">1. Lo que puede hacer el cliente</h3>
                                                                        <div class="text-muted fs-7">Dejá visible solo lo que el bot realmente puede resolver hoy.</div>
                                                                    </div>
                                                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                                                        <input class="form-check-input" type="checkbox" name="allow_customer_self_service" value="1"
                                                                            <?php echo $selfServiceEnabled ? 'checked' : ''; ?>
                                                                            <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                        <span class="form-check-label fw-bold text-gray-800">Habilitar autoservicio</span>
                                                                    </label>
                                                                </div>

                                                                <div class="row g-4">
                                                                    <?php
                                                                    $simpleActions = [
                                                                        ['allow_customer_cancel', 'Cancelar una reserva', 'Permití cancelar sin llamar o escribir.'],
                                                                        ['allow_customer_reschedule', 'Mover una reserva', 'Ideal si querés evitar mensajes manuales por cambios.'],
                                                                        ['allow_customer_pause_request', 'Pausar una fija puntual', 'El cliente puede liberar solo una fecha de su reserva fija desde el bot.'],
                                                                    ];
                                                                    foreach ($simpleActions as [$name, $label, $help]) {
                                                                    ?>
                                                                        <div class="col-md-6">
                                                                            <label class="form-check form-check-custom form-check-solid align-items-start gap-3 p-4 rounded bg-white border border-gray-200 h-100">
                                                                                <input class="form-check-input mt-1" type="checkbox" name="<?php echo $name; ?>" value="1"
                                                                                    <?php echo (int) ($rules[$name] ?? 0) === 1 ? 'checked' : ''; ?>
                                                                                    <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                                <span>
                                                                                    <span class="fw-semibold d-block text-gray-800 mb-1"><?php echo $label; ?></span>
                                                                                    <span class="text-muted fs-7"><?php echo $help; ?></span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-5">
                                                        <div class="card border-0 bg-light-info h-100">
                                                            <div class="card-body p-7">
                                                                <h3 class="fw-bolder mb-2">2. Tiempos simples</h3>
                                                                <div class="text-muted fs-7 mb-6">Estas son las reglas reales que el bot consulta antes de dejar cancelar o re-agendar.</div>

                                                                <div class="mb-5">
                                                                    <label class="form-label fw-semibold">Anticipación mínima para cancelar</label>
                                                                    <select class="form-select form-select-solid" name="customer_cancel_min_hours" <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                        <?php foreach ([0 => 'En cualquier momento', 2 => '2 horas antes', 4 => '4 horas antes', 6 => '6 horas antes', 12 => '12 horas antes', 24 => '1 día antes'] as $value => $label) { ?>
                                                                            <option value="<?php echo $value; ?>" <?php echo ((int) ($rules['customer_cancel_min_hours'] ?? 6) === (int) $value) ? 'selected' : ''; ?>>
                                                                                <?php echo $label; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-5">
                                                                    <label class="form-label fw-semibold">Anticipación mínima para mover una reserva</label>
                                                                    <select class="form-select form-select-solid" name="customer_reschedule_min_hours" <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                        <?php foreach ([0 => 'En cualquier momento', 2 => '2 horas antes', 4 => '4 horas antes', 6 => '6 horas antes', 12 => '12 horas antes', 24 => '1 día antes'] as $value => $label) { ?>
                                                                            <option value="<?php echo $value; ?>" <?php echo ((int) ($rules['customer_reschedule_min_hours'] ?? 6) === (int) $value) ? 'selected' : ''; ?>>
                                                                                <?php echo $label; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-5">
                                                                    <label class="form-label fw-semibold">Anticipación mínima para pausar una fija puntual</label>
                                                                    <select class="form-select form-select-solid" name="customer_pause_min_hours" <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                        <?php foreach ([0 => 'En cualquier momento', 2 => '2 horas antes', 4 => '4 horas antes', 6 => '6 horas antes', 12 => '12 horas antes', 24 => '1 día antes'] as $value => $label) { ?>
                                                                            <option value="<?php echo $value; ?>" <?php echo ((int) ($rules['customer_pause_min_hours'] ?? 6) === (int) $value) ? 'selected' : ''; ?>>
                                                                                <?php echo $label; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <div class="text-muted fs-7 mt-2">Libera solo la fecha puntual elegida y no afecta las semanas siguientes.</div>
                                                                </div>

                                                                <div class="mb-0">
                                                                    <label class="form-label fw-semibold">Hasta cuántos días hacia adelante se puede reservar</label>
                                                                    <select class="form-select form-select-solid" name="max_future_booking_days" <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                        <?php foreach ([7 => '1 semana', 15 => '15 días', 30 => '30 días', 45 => '45 días', 60 => '60 días'] as $value => $label) { ?>
                                                                            <option value="<?php echo $value; ?>" <?php echo ((int) ($rules['max_future_booking_days'] ?? 30) === (int) $value) ? 'selected' : ''; ?>>
                                                                                <?php echo $label; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-5">
                                                    <div class="col-lg-12">
                                                        <div class="card border-0 bg-light-info h-100">
                                                            <div class="card-body p-7">
                                                                <h3 class="fw-bolder mb-4">Resumen rápido</h3>
                                                                <div class="d-flex flex-column gap-3">
                                                                    <?php foreach ($summaryItems as $item) { ?>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="bullet bullet-vertical bg-primary me-4"></span>
                                                                            <span class="fw-semibold text-gray-700"><?php echo htmlspecialchars((string) $item); ?></span>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="separator separator-dashed my-6"></div>
                                                                <div class="text-gray-700 fs-7">
                                                                    Si desde acá se entiende qué puede hacer el bot sin leer nada más, la configuración ya quedó bien enfocada.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if ($canEdit) { ?>
                                                    <div class="d-flex justify-content-end mt-8">
                                                        <button type="submit" class="btn btn-primary btn-lg">
                                                            Guardar configuración
                                                        </button>
                                                    </div>
                                                <?php } ?>
                                            </form>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php if (!$embedded) { ?>
                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
<?php } else { ?>
    </div>
</div>
<?php } ?>
