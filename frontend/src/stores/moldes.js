// src/stores/moldes.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useMoldesStore = defineStore('moldes', {
  state: () => ({
    moldes: [],
    cargando: false,
    error: null
  }),

  getters: {
    cantidad: (s) => s.moldes.length
  },

  actions: {
    async cargarMoldes() {
      this.cargando = true
      this.error = null
      try {
        const { data } = await axios.get('/api/moldes')
        // Se espera una lista de objetos con estructura:
        // { id, nombre, camiseta_izq_url, camiseta_der_url, short_izq_url, short_der_url, manga_izq_url, manga_der_url }
        this.moldes = Array.isArray(data) ? data : []
      } catch (e) {
        this.error = 'No se pudieron cargar los moldes.'
        console.error(e)
      } finally {
        this.cargando = false
      }
    },

    async crearMolde(formData) {
      // formData con: nombre y archivos (camiseta_izq, camiseta_der, short_izq, short_der, manga_izq, manga_der)
      try {
        const { data } = await axios.post('/api/moldes', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        // `data` debe regresar el molde guardado con sus URLs públicas
        this.moldes.unshift(data)
        return data
      } catch (e) {
        console.error('Error al crear molde:', e)
        throw e
      }
    },

    async eliminarMolde(id) {
      try {
        await axios.delete(`/api/moldes/${id}`)
        this.moldes = this.moldes.filter(m => m.id !== id)
      } catch (e) {
        console.error('Error al eliminar molde:', e)
        throw e
      }
    }
  }
})
