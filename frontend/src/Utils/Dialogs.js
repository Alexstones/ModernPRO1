import Swal from 'sweetalert2'
import UserStore from '@/stores/UserStore'

let background = ''
let foreground = ''

const setTheme = () => {
    const userStore = UserStore()
    if (!userStore.modo) {
        background = '#212121'
        foreground = '#fff'
    } else {
        background = '#E0E0E0'
        foreground = '#000'
    }
}

export const showErrorDialog = async (e) => {
    setTheme()

    const error = e.response?.data?.message ? (e + '<br /><br />' + e.response.data.message) : e

    return await Swal.fire({
        background: background,
        color: foreground,
        icon: "error",
        title: "Error",
        html: error,
        confirmButtonColor: '#263238'
    })
}
export const showErrorDialogTemprary = async (e) => {
    setTheme()

    const error = e.response?.data?.message ? ( e.response.data.message) : e
    console.error(error)
    return await Swal.fire({
        position: 'top-end',
        toast: true,
        background: background,
        color: foreground,
        icon: "error",
        text: error,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        width: 400, // Ajusta el ancho del diálogo
        // customClass: {
        //     popup: 'custom-swal-popup'
        // }
    })
}

export const showConfirmDialog = async (title) => {
    setTheme(); // Asegúrate de que esta función esté configurada correctamente

    const Confirm = await Swal.fire({
        color: 'black', // Color del texto
        background: 'white', // Color de fondo
        title: title,
        background: background,
        color: foreground,
        icon: 'warning', // Icono de información
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#32CD32', // Color del botón de confirmación (LimeGreen)
        cancelButtonColor: '#E53935' // Color del botón de cancelación (Rojo)
    });

    return Confirm.isConfirmed;
};
export const showCancelConfirmDialog = async () => {
    setTheme(); // Asegúrate de que esta función esté configurada correctamente

    const paymentConfirm = await Swal.fire({
        color: 'black', // Color del texto
        background: 'white', // Color de fondo
        title: 'Confirmar',
        text: '¿Desea cancelar la suscripción?',
        icon: 'info', // Icono de información
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#32CD32', // Color del botón de confirmación (LimeGreen)
        cancelButtonColor: '#E53935' // Color del botón de cancelación (Rojo)
    });

    return paymentConfirm.isConfirmed;
};

export const showCloseConfirmDialog = async () => {
    setTheme()

    const deleteConfirm = await Swal.fire({
        color: foreground,
        background: background,
        title: t('message.wantClose'),
        icon: "error",
        showCancelButton: true,
        confirmButtonText: t('message.close'),
        cancelButtonText: t('message.cancel'),
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#263238'
    })
    return deleteConfirm.isConfirmed
}

export const showDataTableDeleteConfirmDialog = async () => {
    setTheme()

    const deleteConfirm = await Swal.fire({
        color: foreground,
        background: background,
        title: 'Desea eliminar este elemento?',
        icon: "error",
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#263238'
    })
    return deleteConfirm.isConfirmed
}

export const showDataTableStatusConfirmDialog = async (flag) => {
    setTheme()

    const deleteConfirm = await Swal.fire({
        color: foreground,
        background: background,
        title: flag ? '¿Desea dar de baja este elemento?' : '¿Desea dar de alta este elemento?',
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: flag ? 'Dar de baja' : 'Dar de alta',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: flag ? '#E53935' : '#00FF00',
        cancelButtonColor: '#263238'
    })
    
    
    return deleteConfirm.isConfirmed
}

export const showSuccessDialog = async (text) => {
    setTheme()

    return await Swal.fire({
        position: 'top-end',
        toast: true,
        background: background,
        color: foreground,
        icon: "success",
        text: text,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    })
}