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
                                    <span class="card-label fw-bolder fs-3 mb-1">Reservas Fijas Pausadas</span>
                                    <span class="text-muted fw-bold fs-7">Gestiona pausas activas y su estado por cancha</span>
                                </div>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-3">
                                        <select id="filtroCancha" class="form-select form-select-sm w-200px h-40px">
                                            <option value="">Todas las canchas</option>
                                            <?php foreach(Canchas::getByIdUser() as $cancha){ ?>
                                                <option value="<?php echo $cancha->id ?>"><?php echo $cancha->name ?></option>
                                            <?php } ?>
                                        </select>
                                        <select id="filtroEstado" class="form-select form-select-sm w-150px h-40px">
                                            <option value="paused">Pausadas</option>
                                            <option value="active">Activas</option>
                                            <option value="cancelled">Canceladas</option>
                                            <option value="">Todas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-5 g-xl-8 mb-5">
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-warning">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Pausadas</div>
                                        <div class="fw-bold fs-2" id="kpi-pausadas">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-success">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Activas</div>
                                        <div class="fw-bold fs-2" id="kpi-activas">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-danger">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Canceladas</div>
                                        <div class="fw-bold fs-2" id="kpi-canceladas">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="lista-pausas" class="row g-4">
                            <div class="col-12 text-center py-10 text-muted">Cargando...</div>
                        </div>

                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
        </div>
    </div>
</div>

<!-- Modal Acción -->
<div class="modal fade" id="modalReview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="review-id" />
                <input type="hidden" id="review-decision" />
                <div id="review-info" class="mb-3"></div>
                <label class="form-label">Nota (opcional)</label>
                <textarea id="review-note" class="form-control" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="review-confirmar" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>
