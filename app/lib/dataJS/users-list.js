import {Func} from  './function.js';
const fun = new Func;

function deleteUser(id){
    fun.xhr({
        url: 'deleteUser',
        data: fun.setForm({id: id}),
        success: () => {
            fun.swal({
                icon: 'success',
                title: 'Usuario eliminado correctamente',
                success: () => {
                    location.reload();
                }
            })
        },
        error: (response) => {
            fun.swal({
                icon: response?.icon || 'error',
                title: response?.msg || response?.error || 'No se pudo eliminar el usuario',
            })
        }
    })
}
function changePassword(id, password){
    if (!password || String(password).trim().length < 6) {
        fun.swal({
            icon: 'warning',
            title: 'La contraseña debe tener al menos 6 caracteres',
        })
        return
    }
    fun.xhr({
        url: 'changePassword',
        data: fun.setForm({id: id, password: password}),
        success: () =>{
            fun.swal({
                title: 'La contraseña se actualizó correctamente',
                icon: 'success',
            })
        },
        error: (response) => {
            fun.swal({
                icon: response?.icon || 'error',
                title: response?.msg || response?.error || 'No se pudo cambiar la contraseña',
            })
        }
    })
}
document.querySelectorAll('.btn-delete-user').forEach( btn => {
    btn.addEventListener('click', (ev) => {
        ev.preventDefault()
        fun.confirm({
            icon: 'warning',
            title: '¿Estás seguro de eliminar este usuario?',
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
        fun.swal({
            title: 'Nueva contraseña',
            html: `
                <form id="swal-change-password-form" onsubmit="return false;" autocomplete="off">
                    <input id="swal-password-input" class="swal2-input" type="password" placeholder="Ingresá la nueva contraseña" autocomplete="new-password">
                </form>
            `,
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'primary',
            cancelVariant: 'secondary',
            timer: 0,
            preConfirm: () => {
                const value = document.getElementById('swal-password-input')?.value || ''
                if (!value || String(value).trim().length < 6) {
                    Swal.showValidationMessage('La contraseña debe tener al menos 6 caracteres')
                    return false
                }
                return value
            }
        }).then((_response) => {
            if(_response.isConfirmed && _response.value){
               changePassword(id, _response.value)
            }
        });
    })
    
})
