import {Func} from  './function.js';
const fun = new Func;
let form = document.querySelector('#form-add-user')
let passwordIntput = document.querySelector('#password')
let avatar = document.querySelector('#add-user-select-avatar')
let avatarInput = document.querySelector('#avatar')

passwordIntput.addEventListener('focus', () =>{
    $('#btn-generate-password').slideDown();
})
document.querySelector('#btn-generate-password').addEventListener('click', () => {
    passwordIntput.value = fun.generatePassword()
})

avatar.addEventListener('click', () =>{
    avatarInput.click()
})

avatarInput.addEventListener('change', () => {
    if(avatarInput.files.length == 1){
        fun.setImgSrc(avatarInput.files[0], avatar.id)
    }
})


form.addEventListener('submit', (e) => {
    e.preventDefault()
    fun.xhr({
        url: 'add-user',
        data: new FormData(form),
        error: (_response) =>{
            if(_response.add_fail){
                fun.swal({
                    icon: _response.icon,
                    title: _response.msg
                })
                _response.error.forEach(element => {
                    document.getElementById(element.key).error({class: ['error']})
                });
                
            }
        },
        success: (_response) =>{
            fun.swal({
                title: 'Usuario Canchero Creado Correctamente',
                icon: 'success',
                success: () =>{
                    location.href = 'users-list'
                }
            })
        }
    })
})