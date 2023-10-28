<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="form-modal-add-reserva">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Reserva</h5>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x"></span>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <!--cliente-->
                <div class="row">
                    <div class="col-6 input-floting-label my-3" >
                        <label for="NumeroTelefono" class="form-label">Numero Telefono</label>
                        <input type="text" name="NumeroTelefono" id="NumeroTelefono" class="form-control">
                    </div>
                    <div class="col-6 input-floting-label my-3" >
                        <label for="Nombre" class="form-label">Nombre</label>
                        <input type="text" name="Nombre" id="Nombre" class="form-control">
                    </div>
                    <div class="col-6 input-floting-label my-3" >
                        <label for="Nombre" class="form-label">DNI</label>
                        <input type="text" name="Nombre" id="Nombre" class="form-control">
                    </div>
                    <div class="col-6 input-floting-label my-3" >
                        <label for="Email" class="form-label">Correo</label>
                        <input type="email" name="Correo" id="Correo" class="form-control">
                    </div>
                    <div class="col-6 input-floting-label my-3" >
                        <label for="FechaReserva" class="form-label">Fecha Reserva</label>
                        <input type="text" name="FechaReserva" id="FechaReserva" class="form-control">
                    </div>
                    <div class="col-6 input-floting-label my-3" >
                        <label for="HoraReserva" class="form-label">Fecha Reserva</label>
                        <input type="text" name="HoraReserva" id="HoraReserva" class="form-control">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear</button>
            </div>
        </form>
    </div>
</div>