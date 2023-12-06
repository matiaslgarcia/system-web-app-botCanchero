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
            <div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        <!--begin::Navbar-->
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-body pt-9 pb-0">
                                <form id="add-booking-form" class="row pb-5">
                                    <div class="row">
                                        <div class="col-6 my-3">
                                            <label for="phone" class="form-label">Número de Telefono</label>
                                            <input type="text" name="phone" id="phone" class="form-control" autocomplete="phone">
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="full_name" class="form-label">Nombre</label>
                                            <input type="text" name="full_name" id="full_name" class="form-control">
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="email" class="form-label">Correo</label>
                                            <input type="email" name="email" id="email" class="form-control" autocomplete="email">
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="id_label" >Cancha</label>
                                            <select class="form-control" name="id_field" id="id_field">
                                                <option selected disabled value="" >--SELECIONE--</option>
                                                <?php foreach(Canchas::getByIdUser() AS $cancha)  {?>
                                                    <option value="<?php echo $cancha->id ?>" ><?php echo $cancha->name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="date_booking" class="form-label input-datepiker">Fecha Reserva</label>
                                            <input autocomplete="off" type="text" name="date_booking" id="date_booking" class="form-control" placeholder="dd/mm/yyyy" data-min-date="<?php echo date('d/m/Y')?>" >
                                        </div>
                                        
                                        <div class="col-6 my-3">
                                            <label for="time_booking" class="form-label">Hora Reserva</label>
                                            <select name="time_booking" id="time_booking" class="form-control">
                                                <option selected="true" disabled value="">--SELECIONE--</option>
                                            </select>
                                        </div>
                                        <div class="text-end">
                                            <button class="btn btn-primary">Crear Reserva</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Post-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <?php inc('footer') ?>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>