<?php $reserva = Booking::getById($_GET['reserva']);  ?>
<div class="modal fade" id="reagendar-reserva" data-bs-backdrop="static" tabindex="-1" aria-labelledby="reagendar-reservalLabel" aria-hidden="true">
    <form id="form-reagendar" class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reagendar Reserva #<?php echo $reserva->id ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="<?php echo $reserva->id?>">
                <input type="hidden" name="id_field" id="id_field" value="<?php echo $reserva->id_field ?>">
                <div class="mb-3">
                    <label class="form-label" for="date_booking">Fecha</label>
                    <input type="text" name="date_booking" id="date_booking" class="form-control" value="<?php echo showDate($reserva->fecha) ?>" data-min-date="<?php echo date('d/m/Y')?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="date_booking">Hora</label>
                    <select type="text" name="time_booking" id="time_booking" class="form-control">
                        <option selected="true" disabled value="">--SELECIONE--</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </form>
</div>