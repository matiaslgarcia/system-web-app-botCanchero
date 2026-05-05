import {Func} from  './function.js';
const fun = new Func;

document.querySelectorAll('.btn-delete-cancha').forEach( btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        fun.confirm({
            icon: 'warning',
            title: '¿Estas Seguro De Quieres Eliminar Esta Cancha?',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'danger',
            cancelVariant: 'secondary',
        }).then((_r) => {
            if(_r.isConfirmed){
                fun.xhr({
                    url: 'deleteCancha',
                    data: fun.setForm({id: btn.getAttribute('data-id')}),
                    success: () =>{
                        location.reload();
                    }
                })
            }
        });
    })
})
