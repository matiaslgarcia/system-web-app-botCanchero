let inputDate = document.querySelector('#date')
let inputIdField = document.querySelector('#id_field')

function exportarIngresosPDF() {
    const dateLabel = inputDate?.value || '';
    const cancha = inputIdField?.value || '%';
    const params = new URLSearchParams({ scope: 'admin' });
    if (dateLabel) params.set('date', dateLabel);
    if (cancha) params.set('cancha', cancha);
    const url = `fetch/exportIngresosPdf?${params.toString()}`;
    window.open(url, '_blank');
}

$("#kt_datatable_example_1").dataTable();

document.querySelector('#form-filtro').addEventListener('submit', (e) => {
    e.preventDefault()
    const cancha = inputIdField.value || '%'
    let url = 'ingresos?date=' + encodeURIComponent(inputDate.value) + '&cancha=' + encodeURIComponent(cancha)
    location.href = url
})

$(inputDate).daterangepicker({
    singleDatePicker: true,
    locale: {
        format: 'DD/MM/YYYY'
    }
});

document.querySelector('#btnExportarIngresosPDF')?.addEventListener('click', exportarIngresosPDF);
