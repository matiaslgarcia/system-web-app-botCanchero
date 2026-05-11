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

$(inputDate).daterangepicker({ singleDatePicker: true, locale: { format: 'DD/MM/YYYY' } });
$('#extraIngresoDate').daterangepicker({ singleDatePicker: true, locale: { format: 'DD/MM/YYYY' } });

document.querySelector('#btnExportarIngresosPDF')?.addEventListener('click', exportarIngresosPDF);

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
    // Convertir fecha DD/MM/YYYY → YYYY-MM-DD
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
