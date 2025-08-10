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
      <button @click="addMolde" class="boton-guardar" :disabled="store.cargando">
        {{ store.cargando ? 'Guardando...' : 'Guardar Moldes' }}
      </button>
      <button @click="limpiarCampos" class="boton-limpiar">Limpiar Campos</button>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-lg">
      <h2 class="text-xl font-bold p-4 bg-gray-100 border-b text-gray-900">
        Moldes Registrados <span class="text-gray-500 font-normal">({{ store.cantidad }})</span>
      </h2>

      <div class="px-4 pb-2" v-if="store.error">
        <div class="text-red-600">{{ store.error }}</div>
      </div>

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
            <tr v-if="store.moldes.length === 0 && !store.cargando">
              <td colspan="8" class="text-center text-gray-500 py-6">
                No hay moldes registrados. ¡Sube tu primer conjunto!
              </td>
            </tr>

            <tr v-for="item in store.moldes" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium">{{ item.nombre }}</td>

              <td class="px-6 py-4">
                <a v-if="item.camiseta_izq_url" :href="item.camiseta_izq_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.camiseta_der_url" :href="item.camiseta_der_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.short_izq_url" :href="item.short_izq_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.short_der_url" :href="item.short_der_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.manga_izq_url" :href="item.manga_izq_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <a v-if="item.manga_der_url" :href="item.manga_der_url" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else class="text-gray-400 italic">-</span>
              </td>

              <td class="px-6 py-4">
                <button @click="onEliminar(item.id)" class="text-red-600 hover:text-red-800">Eliminar</button>
              </td>
            </tr>

            <tr v-if="store.cargando">
              <td colspan="8" class="text-center text-gray-500 py-4">Cargando…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useMoldesStore } from '@/stores/moldes'

// Campos definidos
const camposArchivo = [
  { key: 'camiseta_izq', label: 'Molde Camiseta Izquierda' },
  { key: 'camiseta_der', label: 'Molde Camiseta Derecha' },
  { key: 'short_izq', label: 'Molde Short Izquierda' },
  { key: 'short_der', label: 'Molde Short Derecha' },
  { key: 'manga_izq', label: 'Molde Manga Izquierda' },
  { key: 'manga_der', label: 'Molde Manga Derecha' }
]

// Store
const store = useMoldesStore()

// Estado local del formulario (mantenemos los File aquí)
const molde = ref({
  nombre: '',
  archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
})

onMounted(() => {
  store.cargarMoldes()
})

const handleFileUpload = (event, campo) => {
  const file = event.target.files?.[0]
  if (file) {
    molde.value.archivos[campo] = file
  }
}

const removeFile = (campo) => {
  molde.value.archivos[campo] = null
  const input = document.getElementById(`file-${campo}`)
  if (input) input.value = ''
}

const addMolde = async () => {
  if (!molde.value.nombre.trim()) {
    alert('Debes ingresar un nombre para el conjunto')
    return
  }

  const fd = new FormData()
  fd.append('nombre', molde.value.nombre)

  // Solo adjuntamos los archivos seleccionados
  for (const c of camposArchivo) {
    const f = molde.value.archivos[c.key]
    if (f) fd.append(c.key, f) // backend debe esperar estos keys
  }

  try {
    await store.crearMolde(fd)
    limpiarCampos()
  } catch (e) {
    alert('No se pudo guardar el molde. Revisa la consola para más detalles.')
  }
}

const onEliminar = async (id) => {
  if (!confirm('¿Eliminar este molde?')) return
  try {
    await store.eliminarMolde(id)
  } catch (e) {
    alert('No se pudo eliminar el molde.')
  }
}

const limpiarCampos = () => {
  molde.value = {
    nombre: '',
    archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
  }
  // limpiar inputs file
  for (const c of camposArchivo) {
    const input = document.getElementById(`file-${c.key}`)
    if (input) input.value = ''
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
.boton-guardar:hover { background-color: #059669; }

.boton-limpiar {
  background-color: #ec4899;
  color: white;
  padding: 0.75rem 2rem;
  font-weight: bold;
  font-size: 1.125rem;
  border-radius: 0.5rem;
}
.boton-limpiar:hover { background-color: #db2777; }
</style>
