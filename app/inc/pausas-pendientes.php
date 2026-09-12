<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <main id="contenido" tabindex="-1" class="content d-flex flex-column flex-column-fluid pt-5 pt-md-0">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">

                        <div class="card mb-5">
                            <div class="card-header border-0 pt-5">
                                <div class="card-title align-items-start flex-column">
                                    <h1 class="card-label fw-bolder fs-3 mb-1">Pausas Pendientes</h1>
                                    <span class="text-muted fw-bold fs-7">Gestioná las reservas fijas pausadas y su estado por cancha</span>
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

                        <!-- TXT-06: estos contadores son un total de las tres canchas/estados,
                             no una respuesta al filtro de arriba (por diseño: son un resumen
                             general). Antes eso no se explicaba en ningún lado y encima no
                             llevaban a ningún lado. Ahora dicen su alcance y son un atajo al
                             filtro correspondiente. -->
                        <div class="row g-5 g-xl-8 mb-2">
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-warning kpi-filtro-atajo" role="button" tabindex="0" data-status="paused">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Pausadas</div>
                                        <div class="fw-bold fs-2" id="kpi-pausadas">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-success kpi-filtro-atajo" role="button" tabindex="0" data-status="active">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Activas</div>
                                        <div class="fw-bold fs-2" id="kpi-activas">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-light-danger kpi-filtro-atajo" role="button" tabindex="0" data-status="cancelled">
                                    <div class="card-body p-4">
                                        <div class="text-muted fs-7">Canceladas</div>
                                        <div class="fw-bold fs-2" id="kpi-canceladas">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted fs-8 mb-5">Totales de todas tus canchas, en cualquier estado. Tocá una tarjeta para ir a ese filtro.</p>

                        <div id="lista-pausas" class="row g-4">
                            <div class="col-12 text-center py-10 text-muted">Cargando...</div>
                        </div>

                    </div>
                </div>
            </main>
            <?php inc('footer') ?>
        </div>
    </div>
</div>

<!-- Modal Acción -->
<div class="modal fade" id="modalReview" tabindex="-1" aria-hidden="true" role="dialog" aria-labelledby="modalReviewTitle">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReviewTitle">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="review-id" />
                <input type="hidden" id="review-decision" />
                <div id="review-info" class="mb-3"></div>
                <label class="form-label" for="review-note">Nota (opcional)</label>
                <textarea id="review-note" class="form-control" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="review-confirmar" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>
