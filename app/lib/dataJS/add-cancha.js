import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-add-cancha')
let logoCancha = document.querySelector('#logo-cancha')

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
            }
        })
    }else{
        document.getElementById(inputEmpty[0].key).focus()
        inputEmpty.forEach(el => {
            document.getElementById(el.key).error({class: ['error']})
            
        });
        fun.swal({
            icon: 'error',
            title: 'Por Favor! Complete Todos Los Campos',
        })
    }
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