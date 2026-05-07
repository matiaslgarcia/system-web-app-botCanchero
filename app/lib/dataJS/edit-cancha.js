import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-cancha')
const priceRangesBody = document.getElementById('price-ranges-body')
const btnAddPriceRange = document.getElementById('btn-add-price-range')
const btnPresetPriceRanges = document.getElementById('btn-preset-price-ranges')
const btnSavePriceRanges = document.getElementById('btn-save-price-ranges')
let map
let marker
let geocodeUnavailableNotified = false

const optimizeLogoFile = (file) => new Promise((resolve) => {
    if (!file || !file.type || !file.type.startsWith('image/')) return resolve(file)
    const reader = new FileReader()
    reader.onload = () => {
        const img = new Image()
        img.onload = () => {
            const maxSize = 1280
            let { width, height } = img
            if (width > height && width > maxSize) {
                height = Math.round((height * maxSize) / width)
                width = maxSize
            } else if (height >= width && height > maxSize) {
                width = Math.round((width * maxSize) / height)
                height = maxSize
            }

            const canvas = document.createElement('canvas')
            canvas.width = width
            canvas.height = height
            const ctx = canvas.getContext('2d')
            if (!ctx) return resolve(file)
            ctx.drawImage(img, 0, 0, width, height)
            canvas.toBlob((blob) => {
                if (!blob) return resolve(file)
                resolve(new File([blob], `logo_${Date.now()}.jpg`, { type: 'image/jpeg' }))
            }, 'image/jpeg', 0.82)
        }
        img.onerror = () => resolve(file)
        img.src = reader.result
    }
    reader.onerror = () => resolve(file)
    reader.readAsDataURL(file)
})

function notifyGeocodeUnavailable() {
    if (geocodeUnavailableNotified) return
    geocodeUnavailableNotified = true
    fun.swal({
        icon: 'warning',
        title: 'Geocodificación no disponible',
        text: 'No se pudo consultar direcciones externas. Igual podés seleccionar la ubicación moviendo el pin del mapa.',
        timer: 4000
    })
}

async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        if (!response.ok) throw new Error(`Reverse geocode HTTP ${response.status}`)
        const data = await response.json()
        if (data?.display_name) {
            document.getElementById('address').value = data.display_name
        }
    } catch (_error) {
        notifyGeocodeUnavailable()
    }
}

async function forwardGeocode(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
        if (!response.ok) throw new Error(`Forward geocode HTTP ${response.status}`)
        const data = await response.json()
        if (Array.isArray(data) && data.length > 0) {
            const { lat, lon } = data[0]
            const newLat = parseFloat(lat)
            const newLng = parseFloat(lon)
            map.setView([newLat, newLng], 15)
            updateMarker(newLat, newLng, false)
        } else {
            fun.swal({ icon: 'warning', title: 'No se encontró la dirección' })
        }
    } catch (_error) {
        notifyGeocodeUnavailable()
    }
}

function updateMarker(lat, lng, doReverse = true) {
    if (!marker) return
    marker.setLatLng([lat, lng])
    document.getElementById('latitude').value = lat.toFixed(6)
    document.getElementById('length').value = lng.toFixed(6)
    if (doReverse) reverseGeocode(lat, lng)
}

function initMapPicker() {
    const latInput = document.getElementById('latitude')
    const lngInput = document.getElementById('length')
    if (!latInput || !lngInput || !document.getElementById('map-picker') || typeof L === 'undefined') return

    const initialLat = parseFloat(latInput.value) || -34.6037
    const initialLng = parseFloat(lngInput.value) || -58.3816
    latInput.value = initialLat.toFixed(6)
    lngInput.value = initialLng.toFixed(6)

    map = L.map('map-picker').setView([initialLat, initialLng], 14)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map)

    marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map)

    map.on('click', (e) => {
        updateMarker(e.latlng.lat, e.latlng.lng)
    })

    marker.on('dragend', () => {
        const pos = marker.getLatLng()
        updateMarker(pos.lat, pos.lng)
    })

    document.getElementById('btn-recenter')?.addEventListener('click', () => {
        if (!navigator.geolocation) return
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords
            map.setView([latitude, longitude], 15)
            updateMarker(latitude, longitude)
        })
    })

    document.getElementById('btn-search-address')?.addEventListener('click', () => {
        const address = document.getElementById('address')?.value || ''
        if (address.trim()) forwardGeocode(address)
    })

    document.getElementById('address')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault()
            const address = e.target.value || ''
            if (address.trim()) forwardGeocode(address)
        }
    })

    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach((tab) => {
        tab.addEventListener('shown.bs.tab', (e) => {
            if (e.target.getAttribute('href') === '#config') {
                map.invalidateSize()
            }
        })
    })
}

function padTime(value) {
    const [h = '00', m = '00'] = String(value || '').split(':')
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

function timeToMinutes(value) {
    const [h = '0', m = '0'] = padTime(value).split(':')
    return (Number(h) * 60) + Number(m)
}

function createPriceRangeRow(range = {}) {
    if (!priceRangesBody) return
    const tr = document.createElement('tr')
    tr.className = 'price-range-row'
    tr.innerHTML = `
        <td><input type="time" class="form-control form-control-sm range-start" value="${padTime(range.start_time || '08:00')}"></td>
        <td><input type="time" class="form-control form-control-sm range-end" value="${padTime(range.end_time || '18:00')}"></td>
        <td><input type="number" min="1" step="0.01" class="form-control form-control-sm range-price" value="${range.price ?? ''}" placeholder="0.00"></td>
        <td class="text-end"><button type="button" class="btn btn-icon btn-sm btn-light-danger btn-remove-range"><i class="fa-solid fa-trash"></i></button></td>
    `
    priceRangesBody.appendChild(tr)
}

function loadPriceRanges() {
    if (!priceRangesBody) return
    priceRangesBody.innerHTML = ''
    const source = document.getElementById('price-ranges-data')
    let ranges = []
    if (source?.textContent) {
        try {
            ranges = JSON.parse(source.textContent)
        } catch (_e) {
            ranges = []
        }
    }
    if (!Array.isArray(ranges) || ranges.length === 0) {
        const fallbackPrice = document.getElementById('price_hour')?.value || ''
        createPriceRangeRow({ start_time: '08:00', end_time: '18:00', price: fallbackPrice })
        createPriceRangeRow({ start_time: '18:00', end_time: '23:59', price: fallbackPrice })
        return
    }
    ranges.forEach((range) => createPriceRangeRow(range))
}

function collectPriceRanges() {
    if (!priceRangesBody) return []
    const rows = Array.from(priceRangesBody.querySelectorAll('.price-range-row'))
    const collected = []
    for (const row of rows) {
        const startInput = row.querySelector('.range-start')
        const endInput = row.querySelector('.range-end')
        const priceInput = row.querySelector('.range-price')
        const start = padTime(startInput?.value || '')
        const end = padTime(endInput?.value || '')
        const price = Number(String(priceInput?.value || '').replace(',', '.'))
        const startMin = timeToMinutes(start)
        const endMin = timeToMinutes(end)
        if (!startInput?.value || !endInput?.value || !Number.isFinite(price) || price <= 0 || startMin >= endMin) {
            fun.swal({
                icon: 'warning',
                title: 'Franjas inválidas',
                text: 'Revisá inicio, fin y precio de cada franja.'
            })
            return null
        }
        collected.push({
            start_time: start,
            end_time: end,
            price: price.toFixed(2)
        })
    }
    const sorted = [...collected].sort((a, b) => timeToMinutes(a.start_time) - timeToMinutes(b.start_time))
    for (let i = 1; i < sorted.length; i++) {
        if (timeToMinutes(sorted[i].start_time) < timeToMinutes(sorted[i - 1].end_time)) {
            fun.swal({
                icon: 'warning',
                title: 'Franjas superpuestas',
                text: 'No puede haber superposición de horarios.'
            })
            return null
        }
    }
    return sorted
}

let avatar = document.querySelector('#add-user-select-avatar')
let avatarInput = document.querySelector('#logo')
avatar.addEventListener('click', () =>{
    avatarInput.click()
})
avatarInput.addEventListener('change', () => {
    if(avatarInput.files.length == 1){
        fun.setImgSrc(avatarInput.files[0], avatar.id)
    }
})
form.addEventListener('submit', async (e) =>{
    e.preventDefault()
    let inputEmpty = []
    form.querySelectorAll('input[type="text"], textarea').forEach(element =>{
        if(!Boolean(element.value)){
            inputEmpty.push({key: element.id})
        }
    })
    if(inputEmpty.length == 0){
        const data = new FormData(form)
        if (avatarInput.files.length === 1) {
            const optimizedLogo = await optimizeLogoFile(avatarInput.files[0])
            data.delete('logo')
            data.append('logo', optimizedLogo, optimizedLogo.name)
        }
        fun.xhr({
            url: 'edit-cancha',
            data,
            success: (_response) =>{
                fun.swal({
                    icon: _response.icon,
                    title: _response.msg,
                    success: () => {
                        location.href = 'canchas-list'
                    }
                })
            },
            error: (_response) => {
                fun.swal({
                    icon: _response?.icon || 'error',
                    title: _response?.msg || _response?.error || 'No se pudo guardar la cancha. Si el logo es muy pesado, probá con una imagen más liviana.',
                })
            }
        })
    }else{
        inputEmpty.forEach(el => {
            document.getElementById(el.key).error({class: ['error']})
            
        });
        fun.swal({
            icon: 'error',
            title: 'Por favor, completá todos los campos'
        })
    }
})

document.querySelectorAll('.form-horario').forEach((el)=>{
    el.addEventListener('submit', ev =>{
        ev.preventDefault();
        let data = new FormData(el)
        fun.xhr({
            url: 'update-schedules-field',
            data: data,
            success: () =>{
                fun.swal({
                    icon: 'success',
                    title: 'Horarios actualizados correctamente'
                })
            }
        })
    })
})
if (priceRangesBody) {
    loadPriceRanges()
    btnAddPriceRange?.addEventListener('click', () => {
        createPriceRangeRow({ start_time: '08:00', end_time: '09:00', price: '' })
    })
    btnPresetPriceRanges?.addEventListener('click', () => {
        const fallbackPrice = document.getElementById('price_hour')?.value || ''
        priceRangesBody.innerHTML = ''
        createPriceRangeRow({ start_time: '08:00', end_time: '18:00', price: fallbackPrice })
        createPriceRangeRow({ start_time: '18:00', end_time: '23:59', price: fallbackPrice })
    })
    priceRangesBody.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.btn-remove-range')
        if (!btn) return
        btn.closest('.price-range-row')?.remove()
    })
    btnSavePriceRanges?.addEventListener('click', () => {
        const ranges = collectPriceRanges()
        if (!ranges) return
        const formData = fun.setForm({
            id_field: document.getElementById('id')?.value || '',
            ranges_json: JSON.stringify(ranges),
        })
        fun.xhr({
            url: 'update-price-ranges',
            data: formData,
            success: (response) => {
                fun.swal({
                    icon: response?.icon || 'success',
                    title: response?.msg || 'Franjas guardadas correctamente',
                })
            },
            error: (response) => {
                fun.swal({
                    icon: response?.icon || 'error',
                    title: response?.msg || 'No se pudieron guardar las franjas',
                })
            }
        })
    })
}

initMapPicker()
