export class Func{
    static ensureRequestId(){
        if (window.__REQUEST_ID__) return window.__REQUEST_ID__;
        const fromMeta = document.querySelector('meta[name="request-id"]')?.getAttribute('content');
        if (fromMeta) {
            window.__REQUEST_ID__ = fromMeta;
            return window.__REQUEST_ID__;
        }
        const rid = 'rid_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 10);
        window.__REQUEST_ID__ = rid;
        const meta = document.querySelector('meta[name="request-id"]');
        if (meta) meta.setAttribute('content', rid);
        return rid;
    }

    xhr(param = {}){
        if(param.url){
            const xhr = new XMLHttpRequest()
            xhr.responseType = 'json';
            param.method = (param.method) ? param.method.toUpperCase() : 'POST';
            param.url    = 'fetch/' + param.url
            param.data   = (param.data) ?  param.data : '';

            let requestUrl = param.url;
            let payload = param.data;

            if (param.method === 'GET' && param.data) {
                let query = '';
                if (typeof param.data === 'string') {
                    query = param.data;
                } else if (param.data instanceof FormData) {
                    query = new URLSearchParams(param.data).toString();
                }
                if (query) {
                    requestUrl += (requestUrl.includes('?') ? '&' : '?') + query;
                }
                payload = null;
            }

            xhr.open(param.method, requestUrl, true)
            
            // CSRF Token integration
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            }
            xhr.setRequestHeader('X-Request-ID', Func.ensureRequestId());

            xhr.onload = () =>  {
                const responseRequestId = xhr.getResponseHeader('X-Request-ID');
                if (responseRequestId) {
                    window.__REQUEST_ID__ = responseRequestId;
                    const meta = document.querySelector('meta[name="request-id"]');
                    if (meta) meta.setAttribute('content', responseRequestId);
                }
                if(xhr.status == 200){
                    if (typeof param.success === 'function')
                        param.success(xhr.response)
                }else{
                    if (typeof param.error === 'function')
                        param.error(xhr.response)
                }
            };
            if(typeof param.beforeSend === 'function'){
                param.beforeSend(xhr)
            }
            xhr.send(payload)
        }else{
            console.warn('url empty')
        }
    }
    swal(val = {}){
        const buttonClassByVariant = {
            primary: 'btn btn-primary',
            success: 'btn btn-success',
            danger: 'btn btn-danger',
            warning: 'btn btn-warning',
            secondary: 'btn btn-light',
            lightDanger: 'btn btn-light-danger',
        };
        const defaults = {
            icon: '',
            timer: 2500,
            allowOutsideClick: false,
            showConfirmButton: false,
        };
        const merged = { ...defaults, ...val };
        const shouldReload = Boolean(merged.reload);
        const onSuccess = (typeof merged.success === 'function') ? merged.success : null;
        const confirmVariant = merged.confirmVariant || 'success';
        const cancelVariant = merged.cancelVariant || 'lightDanger';
        const {
            reload,
            success,
            confirmVariant: _confirmVariant,
            cancelVariant: _cancelVariant,
            confirmButtonColor,
            cancelButtonColor,
            ...swalParams
        } = merged;

        if (swalParams.showCancelButton || swalParams.showConfirmButton) {
            if (swalParams.showCancelButton && !swalParams.confirmButtonText) {
                swalParams.confirmButtonText = 'Sí, confirmar';
            }
            if (swalParams.showCancelButton && !swalParams.cancelButtonText) {
                swalParams.cancelButtonText = 'No, cancelar';
            }
            const currentCustomClass = swalParams.customClass || {};
            swalParams.buttonsStyling = false;
            swalParams.reverseButtons = typeof swalParams.reverseButtons === 'boolean' ? swalParams.reverseButtons : true;
            swalParams.focusCancel = typeof swalParams.focusCancel === 'boolean' ? swalParams.focusCancel : true;
            swalParams.customClass = {
                ...currentCustomClass,
                actions: `${currentCustomClass.actions || ''} d-flex gap-3 justify-content-center`.trim(),
                confirmButton: currentCustomClass.confirmButton || buttonClassByVariant[confirmVariant] || buttonClassByVariant.primary,
                cancelButton: currentCustomClass.cancelButton || buttonClassByVariant[cancelVariant] || buttonClassByVariant.lightDanger,
            };
        }

        return Swal.fire(swalParams).then((result) => {
            if (shouldReload) location.reload();
            if (onSuccess) onSuccess(result);
            return result;
        })
    }
    confirm(val = {}) {
        return this.swal({
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'No, cancelar',
            confirmVariant: 'success',
            cancelVariant: 'lightDanger',
            timer: 0,
            ...val,
        });
    }
    setForm(params = {}){
        let formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        })
        return formData
    }
    showLoader(){
        $('#loader-page').fadeIn();
    }
    hidenLoader(){
        $('#loader-page').fadeOut();
    }
    generatePassword(){
        const longitud = 8;
        const caracteresPermitidos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let contrasena = '';
      
        for (let i = 0; i < longitud; i++) {
          const indiceAleatorio = Math.floor(Math.random() * caracteresPermitidos.length);
          contrasena += caracteresPermitidos.charAt(indiceAleatorio);
        }
      
        return contrasena;
    }
    setImgSrc(file, id){
        let reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById(id).src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
}
