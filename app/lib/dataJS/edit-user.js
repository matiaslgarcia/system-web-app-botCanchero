import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-user')

form.addEventListener('submit', (e) =>{
    e.preventDefault()
    fun.xhr({
        url: 'edit-user',
        data: new FormData(form),
        success: (_response) =>{
            fun.swal({
                icon: _response.icon,
                title: _response.msg,
                success: () => {
                    location.href = 'users-list'
                }
            })
        }
    })
})