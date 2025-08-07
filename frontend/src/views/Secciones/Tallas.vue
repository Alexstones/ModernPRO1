<template>
  <div class="p-6 text-white">
    <h2 class="text-3xl font-bold mb-2">PESTAÑA TALLAS:</h2>
    <p class="mb-6">En esta sección puedes personalizar las medidas de cada talle.</p>

    <form @submit.prevent="addTalla" class="space-y-4">
      <select v-model="talla.categoria" class="input-dark">
        <option disabled value="">Seleccionar categoría</option>
        <option>CAMISETAS</option>
        <option>MANGAS</option>
        <option>SHORT</option>
      </select>

      <input v-model="talla.talle" placeholder="Talle (0, 2, S, M...)" class="input-dark" />
      <input v-model.number="talla.ancho" placeholder="Ancho (cm)" class="input-dark" />
      <input v-model.number="talla.alto" placeholder="Alto (cm)" class="input-dark" />

      <div class="space-x-2">
        <button class="btn-verde" type="submit">Añadir Talla</button>
        <button type="button" class="btn-azul" @click="limpiarCampos">Limpiar Campos</button>
      </div>
    </form>

    <table class="tabla-dark mt-6 w-full">
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
        <tr v-for="item in tallas" :key="item.id">
          <td>{{ item.categoria }}</td>
          <td>{{ item.talle }}</td>
          <td>{{ item.ancho }} cm</td>
          <td>{{ item.alto }} cm</td>
          <td>
            <button class="btn-rojo" @click="eliminarTalla(item.id)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const talla = ref({
  categoria: '',
  talle: '',
  ancho: '',
  alto: ''
})

const tallas = ref([])

const getTallas = async () => {
  const { data } = await axios.get('/api/tallas')
  tallas.value = data
}

const addTalla = async () => {
  await axios.post('/api/tallas', talla.value)
  getTallas()
  limpiarCampos()
}

const eliminarTalla = async (id) => {
  await axios.delete(`/api/tallas/${id}`)
  getTallas()
}

const limpiarCampos = () => {
  talla.value = {
    categoria: '',
    talle: '',
    ancho: '',
    alto: ''
  }
}

onMounted(() => {
  getTallas()
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
}

.tabla-dark {
  width: 100%;
  border-collapse: collapse;
}

.tabla-dark th, .tabla-dark td {
  border: 1px solid #444;
  padding: 0.5rem;
  text-align: center;
}
</style>
