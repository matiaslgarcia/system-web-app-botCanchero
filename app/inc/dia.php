<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <div class="content d-flex flex-column flex-column-fluid pt-5 pt-md-0" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">

                        <!-- Header con fecha + filtro de fecha -->
                        <div class="card mb-5">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Reservas del Día</span>
                                    <span class="text-muted fw-bold fs-7">Gestiona las reservas de hoy y realiza cobros presenciales</span>
                                </div>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" id="btnExportarPDF" class="btn btn-sm btn-light-danger h-40px d-flex align-items-center px-4">
                                            <i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
                                        </button>
                                        <select id="filtroCancha" class="form-select form-select-sm w-200px h-40px">
                                            <option value="">Todas las canchas</option>
                                            <?php 
                                                foreach(Canchas::getByIdUser() as $cancha){
                                                    echo '<option value="'.$cancha->id.'">'.$cancha->name.'</option>';
                                                }
                                            ?>
                                        </select>
                                        <input type="date" id="filtroFecha" class="form-control form-control-sm w-150px h-40px" value="<?php echo date('Y-m-d') ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen rápido del día -->
                        <div class="row g-5 g-xl-8 mb-5">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-primary">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Reservas hoy</div>
                                        <div class="fw-bold fs-2" id="kpi-total">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Pagadas</div>
                                        <div class="fw-bold fs-2" id="kpi-pagadas">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Con saldo pendiente</div>
                                        <div class="fw-bold fs-2" id="kpi-pendientes">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-light-info">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">A cobrar en cancha</div>
                                        <div class="fw-bold fs-2" id="kpi-saldo">$0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de reservas -->
                        <div class="card">
                            <div class="card-body p-0">
                                <div id="dia-mobile-list" class="p-3"></div>
                                <div id="dia-table-wrapper" class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-3 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4">Hora</th>
                                                <th>N° Cancha</th>
                                                <th>Cancha</th>
                                                <th>Cliente</th>
                                                <th>Teléfono</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Pagado</th>
                                                <th class="text-end">Saldo</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-end pe-4">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla-dia-body">
                                            <tr><td colspan="10" class="text-center py-10 text-muted">Cargando...</td></tr>
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

<!-- Modal Cobrar Saldo -->
<div class="modal fade" id="modalCobrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-cash-register me-2"></i>Cobrar saldo presencial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-bold" id="modal-cliente">—</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Cancha · Hora</small>
                            <div class="fw-bold" id="modal-canchaHora">—</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4">
                            <small class="text-muted">Total</small>
                            <div class="fw-bold" id="modal-total">$0</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Pagado</small>
                            <div class="fw-bold text-success" id="modal-pagado">$0</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Saldo</small>
                            <div class="fw-bold text-warning" id="modal-saldo">$0</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Monto que se cobra ahora</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="modal-monto" class="form-control" min="0" step="0.01" />
                        <button type="button" id="modal-btn-saldo-completo" class="btn btn-light-primary">Saldo total</button>
                    </div>
                    <small class="text-muted">Si cobrás el saldo total, la reserva queda marcada como pagada.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nota (opcional)</label>
                    <input type="text" id="modal-nota" class="form-control" placeholder="Ej: Pagó con efectivo" />
                </div>

                <input type="hidden" id="modal-bookingId" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="modal-btn-confirmar" class="btn btn-primary">
                    <i class="fa-solid fa-check me-1"></i>Confirmar cobro
                </button>
            </div>
        </div>
    </div>
</div>
