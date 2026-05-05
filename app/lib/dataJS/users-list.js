import {Func} from  './function.js';
const fun = new Func;

function deleteUser(id){
    fun.xhr({
        url: 'deleteUser',
        data: fun.setForm({id: id}),
        success: () => {
            fun.swal({
                icon: 'success',
                title: 'Usuario Eliminado Correctamente',
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
                title: 'Se Actualizo Tu Contraseña Correctamente',
                icon: 'success',
            })
        }
    })
}
document.querySelectorAll('.btn-delete-user').forEach( btn => {
    btn.addEventListener('click', () => {
        fun.confirm({
            icon: 'warning',
            title: '¿Estas Seguro De Eliminar Este Usuario?',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'danger',
            cancelVariant: 'secondary',
        }).then((_response) => {
            if(_response.isConfirmed){
                deleteUser(btn.getAttribute('data-user'))
            }
        });
    })
    
})
document.querySelectorAll('.btn-change-password').forEach( btnChangePassword => {
    btnChangePassword.addEventListener('click', () => {
        let id = btnChangePassword.getAttribute('data-user')
        fun.confirm({
            title: 'Nueva Contraseña',
            input: 'password',
            confirmButtonText: 'Guardar',
            cancelButtonText: 'No, cancelar',
            cancelVariant: 'secondary',
        }).then((_response) => {
            if(_response.isConfirmed){
               changePassword(id, _response.value)
            }
        });
    })
    
})
