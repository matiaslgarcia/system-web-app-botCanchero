import {Func} from  './function.js';
const fun = new Func;
const formReagendar = document.querySelector('#form-reagendar')
let btnCancelarReserva = document.querySelector('#btn-action-cancelar-reserva')
let time_booking = document.querySelector('#time_booking')
let id_field = document.querySelector('#id_field')

function limpiarHorarios(){
    time_booking.options.forEach(option => {
        option.remove()
    })
    time_booking.innerHTML = '<option selected="true" disabled value="">--SELECIONE--</option>'
}
$(time_booking).on('changeDate', function(e) {
    alert(e)
})
if(btnCancelarReserva){
    btnCancelarReserva.addEventListener('click', () => {
        let reserva = {
            id: btnCancelarReserva.getAttribute('data-id-reserva'),
            status: 2
        }
        fun.swal({
            icon: 'warning',
            title: '¿Quieres cancelar la reserva #'+reserva.id+'?',
            showConfirmButton: true,
            showCancelButton: true,
            timer: 0,
            confirmButtonText: 'SI',
            cancelButtonText: 'NO',
            success: (_result) =>{
                if(_result.isConfirmed){
                    fun.xhr({
                        url: 'cancel-booking',
                        data: fun.setForm(reserva),
                        success: () =>{
                            location.href = 'reservas'
                        }
                    })
                }
            }
        })
    })
}

formReagendar.addEventListener('submit', (e) =>{
    e.preventDefault()
    fun.xhr({
        url: 'reagendar',
        data: new FormData(formReagendar),
        success: () =>{
            fun.swal({
                icon: 'success',
                title: 'Guardado correctamente',
                success: () =>{
                    location.reload()
                }
            })
            
        }
    })
})

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
            limpiarHorarios()
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