import {Func} from  './function.js';
const fun = new Func;

const loginForm = document.getElementById('kt_sign_in_form');
const forgotForm = document.getElementById('kt_forgot_form');
const resetForm = document.getElementById('kt_reset_form');

const loginPanel = document.getElementById('login-panel');
const forgotPanel = document.getElementById('forgot-panel');
const resetPanel = document.getElementById('reset-panel');
const invalidResetPanel = document.getElementById('invalid-reset-panel');

function showPanel(panel) {
    [loginPanel, forgotPanel, resetPanel, invalidResetPanel].forEach((currentPanel) => {
        if (!currentPanel) return;
        currentPanel.classList.add('d-none');
    });
    if (panel) {
        panel.classList.remove('d-none');
    }
}

function isEmpty(value) {
    return String(value || '').trim() === '';
}

function togglePasswordVisibilityById(inputId, btn) {
    const input = inputId ? document.getElementById(inputId) : null;
    if (!input) return;
    const icon = btn?.querySelector('i') || null;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    if (icon) {
        icon.classList.toggle('fa-eye-slash', !isHidden);
        icon.classList.toggle('fa-eye', isHidden);
    }
}

window.togglePasswordVisibility = function(inputId, btn) {
    togglePasswordVisibilityById(inputId, btn);
};

function login() {
    if (!loginForm) return;
    const email = document.getElementById('email')?.value || '';
    const password = document.getElementById('password')?.value || '';
    if (isEmpty(email) || isEmpty(password)) {
        fun.swal({
            icon: 'error',
            title: 'Completá email y contraseña',
            timerProgressBar: true,
        });
        return;
    }

    fun.xhr({
        url: 'login',
        data: new FormData(loginForm),
        success: (_response) => {
            if (_response.success) {
                location.reload();
            }
        },
        error: (_response) => {
            fun.swal({
                icon: _response?.icon || 'error',
                title: _response?.msg || 'No se pudo iniciar sesión',
                timerProgressBar: true,
            });
        }
    });
}

function requestPasswordReset() {
    if (!forgotForm) return;
    const email = document.getElementById('forgot_email')?.value || '';
    if (isEmpty(email)) {
        fun.swal({
            icon: 'error',
            title: 'Ingresá tu email para recuperar la contraseña',
            timerProgressBar: true,
        });
        return;
    }
    fun.xhr({
        url: 'requestPasswordReset',
        data: new FormData(forgotForm),
        success: (_response) => {
            let title = _response?.msg || 'Te enviamos las instrucciones por email';
            if (_response?.dev_reset_url) {
                title += `\n\nEnlace local:\n${_response.dev_reset_url}`;
            }
            fun.swal({
                icon: _response?.icon || 'success',
                title: title,
                timer: 7000,
                timerProgressBar: true,
            });
            showPanel(loginPanel);
            forgotForm.reset();
        },
        error: (_response) => {
            fun.swal({
                icon: _response?.icon || 'error',
                title: _response?.msg || 'No se pudo procesar la solicitud',
                timerProgressBar: true,
            });
        }
    });
}

function resetPassword() {
    if (!resetForm) return;
    const password = document.getElementById('reset_password')?.value || '';
    const passwordConfirm = document.getElementById('reset_password_confirm')?.value || '';
    const token = document.getElementById('reset_token')?.value || '';

    if (isEmpty(password) || isEmpty(passwordConfirm)) {
        fun.swal({ icon: 'error', title: 'Completá ambos campos de contraseña', timerProgressBar: true });
        return;
    }
    if (password !== passwordConfirm) {
        fun.swal({ icon: 'error', title: 'Las contraseñas no coinciden', timerProgressBar: true });
        return;
    }
    if (password.trim().length < 6) {
        fun.swal({ icon: 'error', title: 'La contraseña debe tener al menos 6 caracteres', timerProgressBar: true });
        return;
    }

    const data = fun.setForm({
        token: token,
        password: password,
        password_confirm: passwordConfirm
    });
    fun.xhr({
        url: 'resetPassword',
        data: data,
        success: (_response) => {
            fun.swal({
                icon: _response?.icon || 'success',
                title: _response?.msg || 'Contraseña actualizada',
                timerProgressBar: true,
                success: () => {
                    const cleanUrl = `${window.location.origin}${window.location.pathname}`;
                    window.history.replaceState({}, document.title, cleanUrl);
                    showPanel(loginPanel);
                    resetForm.reset();
                }
            });
        },
        error: (_response) => {
            fun.swal({
                icon: _response?.icon || 'error',
                title: _response?.error || _response?.msg || 'No se pudo actualizar la contraseña',
                timerProgressBar: true,
            });
        }
    });
}

document.querySelectorAll('.input-password-show').forEach((btn) => {
    btn.addEventListener('click', () => {
        const inputId = btn.getAttribute('data-input');
        togglePasswordVisibilityById(inputId, btn);
    });
});

document.getElementById('forgot-password-trigger')?.addEventListener('click', (e) => {
    e.preventDefault();
    showPanel(forgotPanel);
    window.history.replaceState({}, document.title, `${window.location.pathname}?forgot=1`);
});
document.getElementById('back-to-login-trigger')?.addEventListener('click', (e) => {
    e.preventDefault();
    showPanel(loginPanel);
    window.history.replaceState({}, document.title, window.location.pathname);
});
document.getElementById('go-to-forgot-trigger')?.addEventListener('click', (e) => {
    e.preventDefault();
    showPanel(forgotPanel);
    window.history.replaceState({}, document.title, `${window.location.pathname}?forgot=1`);
});

loginForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    login();
});
forgotForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    requestPasswordReset();
});
resetForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    resetPassword();
});
