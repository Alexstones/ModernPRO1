import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useProductosStore = defineStore('productos', () => {
  // Estado reactivo
  const productos = ref([
    { nombre: 'SET CAMISETA 701', tipo: 'camisas' },
    { nombre: 'CAMISETA D71', tipo: 'camisas' },
  ])

  // Acciones
  const addProduct = (newProduct) => {
    productos.value.push(newProduct)
  }

  const updateProduct = (index, updatedProduct) => {
    productos.value[index] = updatedProduct
  }

  const deleteProduct = (productToDelete) => {
    productos.value = productos.value.filter(item => item !== productToDelete)
  }

  const deleteSelectedProducts = (selectedItems) => {
    productos.value = productos.value.filter(item => !selectedItems.includes(item))
  }

  // Se retornan el estado y las acciones para que el componente pueda usarlos
  return {
    productos,
    addProduct,
    updateProduct,
    deleteProduct,
    deleteSelectedProducts
  }
})