import { Func } from './function.js';
const fun = new Func;
const formReagendar = document.querySelector('#form-reagendar');
const formCerrarPago = document.querySelector('#form-cerrar-pago');
let btnCancelarReserva = document.querySelector('#btn-action-cancelar-reserva');
let btnPausarFija = document.querySelector('#btn-action-pausar-fija');
let time_booking = document.querySelector('#time_booking');
let id_field = document.querySelector('#id_field');

function limpiarHorarios() {
    time_booking.innerHTML = '<option selected="true" disabled value="">--SELECCIONE--</option>';
}

// Cancelar Reserva
if (btnCancelarReserva) {
    btnCancelarReserva.addEventListener('click', () => {
        const idReserva = btnCancelarReserva.getAttribute('data-id-reserva');
        fun.confirm({
            icon: 'warning',
            title: '¿Estás seguro?',
            text: 'Vas a cancelar la reserva #' + idReserva,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, volver',
            confirmVariant: 'danger',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'cancel-booking',
                data: fun.setForm({ id: idReserva, status: 2 }),
                success: () => {
                    fun.swal({
                        icon: 'success',
                        title: 'Reserva cancelada',
                        willClose: () => {
                            location.href = 'reservas';
                        }
                    });
                }
            });
        });
    });
}

// Pausar Reserva Fija
if (btnPausarFija) {
    btnPausarFija.addEventListener('click', () => {
        const recurringId = btnPausarFija.getAttribute('data-recurring-id');
        fun.confirm({
            icon: 'warning',
            title: '¿Pausar reserva fija?',
            text: 'No se generarán nuevos turnos mientras esté pausada.',
            confirmButtonText: 'Sí, pausar',
            cancelButtonText: 'No, volver',
            confirmVariant: 'warning',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'updateRecurringBookingStatus',
                method: 'POST',
                data: fun.setForm({ id: recurringId, status: 'paused' }),
                success: (resp) => {
                    if (resp && resp.ok) {
                        fun.swal({
                            icon: 'success',
                            title: 'Reserva fija pausada',
                            willClose: () => location.reload()
                        });
                    } else {
                        fun.swal({ icon: 'error', title: resp?.error || 'No se pudo pausar la reserva fija' });
                    }
                },
                error: () => {
                    fun.swal({ icon: 'error', title: 'No se pudo pausar la reserva fija' });
                }
            });
        });
    });
}

// Form Reagendar
if (formReagendar) {
    formReagendar.addEventListener('submit', (e) => {
        e.preventDefault();
        fun.xhr({
            url: 'reagendar',
            data: new FormData(formReagendar),
            success: () => {
                fun.swal({
                    icon: 'success',
                    title: 'Reserva re-agendada correctamente',
                    willClose: () => location.reload()
                });
            },
            error: (err) => {
                const message = err?.error || 'No se pudo re-agendar la reserva.';
                fun.swal({
                    icon: 'error',
                    title: message,
                    timer: 3000
                });
            }
        });
    });
}

// Form Cerrar Pago
if (formCerrarPago) {
    formCerrarPago.addEventListener('submit', (e) => {
        e.preventDefault();
        const data = new FormData(formCerrarPago);
        fun.xhr({
            url: 'cerrarpago',
            data: data,
            success: () => {
                fun.swal({
                    icon: 'success',
                    title: 'Pago registrado con éxito',
                    willClose: () => location.reload()
                });
            }
        });
    });
}

// Datepicker Reagendar
if (typeof $ !== 'undefined' && $.fn.daterangepicker) {
    $('#date_booking').daterangepicker({
        singleDatePicker: true,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        }
    });
}

// Select2 Horarios Reagendar
if (time_booking) {
    $(time_booking).select2({
        placeholder: "--SELECCIONE--",
        dropdownParent: $('#reagendar-reserva'),
        language: {
            searching: () => "Buscando...",
            noResults: () => "No se encontraron turnos disponibles",
            errorLoading: () => "Error al cargar horarios"
        },
        ajax: {
            type: "POST",
            dataType: 'json',
            url: 'fetch/get-horas-booking',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            data: () => {
                return {
                    id: document.querySelector('#id').value,
                    id_field: id_field.value,
                    date_booking: document.querySelector('#date_booking').value,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                };
            },
            beforeSend: (xhr) => {
                if (!id_field.value || !document.querySelector('#date_booking').value) {
                    xhr.abort();
                }
            },
            processResults: (data) => {
                return { results: data };
            }
        },
        minimumResultsForSearch: Infinity
    });
}

$('#date_booking')?.on('change', () => {
    limpiarHorarios();
});

// --- Acciones para detalle de reserva fija (RB-*) ---
const btnRbMover = document.querySelector('.btn-rb-mover');
const btnRbPausar = document.querySelector('.btn-rb-pausar');
const btnRbReactivar = document.querySelector('.btn-rb-reactivar');
const btnRbCancelar = document.querySelector('.btn-rb-cancelar');
const rbMoverConfirmar = document.getElementById('mover-confirmar');
const rbCancelarConfirmar = document.getElementById('cancelar-confirmar');

if (btnRbMover) {
    btnRbMover.addEventListener('click', () => {
        document.getElementById('mover-id').value = btnRbMover.dataset.id || '';
        document.getElementById('mover-dow').value = btnRbMover.dataset.dow || '1';
        document.getElementById('mover-start').value = btnRbMover.dataset.start || '';
        document.getElementById('mover-dur').value = btnRbMover.dataset.dur || '60';
        new bootstrap.Modal(document.getElementById('modalMover')).show();
    });
}

if (rbMoverConfirmar) {
    rbMoverConfirmar.addEventListener('click', () => {
        const payload = {
            id: document.getElementById('mover-id')?.value || '',
            day_of_week: document.getElementById('mover-dow')?.value || '',
            start_time: `${document.getElementById('mover-start')?.value || ''}:00`,
            duration_min: document.getElementById('mover-dur')?.value || '60',
        };
        fun.xhr({
            url: 'moveRecurringBooking',
            method: 'POST',
            data: fun.setForm(payload),
            success: (resp) => {
                if (resp && resp.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('modalMover'))?.hide();
                    fun.swal({
                        icon: 'success',
                        title: 'Horario actualizado',
                        willClose: () => location.reload()
                    });
                } else {
                    fun.swal({ icon: 'error', title: resp?.error || 'Error al mover' });
                }
            },
            error: () => fun.swal({ icon: 'error', title: 'Error de red' }),
        });
    });
}

if (btnRbPausar) {
    btnRbPausar.addEventListener('click', () => {
        const id = btnRbPausar.dataset.id;
        fun.confirm({
            title: '¿Estás seguro de pausar esta reserva?',
            text: 'No se generarán nuevos turnos mientras esté pausada.',
            confirmButtonText: 'Sí, pausar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'warning',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'updateRecurringBookingStatus',
                method: 'POST',
                data: fun.setForm({ id, status: 'paused' }),
                success: (resp) => {
                    if (resp && resp.ok) {
                        fun.swal({
                            icon: 'success',
                            title: 'Reserva fija pausada',
                            willClose: () => location.reload()
                        });
                    } else {
                        fun.swal({ icon: 'error', title: resp?.error || 'Error' });
                    }
                },
                error: () => fun.swal({ icon: 'error', title: 'No se pudo pausar la reserva' }),
            });
        });
    });
}

if (btnRbReactivar) {
    btnRbReactivar.addEventListener('click', () => {
        const id = btnRbReactivar.dataset.id;
        fun.confirm({
            title: '¿Estás seguro de reactivar esta reserva?',
            text: 'Se volverán a generar turnos semanales.',
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'success',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'updateRecurringBookingStatus',
                method: 'POST',
                data: fun.setForm({ id, status: 'active' }),
                success: (resp) => {
                    if (resp && resp.ok) {
                        fun.swal({
                            icon: 'success',
                            title: 'Reserva fija reactivada',
                            willClose: () => location.reload()
                        });
                    } else {
                        fun.swal({ icon: 'error', title: resp?.error || 'Error' });
                    }
                },
                error: () => fun.swal({ icon: 'error', title: 'No se pudo reactivar la reserva' }),
            });
        });
    });
}

if (btnRbCancelar) {
    btnRbCancelar.addEventListener('click', () => {
        document.getElementById('cancelar-id').value = btnRbCancelar.dataset.id || '';
        document.getElementById('cancelar-reason').value = '';
        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    });
}

if (rbCancelarConfirmar) {
    rbCancelarConfirmar.addEventListener('click', () => {
        const payload = {
            id: document.getElementById('cancelar-id')?.value || '',
            reason: document.getElementById('cancelar-reason')?.value || '',
            status: 'cancelled',
        };
        fun.xhr({
            url: 'updateRecurringBookingStatus',
            method: 'POST',
            data: fun.setForm(payload),
            success: (resp) => {
                if (resp && resp.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('modalCancelar'))?.hide();
                    fun.swal({
                        icon: 'success',
                        title: 'Reserva fija cancelada',
                        willClose: () => location.reload()
                    });
                } else {
                    fun.swal({ icon: 'error', title: resp?.error || 'Error al cancelar' });
                }
            },
            error: () => fun.swal({ icon: 'error', title: 'Error de red' }),
        });
    });
}
