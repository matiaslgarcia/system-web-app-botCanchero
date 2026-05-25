import { Func } from './function.js';
const fun = new Func;

// --- Estado local ---
const state = {
    fecha: new Date().toLocaleDateString('en-CA'), // Obtiene YYYY-MM-DD en hora local
    bookings: [],
};

const fmtMoney = (n) => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const isUltraMobile = () => window.matchMedia('(max-width: 767.98px)').matches;
const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
const normalizeDateForFilename = (rawDate) => {
    const value = String(rawDate || '').trim();
    let match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) return `${match[1]}-${match[2]}-${match[3]}`;
    match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (match) return `${match[3]}-${match[2]}-${match[1]}`;
    return new Date().toISOString().slice(0, 10);
};

function syncUltraMobileLayout() {
    const mobileList = document.getElementById('dia-mobile-list');
    const tableWrapper = document.getElementById('dia-table-wrapper');
    if (!mobileList || !tableWrapper) return;
    if (isUltraMobile()) {
        mobileList.style.display = 'block';
        tableWrapper.style.display = 'none';
    } else {
        mobileList.style.display = 'none';
        tableWrapper.style.display = 'block';
    }
}

function bindRowNavigation(containerSelector) {
    document.querySelectorAll(containerSelector).forEach((row) => {
        row.addEventListener('click', (event) => {
            const interactive = event.target.closest('a, button, input, select, textarea, label');
            if (interactive) return;
            const url = row?.dataset?.detailUrl || '';
            const recurringId = Number(row?.dataset?.recurringId || 0);
            const dateBooking = row?.dataset?.dateBooking || state.fecha;
            if (url) {
                window.location.href = url;
                return;
            }
            if (recurringId > 0) {
                openOrCreateRecurringDetail(recurringId, dateBooking);
            }
        });
    });
}

window.bcRowNavigate = function bcRowNavigate(event, row) {
    if (event.target.closest('a, button, input, select, textarea, label')) return;
    const url = row?.dataset?.detailUrl || '';
    const recurringId = Number(row?.dataset?.recurringId || 0);
    const dateBooking = row?.dataset?.dateBooking || state.fecha;
    if (url) {
        window.location.href = url;
        return;
    }
    if (recurringId > 0) {
        openOrCreateRecurringDetail(recurringId, dateBooking);
    }
};

function getDecoratedBookings() {
    const slotUsage = new Map();
    state.bookings.forEach((b) => {
        const key = `${b.id_field || ''}|${b.hour_label || ''}`;
        if (!key) return;
        const threshold = Math.max(1, Number(b.threshold || 1));
        const current = slotUsage.get(key) || { occupied: 0, threshold };
        current.occupied += 1;
        current.threshold = Math.max(current.threshold, threshold);
        slotUsage.set(key, current);
    });

    const slotRowIndex = new Map();
    return state.bookings.map((b) => {
        const total = Number(b.total_amount) || 0;
        const paid = Number(b.paid_amount) || 0;
        const saldo = Math.max(0, total - paid);
        const esFija = b.is_fixed == 1;
        const canCharge = Number(b.can_charge || 0) === 1;
        const isRealBooking = /^\d+$/.test(String(b.id || ''));
        const badge =
            saldo <= 0 ? '<span class="badge badge-light-success">Pagada</span>' :
            (paid > 0 ? '<span class="badge badge-light-warning">Parcial</span>' : '<span class="badge badge-light-danger">Pendiente</span>');
        const isPlannedRecurring = !isRealBooking && String(b.source || '') === 'recurring_planned' && Number(b.recurring_booking_id || 0) > 0;
        const recurringId = Number(b.recurring_booking_id || 0);
        const bookingLink = isRealBooking ? `reserva/${b.id}` : '';
        const chargeControl = (saldo > 0 && canCharge)
            ? `<button class="btn btn-sm btn-primary btn-cobrar" data-id="${b.id}"><i class="fa-solid fa-cash-register me-1"></i> Cobrar</button>`
            : (saldo <= 0
                ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Cobrado</span>'
                : (isPlannedRecurring
                    ? `<button class="btn btn-sm btn-primary btn-cobrar-planned" data-recurring-id="${b.recurring_booking_id}"><i class="fa-solid fa-cash-register me-1"></i> Cobrar</button>`
                    : '<span class="badge badge-light-secondary">Sin turno generado</span>'));
        const detailBtn = isRealBooking
            ? `<a href="${bookingLink}" class="btn btn-sm btn-light-info"><i class="fa-solid fa-eye me-1"></i> Ver detalle</a>`
            : `<button class="btn btn-sm btn-light-info btn-open-recurring-detail" data-recurring-id="${recurringId}" data-date-booking="${state.fecha}"><i class="fa-solid fa-eye me-1"></i> Ver detalle</button>`;
        const btn = `<div class="d-inline-flex flex-wrap justify-content-end gap-2">${detailBtn}${chargeControl}</div>`;
        const recurringTag = esFija ? ' <span class="badge badge-light-info ms-1">♻️ Fija</span>' : '';
        const customerName = b.customer_name || 'Cliente sin nombre';
        const slotKey = `${b.id_field || ''}|${b.hour_label || ''}`;
        const slotInfo = slotUsage.get(slotKey);
        const nextIndex = (slotRowIndex.get(slotKey) || 0) + 1;
        slotRowIndex.set(slotKey, nextIndex);
        const numeroCancha = slotInfo
            ? `<span class="badge badge-light-primary" title="Cupo ${nextIndex} de ${slotInfo.threshold}">Cancha ${nextIndex}</span>`
            : '<span class="text-muted">-</span>';

        return { b, total, paid, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking, recurringId };
    });
}

// --- Render ---
function renderTabla() {
    const body = document.getElementById('tabla-dia-body');
    const mobileList = document.getElementById('dia-mobile-list');
    syncUltraMobileLayout();
    if (!state.bookings.length) {
        body.innerHTML = '<tr><td colspan="10" class="text-center py-10 text-muted">Sin reservas para esta fecha</td></tr>';
        mobileList.innerHTML = '<div class="text-center py-8 text-muted">Sin reservas para esta fecha</div>';
        return;
    }
    const decorated = getDecoratedBookings();
    body.innerHTML = decorated.map(({ b, total, paid, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking, recurringId }) => {
        return `<tr class="cursor-pointer" data-detail-url="${bookingLink}" data-recurring-id="${recurringId}" data-date-booking="${state.fecha}" onclick="bcRowNavigate(event, this)">
            <td class="ps-4 fw-bold">${b.hour_label || ''}</td>
            <td>${numeroCancha}</td>
            <td>${b.cancha || ''}${recurringTag}</td>
            <td>${isRealBooking
                ? `<a href="${bookingLink}" class="fw-bold text-hover-primary">${customerName}</a>`
                : `<button class="btn btn-link p-0 fw-bold text-hover-primary btn-open-recurring-detail" data-recurring-id="${recurringId}" data-date-booking="${state.fecha}">${customerName}</button>`}</td>
            <td><a href="https://wa.me/${(b.customer_phone || '').replace(/\D/g, '')}" target="_blank" class="text-muted">${b.customer_phone || ''}</a></td>
            <td class="text-end">${fmtMoney(total)}</td>
            <td class="text-end text-success">${fmtMoney(paid)}</td>
            <td class="text-end ${saldo > 0 ? 'text-warning fw-bold' : 'text-muted'}">${fmtMoney(saldo)}</td>
            <td class="text-center">${badge}</td>
            <td class="text-end pe-4">${btn}</td>
        </tr>`;
    }).join('');

    mobileList.innerHTML = decorated.map(({ b, total, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking, recurringId }) => `
        <div class="dia-mobile-card mb-3 cursor-pointer" data-detail-url="${bookingLink}" data-recurring-id="${recurringId}" data-date-booking="${state.fecha}" onclick="bcRowNavigate(event, this)">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="fw-bolder">${b.hour_label || ''}</div>
                <div>${numeroCancha}</div>
            </div>
            <div class="fw-bold mb-1">${b.cancha || ''}${recurringTag}</div>
            <div class="mb-2">${isRealBooking
                ? `<a href="${bookingLink}" class="fw-bold text-hover-primary">${customerName}</a>`
                : `<button class="btn btn-link p-0 fw-bold text-hover-primary btn-open-recurring-detail" data-recurring-id="${recurringId}" data-date-booking="${state.fecha}">${customerName}</button>`}</div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted fs-8">Total</span>
                <span class="fw-bold">${fmtMoney(total)}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted fs-8">Saldo</span>
                <span class="${saldo > 0 ? 'text-warning fw-bold' : 'text-success fw-bold'}">${fmtMoney(saldo)}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div>${badge}</div>
                <div>${btn}</div>
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.btn-cobrar').forEach((btn) => {
        btn.addEventListener('click', () => abrirModalCobrar(btn.dataset.id));
    });
    document.querySelectorAll('.btn-cobrar-planned').forEach((btn) => {
        btn.addEventListener('click', () => generarYcobrar(btn));
    });
    document.querySelectorAll('.btn-open-recurring-detail').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openOrCreateRecurringDetail(Number(btn.dataset.recurringId || 0), btn.dataset.dateBooking || state.fecha);
        });
    });
    bindRowNavigation('#tabla-dia-body tr[data-detail-url]');
    bindRowNavigation('#dia-mobile-list .dia-mobile-card[data-detail-url]');
}

function renderKPIs() {
    const total = state.bookings.length;
    // Contadores basados en saldo real
    const pagadas = state.bookings.filter((b) => (Number(b.total_amount) - Number(b.paid_amount)) <= 0).length;
    const pendientes = state.bookings.filter((b) => (Number(b.total_amount) - Number(b.paid_amount)) > 0).length;
    const saldoTotal = state.bookings.reduce((acc, b) => acc + Math.max(0, Number(b.total_amount) - Number(b.paid_amount)), 0);
    document.getElementById('kpi-total').textContent = total;
    document.getElementById('kpi-pagadas').textContent = pagadas;
    document.getElementById('kpi-pendientes').textContent = pendientes;
    document.getElementById('kpi-saldo').textContent = fmtMoney(saldoTotal);
}

// --- Cargar bookings del día ---
function cargarDia(afterLoad) {
    document.getElementById('tabla-dia-body').innerHTML = '<tr><td colspan="10" class="text-center py-10 text-muted">Cargando...</td></tr>';
    document.getElementById('dia-mobile-list').innerHTML = '<div class="text-center py-8 text-muted">Cargando...</div>';
    fun.xhr({
        url: 'getDia',
        method: 'POST',
        data: fun.setForm({ 
            fecha: state.fecha,
            field_id: document.getElementById('filtroCancha')?.value || ''
        }),
        success: (resp) => {
            state.bookings = resp.bookings || [];
            renderKPIs();
            renderTabla();
            if (typeof afterLoad === 'function') afterLoad();
        },
        error: (err) => {
            document.getElementById('tabla-dia-body').innerHTML = '<tr><td colspan="10" class="text-center py-10 text-danger">Error al cargar</td></tr>';
            document.getElementById('dia-mobile-list').innerHTML = '<div class="text-center py-8 text-danger">Error al cargar</div>';
            console.error(err);
        },
    });
}

function generarYcobrar(btn) {
    const recurringId = Number(btn.dataset.recurringId || 0);
    if (!recurringId) return;
    btn.disabled = true;
    btn.setAttribute('data-kt-indicator', 'on');
    fun.xhr({
        url: 'generateRecurringBookingForDate',
        method: 'POST',
        data: fun.setForm({ recurring_booking_id: recurringId, date_booking: state.fecha }),
        success: (resp) => {
            btn.removeAttribute('data-kt-indicator');
            btn.disabled = false;
            if (resp && resp.ok && resp.booking_id) {
                cargarDia(() => abrirModalCobrar(resp.booking_id));
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'No se pudo generar el turno' });
            }
        },
        error: (err) => {
            btn.removeAttribute('data-kt-indicator');
            btn.disabled = false;
            fun.swal({ icon: 'error', title: err?.error || 'Error de red' });
        },
    });
}

function openOrCreateRecurringDetail(recurringId, dateBooking) {
    if (!recurringId) return;
    fun.xhr({
        url: 'generateRecurringBookingForDate',
        method: 'POST',
        data: fun.setForm({ recurring_booking_id: recurringId, date_booking: dateBooking || state.fecha }),
        success: (resp) => {
            if (resp && resp.ok && resp.booking_id) {
                window.location.href = `reserva/${resp.booking_id}`;
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'No se pudo abrir el detalle' });
            }
        },
        error: (err) => {
            fun.swal({ icon: 'error', title: err?.error || 'Error de red' });
        },
    });
}

// --- Modal Cobrar ---
function abrirModalCobrar(bookingId) {
    const b = state.bookings.find((x) => String(x.id) === String(bookingId));
    if (!b) return;
    const total = Number(b.total_amount) || 0;
    const paid = Number(b.paid_amount) || 0;
    const saldo = Math.max(0, total - paid);

    document.getElementById('modal-bookingId').value = b.id;
    document.getElementById('modal-cliente').textContent = b.customer_name || '—';
    document.getElementById('modal-canchaHora').textContent = `${b.cancha || ''} · ${b.hour_label || ''}`;
    document.getElementById('modal-total').textContent = fmtMoney(total);
    document.getElementById('modal-pagado').textContent = fmtMoney(paid);
    document.getElementById('modal-saldo').textContent = fmtMoney(saldo);
    document.getElementById('modal-monto').value = saldo.toFixed(2);
    document.getElementById('modal-nota').value = '';

    new bootstrap.Modal(document.getElementById('modalCobrar')).show();
}

document.getElementById('modal-btn-saldo-completo').addEventListener('click', () => {
    const bookingId = document.getElementById('modal-bookingId').value;
    const b = state.bookings.find((x) => String(x.id) === String(bookingId));
    const saldo = Math.max(0, Number(b.total_amount) - Number(b.paid_amount));
    document.getElementById('modal-monto').value = saldo.toFixed(2);
});

document.getElementById('modal-btn-confirmar').addEventListener('click', () => {
    const bookingId = document.getElementById('modal-bookingId').value;
    const monto = parseFloat(document.getElementById('modal-monto').value);
    const nota = document.getElementById('modal-nota').value;

    if (!monto || monto <= 0) {
        fun.swal({ icon: 'warning', title: 'Ingresá un monto válido' });
        return;
    }

    fun.xhr({
        url: 'cobrarSaldo',
        method: 'POST',
        data: fun.setForm({ booking_id: bookingId, amount: monto, note: nota }),
        success: (resp) => {
            if (resp && resp.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalCobrar')).hide();
                fun.swal({ icon: 'success', title: 'Cobro registrado' });
                cargarDia();
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'Error al registrar el cobro' });
            }
        },
        error: () => fun.swal({ icon: 'error', title: 'Error de red' }),
    });
});

// --- Exportar PDF ---
function exportarPDF() {
    const fecha = document.getElementById('filtroFecha')?.value || new Date().toISOString().slice(0, 10);
    const fieldId = document.getElementById('filtroCancha')?.value || '';
    const params = new URLSearchParams({ fecha });
    if (fieldId) params.set('field_id', fieldId);

    const url = `fetch/exportDiaPdf?${params.toString()}`;
    window.open(url, '_blank');
}

// --- Init ---
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('filtroFecha');
    input.value = state.fecha;
    input.addEventListener('change', () => {
        state.fecha = input.value;
        cargarDia();
    });

    const btnPDF = document.getElementById('btnExportarPDF');
    if (btnPDF) {
        btnPDF.addEventListener('click', exportarPDF);
    }

    const selCancha = document.getElementById('filtroCancha');
    if (selCancha) {
        selCancha.addEventListener('change', cargarDia);
    }

    window.addEventListener('resize', () => {
        syncUltraMobileLayout();
        renderTabla();
    });

    cargarDia();
});
