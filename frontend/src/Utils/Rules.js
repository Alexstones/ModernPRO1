export const required = [
  value => {
    
      if (!value && value == null) return 'Este campo es requerido'

      return true
  }
]

export const password = [
  value => {
      if (value.length >= 8) return true

      return 'La contraseña requiere al menos ocho carácteres'
  }
]


export const beforeToday = [
  value => {
      if (!value) return 'Introduce una fecha válida'

      const today = new Date()
      const inputDate = new Date(value)

      inputDate.setUTCHours(0, 0, 0, 0)
      today.setUTCHours(0, 0, 0, 0)

      if (inputDate < today) return true

      return 'Introduce una fecha válida'
  }
]

export const validateDiaPago = (value) => {
  if (value < 1 || value > 28) {
      return 'El día de pago debe estar entre 1 y 28'
  }
  return true
}


export const maxLength = (len) => {
  return [
      value => {
          if(!value) return true
          if (value.length <= len) return true

          return 'La longitud no puede superar los ' + len + ' caracteres'
      }
  ]
}

export const minLength = (len) => {
  return [
      value => {
          if(!value) return true
          if (value.length >= len) return true

          return 'La longitud debe ser al menos ' + len + ' caracteres'
      }
  ]
}

export const email = [
  value => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

      return emailRegex.test(value) || 'Introduce un email válido'
  }
]

export const phoneNumber = [
  value => {
      const phoneRegex = /^\d{10}$/

      return phoneRegex.test(value) || 'Introduce un número de teléfono válido de 10 dígitos'
  }
]

export const imageSize = [
  (value) => {
      if (!value[0]) return true
      if (value[0].size < 500000) return true

      return 'La imagen no puede sobrepasar los 500KB'
  }
]

export const imageSizeRequired = [
  (value) => {
      if (!value[0]) return 'La imagen es requerida'
      if (value[0].size < 500000) return true

      return 'La imagen no puede sobrepasar los 500KB'
  }
]

export const siteImageSizeRequired = [
  (value) => {
      if (!value[0]) return 'La imagen es requerida'
      if (value[0].size < 3000000) return true

      return 'La imagen no puede sobrepasar los 3MB'
  }
]

export const username = [
  (value) => {
      if (!value) return 'El nombre de usuario es requerido'
      const regex = /^[a-zA-Z0-9_]+$/
      if (regex.test(value)) return true

      return 'El nombre de usuario solo puede contener letras, números y guiones bajos, sin espacios en blanco'
  }
]

export const number = [
  (value) => {
      const regex = /^-?\d*\.?\d+$/
      if (regex.test(value) && value > 0) return true

      return 'Sólo valores numéricos'
  }
]

export const intNumber = [
  (value) => {
      const regex = /^[1-9]\d*$/
      if (regex.test(value)) return true

      return 'Sólo valores numéricos enteros positivos'
  }
]

export const minNumber = (min) => [
  (value) => {
      if (value >= min) return true

      return 'Sólo valores numéricos mayores o iguales a ' + min
  }
]

export const maxNumber = (max) => [
  (value) => {
      if (value <= max) return true

      return 'Sólo valores numéricos menores o iguales a ' + max
  }
]

export const requiredPdf = [
  (value) => {
      if(value[0]?.isLabel) return true
      if (!value[0]) return 'Este campo es requerido'

      const file = value[0]
      
      if (file.size > 300 * 1024) return 'El tamaño del archivo no debe exceder los 300Kb'
      
      if (file.type !== 'application/pdf') return 'El archivo debe ser un PDF'

      return true
  }
]
