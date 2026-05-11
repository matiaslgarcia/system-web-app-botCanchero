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
    const btnEnviar     = document.querySelector('#btnEnviar');
    const btnTodos      = document.querySelector('#btnSeleccionarTodos');

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
            renderLista(todosLosClientes);
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

    // --- Contadores y estado del botón ---
    const actualizarContadores = () => {
        const n = seleccionados.size;
        countSelEl.textContent    = n;
        countEnviarEl.textContent = n;
        btnEnviar.disabled = n === 0 || !mensajeEl?.value.trim();
    };

    // --- Preview y contador de caracteres ---
    mensajeEl?.addEventListener('input', () => {
        const txt = mensajeEl.value;
        charCountEl.textContent = `${txt.length} / 4096`;
        previewEl.innerHTML = txt
            ? txt.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            : '<span class="text-muted fst-italic">El mensaje aparecerá aquí...</span>';
        actualizarContadores();
    });

    // --- Envío ---
    btnEnviar?.addEventListener('click', async () => {
        const mensaje = mensajeEl?.value.trim();
        const phones  = [...seleccionados];
        if (!mensaje || !phones.length) return;

        const confirmResult = await swal({
            icon: 'question',
            title: 'Confirmar envío',
            html: `Se enviará el siguiente mensaje a <strong>${phones.length} destinatario(s)</strong>:<br><br>
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
        phones.forEach((p) => formData.append('recipients[]', p));

        try {
            const res  = await fetch('fetch/enviarMensajeWA', { method: 'POST', body: formData });
            const json = await res.json();
            mostrarResultado(json, phones.length);
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

    cargarClientes();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEnviarMensaje);
} else {
    initEnviarMensaje();
}
