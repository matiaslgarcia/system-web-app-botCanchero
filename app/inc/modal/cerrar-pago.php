<?php 
    $reserva = Booking::getById($_GET['reserva']); 
    $valor = Booking::getValorCanchaByBooking($_GET['reserva']); 
    $montoFaltante = max(0, (float)$reserva->precio_cancha - (float)$reserva->pagado);
?>

<div class="modal fade" id="cerrarpago-reserva" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <form id="form-cerrar-pago" class="modal-dialog modal-dialog-centered" method="POST">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark fs-1"></i>
                </div>
            </div>

            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <div class="mb-13 text-center">
                    <h1 class="mb-3">Registrar Pago</h1>
                    <div class="text-muted fw-bold fs-5">Reserva #<?php echo $reserva->id ?> - Saldo: 
                        <span class="text-danger fw-bolder">$<?php echo number_format($montoFaltante, 0, ',', '.') ?></span>
                    </div>
                </div>

                <input type="hidden" name="id_reserva" id="id_reserva" value="<?php echo $reserva->id ?>">
                <input type="hidden" name="id_field" id="id_field" value="<?php echo $reserva->id_field ?>">
                <input type="hidden" name="valorCancha" id="valorCancha" value="<?php echo $montoFaltante ?>">

                <!-- Selección de Método -->
                <div class="fv-row mb-10">
                    <label class="d-flex align-items-center fs-5 fw-bold mb-4">
                        <span class="required">Método de Pago</span>
                    </label>

                    <div class="d-flex flex-column flex-md-row gap-5">
                        <!-- Efectivo -->
                        <div class="flex-row-fluid">
                            <input type="radio" class="btn-check" name="metodo_pago" value="efectivo" id="kt_modal_pay_cash" checked="checked" />
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-success d-flex align-items-center p-5" for="kt_modal_pay_cash">
                                <i class="fa-solid fa-money-bill-1-wave fs-2x me-4 text-success"></i>
                                <span class="d-block fw-bold text-start">
                                    <span class="text-dark fw-bolder d-block fs-4">Efectivo</span>
                                    <span class="text-muted fw-bold fs-7">Pago en el local</span>
                                </span>
                            </label>
                        </div>

                        <!-- Mercado Pago -->
                        <div class="flex-row-fluid">
                            <input type="radio" class="btn-check" name="metodo_pago" value="mercado_pago" id="kt_modal_pay_mp" />
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex align-items-center p-5" for="kt_modal_pay_mp">
                                <i class="fa-solid fa-wallet fs-2x me-4 text-primary"></i>
                                <span class="d-block fw-bold text-start">
                                    <span class="text-dark fw-bolder d-block fs-4">Mercado Pago</span>
                                    <span class="text-muted fw-bold fs-7">Transferencia / QR</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Monto -->
                <div class="fv-row mb-10">
                    <label class="fs-5 fw-bold mb-2">Monto a Cobrar</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text fs-3 fw-bold text-gray-700">$</span>
                        <input type="text" name="cantidad_a_pagar_display" class="form-control ps-3 fs-3 fw-bolder" value="<?php echo number_format($montoFaltante, 0, ',', '.') ?>" readonly>
                        <input type="hidden" name="cantidad_a_pagar" id="cantidad_a_pagar" value="<?php echo $montoFaltante ?>">
                    </div>
                    <div class="text-muted fs-7 mt-2">El monto está bloqueado al saldo total pendiente.</div>
                </div>

                <!-- Acciones -->
                <div class="text-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <span class="indicator-label">Confirmar y Cerrar Pago</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
