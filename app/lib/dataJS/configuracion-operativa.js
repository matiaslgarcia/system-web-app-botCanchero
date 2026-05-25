import { Func } from './function.js';

const fun = new Func();
const form = document.getElementById('form-business-rules');
const reminderHidden = document.getElementById('reminder_lead_minutes_csv');
const reminderCustomInput = document.getElementById('reminder_custom_value');
const reminderPresetInputs = document.querySelectorAll('.js-reminder-preset');

const normalizeReminderText = (value) => value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim();

const parseHumanReminderValue = (value) => {
    const trimmed = value?.trim() || '';
    if (!trimmed) return '720,360';

    if (/^[\d,\s;]+$/.test(trimmed)) {
        return trimmed
            .split(/[\s,;]+/)
            .map((part) => Number(part))
            .filter((part) => Number.isFinite(part) && part > 0)
            .sort((a, b) => b - a)
            .join(',') || '720,360';
    }

    const normalized = normalizeReminderText(trimmed);
    const chunks = normalized
        .split(/\s*(?:,| y )\s*/)
        .map((part) => part.trim())
        .filter(Boolean);

    const values = chunks.map((chunk) => {
        const match = chunk.match(/(\d+)\s*(dia|dias|d|hora|horas|h|min|minuto|minutos|m)\b/);
        if (!match) return null;

        const amount = Number(match[1]);
        const unit = match[2];
        if (!Number.isFinite(amount) || amount <= 0) return null;

        if (unit === 'dia' || unit === 'dias' || unit === 'd') return amount * 1440;
        if (unit === 'hora' || unit === 'horas' || unit === 'h') return amount * 60;
        return amount;
    }).filter((part) => Number.isFinite(part) && part > 0);

    if (!values.length) return null;
    return values.sort((a, b) => b - a).join(',');
};

const syncReminderValue = () => {
    if (!reminderHidden || !reminderPresetInputs.length) return;

    const selectedPreset = Array.from(reminderPresetInputs).find((input) => input.checked)?.value || '720,360';
    const isCustom = selectedPreset === 'custom';

    if (reminderCustomInput) {
        reminderCustomInput.disabled = !isCustom;
        if (!isCustom) {
            reminderCustomInput.classList.add('bg-light');
            reminderCustomInput.setCustomValidity('');
        } else {
            reminderCustomInput.classList.remove('bg-light');
        }
    }

    if (!isCustom) {
        reminderHidden.value = selectedPreset;
        return;
    }

    const parsedValue = parseHumanReminderValue(reminderCustomInput?.value || '');
    if (reminderCustomInput) {
        if (!parsedValue) {
            reminderCustomInput.setCustomValidity('Escribí algo como "1 dia, 6 horas" o elegí una opcion sugerida.');
            reminderHidden.value = '720,360';
            return;
        }
        reminderCustomInput.setCustomValidity('');
    }

    reminderHidden.value = parsedValue || '720,360';
};

if (reminderPresetInputs.length) {
    reminderPresetInputs.forEach((input) => {
        input.addEventListener('change', syncReminderValue);
    });
}

if (reminderCustomInput) {
    reminderCustomInput.addEventListener('input', syncReminderValue);
}

syncReminderValue();

if (form) {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        syncReminderValue();
        fun.xhr({
            url: 'save-business-rules',
            data: new FormData(form),
            success: (response) => {
                fun.swal({
                    icon: 'success',
                    title: response?.msg || 'Reglas operativas guardadas correctamente',
                    willClose: () => {
                        const redirectUrl = form.dataset.redirectUrl || '';
                        const establishmentId = response?.establishment_id || form.querySelector('[name="establishment_id"]')?.value;
                        if (redirectUrl) {
                            let target = redirectUrl;
                            if (establishmentId) {
                                target += `?establishment_id=${encodeURIComponent(establishmentId)}`;
                            }
                            location.href = `${target}#kt_user_operational_tab`;
                            return;
                        }
                        if (establishmentId) {
                            location.href = `configuracion-operativa?establishment_id=${establishmentId}`;
                            return;
                        }
                        location.reload();
                    }
                });
            },
            error: (response) => {
                fun.swal({
                    icon: 'error',
                    title: response?.msg || response?.error || 'No se pudieron guardar las reglas operativas',
                });
            }
        });
    });
}
