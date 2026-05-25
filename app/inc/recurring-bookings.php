<?php
    $bookingFields = Canchas::getBookingFieldSummariesByUser();
    $fieldCount = count($bookingFields);
    $defaultField = $fieldCount > 0 ? $bookingFields[0] : null;
    $showFieldSelector = $fieldCount > 1;
    $showSlotSelector = !$showFieldSelector && $defaultField && (int) ($defaultField->threshold ?? 1) > 1;
?>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <div class="content d-flex flex-column flex-column-fluid pt-5 pt-md-0" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">

                        <div class="card mb-5">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Reservas Fijas</span>
                                    <span class="text-muted fw-bold fs-7">Gestiona los compromisos semanales de tus clientes</span>
                                </div>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-3 recurring-toolbar">
                                        <select id="filtroEstado" class="form-select form-select-sm w-150px h-40px">
                                            <option value="active">Activas</option>
                                            <option value="paused">Pausadas</option>
                                            <option value="cancelled">Canceladas</option>
                                            <option value="">Todas</option>
                                        </select>
                                        <button type="button" class="btn btn-primary btn-sm h-40px" data-bs-toggle="modal" data-bs-target="#modalNueva">
                                            <i class="fa-solid fa-plus me-2"></i>Nueva Reserva Fija
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-0">
                                <div id="recurring-mobile-list" class="p-3"></div>
                                <div id="recurring-table-wrapper" class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-3 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4">Día</th>
                                                <th>Hora</th>
                                                <th>Duración</th>
                                                <th>Cancha</th>
                                                <th>Cliente</th>
                                                <th>Teléfono</th>
                                                <th>Vigencia</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-end pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla-fijas-body">
                                            <tr><td colspan="9" class="text-center py-10 text-muted">Cargando...</td></tr>
                                        </tbody>
                                    </table>
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

<!-- Modal Nueva Reserva Fija -->
<div class="modal fade" id="modalNueva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-calendar-plus me-2"></i>Nueva Reserva Fija</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-nueva-fija">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Cliente existente</label>
                            <select id="nueva-cliente" class="form-select" data-placeholder="Buscar cliente...">
                                <option></option>
                            </select>
                            <div class="form-text">Opcional. Si no existe, completá teléfono y nombre debajo.</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label <?php echo $showFieldSelector ? 'required' : ''; ?>">
                                <?php echo $showFieldSelector ? 'Cancha' : 'Teléfono'; ?>
                            </label>
                            <?php if ($showFieldSelector) : ?>
                                <select id="nueva-cancha" class="form-select" data-placeholder="Selecciona cancha...">
                                    <option value="">Selecciona cancha...</option>
                                    <?php foreach ($bookingFields as $c) : ?>
                                        <option value="<?php echo (int) $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else : ?>
                                <input type="hidden" id="nueva-cancha" value="<?php echo $defaultField ? (int) $defaultField->id : 0; ?>">
                                <input type="text" id="nueva-customer-phone" class="form-control" placeholder="Ej: 5491122334455" autocomplete="tel">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label <?php echo $showFieldSelector ? '' : 'required'; ?>">Nombre</label>
                            <input type="text" id="nueva-customer-name" class="form-control" placeholder="Nombre del cliente" autocomplete="name">
                        </div>
                        <div class="col-md-6 mb-4">
                            <?php if ($showFieldSelector) : ?>
                                <label class="form-label">Teléfono</label>
                                <input type="text" id="nueva-customer-phone" class="form-control" placeholder="Ej: 5491122334455" autocomplete="tel">
                            <?php elseif ($showSlotSelector) : ?>
                                <label class="form-label">Cupo</label>
                                <select id="nueva-slot" class="form-select">
                                    <?php for ($slot = 1; $slot <= (int) $defaultField->threshold; $slot++) : ?>
                                        <option value="<?php echo $slot; ?>">Cupo <?php echo $slot; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="form-text">Referencia visual. El sistema asigna el cupo automáticamente según disponibilidad.</div>
                            <?php else : ?>
                                <div class="h-100 d-flex align-items-end">
                                    <div class="form-text mb-0">Completá un cliente existente o cargá uno nuevo para crear la reserva fija.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label required">Día de la semana</label>
                            <select id="nueva-dow" class="form-select">
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miércoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sábado</option>
                                <option value="7">Domingo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label required">Horario</label>
                            <select id="nueva-start" class="form-select" data-placeholder="Primero elige cancha y día...">
                                <option value="">Selecciona horario...</option>
                            </select>
                            <div id="nueva-start-helper" class="form-text text-muted mt-2">
                                Seleccioná el día y, si corresponde, la cancha para habilitar horarios.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label required">Válida Desde</label>
                            <input type="date" id="nueva-from" class="form-control" value="<?php echo date('Y-m-d') ?>" />
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Válida Hasta (opcional)</label>
                            <input type="date" id="nueva-until" class="form-control" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btn-guardar-fija" class="btn btn-primary" disabled>
                    <span class="indicator-label">Guardar Reserva</span>
                    <span class="indicator-progress">Guardando... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mover horario -->
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

<!-- Modal Cancelar -->
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
