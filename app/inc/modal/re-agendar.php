<?php 
    $idReserva = $_GET['reserva'];
    $reserva = Booking::getById($idReserva); 
?>

<div class="modal fade" id="reagendar-reserva" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <form id="form-reagendar" class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-1"></i>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <div class="mb-13 text-center">
                    <h1 class="mb-3">Re-Agendar Reserva</h1>
                    <div class="text-muted fw-bold fs-5">Reserva #<?php echo $reserva->id ?> - Selecciona el nuevo horario</div>
                </div>

                <input type="hidden" name="id" id="id" value="<?php echo $reserva->id?>">
                <input type="hidden" name="id_field" id="id_field" value="<?php echo $reserva->id_field ?>">

                <!-- Fecha -->
                <div class="fv-row mb-10">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">Nueva Fecha</span>
                    </label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text">
                            <i class="fa-solid fa-calendar-day fs-4"></i>
                        </span>
                        <input type="text" name="date_booking" id="date_booking" class="form-control ps-3 fs-6 fw-bold" value="<?php echo showDate($reserva->fecha) ?>" data-min-date="<?php echo date('d/m/Y')?>">
                    </div>
                </div>

                <!-- Hora -->
                <div class="fv-row mb-10">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">Nueva Hora</span>
                    </label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text">
                            <i class="fa-solid fa-clock fs-4"></i>
                        </span>
                        <select name="time_booking" id="time_booking" class="form-select fs-6 fw-bold">
                            <option selected="true" disabled value="">--SELECCIONE--</option>
                        </select>
                    </div>
                    <div class="text-muted fs-7 mt-2">Solo se muestran los turnos libres para la fecha seleccionada.</div>
                </div>

                <!-- Footer Acciones -->
                <div class="text-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Unifica alturas de inputs/select e iconos del modal */
#reagendar-reserva {
    --ra-control-height: 43px;
}

#reagendar-reserva .input-group .input-group-text {
    height: var(--ra-control-height);
    min-height: var(--ra-control-height);
    display: flex;
    align-items: center;
}

#reagendar-reserva #date_booking {
    height: var(--ra-control-height);
    min-height: var(--ra-control-height);
}

/* Alinea select2 con el icono dentro del input-group */
#reagendar-reserva .input-group .select2-container {
    flex: 1 1 auto;
    width: 1% !important;
}

#reagendar-reserva .input-group .select2-container .select2-selection--single {
    height: var(--ra-control-height) !important;
    border: 0 !important;
    border-radius: 0 .475rem .475rem 0 !important;
    display: flex;
    align-items: center;
    background-color: transparent;
}

#reagendar-reserva .input-group .select2-container .select2-selection__rendered {
    line-height: var(--ra-control-height) !important;
    padding-left: .85rem !important;
}

#reagendar-reserva .input-group .select2-container .select2-selection__arrow {
    height: var(--ra-control-height) !important;
}
</style>
