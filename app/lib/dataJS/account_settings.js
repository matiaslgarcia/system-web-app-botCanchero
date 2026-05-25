import { Func } from './function.js';
const fun = new Func;

const init = () => {
    const activateTabFromHash = () => {
        const hash = window.location.hash || '';
        if (!hash || !window.bootstrap?.Tab) return;
        const trigger = document.querySelector(`a[data-bs-toggle="tab"][href="${hash}"]`);
        if (!trigger) return;
        window.bootstrap.Tab.getOrCreateInstance(trigger).show();
    };

    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach((trigger) => {
        trigger.addEventListener('shown.bs.tab', (event) => {
            const href = event.target.getAttribute('href') || '';
            if (href.startsWith('#')) {
                history.replaceState(null, '', href);
            }
        });
    });

    activateTabFromHash();

    // Avatar Preview
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fun.setImgSrc(this.files[0], 'profile-avatar-preview');
                const reader = new FileReader();
                reader.onload = (e) => {
                    const bgPreview = document.getElementById('avatar-preview-bg');
                    if (bgPreview) bgPreview.style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Perfil Form
    const profileForm = document.getElementById('form-profile-settings');
    if (profileForm) {
        profileForm.onsubmit = function(e) {
            e.preventDefault();
            fun.xhr({
                url: 'edit-user',
                data: new FormData(this),
                success: (res) => {
                    fun.swal({
                        icon: res.icon || 'success',
                        title: res.msg || 'Perfil actualizado',
                        success: () => location.reload()
                    });
                }
            });
            return false;
        };
    }

    // Password Form
    const passwordForm = document.getElementById('form-change-password');
    if (passwordForm) {
        passwordForm.onsubmit = function(e) {
            e.preventDefault();
            const pass = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (pass.length < 6) {
                fun.swal({ icon: 'error', title: 'La contraseña debe tener al menos 6 caracteres' });
                return false;
            }

            if (pass !== confirm) {
                fun.swal({ icon: 'error', title: 'Las contraseñas no coinciden' });
                return false;
            }

            fun.xhr({
                url: 'changePassword',
                data: new FormData(this),
                success: (res) => {
                    fun.swal({
                        icon: 'success',
                        title: 'Contraseña actualizada!',
                        success: () => location.reload()
                    });
                }
            });
            return false;
        };
    }

    // Mercado Pago unlink
    const unlinkBtn = document.getElementById('btn-mp-unlink');
    if (unlinkBtn) {
        unlinkBtn.addEventListener('click', () => {
            fun.confirm({
                title: '¿Desvincular Mercado Pago?',
                text: 'Vas a desconectar la cuenta actual y el bot no podrá generar links de pago hasta volver a vincular.',
                icon: 'warning',
                success: (result) => {
                    if (!result.isConfirmed) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const data = new FormData();
                    if (csrf) data.append('csrf_token', csrf);

                    fun.xhr({
                        url: 'mp-unlink',
                        data,
                        success: (res) => {
                            fun.swal({
                                icon: res?.icon || 'success',
                                title: res?.msg || 'Mercado Pago desvinculado',
                                success: () => location.reload()
                            });
                        },
                        error: (res) => {
                            fun.swal({
                                icon: 'error',
                                title: res?.msg || res?.error || 'No se pudo desvincular Mercado Pago'
                            });
                        }
                    });
                }
            });
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
