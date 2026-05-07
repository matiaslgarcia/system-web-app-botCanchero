import {Func} from  './function.js';
const fun = new Func;
let form = document.querySelector('#form-add-user')
let passwordIntput = document.querySelector('#password')
let avatar = document.querySelector('#add-user-select-avatar')
let avatarInput = document.querySelector('#avatar')

const optimizeAvatarFile = (file) => new Promise((resolve) => {
    if (!file || !file.type || !file.type.startsWith('image/')) return resolve(file)

    const reader = new FileReader()
    reader.onload = () => {
        const img = new Image()
        img.onload = () => {
            const maxSize = 1024
            let { width, height } = img
            if (width > height && width > maxSize) {
                height = Math.round((height * maxSize) / width)
                width = maxSize
            } else if (height >= width && height > maxSize) {
                width = Math.round((width * maxSize) / height)
                height = maxSize
            }

            const canvas = document.createElement('canvas')
            canvas.width = width
            canvas.height = height
            const ctx = canvas.getContext('2d')
            if (!ctx) return resolve(file)
            ctx.drawImage(img, 0, 0, width, height)

            canvas.toBlob((blob) => {
                if (!blob) return resolve(file)
                const optimized = new File([blob], `avatar_${Date.now()}.jpg`, { type: 'image/jpeg' })
                resolve(optimized)
            }, 'image/jpeg', 0.82)
        }
        img.onerror = () => resolve(file)
        img.src = reader.result
    }
    reader.onerror = () => resolve(file)
    reader.readAsDataURL(file)
})

passwordIntput.addEventListener('focus', () =>{
    $('#btn-generate-password').slideDown();
})
document.querySelector('#btn-generate-password').addEventListener('click', () => {
    passwordIntput.value = fun.generatePassword()
})

avatar.addEventListener('click', () =>{
    avatarInput.click()
})

avatarInput.addEventListener('change', () => {
    if(avatarInput.files.length == 1){
        fun.setImgSrc(avatarInput.files[0], avatar.id)
    }
})


form.addEventListener('submit', async (e) => {
    e.preventDefault()
    const data = new FormData(form)
    if (avatarInput.files.length === 1) {
        const optimizedAvatar = await optimizeAvatarFile(avatarInput.files[0])
        data.delete('avatar')
        data.append('avatar', optimizedAvatar, optimizedAvatar.name)
    }

    fun.xhr({
        url: 'add-user',
        data,
        error: (_response) =>{
            if(_response?.add_fail){
                fun.swal({
                    icon: _response.icon || 'error',
                    title: _response.msg || 'No se pudo crear el usuario'
                })
                if (Array.isArray(_response.error)) {
                    _response.error.forEach(element => {
                        const input = document.getElementById(element.key)
                        if (input && typeof input.error === 'function') {
                            input.error({class: ['error']})
                        }
                    });
                }
                
            } else {
                fun.swal({
                    icon: 'error',
                    title: 'No se pudo crear el usuario. Si la imagen es muy pesada, probá con una foto más liviana.'
                })
            }
        },
        success: (_response) =>{
            fun.swal({
                title: 'Usuario Canchero Creado Correctamente',
                icon: 'success',
                success: () =>{
                    location.href = 'users-list'
                }
            })
        }
    })
})
