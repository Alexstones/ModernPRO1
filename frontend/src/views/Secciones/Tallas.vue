<template>
  <div class="p-6 text-white max-w-4xl mx-auto">
    <h2 class="text-3xl font-bold mb-2">PESTAÑA TALLAS:</h2>
    <p class="mb-6">En esta sección puedes personalizar las medidas de cada talle.</p>

    <form @submit.prevent="handleFormSubmit" class="space-y-4">
      <select v-model="talla.categoria" class="input-dark" required>
        <option disabled value="">Seleccionar categoría</option>
        <option>CAMISETAS</option>
        <option>MANGAS</option>
        <option>SHORT</option>
      </select>

      <input v-model="talla.talle" placeholder="Talle (0, 2, S, M...)" class="input-dark" required />
      <input v-model.number="talla.ancho" type="number" min="0" placeholder="Ancho (cm)" class="input-dark" required />
      <input v-model.number="talla.alto" type="number" min="0" placeholder="Alto (cm)" class="input-dark" required />

      <div class="space-x-2">
        <button class="btn-verde" type="submit">Añadir Talla</button>
        <button type="button" class="btn-azul" @click="limpiarCampos">Limpiar Campos</button>
      </div>
    </form>

    <table class="tabla-dark mt-8 w-full">
      <thead>
        <tr>
          <th>Categoría</th>
          <th>Talle</th>
          <th>Ancho</th>
          <th>Alto</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="tallasStore.tallas.length === 0">
          <td colspan="5" class="text-center py-4 text-gray-400">No hay tallas registradas aún.</td>
        </tr>
        <tr v-for="item in tallasStore.tallas" :key="item.id">
          <td>{{ item.categoria }}</td>
          <td>{{ item.talle }}</td>
          <td>{{ item.ancho }} cm</td>
          <td>{{ item.alto }} cm</td>
          <td>
            <button class="btn-rojo" @click="tallasStore.eliminarTalla(item.id)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTallasStore } from '@/stores/tallas'

// Accede al store de Pinia
const tallasStore = useTallasStore()

// Estado del formulario (se mantiene en el componente)
const talla = ref({
  categoria: '',
  talle: '',
  ancho: '',
  alto: ''
})

// Función para manejar el envío del formulario
const handleFormSubmit = async () => {
  await tallasStore.addTalla({ ...talla.value })
  limpiarCampos()
}

// Limpiar formulario
const limpiarCampos = () => {
  talla.value = {
    categoria: '',
    talle: '',
    ancho: '',
    alto: ''
  }
}

// Cargar tallas al iniciar usando la acción del store
onMounted(() => {
  tallasStore.getTallas()
})
</script>

<style scoped>
.input-dark {
  background-color: #222;
  border: 1px solid #444;
  padding: 0.5rem;
  border-radius: 4px;
  width: 100%;
  color: white;
}

.btn-verde {
  background: #28a745;
  padding: 0.5rem 1rem;
  border: none;
  color: white;
  border-radius: 4px;
}

.btn-azul {
  background: #007bff;
  padding: 0.5rem 1rem;
  border: none;
  color: white;
  border-radius: 4px;
}

.btn-rojo {
  background: #dc3545;
  padding: 0.4rem 0.8rem;
  border: none;
  color: white;
  border-radius: 4px;
  font-size: 0.9rem;
}

.tabla-dark {
  width: 100%;
  border-collapse: collapse;
}

.tabla-dark th, .tabla-dark td {
  border: 1px solid #444;
  padding: 0.5rem;
  text-align: center;
  background-color: #111;
}
</style>