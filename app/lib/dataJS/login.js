import {Func} from  './function.js';
const fun = new Func;

const form = document.querySelector('form')
function login(){
    let inputEmpty = []
    form.querySelectorAll('input').forEach(element => {
        if(!Boolean(element.value)){
            inputEmpty.push({key: element.id})
        }
    });
    if(inputEmpty.length == 0){
        fun.xhr({
            url: 'login',
            data: new FormData(form),
            success: (_response) => {
                if(_response.success){
                    location.reload();
                }
            },
            error: (_response) => {
                if(_response.login_fail){
                    fun.swal({
                        icon: _response.icon,
                        title: _response.msg,
                        timerProgressBar: true,
                    })
                }
            }
        })
    }else{
        fun.swal({
            icon: 'error',
            title: 'Por favor! Complete Todos Los Campos',
            timerProgressBar: true,
        })
        inputEmpty.forEach(e => {
            document.getElementById(e.key).error({class: ['error']})
        })
    }
}
form.addEventListener('submit', e => {
    e.preventDefault()
    login()
})