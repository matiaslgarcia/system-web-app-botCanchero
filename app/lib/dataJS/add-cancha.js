import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-add-cancha')
let map
let marker
let geocodeUnavailableNotified = false

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
}

form.addEventListener('submit', (e) =>{
    e.preventDefault()
    let inputEmpty = []
    form.querySelectorAll('input, textarea').forEach(element =>{
        if(element.type != 'file'){
            if(!Boolean(element.value)){
                inputEmpty.push({key: element.id})
            }
        }
    })
    if(inputEmpty.length == 0){
        fun.xhr({
            url: 'add-cancha',
            data: new FormData(form),
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
                    title: _response?.msg || _response?.error || 'No se pudo crear la cancha',
                })
            }
        })
    }else{
        document.getElementById(inputEmpty[0].key).focus()
        inputEmpty.forEach(el => {
            document.getElementById(el.key).error({class: ['error']})
            
        });
        fun.swal({
            icon: 'error',
            title: 'Por favor, completá todos los campos',
        })
    }
})

initMapPicker()
