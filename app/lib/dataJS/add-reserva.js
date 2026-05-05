import { Func } from './function.js';
const fun = new Func;
const form = document.querySelector('#add-booking-form')
let time_booking = document.querySelector('#time_booking')
let id_field = document.querySelector('#id_field')
let date_booking = document.querySelector('#date_booking')

function limpiarHorarios() {
    time_booking.innerHTML = '<option selected="true" disabled value="">--SELECCIONE--</option>'
}

// Datepicker
if (typeof $ !== 'undefined' && $.fn.daterangepicker) {
    $('#date_booking').daterangepicker({
        singleDatePicker: true,
        locale: {
            format: 'DD/MM/YYYY',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        }
    });
}

// Select2 para horarios con traducción y CSRF
$(time_booking).select2({
    placeholder: "--SELECCIONE--",
    language: {
        errorLoading: function () { return "No se pudieron cargar los resultados"; },
        inputTooLong: function (args) { return "Por favor, elimine " + (args.input.length - args.maximum) + " carácteres"; },
        inputTooShort: function (args) { return "Por favor, introduzca " + (args.minimum - args.input.length) + " o más carácteres"; },
        loadingMore: function () { return "Cargando más resultados…"; },
        maximumSelected: function (args) { return "Sólo puede seleccionar " + args.maximum + " elementos"; },
        noResults: function () { return "No se encontraron resultados"; },
        searching: function () { return "Buscando…"; },
        removeAllItems: function () { return "Eliminar todos los objetos"; }
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
                id_field: id_field.value, 
                date_booking: date_booking.value,
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        },
        beforeSend: (xhr) => {
            if (!id_field.value || !date_booking.value) {
                xhr.abort();
                fun.swal({
                    icon: 'warning',
                    title: 'Por favor seleccione cancha y fecha primero'
                });
            }
        },
        processResults: function (data) {
            return { results: data };
        },
    },
    minimumResultsForSearch: Infinity
});

$(date_booking).on('change', () => {
    limpiarHorarios();
});

form.addEventListener('submit', e => {
    e.preventDefault();
    fun.xhr({
        url: 'add-booking',
        data: new FormData(form),
        success: (_r) => {
            fun.swal({
                icon: 'success',
                title: 'Reserva creada con éxito',
                success: () => {
                    location.href = 'reserva/' + _r.id;
                }
            });
        }
    });
});

// Auto-fill from URL
const urlParams = new URLSearchParams(window.location.search);
const pDate = urlParams.get('date');
const pTime = urlParams.get('time');

if (pDate) {
    const [y, m, d] = pDate.split('-');
    $('#date_booking').val(`${d}/${m}/${y}`).trigger('change');
}

if (pTime) {
    setTimeout(() => {
        const option = new Option(pTime, pTime, true, true);
        $(time_booking).append(option).trigger('change');
    }, 500);
}
