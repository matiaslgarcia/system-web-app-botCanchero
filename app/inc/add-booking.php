<?php
    $bookingFields = Canchas::getBookingFieldSummariesByUser();
    $fieldCount = count($bookingFields);
    $defaultField = $fieldCount > 0 ? $bookingFields[0] : null;
    $showFieldSelector = $fieldCount > 1;
    $showSlotSelector = !$showFieldSelector && $defaultField && (int) ($defaultField->threshold ?? 1) > 1;
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
                                        <?php if ($showFieldSelector) : ?>
                                            <div class="col-6 my-3">
                                                <label for="id_field" class="form-label">Cancha</label>
                                                <select class="form-control" name="id_field" id="id_field">
                                                    <option selected disabled value="">--SELECCIONE--</option>
                                                    <?php foreach ($bookingFields as $cancha) : ?>
                                                        <option value="<?php echo (int) $cancha->id; ?>"><?php echo htmlspecialchars($cancha->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php elseif ($defaultField) : ?>
                                            <input type="hidden" name="id_field" id="id_field" value="<?php echo (int) $defaultField->id; ?>">
                                        <?php else : ?>
                                            <div class="col-12 my-3">
                                                <div class="alert alert-danger mb-0">No hay canchas activas configuradas para crear reservas.</div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-6 my-3">
                                            <label for="date_booking" class="form-label input-datepiker">Fecha Reserva</label>
                                            <input autocomplete="off" type="text" name="date_booking" id="date_booking" class="form-control" placeholder="dd/mm/yyyy" data-min-date="<?php echo date('d/m/Y')?>" >
                                        </div>
                                        <?php if ($showSlotSelector) : ?>
                                            <div class="col-6 my-3">
                                                <label for="slot_display" class="form-label">Cupo</label>
                                                <select class="form-control" id="slot_display">
                                                    <?php for ($slot = 1; $slot <= (int) $defaultField->threshold; $slot++) : ?>
                                                        <option value="<?php echo $slot; ?>">Cupo <?php echo $slot; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                                <div class="form-text">Referencia visual. El sistema asigna el cupo automáticamente según disponibilidad.</div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="col-6 my-3">
                                            <label for="time_booking" class="form-label">Hora Reserva</label>
                                            <select name="time_booking" id="time_booking" class="form-control">
                                                <option selected="true" disabled value="">--SELECCIONE--</option>
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
