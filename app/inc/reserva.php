<?php $reserva = Booking::getById($_GET['reserva']);
var_dump($_GET['reserva']);
?>
<?php $total = Booking::getTotalById($_GET['reserva']);
var_dump($_GET['reserva']);
?>
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
            <div class="content d-flex flex-column flex-column-fluid p-md-0" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        <!--begin::Layout-->
                        <div class="d-flex flex-column flex-lg-row">
                            <!--begin::Content-->
                            <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
                                <!--begin::Form-->
                                <form class="form" action="#" id="kt_subscriptions_create_new">
                                    <!--begin::Customer-->

                                    <!--end::Customer-->
                                    <!--begin::Pricing-->
                                    <div class="card card-flush pt-3 mb-5 mb-lg-10">
                                        <!--begin::Card header-->
                                        <div class="card-header">
                                            <!--begin::Card title-->
                                            <div class="card-title">
                                                <h2 class="fw-bolder">Historial</h2>
                                                <span class="mx-3 badge badge-light-<?php echo $reserva->status_color ?>"><?php echo $reserva->status_name ?></span>
                                            </div>
                                            <div class="me-0">
                                                <button class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    <i class="fa-solid fa-bars"></i>
                                                </button>
                                                <!--begin::Menu 3-->
                                                <div id="menu-action-reserva" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true" style="">
                                                    <!--begin::Heading-->
                                                    <div class="menu-item px-3">
                                                        <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Acciones</div>
                                                    </div>
                                                    <!--end::Heading-->
                                                    <!--begin::Menu item-->
                                                    <?php if($reserva->status_id != 2) : ?>
                                                    <div class="menu-item px-3 menu-action-reserva-action">
                                                        <span class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#reagendar-reserva">Re-Agendar</span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div id="btn-action-cancelar-reserva" data-id-reserva="<?php echo $reserva->id ?>" class="menu-item px-3 ">
                                                        <span class="menu-link px-3">Cancelar Reserva</span>
                                                    </div>
                                                    <!--end::Menu item-->
                                                </div>
                                                <!--end::Menu 3-->
                                            </div>
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0">
                                            <!--begin::Table wrapper-->
                                            <div class="table-responsive">
                                                <!--begin::Table-->
                                                <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4" id="kt_subscription_products_table">
                                                    <!--begin::Table head-->
                                                    <thead>
                                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                                            <th class="min-w-100px">Fecha</th>
                                                            <th class="min-w-100px">Hora</th>
                                                            <th class="min-w-100px">Usuario</th>
                                                            <th class="min-w-100px">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <!--end::Table head-->
                                                    <!--begin::Table body-->
                                                    <tbody class="text-gray-600">
                                                        <?php foreach (Booking::getLogs($reserva->id) as $logs) { ?>
                                                            <tr>
                                                                <td><?php echo date('d/m/Y', strtotime($logs->fecha)) ?></td>
                                                                <td><?php echo date('h:i A', strtotime($logs->hora)) ?></td>
                                                                <td><?php echo $logs->user ?></td>
                                                                <td><span class="badge badge-light-<?php echo $logs->logs_color ?>"><?php echo $logs->logs_name ?></span></td>

                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <!--end::Table body-->
                                                </table>
                                                <!--end::Table-->
                                            </div>
                                            <!--end::Table wrapper-->
                                        </div>
                                        <!--end::Card body-->
                                    </div>
                                </form>
                                <!--end::Form-->
                            </div>
                            <!--end::Content-->
                            <!--begin::Sidebar-->
                            <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-350px mb-10 order-1 order-lg-2">
                                <!--begin::Card-->
                                <div class="card card-flush pt-3 mb-0" data-kt-sticky="true" data-kt-sticky-name="subscription-summary" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '250px', xl: '300px'}" data-kt-sticky-left="auto" data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <!--begin::Card title-->
                                        <div class="card-title">
                                            <h2>Datos de la Reserva</h2>
                                        </div>
                                        <!--end::Card title-->
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0 fs-6">
                                        <!--begin::Section-->
                                        <div class="mb-7">
                                            <div class="d-flex align-items-center mb-1">
                                                <!--begin::Name-->
                                                <span class="fw-bolder text-gray-800 text-hover-primary me-3">Jugador: </span><a href="#" class="fw-bolder text-gray-800 text-hover-primary me-2"><?php echo isset($reserva->customer_name) ? $reserva->customer_name : ''; ?></a>
                                            </div>
                                            <!--end::Details-->
                                            <!--begin::Email-->
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="fw-bolder text-gray-800 text-hover-primary me-3">Tel: </span><a href="https://wa.me/<?php echo isset($reserva->customer_phone) ? $reserva->customer_phone : ''; ?>" class="fw-bold text-gray-600 text-hover-primary"><?php echo $reserva->customer_phone ?></a>
                                            </div>
                                            <!--end::Email-->
                                        </div>
                                        <!--end::Section-->
                                        <!--begin::Seperator-->
                                        <div class="separator separator-dashed mb-7"></div>
                                        <div class="mb-7">
                                            <div class="d-flex align-items-center mb-3">
                                            <span class="fw-bolder text-gray-800 text-hover-primary me-3">Cancha: </span><span class="fw-bolder text-gray-800 text-hover-primary me-3"><?php echo isset($reserva->cancha) ? $reserva->cancha : ''; ?></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="fw-bolder text-gray-800 text-hover-primary me-3">Fecha: </span><span class="fw-bolder text-gray-800 text-hover-primary me-3"><?php echo isset($reserva->fecha) ? showDate($reserva->fecha) : ''; ?>
</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="fw-bolder text-gray-800 text-hover-primary me-3">Hora: </span><span class="fw-bolder text-gray-800 text-hover-primary me-3"><?php echo isset($reserva->hora) ? $reserva->hora : ''; ?></span>
                                            </div>
                                            <div class="separator separator-dashed mb-7"></div>
                                            <div class="d-flex align-items-center mb-3">
                                            <span class="fw-bolder text-gray-800 text-hover-primary me-3">Total Pagado: </span><span class="fw-bolder text-gray-800 text-hover-primary me-3">$ <?php echo isset($total->cant) ? $total->cant : '--'; ?></span>
                                            </div>
                                            <!--end::Email-->
                                        </div>
                                    </div>
                                    <!--end::Card body-->
                                </div>
                                <!--end::Card-->
                            </div>
                            <!--end::Sidebar-->
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Post-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->

            <?php inc('footer') ?>
            <?php modal('re-agendar') ?>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>
</div>