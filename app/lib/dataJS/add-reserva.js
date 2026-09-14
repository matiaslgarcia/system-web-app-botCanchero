import { Func } from './function.js';
const fun = new Func;
const form = document.querySelector('#add-booking-form')
let time_booking = document.querySelector('#time_booking')
let id_field = document.querySelector('#id_field')
let date_booking = document.querySelector('#date_booking')
const summaryEl = document.querySelector('#booking-summary')
const phoneInput = document.querySelector('#phone')
const nameInput = document.querySelector('#full_name')

// RES-02: buscador de cliente existente (mismo patrón que Reservas Fijas)
// para no tener que re-tipear a alguien que ya reservó antes.
if (typeof $ !== 'undefined' && $.fn.select2) {
    $('#cliente-existente').select2({
        placeholder: 'Buscar cliente por nombre o teléfono...',
        allowClear: true,
        ajax: {
            url: 'lib/request/searchCustomers.php',
            dataType: 'json',
            delay: 250,
            data: (params) => ({ q: params.term }),
            processResults: (data) => ({ results: data }),
            cache: true,
        },
        minimumInputLength: 0,
        language: {
            inputTooShort: () => 'Ingresá 2 o más caracteres...',
            searching: () => 'Buscando...',
            noResults: () => 'No se encontraron clientes. Podés completar teléfono y nombre manualmente.',
        },
    });

    $('#cliente-existente').on('select2:select', (e) => {
        const selected = e.params?.data || null;
        if (!selected) return;
        if (phoneInput) phoneInput.value = selected.phone || '';
        if (nameInput) nameInput.value = selected.full_name || '';
    });

    $('#cliente-existente').on('select2:clear', () => {
        if (phoneInput) phoneInput.value = '';
        if (nameInput) nameInput.value = '';
    });
}

const fmtMoney = (n) => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

// RES-03/RES-06: el formulario no mostraba ni el precio ni un resumen de lo
// que se estaba por crear, aunque el sistema ya sabe todo esto (franja
// horaria, cancha, cupos). Se arma con lo que haya seleccionado hasta el
// momento, y se completa a medida que se elige cancha/fecha/hora.
let selectedSlot = null;
const depositAmountInput = document.querySelector('#deposit_amount');
const depositTotalHint = document.querySelector('#deposit-total-hint');
const btnDepositTotal = document.querySelector('#btn-deposit-total');

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

    // Item 09: "Cargar el total" precarga la seña con el precio real del
    // turno recién elegido, en vez de obligar a mirar el resumen y
    // tipearlo a mano.
    if (selectedSlot?.price != null) {
        if (depositTotalHint) depositTotalHint.textContent = `Precio del turno: ${fmtMoney(selectedSlot.price)}.`;
        if (btnDepositTotal) {
            btnDepositTotal.disabled = false;
            btnDepositTotal.textContent = `Cargar el total (${fmtMoney(selectedSlot.price)})`;
            btnDepositTotal.dataset.total = selectedSlot.price;
        }
    } else {
        if (depositTotalHint) depositTotalHint.textContent = 'Elegí cancha, fecha y hora para ver el precio del turno.';
        if (btnDepositTotal) {
            btnDepositTotal.disabled = true;
            btnDepositTotal.textContent = 'Cargar el total';
            delete btnDepositTotal.dataset.total;
        }
    }
}

btnDepositTotal?.addEventListener('click', () => {
    const total = btnDepositTotal.dataset.total;
    if (total != null && depositAmountInput) {
        depositAmountInput.value = Number(total).toFixed(2);
    }
});

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
            const seniaOk = Number(depositAmountInput?.value || 0) > 0 && !_r.deposit_error;
            fun.swal({
                icon: _r.deposit_error ? 'warning' : 'success',
                title: 'Reserva creada con éxito',
                text: _r.deposit_error
                    ? `La reserva se creó, pero la seña no se pudo registrar: ${_r.deposit_error}. Registrala a mano desde el detalle.`
                    : (seniaOk ? 'Seña registrada.' : undefined),
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
