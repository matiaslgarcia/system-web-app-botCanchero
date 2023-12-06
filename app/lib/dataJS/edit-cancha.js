import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-cancha')

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