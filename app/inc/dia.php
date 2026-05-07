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
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <div class="mb-13 text-center">
                    <h1 class="mb-3">Registrar Pago</h1>
                    <div class="text-muted fw-bold fs-5">
                        Cliente: <span class="text-gray-800 fw-bolder" id="modal-cliente">—</span>
                    </div>
                    <div class="text-muted fw-bold fs-7 mt-1" id="modal-canchaHora">—</div>
                </div>

                <div class="fv-row mb-8">
                    <div class="card card-bordered bg-light">
                        <div class="card-body py-4 px-5">
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="text-muted fs-8">Total</div>
                                    <div class="fw-bolder fs-5" id="modal-total">$0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted fs-8">Pagado</div>
                                    <div class="fw-bolder fs-5 text-success" id="modal-pagado">$0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted fs-8">Saldo</div>
                                    <div class="fw-bolder fs-5 text-warning" id="modal-saldo">$0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fv-row mb-8">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span>Monto que se cobra ahora</span>
                    </label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text fs-3 fw-bold text-gray-700">$</span>
                        <input type="number" id="modal-monto" class="form-control ps-3 fs-3 fw-bolder" min="0" step="0.01" />
                        <button type="button" id="modal-btn-saldo-completo" class="btn btn-light-primary fw-bold">Saldo total</button>
                    </div>
                    <div class="text-muted fs-7 mt-2">Si cobrás el saldo total, la reserva queda marcada como pagada.</div>
                </div>

                <div class="fv-row mb-10">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span>Nota (opcional)</span>
                    </label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text">
                            <i class="fa-solid fa-note-sticky fs-4"></i>
                        </span>
                        <input type="text" id="modal-nota" class="form-control ps-3 fw-bold" placeholder="Ej: Pagó con efectivo" />
                    </div>
                </div>

                <input type="hidden" id="modal-bookingId" />

                <div class="text-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="modal-btn-confirmar" class="btn btn-success">
                        <i class="fa-solid fa-check me-1"></i>Confirmar y Cerrar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
