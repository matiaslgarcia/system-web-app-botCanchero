function exportarIngresosPDF(inputDate) {
    const dateLabel = inputDate?.value || '';
    const params = new URLSearchParams({ scope: 'mi' });
    if (dateLabel) params.set('date', dateLabel);
    const url = `fetch/exportIngresosPdf?${params.toString()}`;
    window.open(url, '_blank');
}

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

    document.querySelector('#btnExportarIngresosPDF')?.addEventListener('click', () => {
        exportarIngresosPDF(inputDate);
    });

    // Datepicker para el modal
    const extraDate = document.querySelector('#extraIngresoDate');
    if (extraDate && typeof $ !== 'undefined' && $.fn.daterangepicker) {
        $(extraDate).daterangepicker({
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

    // --- Ingreso extra: guardar ---
    document.querySelector('#formExtraIngreso')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const btn = document.querySelector('#btnGuardarExtra');
        const errorBox = document.querySelector('#extraIngresoError');
        errorBox.classList.add('d-none');
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        btn.disabled = true;

        const data = new FormData(form);
        const rawDate = data.get('date_income') || '';
        if (/^\d{2}\/\d{2}\/\d{4}$/.test(rawDate)) {
            const [d, m, y] = rawDate.split('/');
            data.set('date_income', `${y}-${m}-${d}`);
        }
        data.set('csrf_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

        try {
            const res = await fetch('fetch/addExtraIngreso', { method: 'POST', body: data });
            const json = await res.json();
            if (json.success) {
                bootstrap.Modal.getInstance(document.querySelector('#modalExtraIngreso'))?.hide();
                location.reload();
            } else {
                errorBox.textContent = json.error || 'Error al guardar.';
                errorBox.classList.remove('d-none');
            }
        } catch {
            errorBox.textContent = 'Error de conexión.';
            errorBox.classList.remove('d-none');
        } finally {
            btn.querySelector('.indicator-label').classList.remove('d-none');
            btn.querySelector('.indicator-progress').classList.add('d-none');
            btn.disabled = false;
        }
    });

    // --- Ingreso extra: eliminar ---
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete-extra');
        if (!btn) return;
        if (!confirm('¿Eliminar este ingreso extra?')) return;
        const id = btn.dataset.id;
        const data = new FormData();
        data.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
        data.append('id', id);
        try {
            const res = await fetch('fetch/deleteExtraIngreso', { method: 'POST', body: data });
            const json = await res.json();
            if (json.success) location.reload();
            else alert('Error al eliminar: ' + (json.error || ''));
        } catch {
            alert('Error de conexión.');
        }
    });
};

// Cargar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMiIngresos);
} else {
    initMiIngresos();
}
