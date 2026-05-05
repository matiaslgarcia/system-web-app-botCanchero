import { Func } from './function.js';
const fun = new Func;
const formReagendar = document.querySelector('#form-reagendar');
const formCerrarPago = document.querySelector('#form-cerrar-pago');
let btnCancelarReserva = document.querySelector('#btn-action-cancelar-reserva');
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
