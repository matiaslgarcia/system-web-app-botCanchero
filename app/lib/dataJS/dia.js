import { Func } from './function.js';
const fun = new Func;

// --- Estado local ---
const state = {
    fecha: new Date().toLocaleDateString('en-CA'), // Obtiene YYYY-MM-DD en hora local
    bookings: [],
};

const fmtMoney = (n) => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const isUltraMobile = () => window.matchMedia('(max-width: 767.98px)').matches;

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
        const btn = (saldo > 0 && canCharge)
            ? `<button class="btn btn-sm btn-primary btn-cobrar" data-id="${b.id}"><i class="fa-solid fa-cash-register me-1"></i> Cobrar</button>`
            : (saldo <= 0
                ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Cobrado</span>'
                : '<span class="badge badge-light-secondary">Sin turno generado</span>');
        const recurringTag = esFija ? ' <span class="badge badge-light-info ms-1">♻️ Fija</span>' : '';
        const customerName = b.customer_name || 'Cliente sin nombre';
        const bookingLink = isRealBooking ? `reserva/${b.id}` : '';
        const slotKey = `${b.id_field || ''}|${b.hour_label || ''}`;
        const slotInfo = slotUsage.get(slotKey);
        const nextIndex = (slotRowIndex.get(slotKey) || 0) + 1;
        slotRowIndex.set(slotKey, nextIndex);
        const numeroCancha = slotInfo
            ? `<span class="badge badge-light-primary" title="Cupo ${nextIndex} de ${slotInfo.threshold}">Cancha ${nextIndex}</span>`
            : '<span class="text-muted">-</span>';

        return { b, total, paid, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking };
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
    body.innerHTML = decorated.map(({ b, total, paid, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking }) => {
        return `<tr>
            <td class="ps-4 fw-bold">${b.hour_label || ''}</td>
            <td>${numeroCancha}</td>
            <td>${b.cancha || ''}${recurringTag}</td>
            <td>${isRealBooking ? `<a href="${bookingLink}" class="fw-bold text-hover-primary">${customerName}</a>` : customerName}</td>
            <td><a href="https://wa.me/${(b.customer_phone || '').replace(/\D/g, '')}" target="_blank" class="text-muted">${b.customer_phone || ''}</a></td>
            <td class="text-end">${fmtMoney(total)}</td>
            <td class="text-end text-success">${fmtMoney(paid)}</td>
            <td class="text-end ${saldo > 0 ? 'text-warning fw-bold' : 'text-muted'}">${fmtMoney(saldo)}</td>
            <td class="text-center">${badge}</td>
            <td class="text-end pe-4">${btn}</td>
        </tr>`;
    }).join('');

    mobileList.innerHTML = decorated.map(({ b, total, saldo, badge, btn, recurringTag, customerName, bookingLink, numeroCancha, isRealBooking }) => `
        <div class="dia-mobile-card mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="fw-bolder">${b.hour_label || ''}</div>
                <div>${numeroCancha}</div>
            </div>
            <div class="fw-bold mb-1">${b.cancha || ''}${recurringTag}</div>
            <div class="mb-2">${isRealBooking ? `<a href="${bookingLink}" class="fw-bold text-hover-primary">${customerName}</a>` : customerName}</div>
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
function cargarDia() {
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
        },
        error: (err) => {
            document.getElementById('tabla-dia-body').innerHTML = '<tr><td colspan="10" class="text-center py-10 text-danger">Error al cargar</td></tr>';
            document.getElementById('dia-mobile-list').innerHTML = '<div class="text-center py-8 text-danger">Error al cargar</div>';
            console.error(err);
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
    const element = document.getElementById('kt_content_container');
    const fechaText = document.getElementById('filtroFecha').value;
    
    // Configuración de html2pdf
    const opt = {
        margin:       [0.5, 0.5],
        filename:     `Reservas_${fechaText}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // Ocultar elementos que no queremos en el PDF
    const toolbars = document.querySelectorAll('.card-toolbar, .btn-cobrar, .pe-4');
    toolbars.forEach(el => el.style.visibility = 'hidden');

    fun.swal({
        title: 'Generando PDF...',
        text: 'Por favor espera un momento',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    html2pdf().set(opt).from(element).save().then(() => {
        toolbars.forEach(el => el.style.visibility = 'visible');
        Swal.close();
    });
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
