import { Func } from './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-cancha');
let map, marker;
const MAX_LOGO_SIZE_BYTES = 3 * 1024 * 1024; // 3MB
const ALLOWED_LOGO_TYPES = ['image/jpeg', 'image/png'];
let geocodeUnavailableNotified = false;
const priceRangesBody = document.getElementById('price-ranges-body');
const btnAddPriceRange = document.getElementById('btn-add-price-range');
const btnPresetPriceRanges = document.getElementById('btn-preset-price-ranges');
const btnSavePriceRanges = document.getElementById('btn-save-price-ranges');

function notifyGeocodeUnavailable() {
    if (geocodeUnavailableNotified) return;
    geocodeUnavailableNotified = true;
    fun.swal({
        icon: 'warning',
        title: 'Geocodificación no disponible',
        text: 'No se pudo consultar direcciones externas. Podés mover el pin en el mapa y guardar latitud/longitud igual.',
        timer: 4000
    });
}

function setFieldError(field, message) {
    if (!field) return;
    field.classList.add('is-invalid');
    let feedback = field.parentElement.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentElement.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFieldError(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentElement.querySelector('.invalid-feedback');
    if (feedback) feedback.remove();
}

function validateCanchaForm() {
    const fullName = document.getElementById('full_name');
    const phone = document.getElementById('phone');
    const priceHour = document.getElementById('price_hour');
    let isValid = true;

    [fullName, phone, priceHour].forEach(clearFieldError);

    if (!fullName.value.trim()) {
        setFieldError(fullName, 'El nombre de la cancha es obligatorio.');
        isValid = false;
    }

    const cleanPhone = phone.value.replace(/\D/g, '');
    if (cleanPhone.length < 8) {
        setFieldError(phone, 'Ingresá un teléfono válido (mínimo 8 dígitos).');
        isValid = false;
    }

    const price = Number(String(priceHour.value).replace(',', '.'));
    if (!Number.isFinite(price) || price <= 0) {
        setFieldError(priceHour, 'Ingresá un precio por hora mayor a 0.');
        isValid = false;
    }

    if (!isValid) {
        fun.swal({
            icon: 'warning',
            title: 'Revisá los campos marcados',
            text: 'Corregí los datos para poder guardar.'
        });
    }

    return isValid;
}

function padTime(value) {
    const [h = '00', m = '00'] = String(value || '').split(':');
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}

function timeToMinutes(value) {
    const [h = '0', m = '0'] = padTime(value).split(':');
    return (Number(h) * 60) + Number(m);
}

function clearRangeErrors() {
    if (!priceRangesBody) return;
    priceRangesBody.querySelectorAll('input').forEach(input => input.classList.remove('is-invalid'));
}

function createPriceRangeRow(range = {}) {
    if (!priceRangesBody) return;
    const tr = document.createElement('tr');
    tr.className = 'price-range-row';
    tr.innerHTML = `
        <td><input type="time" class="form-control form-control-sm range-start" value="${padTime(range.start_time || '08:00')}"></td>
        <td><input type="time" class="form-control form-control-sm range-end" value="${padTime(range.end_time || '18:00')}"></td>
        <td><input type="number" min="1" step="0.01" class="form-control form-control-sm range-price" value="${range.price ?? ''}" placeholder="0.00"></td>
        <td class="text-end"><button type="button" class="btn btn-icon btn-sm btn-light-danger btn-remove-range"><i class="fa-solid fa-trash"></i></button></td>
    `;
    priceRangesBody.appendChild(tr);
}

function loadPriceRanges() {
    if (!priceRangesBody) return;
    priceRangesBody.innerHTML = '';
    const source = document.getElementById('price-ranges-data');
    let ranges = [];
    if (source?.textContent) {
        try {
            ranges = JSON.parse(source.textContent);
        } catch (_e) {
            ranges = [];
        }
    }
    if (!Array.isArray(ranges) || ranges.length === 0) {
        const fallbackPrice = document.getElementById('price_hour')?.value || '';
        createPriceRangeRow({ start_time: '08:00', end_time: '18:00', price: fallbackPrice });
        createPriceRangeRow({ start_time: '18:00', end_time: '23:59', price: fallbackPrice });
        return;
    }
    ranges.forEach((range) => createPriceRangeRow(range));
}

function collectPriceRanges() {
    if (!priceRangesBody) return [];
    clearRangeErrors();

    const rows = Array.from(priceRangesBody.querySelectorAll('.price-range-row'));
    const collected = [];
    for (const row of rows) {
        const startInput = row.querySelector('.range-start');
        const endInput = row.querySelector('.range-end');
        const priceInput = row.querySelector('.range-price');
        const start = padTime(startInput?.value || '');
        const end = padTime(endInput?.value || '');
        const price = Number(String(priceInput?.value || '').replace(',', '.'));

        const startMin = timeToMinutes(start);
        const endMin = timeToMinutes(end);
        let rowValid = true;

        if (!startInput?.value) {
            startInput.classList.add('is-invalid');
            rowValid = false;
        }
        if (!endInput?.value) {
            endInput.classList.add('is-invalid');
            rowValid = false;
        }
        if (!Number.isFinite(price) || price <= 0) {
            priceInput.classList.add('is-invalid');
            rowValid = false;
        }
        if (startMin >= endMin) {
            startInput.classList.add('is-invalid');
            endInput.classList.add('is-invalid');
            rowValid = false;
        }
        if (!rowValid) {
            fun.swal({
                icon: 'warning',
                title: 'Revisá las franjas',
                text: 'Cada franja debe tener inicio, fin y precio válido.'
            });
            return null;
        }
        collected.push({
            start_time: start,
            end_time: end,
            price: price.toFixed(2)
        });
    }

    const sorted = [...collected].sort((a, b) => timeToMinutes(a.start_time) - timeToMinutes(b.start_time));
    for (let i = 1; i < sorted.length; i++) {
        if (timeToMinutes(sorted[i].start_time) < timeToMinutes(sorted[i - 1].end_time)) {
            fun.swal({
                icon: 'warning',
                title: 'Franjas superpuestas',
                text: 'No puede haber superposición de horarios entre franjas.'
            });
            return null;
        }
    }
    return sorted;
}

// --- Leaflet Map Logic with Geocoding ---
async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        if (!response.ok) throw new Error(`Reverse geocode HTTP ${response.status}`);
        const data = await response.json();
        if (data && data.display_name) {
            document.getElementById('address').value = data.display_name;
        }
    } catch (error) {
        console.error('Error in reverse geocoding:', error);
        notifyGeocodeUnavailable();
    }
}

async function forwardGeocode(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`);
        if (!response.ok) throw new Error(`Forward geocode HTTP ${response.status}`);
        const data = await response.json();
        if (data && data.length > 0) {
            const { lat, lon } = data[0];
            const newLat = parseFloat(lat);
            const newLng = parseFloat(lon);
            map.setView([newLat, newLng], 15);
            updateMarker(newLat, newLng, false); // No reverse geocode again to avoid overwriting typed text
        } else {
            fun.swal({ icon: 'warning', title: 'No se encontró la dirección' });
        }
    } catch (error) {
        console.error('Error in forward geocoding:', error);
        notifyGeocodeUnavailable();
    }
}

function updateMarker(lat, lng, doReverse = true) {
    marker.setLatLng([lat, lng]);
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('length').value = lng.toFixed(6);
    if (doReverse) reverseGeocode(lat, lng);
}

function initMap() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('length');

    let initialLat = parseFloat(latInput.value) || -34.6037;
    let initialLng = parseFloat(lngInput.value) || -58.3816;

    map = L.map('map-picker').setView([initialLat, initialLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

    map.on('click', function (e) {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });

    marker.on('dragend', function (e) {
        let pos = marker.getLatLng();
        updateMarker(pos.lat, pos.lng);
    });

    document.getElementById('btn-recenter')?.addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 15);
                updateMarker(latitude, longitude);
            });
        }
    });

    document.getElementById('btn-search-address')?.addEventListener('click', () => {
        const address = document.getElementById('address').value;
        if (address) forwardGeocode(address);
    });

    // Enter en el campo dirección busca en el mapa
    document.getElementById('address')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            forwardGeocode(e.target.value);
        }
    });

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.hash === '#config') {
            map.invalidateSize();
        }
    });
}

// --- Form Handling ---
form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!validateCanchaForm()) return;
    fun.xhr({
        url: 'edit-cancha',
        data: new FormData(form),
        success: (_response) => {
            fun.swal({
                icon: _response.icon,
                title: _response.msg,
                willClose: () => {
                    // Recargar en la misma pestaña
                    window.location.href = window.location.pathname + '?tab=config';
                }
            });
        }
    });
});

document.getElementById('add-user-select-avatar')?.addEventListener('click', () => {
    document.getElementById('logo').click();
});

document.getElementById('logo')?.addEventListener('change', function () {
    if (!this.files || !this.files[0]) return;
    const file = this.files[0];

    if (!ALLOWED_LOGO_TYPES.includes(file.type)) {
        this.value = '';
        fun.swal({
            icon: 'error',
            title: 'Formato no permitido',
            text: 'Solo se permiten imágenes JPG o PNG.'
        });
        return;
    }

    if (file.size > MAX_LOGO_SIZE_BYTES) {
        this.value = '';
        fun.swal({
            icon: 'error',
            title: 'Imagen demasiado pesada',
            text: 'El tamaño máximo permitido es 3MB.'
        });
        return;
    }

    fun.setImgSrc(file, 'add-user-select-avatar');
});

if (priceRangesBody) {
    loadPriceRanges();
    btnAddPriceRange?.addEventListener('click', () => {
        createPriceRangeRow({ start_time: '08:00', end_time: '09:00', price: '' });
    });
    btnPresetPriceRanges?.addEventListener('click', () => {
        const fallbackPrice = document.getElementById('price_hour')?.value || '';
        priceRangesBody.innerHTML = '';
        createPriceRangeRow({ start_time: '08:00', end_time: '18:00', price: fallbackPrice });
        createPriceRangeRow({ start_time: '18:00', end_time: '23:59', price: fallbackPrice });
    });
    priceRangesBody.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.btn-remove-range');
        if (!btn) return;
        btn.closest('.price-range-row')?.remove();
    });
    btnSavePriceRanges?.addEventListener('click', () => {
        const ranges = collectPriceRanges();
        if (!ranges) return;
        const formData = fun.setForm({
            id_field: document.getElementById('id')?.value || '',
            ranges_json: JSON.stringify(ranges),
        });
        fun.xhr({
            url: 'update-price-ranges',
            data: formData,
            success: (response) => {
                fun.swal({
                    icon: response?.icon || 'success',
                    title: response?.msg || 'Franjas guardadas correctamente',
                });
            },
            error: (response) => {
                fun.swal({
                    icon: response?.icon || 'error',
                    title: response?.msg || 'No se pudieron guardar las franjas',
                });
            }
        });
    });
}

// --- Schedules & Services Logic ---

// Selección rápida por grupos (Mañana, Tarde, Noche)
document.querySelectorAll('.btn-select-group').forEach(btn => {
    btn.addEventListener('click', function () {
        const group = this.dataset.group;
        const form = this.closest('form');
        const checks = form.querySelectorAll('input[type="checkbox"]');

        // Determinar si todos los de este grupo ya están marcados
        let allChecked = true;
        checks.forEach(cb => {
            const startTime = cb.nextElementSibling.innerText.trim().split(' - ')[0];
            const hour = parseInt(startTime.split(':')[0]);
            const isPM = startTime.includes('PM');
            let militaryHour = (isPM && hour !== 12) ? hour + 12 : ((!isPM && hour === 12) ? 0 : hour);

            let shouldCheck = false;
            if (group === 'morning' && militaryHour >= 6 && militaryHour < 12) shouldCheck = true;
            if (group === 'afternoon' && militaryHour >= 12 && militaryHour < 18) shouldCheck = true;
            if (group === 'night' && (militaryHour >= 18 || militaryHour < 6)) shouldCheck = true;

            if (shouldCheck && !cb.checked) allChecked = false;
        });

        // Alternar (si todos están marcados, desmarcar todos; si no, marcar todos)
        checks.forEach(cb => {
            const startTime = cb.nextElementSibling.innerText.trim().split(' - ')[0];
            const hour = parseInt(startTime.split(':')[0]);
            const isPM = startTime.includes('PM');
            let militaryHour = (isPM && hour !== 12) ? hour + 12 : ((!isPM && hour === 12) ? 0 : hour);

            let shouldCheck = false;
            if (group === 'morning' && militaryHour >= 6 && militaryHour < 12) shouldCheck = true;
            if (group === 'afternoon' && militaryHour >= 12 && militaryHour < 18) shouldCheck = true;
            if (group === 'night' && (militaryHour >= 18 || militaryHour < 6)) shouldCheck = true;

            if (shouldCheck) cb.checked = !allChecked;
        });
    });
});

// Copiar a todos los días
document.querySelectorAll('.btn-copy-to-all').forEach(btn => {
    btn.addEventListener('click', function () {
        const currentForm = this.closest('form');
        const currentChecks = Array.from(currentForm.querySelectorAll('input[type="checkbox"]')).map(cb => ({
            hour: cb.nextElementSibling.innerText.trim(),
            checked: cb.checked
        }));

        fun.confirm({
            icon: 'question',
            title: '¿Copiar estos horarios a todos los días?',
            confirmButtonText: 'Sí, copiar y guardar todo',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'success',
            cancelVariant: 'secondary',
        }).then((res) => {
            if (!res.isConfirmed) return;
            const allForms = document.querySelectorAll('.form-horario');
            let completed = 0;

            allForms.forEach(form => {
                const formChecks = form.querySelectorAll('input[type="checkbox"]');
                formChecks.forEach(cb => {
                    const label = cb.nextElementSibling.innerText.trim();
                    const match = currentChecks.find(c => c.hour === label);
                    if (match) cb.checked = match.checked;
                });

                // Enviar cada formulario
                fun.xhr({
                    url: 'update-schedules-field',
                    data: new FormData(form),
                    success: () => {
                        completed++;
                        if (completed === allForms.length) {
                            fun.swal({ icon: 'success', title: 'Todos los horarios actualizados correctamente!' });
                        }
                    }
                });
            });
        });
    });
});

document.querySelectorAll('.form-horario').forEach((el) => {
    el.addEventListener('submit', ev => {
        ev.preventDefault();
        fun.xhr({
            url: 'update-schedules-field',
            data: new FormData(el),
            success: () => {
                fun.swal({ 
                    icon: 'success', 
                    title: 'Horarios del día actualizados!',
                    willClose: () => {
                        window.location.href = window.location.pathname + '?tab=horarios';
                    }
                });
            }
        });
    });
});

document.querySelectorAll('.form-servicios').forEach((el) => {
    el.addEventListener('submit', ev => {
        ev.preventDefault();
        fun.xhr({
            url: 'update-services-field',
            data: new FormData(el),
            success: () => {
                fun.swal({ 
                    icon: 'success', 
                    title: 'Servicios Actualizados!',
                    willClose: () => {
                        window.location.href = window.location.pathname + '?tab=servicios';
                    }
                });
            }
        });
    });
});

// Init
if (document.getElementById('map-picker')) {
    initMap();
}

// Tab support
const tab = new URLSearchParams(window.location.search).get('tab');
if (tab) {
    const triggerEl = document.querySelector('a[href="#' + tab + '"]');
    if (triggerEl) triggerEl.click();
}
