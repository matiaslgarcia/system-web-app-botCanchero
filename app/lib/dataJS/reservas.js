import { Func } from './function.js';
const fun = new Func;
var calendarEl = document.getElementById('reservas');
var calendar;
const RESERVAS_POLL_MS = 30000;
const RESERVAS_POLL_MAX_MS = 120000;
const RESERVAS_SAME_SIGNATURE_HITS_FOR_BACKOFF = 3;
let reservasPollTimer = null;
let loadingReservas = false;
let loadingReservasMeta = false;
let lastReservasSignature = null;
let reservasCurrentPollMs = RESERVAS_POLL_MS;
let reservasSameSignatureHits = 0;
let slotUsageByKey = new Map();
let slotLabelByEventId = new Map();
const isMobileViewport = () => window.matchMedia('(max-width: 767.98px)').matches;

const fmtMoney = (n) => '$' + Number(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const isFixedBooking = (ev = {}) => {
    const source = String(ev.source || '');
    const recurringId = Number(ev.recurring_booking_id || 0);
    return ev.is_fixed == 1 || source === 'recurring' || source === 'recurring_planned' || recurringId > 0;
};
const getBookingOrigin = (ev = {}) => {
    const source = String(ev.source || '').toLowerCase();
    if (source === 'bot' || source === 'customer_bot' || source === 'whatsapp' || source === 'bot_whatsapp') {
        return 'bot';
    }
    return 'web';
};
const getBookingOriginIcon = (ev = {}) => (getBookingOrigin(ev) === 'bot' ? '🤖' : '🖥');
// DAT-03: "Matias Garcia" no entra en el chip de 118px del mes y se corta a
// "Matias G"/"Matias C" -- con varios clientes que comparten nombre de pila
// eso no alcanza para distinguirlos. Apellido primero entra en el mismo
// ancho y sí distingue.
const formatShortName = (fullName = '') => {
    const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
    if (parts.length <= 1) return parts[0] || '';
    return `${parts.slice(1).join(' ')}, ${parts[0].charAt(0)}.`;
};
const getPaymentStatusClass = (ev = {}) => {
    const total = Number(ev.total_amount) || 0;
    const paid = Number(ev.paid_amount) || 0;
    // HOY-06 (7ª pasada): un sobrepago (precio cambiado después de cobrar)
    // dejaba payment_status en "partial" en la base -- confiar ciegamente en
    // ese campo pintaba de ámbar "Pago parcial" una reserva que en realidad
    // está pagada de más. Si lo pagado ya cubre el total, es verde, gane lo
    // que gane payment_status.
    if (total > 0 && paid >= total) return 'fc-event-paid';

    // CRU-01: el campo payment_status ('paid'/'partial'/'pending') lo mantiene
    // el backend en cada cobro y es la fuente de verdad; derivarlo de nuevo acá
    // a partir de total/paid amounts fallaba en casos como una reserva con un
    // pago parcial registrado pero total_amount todavía en 0 (quedaba "pending"
    // en vez de "partial"). Se usa como primera fuente, con el cálculo por
    // monto sólo como respaldo si no viniera el campo.
    const paymentStatus = String(ev.payment_status || '').toLowerCase();
    if (paymentStatus === 'paid') return 'fc-event-paid';
    if (paymentStatus === 'partial') return 'fc-event-partial';
    if (paymentStatus === 'pending') return 'fc-event-pending';

    const balance = Number(ev.balance_due) || Math.max(0, total - paid);
    if (total <= 0) return 'fc-event-pending';
    if (balance <= 0) return 'fc-event-paid';
    if (paid > 0) return 'fc-event-partial';
    return 'fc-event-pending';
};
const toYmd = (d) => {
    const dt = new Date(d);
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const day = String(dt.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};
const getRangePayload = () => {
    if (calendar && calendar.view) {
        const view = calendar.view;
        const startBase = view.activeStart || view.currentStart;
        const endBaseExclusive = view.activeEnd || view.currentEnd;
        const endBaseInclusive = new Date(endBaseExclusive.getTime() - 24 * 60 * 60 * 1000);

        const start = typeof calendar.formatIso === 'function'
            ? calendar.formatIso(startBase, { omitTime: true })
            : toYmd(startBase);
        const end = typeof calendar.formatIso === 'function'
            ? calendar.formatIso(endBaseInclusive, { omitTime: true })
            : toYmd(endBaseInclusive);

        return { start_date: start, end_date: end };
    }
    const now = new Date();
    const end = new Date(now);
    end.setDate(end.getDate() + 60);
    return { start_date: toYmd(now), end_date: toYmd(end) };
};

function getSlotKeyFromEventLike(ev) {
    const start = String(ev.start || ev.startStr || '');
    const [datePart, timePartRaw] = start.split('T');
    const timePart = (timePartRaw || '').substring(0, 5);
    const fieldId = ev.id_field ?? ev?.extendedProps?.id_field ?? '';
    return `${fieldId}|${datePart || ''}|${timePart || ''}`;
}

function recomputeSlotUsage(events = []) {
    const map = new Map();
    const grouped = new Map();
    events.forEach((ev) => {
        const key = getSlotKeyFromEventLike(ev);
        if (!key || key.startsWith('|')) return;
        const threshold = Math.max(1, Number(ev.threshold ?? ev?.extendedProps?.threshold ?? 1));
        const current = map.get(key) || { occupied: 0, threshold };
        current.occupied += 1;
        current.threshold = Math.max(current.threshold, threshold);
        map.set(key, current);

        const bucket = grouped.get(key) || [];
        bucket.push(ev);
        grouped.set(key, bucket);
    });
    slotUsageByKey = map;

    const labels = new Map();
    grouped.forEach((bucket, key) => {
        const info = map.get(key);
        if (!info) return;
        bucket.forEach((ev, i) => {
            labels.set(String(ev.id), `Cancha ${i + 1}`);
        });
    });
    slotLabelByEventId = labels;
}

function splitDateTime(dateTimeString) {
    const parts = dateTimeString.split('T');
    return { date: parts[0], time: parts[1] };
}

function updateEvent(data, onError) {
    fun.xhr({
        url: 'updateEvent',
        data: fun.setForm(data),
        error: (err) => {
            if (typeof onError === 'function') onError(err);
        }
    });
}

function pad2(n) {
    return String(n).padStart(2, '0');
}

// One hour before the establishment's first configured schedule slot, so the
// week/day view opens near where reservations actually start instead of 00:00.
function getDefaultScrollTime() {
    const minHour = (calendarEl.dataset.minHour || '').trim();
    if (!/^\d{2}:\d{2}$/.test(minHour)) return '06:00:00';
    const [h] = minHour.split(':').map(Number);
    return `${pad2(Math.max(0, h - 1))}:00:00`;
}

// If the visible range includes right now, scroll near the current time
// instead; otherwise fall back to the establishment's opening hour.
function getScrollTimeForRange(rangeStart, rangeEnd) {
    const now = new Date();
    if (rangeStart && rangeEnd && now >= rangeStart && now < rangeEnd) {
        const h = Math.max(0, now.getHours() - 1);
        return `${pad2(h)}:00:00`;
    }
    return getDefaultScrollTime();
}

// This grid renders with contentHeight:'auto' (no fixed height), which
// means FullCalendar never creates its own internal scroller — the browser
// scrolls the page itself. calendar.scrollToTime()/the `scrollTime` option
// only move FullCalendar's *internal* scroller, so on this config they're a
// silent no-op. Scroll the actual target row into view natively instead,
// which works no matter who owns the scrolling.
function scrollGridToTime(hhmmss) {
    if (!calendarEl) return;
    requestAnimationFrame(() => {
        const slot = calendarEl.querySelector(`.fc-timegrid-slot-lane[data-time="${hhmmss}"]`)
            || calendarEl.querySelector(`.fc-timegrid-slot[data-time="${hhmmss}"]`);
        if (slot && typeof slot.scrollIntoView === 'function') {
            slot.scrollIntoView({ block: 'start', behavior: 'auto' });
        }
    });
}

// CAL-01: la grilla mostraba las 24 horas del día aunque la cancha solo
// opere, por ejemplo, de 16 a 00 — ocho filas siempre vacías arriba, dos
// pantallas de alto. slotMinTime/slotMaxTime ya vienen calculados en el
// servidor a partir de los horarios habilitados (Mi Cancha → Horarios).
function getSlotBounds() {
    const min = (calendarEl.dataset.minHour || '').trim();
    const max = (calendarEl.dataset.maxHour || '').trim();
    const valid = /^\d{2}:\d{2}$/;
    if (!valid.test(min) || !valid.test(max)) return null;
    return { min: `${min}:00`, max: `${max}:00` };
}

// DAT-05: tocar "14" en un día con 7 reservas cargadas abría "Nueva reserva"
// en vez de mostrar ese día, y una fecha de cuatro meses atrás se aceptaba
// sin avisar. Un mismo punto de entrada para crear reserva desde el
// calendario, con aviso cuando la fecha ya pasó.
function goToAddBooking(date, time) {
    const target = `nueva-reserva?date=${date}&time=${time}`;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const clicked = new Date(`${date}T00:00:00`);
    if (clicked < today) {
        fun.confirm({
            title: 'Fecha pasada',
            text: `Vas a cargar una reserva para el ${date.split('-').reverse().join('/')}, que ya pasó. ¿Confirmás que es correcto?`,
            confirmButtonText: 'Sí, cargar igual',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) window.location.href = target;
        });
        return;
    }
    window.location.href = target;
}

function initCalendar(events) {
    const mobile = isMobileViewport();
    const bounds = getSlotBounds();
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: mobile ? 'timeGridDay' : 'timeGridWeek',
        ...(bounds ? { slotMinTime: bounds.min, slotMaxTime: bounds.max } : {}),
        scrollTime: getDefaultScrollTime(),
        scrollTimeReset: false,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: mobile ? 'timeGridDay,dayGridMonth' : 'timeGridWeek,timeGridDay,dayGridMonth',
        },
        buttonText: { today: 'Hoy' },
        selectable: true,
        selectMirror: true,
        unselectAuto: false,
        select: (info) => {
            const date = info.startStr.split('T')[0];
            const time = info.startStr.split('T')[1] ? info.startStr.split('T')[1].substring(0, 5) : '';
            goToAddBooking(date, time);
        },
        dateClick: (info) => {
            const date = info.dateStr.split('T')[0];
            const time = info.dateStr.split('T')[1] ? info.dateStr.split('T')[1].substring(0, 5) : '';
            goToAddBooking(date, time);
        },
        // DAT-05: en vista Mes, el número del día navega a esa vista Día; crear
        // una reserva queda para el espacio vacío de la celda (dateClick arriba).
        navLinks: true,
        navLinkDayClick: (date) => {
            calendar.changeView('timeGridDay', date);
        },
        eventDrop: (info) => {
            const startStr = splitDateTime(info.event.startStr);
            updateEvent({
                id: info.event.id,
                date_booking: startStr.date,
                time_booking: startStr.time,
            }, (err) => {
                info.revert();
                const message = err?.error || 'No se pudo re-agendar porque el horario ya está ocupado.';
                fun.swal({
                    icon: 'error',
                    title: message,
                    timer: 3000
                });
            });
        },
        timeZone: 'America/Argentina/Buenos_Aires',
        views: {
            timeGridWeek: { buttonText: 'Semana' },
            timeGridDay: { buttonText: 'Día' },
            dayGridMonth: { buttonText: 'Mes' },
        },
        slotDuration: '01:00',
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short'
        },
        allDaySlot: false,
        contentHeight: 'auto',
        expandRows: true,
        nowIndicator: true,
        handleWindowResize: true,
        // DAT-03: por defecto FullCalendar omite los minutos en punto ("19" en
        // vez de "19:00"), y como todos los turnos son en punto el chip parece
        // un dato incompleto en vez de una hora redonda.
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        // DAT-06: el día 14 con 7 reservas estiraba la fila del mes para meterlas
        // todas; con dayMaxEvents corta con un "+N más" nativo de FullCalendar.
        dayMaxEvents: true,
        datesSet: function (info) {
            // Al navegar semana/mes/día refrescamos con el rango visible actual.
            if (calendar) {
                lastReservasSignature = null;
                resetReservasPollingCadence();
                cargarReservas({ silent: true });
                scheduleReservasPolling();
            }
            if (calendar && info?.view?.type !== 'dayGridMonth') {
                const target = getScrollTimeForRange(info?.view?.currentStart, info?.view?.currentEnd);
                calendar.scrollToTime(target);
                scrollGridToTime(target);
            }
        },
        windowResize: function () {
            if (!calendar) return;
            const wantView = isMobileViewport() ? 'timeGridDay' : 'timeGridWeek';
            if (calendar.view?.type !== wantView) {
                calendar.changeView(wantView);
            }
        },
        events: events,
        eventClassNames: function(arg) {
            const classes = ['shadow-sm'];
            const ev = arg.event.extendedProps || {};
            const isFija = isFixedBooking(ev);
            const status = Number(ev.id_status ?? ev.status ?? 0);
            const isPast = !!(arg.event.end && arg.event.end < new Date());

            // DAT-04: "reserva fija" (tipo) y "sin pagar" (cobro) son dos
            // preguntas distintas, pero antes competían por el mismo color de
            // fondo y una fija pagada se veía igual que una fija impaga. El
            // fondo ahora es siempre el estado de cobro; lo fijo se marca con
            // un borde aparte (CSS) para no perder ninguna de las dos lecturas.
            if (status === 2) {
                classes.push('fc-event-cancelled');
            } else {
                // CAL-04: antes coloreaba por origen (bot/web) — dato que ya se
                // ve como ícono en el título — ahora por estado de cobro.
                classes.push(getPaymentStatusClass(ev));
            }
            if (isFija) classes.push('fc-event-fija');

            // DAT-01/DAT-02: antes "pasada" pisaba el color de estado con gris
            // y tachaba el texto aunque estuviera pagada (texto blanco sobre
            // gris clarito, 1.13:1 -- el peor número de toda la auditoría).
            // Ahora lo pasado conserva su color real (ver CSS) y el tachado
            // queda sólo para lo efectivamente cancelado.
            if (isPast && status !== 2) classes.push('fc-event-past');

            return classes;
        },
        eventDidMount: function (info) {
            const ev = info.event.extendedProps;
            const isFija = isFixedBooking(ev);
            const origin = getBookingOrigin(ev);
            const status = Number(ev.id_status ?? ev.status ?? 0);
            const total = Number(ev.total_amount) || 0;
            const paid = Number(ev.paid_amount) || 0;
            const balance = Number(ev.balance_due) || Math.max(0, total - paid);
            const slotKey = `${ev.id_field || ''}|${(info.event.startStr || '').split('T')[0] || ''}|${(info.event.startStr || '').split('T')[1]?.substring(0, 5) || ''}`;
            const slotInfo = slotUsageByKey.get(slotKey);
            const canchaNumero = slotLabelByEventId.get(String(info.event.id));

            const lines = [];
            lines.push(`<strong>${ev.cancha || ''}</strong>`);
            if (slotInfo) lines.push(`Cupos: <strong>${slotInfo.occupied}/${slotInfo.threshold}</strong>`);
            if (canchaNumero) lines.push(`N° Cancha: <strong>${canchaNumero}</strong>`);
            lines.push(`Origen: <strong>${origin === 'bot' ? 'Bot' : 'Web'}</strong>`);
            if (isFija && status === 2) {
                lines.push('<span style="color:#f1416c"><strong>♻️ RESERVA FIJA CANCELADA</strong></span>');
            } else if (isFija) {
                lines.push('<span style="color:#fd7e14">♻️ RESERVA FIJA</span>');
            }
            if (total > 0) {
                lines.push(`Total: ${fmtMoney(total)}`);
                lines.push(`Pagado: ${fmtMoney(paid)}`);
                if (balance > 0) {
                    lines.push(`<span style="color:#dc3545"><strong>Saldo: ${fmtMoney(balance)}</strong></span>`);
                } else {
                    lines.push('<span style="color:#198754">✅ Pagado</span>');
                }
            }

            new bootstrap.Popover(info.el, {
                title: '#' + info.event.id + ' ' + (ev.customer_name || info.event.title),
                content: lines.join('<br>'),
                html: true,
                trigger: 'hover',
                placement: 'top',
                container: 'body',
            });

            info.el.addEventListener('dblclick', () => {
                window.location.href = ev.url;
            });
        },
    });
    calendar.render();
}

function cargarReservas(options = {}) {
    if (loadingReservas) return;
    loadingReservas = true;
    const field_id = $('#filtroCancha').val();
    const range = getRangePayload();
    fun.xhr({
        url: 'getReservas',
        data: fun.setForm({ field_id, ...range }),
        success: (resp) => {
            if (resp?.meta?.signature) {
                lastReservasSignature = resp.meta.signature;
            }
            const rawReservas = resp?.reservas || [];
            recomputeSlotUsage(rawReservas);

            // CAL-02: cupos ocupados visibles en el bloque del calendario, no
            // solo al pasar el mouse — antes solo se veía en el popover.
            const reservasDecoradas = rawReservas.map((ev) => {
                const icon = getBookingOriginIcon(ev);
                const shortName = formatShortName(ev.customer_name || ev.title);
                let title = shortName ? `${icon} ${shortName}` : String(ev.title || '').trim();
                const threshold = Math.max(1, Number(ev.threshold ?? 1));
                if (threshold > 1) {
                    const slotInfo = slotUsageByKey.get(getSlotKeyFromEventLike(ev));
                    if (slotInfo) title = `${title} (${slotInfo.occupied}/${slotInfo.threshold})`;
                }
                return { ...ev, title };
            });

            if (!calendar) {
                initCalendar(reservasDecoradas);
            } else {
                calendar.removeAllEvents();
                calendar.addEventSource(reservasDecoradas);
            }
            loadingReservas = false;
        },
        error: () => {
            loadingReservas = false;
            if (!options.silent) {
                fun.swal({
                    icon: 'error',
                    title: 'No se pudieron actualizar las reservas.',
                    timer: 2500
                });
            }
        }
    });
}

function resetReservasPollingCadence() {
    reservasCurrentPollMs = RESERVAS_POLL_MS;
    reservasSameSignatureHits = 0;
}

function scheduleReservasPolling() {
    if (reservasPollTimer) clearTimeout(reservasPollTimer);
    reservasPollTimer = setTimeout(() => {
        if (!document.hidden) {
            cargarReservasMeta();
        } else {
            scheduleReservasPolling();
        }
    }, reservasCurrentPollMs);
}

function cargarReservasMeta() {
    if (loadingReservas || loadingReservasMeta) {
        scheduleReservasPolling();
        return;
    }
    loadingReservasMeta = true;
    const field_id = $('#filtroCancha').val();
    const range = getRangePayload();
    fun.xhr({
        url: 'getReservas',
        data: fun.setForm({ field_id, meta_only: 1, signature: lastReservasSignature || '', ...range }),
        success: (resp) => {
            loadingReservasMeta = false;
            const nextSignature = resp?.meta?.signature || null;
            const changed = resp?.meta?.changed;
            if (!nextSignature) {
                resetReservasPollingCadence();
                cargarReservas({ silent: true });
                scheduleReservasPolling();
                return;
            }
            if (changed === true || lastReservasSignature !== nextSignature) {
                resetReservasPollingCadence();
                cargarReservas({ silent: true });
                scheduleReservasPolling();
                return;
            }
            reservasSameSignatureHits += 1;
            if (reservasSameSignatureHits >= RESERVAS_SAME_SIGNATURE_HITS_FOR_BACKOFF) {
                reservasCurrentPollMs = Math.min(RESERVAS_POLL_MAX_MS, reservasCurrentPollMs * 2);
                reservasSameSignatureHits = 0;
            }
            scheduleReservasPolling();
        },
        error: () => {
            loadingReservasMeta = false;
            reservasCurrentPollMs = Math.min(RESERVAS_POLL_MAX_MS, reservasCurrentPollMs * 2);
            scheduleReservasPolling();
        }
    });
}

function startReservasPolling() {
    resetReservasPollingCadence();
    scheduleReservasPolling();
}

// Eventos
$('#filtroCancha').on('change', () => {
    lastReservasSignature = null;
    resetReservasPollingCadence();
    cargarReservas();
    scheduleReservasPolling();
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        resetReservasPollingCadence();
        cargarReservasMeta();
    }
});

// Init
if (calendarEl) {
    initCalendar([]);
    startReservasPolling();
}
