import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useTallasStore = defineStore('tallas', () => {
  // Estado reactivo
  const tallas = ref(['108', '150', '151', '152', '153'])
  const tallasSeleccionadas = ref([])

  // Acciones
  const addTalla = (nuevaTalla) => {
    if (nuevaTalla && !tallas.value.includes(nuevaTalla)) {
      tallas.value.push(nuevaTalla)
    }
  }

  const eliminarTalla = (index) => {
    const tallaAEliminar = tallas.value[index]
    tallas.value.splice(index, 1)
    tallasSeleccionadas.value = tallasSeleccionadas.value.filter(t => t !== tallaAEliminar)
  }

  // Se retornan el estado y las acciones para que el componente pueda usarlos
  return {
    tallas,
    tallasSeleccionadas,
    addTalla,
    eliminarTalla
  }
})