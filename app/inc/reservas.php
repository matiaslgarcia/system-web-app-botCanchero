<div class="d-flex flex-column flex-root">
    <!--begin::Page-->
    <div class="page d-flex flex-row flex-column-fluid">
        <!--begin::Aside-->
        <?php inc('sidebar') ?>
        <!--end::Aside-->
        <!--begin::Wrapper-->
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <!--begin::Header-->
            <?php inc('header') ?>
            <!--end::Header-->
            <!--begin::Content-->
            <div class="content d-flex flex-column flex-column-fluid pt-5 pt-md-0" id="kt_content">
                <!--begin::Post-->
                <div class="post d-flex flex-column-fluid " id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <div class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Calendario de Reservas</span>
                                    <span class="text-muted fw-bold fs-7">Visualiza y gestiona las reservas de todas tus canchas</span>
                                </div>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-3">
                                        <select id="filtroCancha" class="form-select form-select-sm w-200px" data-control="select2" data-placeholder="Todas las canchas">
                                            <option value="">Todas las canchas</option>
                                            <?php 
                                                foreach(Canchas::getByIdUser() as $cancha){
                                                    echo '<option value="'.$cancha->id.'">'.$cancha->name.'</option>';
                                                }
                                            ?>
                                        </select>
                                        <a href="add-booking" class="btn btn-sm btn-primary h-40px d-flex align-items-center px-4">
                                            <i class="fa-solid fa-plus me-2"></i>Agregar Reserva
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                                    <span class="badge badge-light d-flex align-items-center gap-2">
                                        <span class="bc-origin-dot bc-origin-dot-bot"></span> Desde Bot
                                    </span>
                                    <span class="badge badge-light d-flex align-items-center gap-2">
                                        <span class="bc-origin-dot bc-origin-dot-web"></span> Desde Web
                                    </span>
                                    <span class="badge badge-light-warning d-flex align-items-center gap-2">
                                        <span class="bc-origin-dot bc-origin-dot-fixed"></span> Reserva Fija
                                    </span>
                                    <span class="badge badge-light-danger d-flex align-items-center gap-2">
                                        <span class="bc-origin-dot bc-origin-dot-fixed-cancelled"></span> Fija Cancelada
                                    </span>
                                </div>
                                <!--begin::Calendar-->
                                <div id="reservas"></div>
                                <!--end::Calendar-->
                            </div>
                            <!--end::Card body-->
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Post-->
            </div>
            <?php inc('footer') ?>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>
