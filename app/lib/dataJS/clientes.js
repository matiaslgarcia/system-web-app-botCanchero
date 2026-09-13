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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClientes);
} else {
    initClientes();
}
