import { Func } from './function.js';

const fun = new Func();
const noteForm = document.getElementById('form-customer-note');
const tagForm = document.getElementById('form-customer-tag');
const consentForm = document.getElementById('form-customer-consent');
const visibilityForm = document.getElementById('form-customer-visibility');
const anonymizeForm = document.getElementById('form-customer-anonymize');

if (noteForm) {
    noteForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fun.xhr({
            url: 'save-customer-note',
            data: new FormData(noteForm),
            success: (response) => {
                fun.swal({
                    icon: 'success',
                    title: response?.msg || 'Nota guardada correctamente',
                    willClose: () => location.reload(),
                });
            },
            error: (response) => {
                fun.swal({
                    icon: 'error',
                    title: response?.error || response?.msg || 'No se pudo guardar la nota',
                });
            }
        });
    });
}

if (tagForm) {
    tagForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fun.xhr({
            url: 'save-customer-tag',
            data: new FormData(tagForm),
            success: (response) => {
                fun.swal({
                    icon: 'success',
                    title: response?.msg || 'Tag guardado correctamente',
                    willClose: () => location.reload(),
                });
            },
            error: (response) => {
                fun.swal({
                    icon: 'error',
                    title: response?.error || response?.msg || 'No se pudo guardar el tag',
                });
            }
        });
    });
}

if (consentForm) {
    consentForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fun.xhr({
            url: 'save-customer-consent',
            data: new FormData(consentForm),
            success: (response) => {
                fun.swal({
                    icon: 'success',
                    title: response?.msg || 'Consentimiento guardado correctamente',
                    willClose: () => location.reload(),
                });
            },
            error: (response) => {
                fun.swal({
                    icon: 'error',
                    title: response?.error || response?.msg || 'No se pudo guardar el consentimiento',
                });
            }
        });
    });
}

if (anonymizeForm) {
    anonymizeForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fun.confirm({
            title: '¿Anonimizar este cliente?',
            text: 'Si el cliente solo pertenece a esta sede, se anonimizarán nombre, teléfono y email. Si opera en otras sedes, se limpiará y ocultará solo en este establecimiento.',
            confirmButtonText: 'Sí, anonimizar',
            cancelButtonText: 'Cancelar',
            confirmVariant: 'danger',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'anonymize-customer',
                data: new FormData(anonymizeForm),
                success: (response) => {
                    fun.swal({
                        icon: 'success',
                        title: response?.msg || 'Cliente anonimizado correctamente',
                        willClose: () => {
                            const establishmentId = anonymizeForm.querySelector('[name="establishment_id"]')?.value || '';
                            location.href = establishmentId ? `clientes?establishment_id=${encodeURIComponent(establishmentId)}` : 'clientes';
                        },
                    });
                },
                error: (response) => {
                    fun.swal({
                        icon: 'error',
                        title: response?.error || response?.msg || 'No se pudo anonimizar el cliente',
                    });
                }
            });
        });
    });
}

if (visibilityForm) {
    visibilityForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const status = visibilityForm.querySelector('[name="status"]')?.value || 'inactive';
        const isRestore = status === 'active';
        fun.confirm({
            title: isRestore ? '¿Reactivar este cliente?' : '¿Dar de baja este cliente?',
            text: isRestore
                ? 'El cliente volverá a estar disponible operativamente en este establecimiento.'
                : 'El cliente dejará de aparecer en búsquedas y listados operativos de este establecimiento, pero conservará sus datos e históricos.',
            confirmButtonText: isRestore ? 'Sí, reactivar' : 'Sí, dar de baja',
            cancelButtonText: 'Cancelar',
            confirmVariant: isRestore ? 'success' : 'warning',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'set-customer-visibility',
                data: new FormData(visibilityForm),
                success: (response) => {
                    fun.swal({
                        icon: 'success',
                        title: response?.msg || (isRestore ? 'Cliente reactivado correctamente' : 'Cliente dado de baja correctamente'),
                        willClose: () => location.reload(),
                    });
                },
                error: (response) => {
                    fun.swal({
                        icon: 'error',
                        title: response?.error || response?.msg || 'No se pudo actualizar la visibilidad del cliente',
                    });
                }
            });
        });
    });
}

document.querySelectorAll('.btn-remove-customer-tag').forEach((button) => {
    button.addEventListener('click', () => {
        const tagMapId = button.dataset.tagMapId || '';
        const establishmentId = button.dataset.establishmentId || '';
        fun.confirm({
            title: '¿Quitar este tag?',
            text: 'El cliente perderá esta etiqueta en el establecimiento actual.',
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'No, volver',
            confirmVariant: 'danger',
            cancelVariant: 'secondary',
        }).then((result) => {
            if (!result.isConfirmed) return;
            fun.xhr({
                url: 'delete-customer-tag',
                data: fun.setForm({
                    tag_map_id: tagMapId,
                    establishment_id: establishmentId,
                }),
                success: (response) => {
                    fun.swal({
                        icon: 'success',
                        title: response?.msg || 'Tag removido correctamente',
                        willClose: () => location.reload(),
                    });
                },
                error: (response) => {
                    fun.swal({
                        icon: 'error',
                        title: response?.error || response?.msg || 'No se pudo quitar el tag',
                    });
                }
            });
        });
    });
});
