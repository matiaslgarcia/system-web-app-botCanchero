import { Func } from './function.js';
const fun = new Func;
const form = document.querySelector('#add-booking-form')
let time_booking = document.querySelector('#time_booking')
let id_field = document.querySelector('#id_field')
let date_booking = document.querySelector('#date_booking')
const summaryEl = document.querySelector('#booking-summary')

const fmtMoney = (n) => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

// RES-03/RES-06: el formulario no mostraba ni el precio ni un resumen de lo
// que se estaba por crear, aunque el sistema ya sabe todo esto (franja
// horaria, cancha, cupos). Se arma con lo que haya seleccionado hasta el
// momento, y se completa a medida que se elige cancha/fecha/hora.
let selectedSlot = null;
function updateSummary() {
    if (!summaryEl) return;
    const parts = [];
    if (date_booking?.value) parts.push(date_booking.value);
    const canchaText = id_field?.tagName === 'SELECT' ? id_field.options[id_field.selectedIndex]?.text : '';
    if (canchaText && canchaText !== '--SELECCIONE--') parts.push(canchaText);
    if (selectedSlot?.text) parts.push(selectedSlot.text.replace(/\s*\(\d+\/\d+ cupos libres\).*/, ''));
    if (selectedSlot?.price != null) {
        const rango = selectedSlot.price_range ? ` — franja ${selectedSlot.price_range}` : '';
        parts.push(`${fmtMoney(selectedSlot.price)}${rango}`);
    }
    summaryEl.textContent = parts.length ? parts.join(' · ') : 'Completá los datos del turno.';
}

function limpiarHorarios() {
    time_booking.innerHTML = '<option selected="true" disabled value="">--SELECCIONE--</option>'
}

const DAY_NAMES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
let lastHasSchedule = true;

function selectedDayName() {
    const value = date_booking.value || '';
    const [d, m, y] = value.split('/');
    if (!d || !m || !y) return '';
    const date = new Date(`${y}-${m}-${d}T00:00:00`);
    return Number.isNaN(date.getTime()) ? '' : DAY_NAMES[date.getDay()];
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
        noResults: function () {
            if (!lastHasSchedule) {
                const day = selectedDayName();
                return (day ? `${day} sin horarios cargados. ` : 'Este día no tiene horarios cargados. ')
                    + 'Configurar en Mi Cancha → Horarios.';
            }
            return "No hay horarios libres para esa fecha";
        },
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
            lastHasSchedule = data?.has_schedule !== false;
            return { results: data?.results || [] };
        },
    },
    minimumResultsForSearch: Infinity
});

$(date_booking).on('change', () => {
    limpiarHorarios();
    selectedSlot = null;
    updateSummary();
});

$(id_field).on('change', updateSummary);

$(time_booking).on('select2:select', (e) => {
    selectedSlot = e.params?.data || null;
    updateSummary();
});
$(time_booking).on('select2:clear', () => {
    selectedSlot = null;
    updateSummary();
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
