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
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <div class="card-body">
                    <div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
                        <div class="post d-flex flex-column-fluid" id="kt_post">
                            <div id="kt_content_container" class="container-xxl">
                                <div class="card mb-5 mb-xl-10">
                                    <div class="card-body pt-9 pb-0">
                                        <div id="kt_billing_payment_tab_content" class="card-body tab-content">
                                            <!--begin::Tab panel-->
                                            <div id="kt_billing_creditcard" class="tab-pane fade show active" role="tabpanel">
                                                <div class="row gx-9 gy-6 justify-content-center ">
                                                    <!--begin::Col-->
                                                    <div class="col-xl-6">
                                                        <!--begin::Card-->
                                                        <div class="card card-dashed h-xl-100 flex-row flex-stack flex-wrap p-6">
                                                            <!--begin::Info-->
                                                            <div class="d-flex flex-column py-2">
                                                                <!--begin::Owner-->
                                                                <div class="d-flex align-items-center fs-4 fw-bolder mb-5">
                                                                    Mercado Pago
                                                                    <i style="cursor: help;" class="ms-2 fa-solid fa-circle-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Permitir pagos con Mercado Pago"></i>
                                                                </div>
                                                                <!--end::Owner-->
                                                                <!--begin::Wrapper-->
                                                                <div class="d-flex align-items-center">
                                                                    <!--begin::Icon-->
                                                                    <img src="assets/img/marcado_pago.png" alt="" class="me-4">
                                                                    <!--end::Icon-->
                                                                    <!--begin::Details-->
                                                                    <div>
                                                                        <div class="fs-4 fw-bolder"><?php echo Users::infoUser('token_id'); ?></div>
                                                                    </div>
                                                                    <!--end::Details-->
                                                                </div>
                                                                <!--end::Wrapper-->
                                                            </div>
                                                            <!--end::Info-->
                                                            <!--begin::Actions-->
                                                            <div class="d-flex align-items-center py-2">
                                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                                    <?php if(empty(Users::infoUser('token_id'))) : ?>
                                                                    <a href="<?php echo MercadoPago::getUrlOAuth() ?>" target="_blank" class="btn btn-primary">Vincular</a>
                                                                    <?php else : ?>
                                                                    <a  disabled target="_blank" class="btn btn-secondary">!Tu Cuenta Ya Esta Vinculada!</a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <!--end::Actions-->
                                                        </div>
                                                        <!--end::Card-->
                                                    </div>
                                                </div>
                                                <!--end::Row-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <?php inc('footer') ?>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>