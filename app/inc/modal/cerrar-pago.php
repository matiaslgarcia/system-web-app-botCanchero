<?php $reserva = Booking::getById($_GET['reserva']); ?>
<?php $valor = Booking::getValorCanchaByBooking($_GET['reserva']); ?>

<div class="modal fade" id="cerrarpago-reserva" data-bs-backdrop="static" tabindex="-1" aria-labelledby="cerrar-pago-reservalLabel" aria-hidden="true">
    <form id="form-cerrar-pago" class="modal-dialog modal-dialog-centered" method="POST">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cerrar Pago - Reserva N°<?php echo $reserva->id ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_reserva" id="id_reserva" value="<?php echo $reserva->id ?>">
                <input type="hidden" name="id_field" id="id_field" value="<?php echo $reserva->id_field ?>">
                <input type="hidden" name="valorCancha" id="valorCancha" value="<?php echo $valor->precio_cancha ?>">
                <div class="mb-3">
                    <label class="form-label" for="metodo_pago">Método de Pago</label>
                    <select class="form-control" id="metodo_pago" name="metodo_pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="mercadoPago">Mercado Pago</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="cantidad_a_pagar">Monto</label>
                    <input type="number" name="cantidad_a_pagar" id="cantidad_a_pagar" class="form-control" placeholder="<?php echo $valor -> precio_cancha?>">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Cerrar Pago</button>
            </div>
        </div>
    </form>
</div>

