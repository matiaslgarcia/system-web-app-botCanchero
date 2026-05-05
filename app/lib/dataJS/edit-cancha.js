import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-cancha')
const priceRangesBody = document.getElementById('price-ranges-body')
const btnAddPriceRange = document.getElementById('btn-add-price-range')
const btnPresetPriceRanges = document.getElementById('btn-preset-price-ranges')
const btnSavePriceRanges = document.getElementById('btn-save-price-ranges')

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
form.addEventListener('submit', (e) =>{
    e.preventDefault()
    let inputEmpty = []
    form.querySelectorAll('input[type="text"], textarea').forEach(element =>{
        if(!Boolean(element.value)){
            inputEmpty.push({key: element.id})
        }
    })
    if(inputEmpty.length == 0){
        fun.xhr({
            url: 'edit-cancha',
            data: new FormData(form),
            success: (_response) =>{
                fun.swal({
                    icon: _response.icon,
                    title: _response.msg,
                    success: () => {
                        location.href = 'canchas-list'
                    }
                })
            }
        })
    }else{
        inputEmpty.forEach(el => {
            document.getElementById(el.key).error({class: ['error']})
            
        });
        fun.swal({
            icon: 'error',
            title: 'Por Favor! Complete Todos Los Campos'
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
                    title: 'Horarios Actualizados Correctamente'
                })
            }
        })
    })
})
$('#id_province').on('change', (e) =>{
    let id_province =  e.target.value
    let selectCity = document.querySelector('#id_city')
    selectCity.value = ''
    selectCity.children.forEach((o) => {
        if(o.getAttribute('data-id-province') != id_province){
            o.hidden = true
        }else{
            o.hidden = false
        }
    });
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
