// src/api.js
import axios from 'axios'

// Si defines VITE_API_BASE_URL (p.ej. http://127.0.0.1:8000/api), se usa esa.
// Si no, usamos '/api' para que el proxy de Vite lo redirija al backend.
const baseURL = import.meta.env.VITE_API_BASE_URL || '/api'

// ⚠️ Importante:
// - Con baseURL = '/api' tus rutas deben ser 'api.post("/fuentes")' (SIN repetir '/api')
// - Si pones VITE_API_BASE_URL='http://127.0.0.1:8000', entonces sí usarías 'api.post("/api/fuentes")'

const api = axios.create({
  baseURL,
  // Ponlo en true SOLO si usas cookies/sesiones (Sanctum) y CORS con credentials.
  withCredentials: false,
  headers: {
    Accept: 'application/json',
  },
})

// Interceptor opcional: log limpio de errores
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const status = err.response?.status
    const data = err.response?.data
    // Mensaje corto al console para depurar rápido
    console.error('[API ERROR]', {
      url: err.config?.url,
      method: err.config?.method,
      status,
      data,
      message: err.message,
    })
    return Promise.reject(err)
  }
)

export default api
