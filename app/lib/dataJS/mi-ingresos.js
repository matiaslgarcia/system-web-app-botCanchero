import { Func } from './function.js';
const fun = new Func;

const initMiIngresos = () => {
    console.log('Iniciando Mi Ingresos v1.0.11');
    const inputDate = document.querySelector('#date');

    // Destruir si ya existe para evitar conflictos
    if ($.fn.DataTable.isDataTable('#kt_datatable_example_1')) {
        $('#kt_datatable_example_1').DataTable().destroy();
    }

    // Inicializar DataTable con idioma local corregido
    $("#kt_datatable_example_1").DataTable({
        "language": {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "order": [[0, "desc"]],
        "pageLength": 10,
        "dom": "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Filtro de fecha
    document.querySelector('#form-filtro')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const url = 'mi-ingresos?date=' + inputDate.value;
        location.href = url;
    });

    // Datepicker
    if (inputDate && typeof $ !== 'undefined' && $.fn.daterangepicker) {
        $(inputDate).daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            locale: {
                format: 'DD/MM/YYYY',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            }
        });
    }
};

// Cargar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMiIngresos);
} else {
    initMiIngresos();
}