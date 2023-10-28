import {Func} from  './function.js';
const fun = new Func;

let inputDate = document.querySelector('#date')
let inputIdField = document.querySelector('#id_field')

$("#kt_datatable_example_1").dataTable();

document.querySelector('#form-filtro').addEventListener('submit', (e) => {
    e.preventDefault()
    let url = 'ingresos?date=' + inputDate.value + '&cancha=' + inputIdField.value
    location.href = url
})

$(inputDate).daterangepicker({
    singleDatePicker: true,
    locale: {
        format: 'DD/MM/YYYY'
    }
});