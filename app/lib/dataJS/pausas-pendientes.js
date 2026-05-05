import { Func } from './function.js';
const fun = new Func;

const DAYS = ['', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

const state = {
    items: [],
    filtroEstado: 'paused',
    counts: {
        paused: 0,
        active: 0,
        cancelled: 0,
    }
};

function renderKPIs() {
    document.getElementById('kpi-pausadas').textContent = state.counts.paused || 0;
    document.getElementById('kpi-activas').textContent = state.counts.active || 0;
    document.getElementById('kpi-canceladas').textContent = state.counts.cancelled || 0;
}

function getCountsFallback(items = [], currentStatus = '') {
    if (currentStatus === 'paused') return { paused: items.length, active: 0, cancelled: 0 };
    if (currentStatus === 'active') return { paused: 0, active: items.length, cancelled: 0 };
    if (currentStatus === 'cancelled') return { paused: 0, active: 0, cancelled: items.length };
    return {
        paused: items.filter((x) => x.status === 'paused').length,
        active: items.filter((x) => x.status === 'active').length,
        cancelled: items.filter((x) => x.status === 'cancelled').length,
    };
}

function renderItems() {
    const wrap = document.getElementById('lista-pausas');
    if (!state.items.length) {
        const estadoLabel = state.filtroEstado || 'disponibles';
        wrap.innerHTML = `
            <div class="col-12">
                <div class="card border border-dashed border-gray-300">
                    <div class="card-body text-center py-12">
                        <i class="fa-solid fa-inbox fs-2x text-gray-400 mb-3"></i>
                        <div class="fw-bold fs-5 mb-2">No hay reservas fijas ${estadoLabel}</div>
                        <div class="text-muted mb-4">Cuando existan, se van a mostrar acá.</div>
                        <button id="btn-refrescar" class="btn btn-light-primary btn-sm">Actualizar</button>
                    </div>
                </div>
            </div>`;
        document.getElementById('btn-refrescar')?.addEventListener('click', cargar);
        return;
    }
    wrap.innerHTML = state.items.map((p) => {
        const fromVig = (p.valid_from || '').split('-').reverse().join('/');
        const toVig = p.valid_until ? p.valid_until.split('-').reverse().join('/') : 'Indefinido';
        const reason = p.cancelled_reason || '<em class="text-muted">sin motivo</em>';
        const statusText = p.status === 'paused'
            ? '<span class="badge badge-light-warning">Pausada</span>'
            : (p.status === 'active'
                ? '<span class="badge badge-light-success">Activa</span>'
                : '<span class="badge badge-light-danger">Cancelada</span>');

        const actions = p.status === 'paused' ? `
            <button class="btn btn-sm btn-light-success btn-reactivar" data-id="${p.id}">
                <i class="fa-solid fa-play me-1"></i>Reactivar
            </button>
            <button class="btn btn-sm btn-light-danger ms-2 btn-cancelar" data-id="${p.id}">
                <i class="fa-solid fa-trash me-1"></i>Cancelar
            </button>` : `
            ${statusText}`;

        return `
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-bold fs-5">${p.customer_name || 'Cliente'}</div>
                            <div class="text-muted fs-7">${p.customer_phone || ''}</div>
                        </div>
                        <span class="badge badge-light-info">${DAYS[p.day_of_week]} ${(p.start_time || '').slice(0,5)}</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa-solid fa-futbol text-muted me-1"></i>
                        <span>${p.field_name || ''}</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa-solid fa-calendar text-muted me-1"></i>
                        <span>${fromVig} → ${toVig}</span>
                    </div>
                    ${p.status === 'cancelled' ? `<div class="mb-3 p-2 bg-light rounded fs-7">${reason}</div>` : ''}
                    <div class="d-flex justify-content-end">${actions}</div>
                </div>
            </div>
        </div>`;
    }).join('');

    document.querySelectorAll('.btn-reactivar').forEach((b) => b.addEventListener('click', () => abrirReview(b.dataset.id, 'active')));
    document.querySelectorAll('.btn-cancelar').forEach((b) => b.addEventListener('click', () => abrirReview(b.dataset.id, 'cancelled')));
}

function cargar() {
    document.getElementById('lista-pausas').innerHTML = '<div class="col-12 text-center py-10 text-muted">Cargando...</div>';
    fun.xhr({
        url: 'getPausasPendientes',
        method: 'POST',
        data: fun.setForm({
            status: state.filtroEstado,
            field_id: document.getElementById('filtroCancha')?.value || ''
        }),
        success: (resp) => {
            state.items = resp.items || [];
            state.counts = resp.counts || getCountsFallback(state.items, state.filtroEstado);
            renderKPIs();
            renderItems();
        },
    });
}

function abrirReview(id, decision) {
    const p = state.items.find((x) => String(x.id) === String(id));
    document.getElementById('review-id').value = id;
    document.getElementById('review-decision').value = decision;
    document.getElementById('review-info').innerHTML =
        `Vas a <strong class="text-${decision === 'active' ? 'success' : 'danger'}">${decision === 'active' ? 'reactivar' : 'cancelar'}</strong> la reserva fija de <strong>${p.customer_name}</strong>.`;
    document.getElementById('review-note').value = '';
    new bootstrap.Modal(document.getElementById('modalReview')).show();
}

document.getElementById('review-confirmar').addEventListener('click', () => {
    const payload = {
        id: document.getElementById('review-id').value,
        status: document.getElementById('review-decision').value,
        reason: document.getElementById('review-note').value,
    };
    fun.xhr({
        url: 'updateRecurringBookingStatus',
        method: 'POST',
        data: fun.setForm(payload),
        success: (resp) => {
            if (resp && resp.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalReview')).hide();
                fun.swal({ icon: 'success', title: 'Acción aplicada' });
                cargar();
            } else {
                fun.swal({ icon: 'error', title: resp?.error || 'Error' });
            }
        },
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filtroCancha')?.addEventListener('change', cargar);
    document.getElementById('filtroEstado').addEventListener('change', (e) => {
        state.filtroEstado = e.target.value;
        cargar();
    });
    cargar();
});
