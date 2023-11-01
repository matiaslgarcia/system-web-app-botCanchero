import {Func} from  './function.js';
const fun = new Func;

let btnMercadoPago = document.querySelector('#bing_mercado_pago')


btnMercadoPago.addEventListener('input', () => {
    fun.xhr({
        url: 'mercado-pago-account',
        data: fun.setForm(),
        success: () =>{

        }
    })
})