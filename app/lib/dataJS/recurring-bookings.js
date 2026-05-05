import { Func } from './function.js';
const fun = new Func;

const DAYS = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

const state = {
    items: [],
    filtroEstado: 'active',
    hasAvailableSlots: false,
};

function badgeStatus(s) {
    const map = {
        active:           ['success', 'Activa'],
        pending_payment:  ['warning', 'Pendiente de pago'],
        paused:           ['info', 'Pausada'],
        cancelled:        ['danger', 'Cancelada'],
    };
    const [color, label] = map[s] || ['secondary', s];
    return `<span class="badge badge-light-${color}">${label}</span>`;
}

function vigenciaTxt(rb) {
    const from = rb.valid_from ? rb.valid_from.split('-').reverse().join('/') : '';
    const until = rb.valid_until ? rb.valid_until.split('-').reverse().join('/') : 'Indefinido';
    return `${from} → ${until}`;
}

function renderTabla() {
    const body = document.getElementById('tabla-fijas-body');
    if (!state.items.length) {
        body.innerHTML = '<tr><td colspan="9" class="text-center py-10 text-muted">No hay reservas fijas</td></tr>';
        return;
    }
    body.innerHTML = state.items.map((rb) => `
        <tr>
            <td class="ps-4 fw-bold">${DAYS[rb.day_of_week] || rb.day_of_week}</td>
            <td>${(rb.start_time || '').slice(0, 5)}</td>
            <td>${rb.duration_min} min</td>
            <td>${rb.field_name || ''}</td>
            <td>${rb.customer_name || ''}</td>
            <td><a href="https://wa.me/${(rb.customer_phone || '').replace(/\D/g, '')}" target="_blank" class="text-muted">${rb.customer_phone || ''}</a></td>
            <td>${vigenciaTxt(rb)}</td>
            <td class="text-center">${badgeStatus(rb.status)}</td>
            <td class="text-end pe-4">
                <div class="d-flex justify-content-end flex-wrap gap-2">
                    ${rb.status === 'active' ? `
                        <button class="btn btn-sm btn-light-primary btn-mover" data-id="${rb.id}" data-dow="${rb.day_of_week}" data-start="${(rb.start_time || '').slice(0,5)}" data-dur="${rb.duration_min}">
                            <i class="fa-solid fa-arrow-right-arrow-left me-1"></i>Mover Horario
                        </button>
                        <button class="btn btn-sm btn-light-warning btn-pausar" data-id="${rb.id}">
                            <i class="fa-solid fa-pause me-1"></i>Pausar
                        </button>
                    ` : ''}
                    ${rb.status === 'paused' ? `
                        <button class="btn btn-sm btn-light-success btn-reactivar" data-id="${rb.id}">
                            <i class="fa-solid fa-play me-1"></i>Reactivar
                        </button>
                    ` : ''}
                    ${rb.status !== 'cancelled' ? `
                        <button class="btn btn-sm btn-light-danger btn-cancelar" data-id="${rb.id}">
                            <i class="fa-solid fa-trash me-1"></i>Cancelar
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');

    document.querySelectorAll('.btn-mover').forEach((b) => b.addEventListener('click', () => abrirMover(b)));
    document.querySelectorAll('.btn-pausar').forEach((b) => b.addEventListener('click', () => cambiarEstado(b.dataset.id, 'paused')));
    document.querySelectorAll('.btn-reactivar').forEach((b) => b.addEventListener('click', () => cambiarEstado(b.dataset.id, 'active')));
    document.querySelectorAll('.btn-cancelar').forEach((b) => b.addEventListener('click', () => abrirCancelar(b.dataset.id)));
}

function cargar() {
    const body = document.getElementById('tabla-fijas-body');
    body.innerHTML = '<tr><td colspan="9" class="text-center py-10 text-muted"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Cargando...</td></tr>';
    fun.xhr({
        url: 'getRecurringBookings',
        method: 'POST',
        data: fun.setForm({ status: state.filtroEstado }),
        success: (resp) => {
            state.items = resp.items || [];
            renderTabla();
        },
        error: () => {
            body.innerHTML = '<tr><td colspan="9" class="text-center py-10 text-danger">Error al cargar datos</td></tr>';
        },
    });
}

function cambiarEstado(id, status) {
    const labels = { paused: 'pausar', active: 'reactivar' };
    const confirmTitle = status === 'paused'
        ? '¿Estás seguro de pausar esta reserva?'
        : '¿Estás seguro de reactivar esta reserva?';
    const confirmText = status === 'paused'
        ? 'No se generarán nuevos turnos mientras esté pausada.'
        : 'Se volverán a generar turnos semanales.';
    const confirmButtonText = status === 'paused' ? 'Sí, pausar' : 'Sí, reactivar';
    const confirmVariant = status === 'paused' ? 'warning' : 'success';

    fun.confirm({
        title: confirmTitle,
        text: confirmText,
        icon: 'warning',
        confirmButtonText,
        cancelButtonText: 'No, cancelar',
        confirmVariant,
        cancelVariant: 'secondary',
    }).then((result) => {
        if (!result.isConfirmed) return;
        fun.xhr({
            url: 'updateRecurringBookingStatus',
            method: 'POST',
            data: fun.setForm({ id, status }),
            success: (resp) => {
                if (resp && resp.ok) {
                    fun.swal({ icon: 'success', title: 'Estado actualizado' });
                    cargar();
                } else {
                    fun.swal({ icon: 'error', title: resp?.error || 'Error' });
                }
            },
            error: () => {
                fun.swal({ icon: 'error', title: `No se pudo ${labels[status]} la reserva` });
            }
        });
    });
}


function initSelect2() {
    // Selector de Clientes
    $('#nueva-cliente').select2({
        dropdownParent: $('#modalNueva'),
        placeholder: "Buscar cliente...",
        allowClear: true,
        ajax: {
            url: 'lib/request/searchCustomers.php',
            dataType: 'json',
            delay: 250,
            data: (params) => ({ q: params.term }),
            processResults: (data) => ({ results: data }),
            cache: true
        },
        minimumInputLength: 0, // Permite cargar por defecto
        language: {
            inputTooShort: () => "Ingresa 2 o más caracteres...",
            searching: () => "Buscando...",
            noResults: () => "No se encontraron clientes"
        }
    });

    // Forzar carga inicial al abrir el modal
    $('#modalNueva').on('shown.bs.modal', function () {
        $('#nueva-cliente').select2('open');
        $('#nueva-cliente').select2('close');
    });

    // Selector de Canchas
    $('#nueva-cancha').select2({
        dropdownParent: $('#modalNueva'),
        placeholder: "Selecciona cancha...",
        minimumResultsForSearch: Infinity
    }).on('change', cargarHorarios);

    $('#nueva-dow').on('change', cargarHorarios);
}

function ensureHorarioHelper() {
    let helper = document.getElementById('nueva-start-helper');
    if (helper) return helper;

    const select = document.getElementById('nueva-start');
    helper = document.createElement('div');
    helper.id = 'nueva-start-helper';
    helper.className = 'form-text text-muted mt-2';
    select.parentElement.appendChild(helper);
    return helper;
}

function setGuardarNuevaState(enabled, message = '', helperClass = 'text-muted') {
    const btn = document.getElementById('btn-guardar-fija');
    btn.disabled = !enabled;
    btn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
    btn.classList.toggle('disabled', !enabled);
    state.hasAvailableSlots = enabled;

    const helper = ensureHorarioHelper();
    helper.className = `form-text mt-2 ${helperClass}`;
    helper.innerHTML = message;
}

function cargarHorarios() {
    const id_field = $('#nueva-cancha').val();
    const id_day = $('#nueva-dow').val();
    const select = document.getElementById('nueva-start');

    if (!id_field || !id_day) {
        select.innerHTML = '<option value="">Primero elige cancha y día...</option>';
        setGuardarNuevaState(false, 'Seleccioná cancha y día para habilitar horarios.', 'text-muted');
        return;
    }

    select.innerHTML = '<option value=""><span class="spinner-border spinner-border-sm"></span> Cargando...</option>';
    setGuardarNuevaState(false, 'Buscando horarios disponibles...', 'text-muted');
    
    fun.xhr({
        url: 'getFieldSchedules',
        method: 'GET',
        data: `id_field=${id_field}&id_day=${id_day}`,
        success: (resp) => {
            if (resp && resp.length) {
                const toOptionText = (h) => {
                    const threshold = Math.max(1, Number(h.threshold || 1));
                    const occupied = Math.max(0, Number(h.occupied || 0));
                    const free = Math.max(0, threshold - occupied);
                    return `${h.hour12} (${free}/${threshold} cupos libres)`;
                };
                select.innerHTML = '<option value="">Selecciona horario...</option>' + 
                    resp.map((h) => `<option value="${h.time || h.hour}">${toOptionText(h)} (1 hora)</option>`).join('');
                setGuardarNuevaState(true);
            } else {
                select.innerHTML = '<option value="">No hay horarios configurados</option>';
                setGuardarNuevaState(
                    false,
                    'No hay horarios configurados para esa cancha y día. Configuralos en <a href="dia" class="fw-bold">Horarios</a>.',
                    'text-warning'
                );
            }
        },
        error: () => {
            select.innerHTML = '<option value="">Error al cargar</option>';
            setGuardarNuevaState(false, 'No se pudieron cargar horarios. Probá de nuevo.', 'text-danger');
        }
    });
}

function guardarNueva() {
    const btn = document.getElementById('btn-guardar-fija');
    const form = document.getElementById('form-nueva-fija');
    
    const payload = {
        customer_id: $('#nueva-cliente').val(),
        field_id: $('#nueva-cancha').val(),
        day_of_week: $('#nueva-dow').val(),
        start_time: $('#nueva-start').val(),
        duration_min: 60, // Fijo 1 hora siempre
        valid_from: document.getElementById('nueva-from').value,
        valid_until: document.getElementById('nueva-until').value,
    };

    if (!state.hasAvailableSlots) {
        fun.swal({
            icon: 'warning',
            title: 'No hay horarios disponibles',
            text: 'Primero configurá horarios para esa cancha y día.'
        });
        return;
    }

    if (!payload.customer_id || !payload.field_id || !payload.start_time) {
        fun.swal({ icon: 'error', title: 'Campos incompletos', text: 'Por favor selecciona cliente, cancha y horario.' });
        return;
    }

    btn.setAttribute('data-kt-indicator', 'on');
    btn.disabled = true;

    fun.xhr({
        url: 'addRecurringBooking',
        method: 'POST',
        data: fun.setForm(payload),
        success: (resp) => {
            btn.removeAttribute('data-kt-indicator');
            btn.disabled = false;
            if (resp && resp.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalNueva')).hide();
                form.reset();
                $('#nueva-cliente').val(null).trigger('change');
                $('#nueva-cancha').val(null).trigger('change');
                fun.swal({ icon: 'success', title: 'Reserva fija creada correctamente' });
                cargar();
            } else {
                fun.swal({ icon: 'error', title: 'Conflicto', text: resp?.error || 'Error al guardar' });
            }
        },
        error: () => {
            btn.removeAttribute('data-kt-indicator');
            btn.disabled = false;
            fun.swal({ icon: 'error', title: 'Error de conexión' });
        }
    });
}

function abrirMover(btn) {
    document.getElementById('mover-id').value = btn.dataset.id;
    document.getElementById('mover-dow').value = btn.dataset.dow;
    document.getElementById('mover-start').value = btn.dataset.start;
    document.getElementById('mover-dur').value = btn.dataset.dur;
    new bootstrap.Modal(document.getElementById('modalMover')).show();
}

function abrirCancelar(id) {
    document.getElementById('cancelar-id').value = id;
    document.getElementById('cancelar-reason').value = '';
    new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}

document.getElementById('mover-confirmar').addEventListener('click', () => {
    const payload = {
        id: document.getElementById('mover-id').value,
        day_of_week: document.getElementById('mover-dow').value,
        start_time: document.getElementById('mover-start').value + ':00',
        duration_min: document.getElementById('mover-dur').value,
    };
    fun.xhr({
        url: 'moveRecurringBooking',
        method: 'POST',
        data: fun.setForm(payload),
        success: (resp) => {
            if (resp && resp.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalMover')).hide();
                fun.swal({ icon: 'success', title: 'Horario actualizado' });
                cargar();
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'Error al mover' });
            }
        },
        error: () => fun.swal({ icon: 'error', title: 'Error de red' }),
    });
});

document.getElementById('cancelar-confirmar').addEventListener('click', () => {
    const payload = {
        id: document.getElementById('cancelar-id').value,
        reason: document.getElementById('cancelar-reason').value,
        status: 'cancelled'
    };
    // Reutilizamos el endpoint de status para consistencia
    fun.xhr({
        url: 'updateRecurringBookingStatus',
        method: 'POST',
        data: fun.setForm(payload),
        success: (resp) => {
            if (resp && resp.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalCancelar')).hide();
                fun.swal({ icon: 'success', title: 'Reserva fija cancelada' });
                cargar();
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'Error al cancelar' });
            }
        },
        error: () => fun.swal({ icon: 'error', title: 'Error de red' }),
    });
});

document.getElementById('btn-guardar-fija').addEventListener('click', guardarNueva);

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filtroEstado').addEventListener('change', (e) => {
        state.filtroEstado = e.target.value;
        cargar();
    });
    initSelect2();
    setGuardarNuevaState(false, 'Seleccioná cancha y día para habilitar horarios.', 'text-muted');
    const canchaSelect = document.getElementById('nueva-cancha');
    const availableFields = Array.from(canchaSelect.options).filter((opt) => opt.value);
    if (availableFields.length === 1) {
        $('#nueva-cancha').val(availableFields[0].value).trigger('change');
    }
    if (canchaSelect.value && document.getElementById('nueva-dow').value) {
        cargarHorarios();
    }
    document.getElementById('modalNueva').addEventListener('shown.bs.modal', () => {
        if (canchaSelect.value && document.getElementById('nueva-dow').value) {
            cargarHorarios();
        }
    });
    cargar();
});
