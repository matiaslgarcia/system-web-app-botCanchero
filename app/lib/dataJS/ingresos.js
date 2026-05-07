import {Func} from  './function.js';
const fun = new Func;

let inputDate = document.querySelector('#date')
let inputIdField = document.querySelector('#id_field')

$("#kt_datatable_example_1").dataTable();

document.querySelector('#form-filtro').addEventListener('submit', (e) => {
    e.preventDefault()
    const cancha = inputIdField.value || '%'
    let url = 'ingresos?date=' + encodeURIComponent(inputDate.value) + '&cancha=' + encodeURIComponent(cancha)
    location.href = url
})

$(inputDate).daterangepicker({
    singleDatePicker: true,
    locale: {
        format: 'DD/MM/YYYY'
    }
});
