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
            <main id="contenido" tabindex="-1" class="content d-flex flex-column flex-column-fluid pt-5 pt-0">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        <!--begin::Navbar-->
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title flex-column align-items-start">
                                    <h1 class="fs-2 fw-bolder mb-1">Nueva reserva</h1>
                                    <div class="text-muted fs-7" id="booking-summary">Completá los datos del turno.</div>
                                </div>
                                <div class="card-toolbar">
                                    <a href="./" class="btn btn-sm btn-light">← Volver al calendario</a>
                                </div>
                            </div>
                            <div class="card-body pt-9 pb-0">
                                <form id="add-booking-form" class="row pb-5" method="post">
                                    <div class="row">
                                        <div class="col-6 my-3">
                                            <label for="phone" class="form-label">Número de Teléfono</label>
                                            <input type="tel" name="phone" id="phone" class="form-control" inputmode="numeric" autocomplete="tel" required>
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="full_name" class="form-label">Nombre</label>
                                            <input type="text" name="full_name" id="full_name" class="form-control" autocomplete="name" required>
                                        </div>
                                        <?php if ($showFieldSelector) : ?>
                                            <div class="col-6 my-3">
                                                <label for="id_field" class="form-label">Cancha</label>
                                                <select class="form-control" name="id_field" id="id_field" required>
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
                                            <input autocomplete="off" inputmode="none" type="text" name="date_booking" id="date_booking" class="form-control" placeholder="dd/mm/yyyy" data-min-date="<?php echo date('d/m/Y')?>" required>
                                        </div>
                                        <div class="col-6 my-3">
                                            <label for="time_booking" class="form-label">Hora Reserva</label>
                                            <select name="time_booking" id="time_booking" class="form-control" required>
                                                <option selected="true" disabled value="">--SELECCIONE--</option>
                                            </select>
                                        </div>
                                        <?php if ($showSlotSelector) : ?>
                                            <!-- RES-05: esto era un <select> que se veía editable pero no
                                                 hacía nada — el cupo lo asigna el sistema, no el usuario.
                                                 Un control que invita a decidir algo que no se decide. -->
                                            <div class="col-12 my-1">
                                                <div class="text-muted fs-8">
                                                    <i class="fa-solid fa-circle-info me-1"></i>
                                                    Esta cancha admite hasta <?php echo (int) $defaultField->threshold; ?> reservas simultáneas por horario. El cupo se asigna automáticamente según disponibilidad.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-end d-flex justify-content-end gap-3">
                                            <a href="./" class="btn btn-light">Cancelar</a>
                                            <button type="submit" class="btn btn-primary">Crear Reserva</button>
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
            </main>
            <!--end::Content-->
            <!--begin::Footer-->
            <?php inc('footer') ?>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->
    </div>
