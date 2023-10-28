import {Func} from  './function.js';
const fun = new Func;

let inputDate = document.querySelector('#date')

$("#kt_datatable_example_1").dataTable();

document.querySelector('#form-filtro').addEventListener('submit', (e) => {
    e.preventDefault()
    let url = 'mi-ingresos?date=' + inputDate.value
    location.href = url
})

$(inputDate).daterangepicker({
    singleDatePicker: true,
    locale: {
        format: 'DD/MM/YYYY'
    }
});