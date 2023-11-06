import {Func} from  './function.js';
const fun = new Func;

let form = document.querySelector('#form-edit-user')
let avatar = document.querySelector('#add-user-select-avatar')
let avatarInput = document.querySelector('#avatar')
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