import { defineStore } from 'pinia'
import { ref } from 'vue'

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

async function apiFetch(path, options = {}) {
  const res = await fetch(`${API}${path}`, {
    headers: options.body instanceof FormData
      ? { Accept: 'application/json' } // multipart, no forzar content-type
      : { Accept: 'application/json', 'Content-Type': 'application/json' },
    ...options,
  })
  if (!res.ok) {
    let body; try { body = await res.json() } catch { body = await res.text() }
    const msg = typeof body === 'string' ? body : (body.message || JSON.stringify(body))
    throw new Error(`HTTP ${res.status} - ${msg}`)
  }
  return res.status === 204 ? null : res.json()
}

export const useTallasStore = defineStore('tallas', () => {
  const tallas = ref([])
  const loading = ref(false)
  const error = ref(null)

  const getTallas = async () => {
    loading.value = true; error.value = null
    try { tallas.value = await apiFetch('/tallas', { method: 'GET', cache: 'no-store' }) }
    catch (e) { error.value = e.message; console.error(e) }
    finally { loading.value = false }
  }

  // Crea y devuelve el registro creado
  const addTalla = async (t) => {
    error.value = null
    try {
      const created = await apiFetch('/tallas', {
        method: 'POST',
        body: JSON.stringify({
          ...t,
          ancho: Number(t.ancho ?? 0),
          alto: Number(t.alto ?? 0),
          molderias: Array.isArray(t.molderias) ? t.molderias : [],
          composiciones: Array.isArray(t.composiciones) ? t.composiciones : []
        })
      })
      await getTallas()
      // retorna el creado (útil para subir molderías luego)
      return created
    } catch (e) {
      error.value = e.message
      console.error('addTalla:', e)
      throw e
    }
  }

  const updateTalla = async (id, patch) => {
    error.value = null
    try {
      const updated = await apiFetch(`/tallas/${id}`, {
        method: 'PATCH',
        body: JSON.stringify(patch)
      })
      // refresco local mínimo
      tallas.value = tallas.value.map(t => t.id === id ? { ...t, ...updated } : t)
      return updated
    } catch (e) {
      error.value = e.message
      console.error('updateTalla:', e)
      throw e
    }
  }

  const bulkUpdate = async (items) => {
    // items: [{id, ancho, alto}, ...]
    error.value = null
    try {
      const resp = await apiFetch('/tallas/bulk', {
        method: 'PATCH',
        body: JSON.stringify({ items })
      })
      await getTallas()
      return resp
    } catch (e) {
      error.value = e.message
      console.error('bulkUpdate:', e)
      throw e
    }
  }

  const eliminarTalla = async (id) => {
    error.value = null
    try {
      await apiFetch(`/tallas/${id}`, { method: 'DELETE' })
      tallas.value = tallas.value.filter(x => x.id !== id)
    } catch (e) {
      error.value = e.message
      console.error('eliminarTalla:', e)
      throw e
    }
  }

  // Sube una moldería (imagen) y devuelve la URL pública
  const uploadMolderia = async (tallaId, file) => {
    const fd = new FormData()
    fd.append('file', file)
    const res = await apiFetch(`/tallas/${tallaId}/molderias`, {
      method: 'POST',
      body: fd
    })
    // API debe devolver { url: 'https://...' }
    return res.url
  }

  // Sube una composición (PNG blob) y devuelve la URL pública
  const uploadComposicion = async (tallaId, blob) => {
    const fd = new FormData()
    fd.append('file', blob, `comp_${Date.now()}.png`)
    const res = await apiFetch(`/tallas/${tallaId}/composiciones`, {
      method: 'POST',
      body: fd
    })
    return res.url
  }

  return {
    tallas, loading, error,
    getTallas, addTalla, updateTalla, bulkUpdate, eliminarTalla,
    uploadMolderia, uploadComposicion
  }
})
