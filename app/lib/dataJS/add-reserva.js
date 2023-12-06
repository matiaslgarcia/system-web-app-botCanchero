import {Func} from  './function.js';
const fun = new Func;
const form = document.querySelector('#add-booking-form')
let time_booking = document.querySelector('#time_booking')
let id_field = document.querySelector('#id_field')
let date_booking = document.querySelector('#date_booking')

function limpiarHorarios(){
    time_booking.options.forEach(option => {
        option.remove()
    })
    time_booking.innerHTML = '<option selected="true" disabled value="">--SELECIONE--</option>'
}
$('#date_booking').daterangepicker({
    singleDatePicker: true,
    locale: {
        format: 'DD/MM/YYYY'
    }
});
$(time_booking).select2({
    ajax: {
        type: "POST",
        dataType: 'json',
        url: 'fetch/get-horas-booking',
        data: () => {
            return {id_field: id_field.value, date_booking: date_booking.value}
        },
        beforeSend: (xhr) => {
            if(!Boolean(id_field.value) && !Boolean(date_booking.value)){
                xhr.abort()
                fun.swal({
                    icon: 'error',
                    title: 'verifique la cancha y fecha'
                })
            }
        },
        processResults: function (data) {
            return {
              results: data
            };
          },
    },
    minimumResultsForSearch: Infinity
});
$(date_booking).on('changeDate', () =>{
    limpiarHorarios();
})
form.addEventListener('submit', e =>{
    e.preventDefault()
    fun.xhr({
        url: 'add-booking',
        data: new FormData(form),
        error: (_e) => {
            _e.error.forEach(element => {
                document.getElementById(element.key).error({
                    class: ['error']
                })
            });
            fun.swal({
                icon: _e.icon,
                title: _e.msg
            })
        },
        success: (_r) =>{
            location.href = 'reserva/' + _r.id
        }
    })
})