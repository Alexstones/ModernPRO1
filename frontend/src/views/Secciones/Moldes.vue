<template>
  <div class="p-8 max-w-6xl mx-auto bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold mb-4 text-gray-900 border-b pb-2">PESTAÑA MOLDES</h1>

    <p class="mb-6 text-gray-700 text-lg">
      Sube los moldes del conjunto: camiseta (izquierda/derecha), short (izquierda/derecha), mangas (izquierda/derecha)
    </p>

    <!-- Formulario -->
    <div class="space-y-6 mb-8 bg-gray-50 p-6 rounded-xl border-2 border-gray-200">
      <div class="mb-6">
        <label class="block mb-2 text-lg font-medium text-gray-900">Nombre del conjunto</label>
        <input
          v-model="molde.nombre"
          placeholder="Ej. Set CAMISETA 701"
          class="w-full p-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900"
        />
      </div>

      <!-- Campos de archivo -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="(campo, index) in camposArchivo" :key="index">
          <label class="block mb-2 text-lg font-medium text-gray-900">{{ campo.label }}</label>
          <div class="flex items-center">
            <input
              type="file"
              class="hidden"
              @change="handleFileUpload($event, campo.key)"
              :id="'file-'+campo.key"
            />
            <label
              :for="'file-'+campo.key"
              class="bg-white border-2 border-gray-300 rounded-lg px-4 py-2 cursor-pointer hover:bg-gray-100 flex-grow mr-2 text-gray-700"
            >
              {{ molde.archivos[campo.key]?.name || 'Elegir archivo' }}
            </label>
            <button
              v-if="molde.archivos[campo.key]"
              @click="removeFile(campo.key)"
              class="text-red-500 hover:text-red-700"
            >
              ✕
            </button>
          </div>
          <p class="text-sm text-gray-500 mt-1">
            {{ molde.archivos[campo.key] ? '' : 'No se eligió ningún archivo' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Botones -->
    <div class="flex gap-4 mb-8">
      <button @click="addMolde" class="boton-guardar">Guardar Moldes</button>
      <button @click="limpiarCampos" class="boton-limpiar">Limpiar Campos</button>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-lg">
      <h2 class="text-xl font-bold p-4 bg-gray-100 border-b text-gray-900">Moldes Registrados</h2>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th rowspan="2" class="px-6 py-3 font-bold text-gray-900 uppercase">Conjunto</th>
              <th colspan="6" class="px-6 py-3 text-center font-bold text-gray-900 uppercase">Lo que trae</th>
              <th rowspan="2" class="px-6 py-3 font-bold text-gray-900 uppercase">Acciones</th>
            </tr>
            <tr>
              <th class="px-6 py-3">Camiseta Izq</th>
              <th class="px-6 py-3">Camiseta Der</th>
              <th class="px-6 py-3">Short Izq</th>
              <th class="px-6 py-3">Short Der</th>
              <th class="px-6 py-3">Manga Izq</th>
              <th class="px-6 py-3">Manga Der</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="moldes.length === 0">
              <td colspan="8" class="text-center text-gray-500 py-6">
                No hay moldes registrados. Sube tu primer conjunto!
              </td>
            </tr>
            <tr v-for="(item, index) in moldes" :key="index" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium">{{ item.nombre }}</td>

              <td class="px-6 py-4">
                <a v-if="item.camiseta_izq" :href="item.camiseta_izq.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.camiseta_der" :href="item.camiseta_der.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.short_izq" :href="item.short_izq.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.short_der" :href="item.short_der.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.manga_izq" :href="item.manga_izq.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.manga_der" :href="item.manga_der.url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <button @click="eliminarMolde(index)" class="text-red-600 hover:text-red-800">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Campos definidos
const camposArchivo = [
  { key: 'camiseta_izq', label: 'Molde Camiseta Izquierda' },
  { key: 'camiseta_der', label: 'Molde Camiseta Derecha' },
  { key: 'short_izq', label: 'Molde Short Izquierda' },
  { key: 'short_der', label: 'Molde Short Derecha' },
  { key: 'manga_izq', label: 'Molde Manga Izquierda' },
  { key: 'manga_der', label: 'Molde Manga Derecha' }
]

const molde = ref({
  nombre: '',
  archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
})

const moldes = ref([])

const handleFileUpload = (event, campo) => {
  const file = event.target.files[0]
  if (file) {
    molde.value.archivos[campo] = file
  }
}

const removeFile = (campo) => {
  molde.value.archivos[campo] = null
  document.getElementById(`file-${campo}`).value = ''
}

const generarUrl = (archivo) => {
  if (!archivo) return null
  return {
    name: archivo.name,
    url: URL.createObjectURL(archivo)
  }
}

const addMolde = () => {
  if (!molde.value.nombre.trim()) {
    alert('Debes ingresar un nombre para el conjunto')
    return
  }

  const nuevoMolde = {
    nombre: molde.value.nombre,
    camiseta_izq: generarUrl(molde.value.archivos.camiseta_izq),
    camiseta_der: generarUrl(molde.value.archivos.camiseta_der),
    short_izq: generarUrl(molde.value.archivos.short_izq),
    short_der: generarUrl(molde.value.archivos.short_der),
    manga_izq: generarUrl(molde.value.archivos.manga_izq),
    manga_der: generarUrl(molde.value.archivos.manga_der)
  }

  moldes.value.unshift(nuevoMolde)
  limpiarCampos()
}

const eliminarMolde = (index) => {
  moldes.value.splice(index, 1)
}

const limpiarCampos = () => {
  molde.value = {
    nombre: '',
    archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
  }
}
</script>

<style scoped>
.boton-guardar {
  background-color: #10b981;
  color: white;
  padding: 0.75rem 2rem;
  font-weight: bold;
  font-size: 1.125rem;
  border-radius: 0.5rem;
}

.boton-guardar:hover {
  background-color: #059669;
}

.boton-limpiar {
  background-color: #ec4899;
  color: white;
  padding: 0.75rem 2rem;
  font-weight: bold;
  font-size: 1.125rem;
  border-radius: 0.5rem;
}

.boton-limpiar:hover {
  background-color: #db2777;
}
</style>
