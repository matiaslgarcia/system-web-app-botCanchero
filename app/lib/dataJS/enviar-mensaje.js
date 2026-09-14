const initEnviarMensaje = () => {
    console.log('Iniciando Enviar Mensaje WA v1.1.0');

    let todosLosClientes = [];
    let seleccionados = new Set();

    const listaEl       = document.querySelector('#listaClientes');
    const buscarEl      = document.querySelector('#buscarCliente');
    const mensajeEl     = document.querySelector('#mensajeTexto');
    const previewEl     = document.querySelector('#previewMensaje');
    const charCountEl   = document.querySelector('#charCount');
    const countSelEl    = document.querySelector('#countSeleccionados');
    const countTotalEl  = document.querySelector('#countTotal');
    const countEnviarEl = document.querySelector('#countEnviar');
    const destinatarioLabelEl = document.querySelector('#destinatarioLabel');
    const tiempoEstimadoEl = document.querySelector('#tiempoEstimado');
    const btnEnviar     = document.querySelector('#btnEnviar');
    const btnTodos      = document.querySelector('#btnSeleccionarTodos');
    const historialEl   = document.querySelector('#historialWA');
    let templateKeyActual = null;

    const swal = (opts) => Swal.fire({
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            actions: 'd-flex gap-3 justify-content-center',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-light-danger',
            denyButton: 'btn btn-light',
        },
        ...opts,
    });

    // --- Carga de clientes ---
    const cargarClientes = async () => {
        try {
            const res = await fetch('fetch/getClientesMensaje');
            todosLosClientes = await res.json();
            countTotalEl.textContent = todosLosClientes.length;

            // Item 17 (auditoría UX/UI): "Enviar WhatsApp a estos" desde
            // Clientes llega acá con ?phones=549...,549... -- se preseleccionan
            // en vez de obligar a volver a tildarlos uno por uno.
            const phonesParam = new URLSearchParams(window.location.search).get('phones');
            if (phonesParam) {
                const digitsOnly = (p) => String(p || '').replace(/\D/g, '');
                const wanted = new Set(phonesParam.split(',').map(digitsOnly).filter(Boolean));
                todosLosClientes.forEach((c) => {
                    if (wanted.has(digitsOnly(c.phone))) seleccionados.add(c.phone);
                });
            }

            renderLista(todosLosClientes);
            actualizarContadores();
        } catch {
            listaEl.innerHTML = '<div class="text-danger text-center py-5"><i class="fa-solid fa-triangle-exclamation me-2"></i>Error al cargar clientes.</div>';
        }
    };

    // --- Render de la lista filtrada ---
    const renderLista = (clientes) => {
        if (!clientes.length) {
            listaEl.innerHTML = '<div class="text-muted text-center py-5">No se encontraron clientes.</div>';
            return;
        }
        listaEl.innerHTML = clientes.map((c) => `
            <label class="d-flex align-items-center gap-3 p-3 rounded cursor-pointer mb-1 cliente-item ${seleccionados.has(c.phone) ? 'bg-light-success' : 'bg-hover-light'}"
                   style="cursor:pointer;" data-phone="${c.phone}">
                <input type="checkbox" class="form-check-input flex-shrink-0" value="${c.phone}"
                       ${seleccionados.has(c.phone) ? 'checked' : ''}>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-gray-800 text-truncate">${escapeHtml(c.full_name)}</div>
                    <div class="text-muted fs-8">${escapeHtml(c.phone)}</div>
                </div>
            </label>
        `).join('');

        listaEl.querySelectorAll('input[type=checkbox]').forEach((cb) => {
            cb.addEventListener('change', () => toggleSeleccion(cb.value, cb.checked, cb.closest('label')));
        });
    };

    const escapeHtml = (str) => String(str).replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

    // TXT-01: "destinatario(s)" no dice nada para 1 — plural real según cantidad.
    const plural = (n, singular, pluralWord = singular + 's') => (n === 1 ? singular : pluralWord);

    // --- Toggle individual ---
    const toggleSeleccion = (phone, checked, labelEl) => {
        if (checked) {
            seleccionados.add(phone);
            labelEl?.classList.replace('bg-hover-light', 'bg-light-success');
        } else {
            seleccionados.delete(phone);
            labelEl?.classList.replace('bg-light-success', 'bg-hover-light');
        }
        actualizarContadores();
    };

    // --- Seleccionar / deseleccionar todos (filtrados) ---
    let todosSeleccionados = false;
    btnTodos?.addEventListener('click', () => {
        const filtrados = getClientesFiltrados();
        todosSeleccionados = !todosSeleccionados;
        filtrados.forEach((c) => {
            if (todosSeleccionados) seleccionados.add(c.phone);
            else seleccionados.delete(c.phone);
        });
        btnTodos.textContent = todosSeleccionados ? 'Deseleccionar todos' : 'Seleccionar todos';
        renderLista(filtrados);
        actualizarContadores();
    });

    // --- Búsqueda ---
    buscarEl?.addEventListener('input', () => {
        todosSeleccionados = false;
        if (btnTodos) btnTodos.textContent = 'Seleccionar todos';
        renderLista(getClientesFiltrados());
    });

    const getClientesFiltrados = () => {
        const q = (buscarEl?.value || '').toLowerCase().trim();
        if (!q) return todosLosClientes;
        return todosLosClientes.filter((c) =>
            c.full_name.toLowerCase().includes(q) || c.phone.includes(q)
        );
    };

    // OP-01: "~300ms por mensaje para respetar límites de Meta" es
    // información técnica que no contesta lo que el canchero quiere saber
    // (¿cuánto tarda esto?). Traducido a lo que sí importa.
    const tiempoEstimadoTexto = (n) => {
        if (n === 0) return 'Se envía al instante.';
        const segundos = Math.max(1, Math.ceil(n * 0.3));
        return segundos <= 1
            ? 'Se envía en menos de 1 segundo.'
            : `Se envía en aproximadamente ${segundos} segundos.`;
    };

    // --- Contadores y estado del botón ---
    const actualizarContadores = () => {
        const n = seleccionados.size;
        countSelEl.textContent    = n;
        countEnviarEl.textContent = n;
        if (destinatarioLabelEl) destinatarioLabelEl.textContent = plural(n, 'destinatario');
        if (tiempoEstimadoEl) tiempoEstimadoEl.textContent = tiempoEstimadoTexto(n);
        btnEnviar.disabled = n === 0 || !mensajeEl?.value.trim();
    };

    // --- Preview y contador de caracteres ---
    mensajeEl?.addEventListener('input', () => {
        const txt = mensajeEl.value;
        charCountEl.textContent = `${txt.length} / 4096`;
        // La vista previa muestra {nombre} tal cual -- cada destinatario recibe
        // su propio nombre recién al enviar, uno por uno.
        previewEl.innerHTML = txt
            ? txt.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\{nombre\}/gi, '<strong>{nombre}</strong>')
            : '<span class="text-muted fst-italic">El mensaje aparecerá aquí...</span>';
        templateKeyActual = null;
        actualizarContadores();
    });

    // --- Plantillas ---
    document.querySelectorAll('.btn-plantilla').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!mensajeEl) return;
            mensajeEl.value = btn.dataset.templateText || '';
            templateKeyActual = btn.dataset.templateKey || null;
            mensajeEl.dispatchEvent(new Event('input'));
            mensajeEl.focus();
        });
    });
    document.querySelector('#btnLimpiarPlantilla')?.addEventListener('click', () => {
        if (!mensajeEl) return;
        mensajeEl.value = '';
        templateKeyActual = null;
        mensajeEl.dispatchEvent(new Event('input'));
    });

    // --- Envío ---
    btnEnviar?.addEventListener('click', async () => {
        const mensaje = mensajeEl?.value.trim();
        const phones  = [...seleccionados];
        if (!mensaje || !phones.length) return;

        const confirmResult = await swal({
            icon: 'question',
            title: 'Confirmar envío',
            html: `Se enviará el siguiente mensaje a <strong>${phones.length} ${plural(phones.length, 'destinatario')}</strong> (${tiempoEstimadoTexto(phones.length).toLowerCase()}):<br><br>
                   <div class="text-start bg-light rounded p-3 fs-7" style="white-space:pre-wrap;max-height:120px;overflow-y:auto">${escapeHtml(mensaje)}</div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-brands fa-whatsapp me-2"></i>Sí, enviar',
            cancelButtonText: 'Cancelar',
        });
        if (!confirmResult.isConfirmed) return;

        btnEnviar.querySelector('.indicator-label').classList.add('d-none');
        btnEnviar.querySelector('.indicator-progress').classList.remove('d-none');
        btnEnviar.disabled = true;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const formData = new FormData();
        formData.append('csrf_token', csrf);
        formData.append('message', mensaje);
        if (templateKeyActual) formData.append('template_key', templateKeyActual);
        phones.forEach((p) => formData.append('recipients[]', p));

        try {
            const res  = await fetch('fetch/enviarMensajeWA', { method: 'POST', body: formData });
            const json = await res.json();
            mostrarResultado(json, phones.length);
            cargarHistorial();
        } catch {
            mostrarResultado({ success: false, sent: 0, failed: phones.length, errors: ['Error de conexión.'] }, phones.length);
        } finally {
            btnEnviar.querySelector('.indicator-label').classList.remove('d-none');
            btnEnviar.querySelector('.indicator-progress').classList.add('d-none');
            btnEnviar.disabled = seleccionados.size === 0;
        }
    });

    // --- Mostrar resultado con SweetAlert2 ---
    const mostrarResultado = (json, total) => {
        const allOk   = json.failed === 0 && json.sent > 0;
        const allFail = json.sent === 0;

        let errorsHtml = '';
        if (json.errors?.length) {
            errorsHtml = `<div class="text-start mt-3"><ul class="mb-0 ps-3 fs-7">${json.errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('')}</ul></div>`;
        }

        if (allOk) {
            swal({
                icon: 'success',
                title: '¡Mensajes enviados!',
                html: `Se enviaron correctamente <strong>${json.sent}</strong> de <strong>${total}</strong> mensajes.`,
                confirmButtonText: 'Aceptar',
                showCancelButton: false,
            });
        } else if (allFail) {
            swal({
                icon: 'error',
                title: 'Error al enviar',
                html: `No se pudo enviar ningún mensaje.${errorsHtml}`,
                confirmButtonText: 'Cerrar',
                showCancelButton: false,
                customClass: {
                    actions: 'd-flex gap-3 justify-content-center',
                    confirmButton: 'btn btn-light-danger',
                },
            });
        } else {
            swal({
                icon: 'warning',
                title: 'Envío parcial',
                html: `Enviados: <strong class="text-success">${json.sent}</strong> &nbsp;|&nbsp; Fallidos: <strong class="text-danger">${json.failed}</strong>${errorsHtml}`,
                confirmButtonText: 'Aceptar',
                showCancelButton: false,
                customClass: {
                    actions: 'd-flex gap-3 justify-content-center',
                    confirmButton: 'btn btn-warning',
                },
            });
        }
    };

    // --- Historial de envíos (item 11) ---
    const fmtFecha = (iso) => {
        const d = new Date((iso || '').replace(' ', 'T'));
        return Number.isNaN(d.getTime()) ? (iso || '—') : d.toLocaleString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    };
    const TEMPLATE_LABELS = {
        recordatorio: 'Recordatorio de turno',
        horario_libre: 'Se liberó un horario',
        promo_semana: 'Promo día de semana',
    };

    const cargarHistorial = async () => {
        if (!historialEl) return;
        try {
            const res = await fetch('fetch/getWhatsappBroadcasts');
            const json = await res.json();
            renderHistorial(json.broadcasts || []);
        } catch {
            historialEl.innerHTML = '<div class="text-danger text-center py-5">Error al cargar el historial.</div>';
        }
    };

    const renderHistorial = (broadcasts) => {
        if (!broadcasts.length) {
            historialEl.innerHTML = `
                <div class="text-center text-muted py-8">
                    <i class="fa-solid fa-clock-rotate-left fs-2x text-gray-300 mb-3"></i>
                    <div class="fw-bold">Todavía no enviaste ningún mensaje</div>
                    <div class="fs-7">Los envíos que hagas van a aparecer acá, con el resultado por destinatario.</div>
                </div>`;
            return;
        }
        historialEl.innerHTML = `
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-3 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th>Fecha</th>
                            <th>Mensaje</th>
                            <th>Plantilla</th>
                            <th class="text-center">Enviados</th>
                            <th class="text-center">Fallidos</th>
                            <th class="text-end pe-4">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${broadcasts.map((b) => `
                            <tr>
                                <td class="text-nowrap">${fmtFecha(b.created_at)}</td>
                                <td class="text-truncate" style="max-width:280px" title="${escapeHtml(b.message)}">${escapeHtml(b.message)}</td>
                                <td>${b.template_key ? `<span class="badge badge-light-primary">${escapeHtml(TEMPLATE_LABELS[b.template_key] || b.template_key)}</span>` : '<span class="text-muted">—</span>'}</td>
                                <td class="text-center"><span class="badge badge-light-success">${b.total_sent}</span></td>
                                <td class="text-center">${Number(b.total_failed) > 0 ? `<span class="badge badge-light-danger">${b.total_failed}</span>` : '<span class="text-muted">0</span>'}</td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-light-info btn-ver-detalle-wa" data-broadcast-id="${b.id}">Ver</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>`;

        historialEl.querySelectorAll('.btn-ver-detalle-wa').forEach((btn) => {
            btn.addEventListener('click', () => abrirDetalleWA(btn.dataset.broadcastId));
        });
    };

    const abrirDetalleWA = async (broadcastId) => {
        const body = document.querySelector('#detalleWABody');
        if (!body) return;
        body.innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</div>';
        new bootstrap.Modal(document.querySelector('#modalDetalleWA')).show();
        try {
            const res = await fetch(`fetch/getWhatsappBroadcasts?broadcast_id=${encodeURIComponent(broadcastId)}`);
            const json = await res.json();
            if (json.error || !json.broadcast) {
                body.innerHTML = '<div class="text-danger text-center py-5">No se pudo cargar el detalle.</div>';
                return;
            }
            const recipients = json.recipients || [];
            body.innerHTML = `
                <div class="mb-4">
                    <div class="text-gray-800 fw-bold mb-1">Mensaje enviado</div>
                    <div class="bg-light rounded p-3 fs-7" style="white-space:pre-wrap">${escapeHtml(json.broadcast.message)}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-3 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th class="text-center">Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${recipients.map((r) => `
                                <tr>
                                    <td>${escapeHtml(r.full_name || '—')}</td>
                                    <td>${escapeHtml(r.phone)}</td>
                                    <td class="text-center">${r.status === 'sent' ? '<span class="badge badge-light-success">Enviado</span>' : '<span class="badge badge-light-danger">Falló</span>'}</td>
                                    <td class="fs-8 text-muted">${escapeHtml(r.error || '—')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>`;
        } catch {
            body.innerHTML = '<div class="text-danger text-center py-5">No se pudo cargar el detalle.</div>';
        }
    };

    cargarClientes();
    cargarHistorial();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEnviarMensaje);
} else {
    initEnviarMensaje();
}
