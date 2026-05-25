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

function initCalendar(events) {
    const mobile = isMobileViewport();
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: mobile ? 'timeGridDay' : 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: mobile ? 'timeGridDay,dayGridMonth' : 'timeGridWeek,timeGridDay,dayGridMonth',
        },
        selectable: true,
        selectMirror: true,
        unselectAuto: false,
        select: (info) => {
            const date = info.startStr.split('T')[0];
            const time = info.startStr.split('T')[1] ? info.startStr.split('T')[1].substring(0, 5) : '';
            window.location.href = `add-booking?date=${date}&time=${time}`;
        },
        dateClick: (info) => {
            const date = info.dateStr.split('T')[0];
            const time = info.dateStr.split('T')[1] ? info.dateStr.split('T')[1].substring(0, 5) : '';
            window.location.href = `add-booking?date=${date}&time=${time}`;
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
        datesSet: function () {
            // Al navegar semana/mes/día refrescamos con el rango visible actual.
            if (calendar) {
                lastReservasSignature = null;
                resetReservasPollingCadence();
                cargarReservas({ silent: true });
                scheduleReservasPolling();
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
            const now = new Date();
            const classes = ['shadow-sm'];
            const isFija = isFixedBooking(arg.event.extendedProps || {});
            const origin = getBookingOrigin(arg.event.extendedProps || {});
            const status = Number(arg.event.extendedProps?.id_status ?? arg.event.extendedProps?.status ?? 0);
            classes.push(origin === 'bot' ? 'fc-event-source-bot' : 'fc-event-source-web');
            
            // Lógica de "Reserva Pasada"
            if (arg.event.end && arg.event.end < now) {
                classes.push('fc-event-past', 'opacity-50', 'grayscale');
            } else {
                if (status === 2) {
                    classes.push(isFija ? 'fc-event-fixed-cancelled' : 'fc-event-danger');
                } else if (isFija) {
                    classes.push('fc-event-fixed');
                } else {
                    classes.push('fc-event-primary');
                }
            }
            
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
                title: '#' + info.event.id + ' ' + info.event.title,
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
            const reservasDecoradas = (resp?.reservas || []).map((ev) => {
                const icon = getBookingOriginIcon(ev);
                const title = String(ev.title || '').trim();
                if (title.startsWith(icon)) return ev;
                return {
                    ...ev,
                    title: title ? `${icon} ${title}` : icon,
                };
            });

            recomputeSlotUsage(reservasDecoradas);
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
cargarReservas();
startReservasPolling();
