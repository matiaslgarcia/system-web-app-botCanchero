export class Func{
    xhr(param = {}){
        if(param.url){
            const xhr = new XMLHttpRequest()
            xhr.responseType = 'json';
            param.method = (param.method) ? param.method.toUpperCase() : 'POST';
            param.url    = 'fetch/' + param.url
            param.data   = (param.data) ?  param.data : '';
            xhr.open(param.method, param.url, true)
            xhr.onload = () =>  {
                if(xhr.status == 200){
                    if (typeof param.success === 'function')
                        param.success(xhr.response)
                }else{
                    if (typeof param.error === 'function')
                        param.error(xhr.response)
                }
            };
            xhr.send(param.data)
            if(typeof param.beforeSend === 'function'){
                param.beforeSend(xhr.readyState)
            }
        }else{
            console.warn('url empty')
        }
    }
    swal(val = {}){
        var Param = {'icon': '', 'timer': 2500, 'reload': false, allowOutsideClick: false, showConfirmButton: false}
      
        Object.keys(val).forEach((element)=> {
           var key    = element
           var value  = val[element]
           Param[key] = value
        });
        
        Swal.fire(Param).then((result) =>{
            
            if (typeof Param.success === 'function')
                Param.success(result)
        })
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