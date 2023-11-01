import {Func} from  './function.js';
const fun = new Func;
var calendarEl = document.getElementById('reservas');

if(document.referrer.match('mercadopago_OAuth')){
    fun.swal({
        icon: 'success'
    })
}

function updateEvent(data){
    fun.xhr({
        url: 'updateEvent',
        data: fun.setForm(data),
        
    })
}
function splitDateTime(dateTimeString) {
    var dateTimeParts = dateTimeString.split('T'); // Dividir en fecha y hora

    return {
        date: dateTimeParts[0],
        time: dateTimeParts[1]
    };
}
fun.xhr({
    url: 'getReservas',
    success: (_response) =>{
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'timeGridWeek,timeGridDay, dayGridMonth',
            },
            eventDrop: (info) =>{
                let startStr = splitDateTime(info.event.startStr)
                let data = {
                    id: info.event.id,
                    date_booking: startStr.date,
                    time_booking: startStr.time
                }
                updateEvent(data)
            },
            
            timeZone: 'America/Argentina/Buenos_Aires',
            views: {
                timeGridWeek: {
                    buttonText: 'Semana',
                },
                timeGridDay: {
                    buttonText: 'Día',
                },
                dayGridMonth: {
                    buttonText: 'Mes',
                },
            },
            
            slotDuration: '01:00',
            events: _response.reservas,
            eventDidMount: function (info) {
                var popover = new bootstrap.Popover(info.el, {
                    title: '#' + info.event.id + ' ' + info.event.title ,
                    content: info.event.extendedProps.cancha,
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body',
                });
                info.el.addEventListener('dblclick', function() {
                    window.location.href = info.event.extendedProps.url
                });
                info.el.addEventListener('contextmenu', (e) => {
                    e.preventDefault()
                    
                })
            }
          });
          calendar.render();
    }
})