import {Func} from  './function.js';
const fun = new Func;

document.querySelectorAll('.btn-delete-cancha').forEach( btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        fun.swal({
            icon: 'warning',
            title: '¿Estas Seguro De Quieres Eliminar Esta Cancha?',
            showConfirmButton: true,
            confirmButtonText: 'SI',
            showCancelButton: true,
            cancelButtonText: 'NO',
            timer: 0,
            success: (_r) =>{
                if(_r.isConfirmed){
                    fun.xhr({
                        url: 'deleteCancha',
                        data: fun.setForm({id: btn.getAttribute('data-id')}),
                        success: () =>{
                            location.reload();
                        }
                    })
                }
            }
        })
    })
})