import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useFuentesStore = defineStore('fuentes', () => {
  // Estado reactivo
  const fuentes = ref([])
  const perfiles = ref([])

  // Acciones (métodos para interactuar con el estado y la API)
  const fetchFuentes = async () => {
    try {
      const res = await axios.get('/api/fuentes')
      fuentes.value = res.data
    } catch (err) {
      console.error('Error al obtener fuentes:', err)
      alert('Hubo un error al cargar las fuentes.')
    }
  }

  const fetchPerfiles = async () => {
    try {
      const res = await axios.get('/api/perfiles')
      perfiles.value = res.data
    } catch (err) {
      console.error('Error al obtener perfiles:', err)
      alert('Hubo un error al cargar los perfiles.')
    }
  }

  const uploadFuente = async (file) => {
    const formData = new FormData()
    formData.append('fuente', file)
    try {
      await axios.post('/api/fuentes', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      await fetchFuentes() // Recarga la lista después de subir
      alert('Fuente subida con éxito.')
    } catch (err) {
      console.error('Error subiendo fuente:', err)
      alert('Error al subir la fuente.')
    }
  }

  const eliminarFuente = async (id) => {
    try {
      await axios.delete(`/api/fuentes/${id}`)
      await fetchFuentes() // Recarga la lista después de eliminar
      alert('Fuente eliminada con éxito.')
    } catch (err) {
      console.error('Error eliminando fuente:', err)
      alert('Error al eliminar la fuente.')
    }
  }

  const eliminarPerfil = async (id) => {
    try {
      await axios.delete(`/api/perfiles/${id}`)
      await fetchPerfiles() // Recarga la lista después de eliminar
      alert('Perfil eliminado con éxito.')
    } catch (err) {
      console.error('Error eliminando perfil:', err)
      alert('Error al eliminar el perfil.')
    }
  }

  // Se retornan el estado y las acciones para que el componente pueda usarlos
  return {
    fuentes,
    perfiles,
    fetchFuentes,
    fetchPerfiles,
    uploadFuente,
    eliminarFuente,
    eliminarPerfil
  }
})