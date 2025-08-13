import axios from 'axios'

// Usa el proxy de Vite: /api  →  http://127.0.0.1:8000/api
// Si no existe la env, también cae a /api.
const baseURL = import.meta.env.VITE_API_BASE_URL || '/api'

export const api = axios.create({
  baseURL,
  withCredentials: false, // ponlo en true SOLO si usas cookies (Sanctum) y ajustas CORS a supports_credentials=true
})

// (Opcional) Token Bearer si usas JWT
// api.interceptors.request.use((config) => {
//   const token = localStorage.getItem('token')
//   if (token) config.headers.Authorization = `Bearer ${token}`
//   return config
// })

// (Opcional) logging de errores para depurar
api.interceptors.response.use(
  (res) => res,
  (err) => {
    console.error('[API ERROR]', {
      url: err.config?.url,
      method: err.config?.method,
      status: err.response?.status,
      data: err.response?.data,
      message: err.message,
    })
    return Promise.reject(err)
  }
)

export default api
