// OP-03: la tabla de Clientes no se podía ordenar. Búsqueda y filtro por
// establecimiento/cancha ya se resuelven server-side (GET), así que acá solo
// se habilita el ordenamiento por columna; "Acciones" queda sin ordenar.
const initClientes = () => {
    const tabla = document.querySelector('#tabla-clientes');
    if (!tabla || typeof $ === 'undefined' || !$.fn.DataTable) return;

    if ($.fn.DataTable.isDataTable('#tabla-clientes')) {
        $('#tabla-clientes').DataTable().destroy();
    }

    $('#tabla-clientes').DataTable({
        paging: false,
        searching: false,
        info: false,
        order: [],
        columnDefs: [
            { targets: 'no-sort', orderable: false },
        ],
        language: {
            zeroRecords: 'Sin resultados encontrados',
        },
    });
};

// Item 17 (auditoría UX/UI): la pantalla ya calculaba segmentos (canceladores,
// top facturación, etc.) pero no dejaba accionar sobre un grupo -- selección
// múltiple acá, que arma la lista de teléfonos para Enviar Mensaje.
const initSeleccionClientesWA = () => {
    const checks = () => Array.from(document.querySelectorAll('.chk-cliente-wa'));
    const btnEnviar = document.querySelector('#btn-whatsapp-seleccionados');
    const countEl = document.querySelector('#count-seleccionados-clientes');
    const chkTodos = document.querySelector('#chk-seleccionar-todos-clientes');
    if (!btnEnviar) return;

    const actualizar = () => {
        const seleccionados = checks().filter((c) => c.checked);
        if (countEl) countEl.textContent = seleccionados.length;
        btnEnviar.disabled = seleccionados.length === 0;
    };

    document.addEventListener('change', (e) => {
        if (e.target.classList?.contains('chk-cliente-wa')) actualizar();
    });

    chkTodos?.addEventListener('change', () => {
        checks().forEach((c) => { c.checked = chkTodos.checked; });
        actualizar();
    });

    btnEnviar.addEventListener('click', () => {
        const phones = [...new Set(checks().filter((c) => c.checked).map((c) => c.value))];
        if (!phones.length) return;
        window.location.href = 'enviar-mensaje?phones=' + encodeURIComponent(phones.join(','));
    });

    actualizar();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientes);
    document.addEventListener('DOMContentLoaded', initSeleccionClientesWA);
} else {
    initClientes();
    initSeleccionClientesWA();
}
