import { defineStore } from 'pinia'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

export const useMoldesStore = defineStore('moldes', {
  state: () => ({
    items: [],
    loading: false,
  }),

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const { data } = await axios.get(`${API}/moldes`)
        this.items = data
      } finally {
        this.loading = false
      }
    },

    async addOne(payload, onUploadProgress) {
      // payload = { nombre, archivos: {camiseta_frente?, ...} }
      const fd = new FormData()
      fd.append('nombre', payload.nombre)
      ;['camiseta_frente','camiseta_espalda','short_izq','short_der','manga_izq','manga_der'].forEach(k => {
        const f = payload.archivos?.[k]
        if (f) fd.append(k, f, f.name)
      })

      const { data } = await axios.post(`${API}/moldes`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress
      })
      this.items.unshift(data)
      return data
    },

    async deleteOne(id) {
      await axios.delete(`${API}/moldes/${id}`)
      this.items = this.items.filter(x => x.id !== id)
    }
  }
})
