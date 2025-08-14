import { defineStore } from 'pinia'
import { ref } from 'vue'

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

async function apiFetch(path, options = {}) {
  const res = await fetch(`${API}${path}`, {
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
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

  const addTalla = async (t) => {
    error.value = null
    try {
      await apiFetch('/tallas', {
        method: 'POST',
        body: JSON.stringify({ ...t, ancho: Number(t.ancho), alto: Number(t.alto) })
      })
      await getTallas()
    } catch (e) {
      error.value = e.message
      console.error('addTalla:', e)
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

  return { tallas, loading, error, getTallas, addTalla, eliminarTalla }
})
