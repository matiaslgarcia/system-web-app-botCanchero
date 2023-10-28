import {Func} from  './function.js';
const fun = new Func;

function deleteUser(id){
    fun.xhr({
        url: 'deleteUser',
        data: fun.setForm({id: id}),
        success: () => {
            fun.swal({
                icon: 'success',
                title: 'Usuario eliminado',
                success: () => {
                    location.reload();
                }
            })
        }
    })
}
function changePassword(id, password){
    fun.xhr({
        url: 'changePassword',
        data: fun.setForm({id: id, password: password}),
        success: () =>{
            fun.swal({
                title: 'Se actualizo la contraseña correctamente',
                icon: 'success',
            })
        }
    })
}
document.querySelectorAll('.btn-delete-user').forEach( btn => {
    btn.addEventListener('click', () => {
        fun.swal({
            icon: 'warning',
            title: '¿Quieres eliminar el usuario?',
            success: (_response) =>{
                if(_response.isConfirmed){
                    deleteUser(btn.getAttribute('data-user'))
                }
            },
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'Si',
            cancelButtonText: 'No',
            timer: 0
        })
    })
    
})
document.querySelectorAll('.btn-change-password').forEach( btnChangePassword => {
    btnChangePassword.addEventListener('click', () => {
        let id = btnChangePassword.getAttribute('data-user')
        fun.swal({
            title: 'Nueva contraseña',
            input: 'password',
            success: (_response) =>{
                if(_response.isConfirmed){
                   changePassword(id, _response.value)
                }
            },
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            timer: 0
        })
    })
    
})