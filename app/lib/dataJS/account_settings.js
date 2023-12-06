import {Func} from  './function.js';
const fun = new Func;

if(document.referrer.match('https://auth.mercadopago.com/')){
    fun.swal({
        icon: 'success',
        title: 'Mercado Pago Agregado Correctamente'
    })
}