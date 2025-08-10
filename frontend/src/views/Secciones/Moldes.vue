<template>
  <div class="product-form-container-dark">
    <div class="max-w-6xl mx-auto">
      <!-- Título principal -->
      <div class="product-card-dark overflow-hidden mb-8">
        <div class="product-title-dark">
          <h1 class="text-h5 text-white m-0">PESTAÑA MOLDES</h1>
        </div>
        <div class="p-6">
          <p class="text-gray-200 text-lg m-0">
            Sube los moldes del conjunto: camiseta (izquierda/derecha), short (izquierda/derecha), mangas (izquierda/derecha)
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card: Formulario -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark">
            <h2 class="text-h5 text-white m-0">Añadir/Editar Moldes</h2>
          </div>

          <div class="p-6">
            <div class="mb-6">
              <label class="block mb-2 text-lg font-medium text-white">Nombre del conjunto</label>
              <input
                v-model="molde.nombre"
                placeholder="Ej. Set CAMISETA 701"
                class="product-input w-full"
              />
            </div>

            <!-- Campos de archivo -->
            <div class="grid grid-cols-1 gap-4">
              <div v-for="(campo, index) in camposArchivo" :key="index" class="flex flex-col gap-2">
                <label class="text-white font-medium">{{ campo.label }}</label>
                <div class="flex items-center gap-2">
                  <input
                    type="file"
                    class="hidden"
                    @change="handleFileUpload($event, campo.key)"
                    :id="'file-'+campo.key"
                  />
                  <label
                    :for="'file-'+campo.key"
                    class="product-input flex-1 cursor-pointer truncate"
                  >
                    {{ molde.archivos[campo.key]?.name || 'Elegir archivo' }}
                  </label>
                  <button
                    v-if="molde.archivos[campo.key]"
                    @click="removeFile(campo.key)"
                    class="product-btn-danger px-3 py-2"
                    title="Quitar archivo"
                  >✕</button>
                </div>
                <p class="text-sm text-gray-400 m-0">
                  {{ molde.archivos[campo.key] ? '' : 'No se eligió ningún archivo' }}
                </p>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 mt-8 justify-end">
              <button @click="limpiarCampos" class="product-btn-secondary">Limpiar Campos</button>
              <button @click="addMolde" class="product-btn-primary">Guardar Moldes</button>
            </div>
          </div>
        </div>

        <!-- Card: Tabla -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark">
            <h2 class="text-h5 text-white m-0">Moldes Registrados</h2>
          </div>

          <div class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-left product-table-dark">
                <thead>
                  <tr>
                    <th rowspan="2" class="px-6 py-3">Conjunto</th>
                    <th colspan="6" class="px-6 py-3 text-center">Lo que trae</th>
                    <th rowspan="2" class="px-6 py-3">Acciones</th>
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
                    <td colspan="8" class="text-center text-gray-400 py-6">
                      No hay moldes registrados. ¡Sube tu primer conjunto!
                    </td>
                  </tr>

                  <tr v-for="(item, index) in moldes" :key="index">
                    <td class="px-6 py-4 font-semibold text-white">{{ item.nombre }}</td>

                    <td class="px-6 py-4">
                      <a v-if="item.camiseta_izq" :href="item.camiseta_izq.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.camiseta_der" :href="item.camiseta_der.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.short_izq" :href="item.short_izq.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.short_der" :href="item.short_der.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.manga_izq" :href="item.manga_izq.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.manga_der" :href="item.manga_der.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>

                    <td class="px-6 py-4">
                      <button @click="eliminarMolde(index)" class="product-btn-danger">Eliminar</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Footer acciones tabla -->
            <div class="p-4 text-right">
              <span class="text-gray-300 text-sm">Total: {{ moldes.length }}</span>
            </div>
          </div>
        </div>
      </div> <!-- grid -->
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

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
  const el = document.getElementById(`file-${campo}`)
  if (el) el.value = ''
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
/* Fondo con degradado y sensación premium */
.product-form-container-dark {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  min-height: 100vh;
  padding: 48px 16px;
}

/* Tarjetas oscuras estilo “glass” */
.product-card-dark {
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

/* Header con degradado cálido */
.product-title-dark {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: white;
  padding: 18px 24px;
  font-weight: 700;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Inputs oscuros */
.product-input {
  background-color: #3e3e57;
  border: 1px solid #4f4f72;
  color: #f1f5f9;
  border-radius: 10px;
  padding: 12px 14px;
  outline: none;
  transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease;
}

.product-input::placeholder {
  color: #a7a7bf;
}

.product-input:focus {
  border-color: #8b8bd6;
  box-shadow: 0 0 0 3px rgba(139, 139, 214, 0.35);
  transform: translateY(-1px);
}

/* Botones */
.product-btn-primary {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}

.product-btn-primary:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

.product-btn-secondary {
  background: linear-gradient(135deg, #ec4899, #db2777);
  color: white;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}

.product-btn-secondary:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

.product-btn-danger {
  background: rgba(239, 83, 80, 0.18);
  color: #ef5350;
  font-weight: 800;
  border-radius: 10px;
  padding: 8px 14px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, background .2s ease, color .2s ease;
}

.product-btn-danger:hover {
  background: rgba(239, 83, 80, 0.28);
  color: #ff6b6b;
  transform: translateY(-2px);
  box-shadow: 0 8px 18px rgba(0,0,0,.2);
}

/* Tabla oscura */
.product-table-dark {
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}

.product-table-dark thead tr:first-child th:first-child {
  border-top-left-radius: 12px;
}
.product-table-dark thead tr:first-child th:last-child {
  border-top-right-radius: 12px;
}

.product-table-dark thead {
  background-color: #3e3e57;
  color: #ffffff;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: .4px;
}

.product-table-dark th,
.product-table-dark td {
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.product-table-dark tbody tr {
  background-color: #2c2c3e;
  transition: background-color .18s ease;
}

.product-table-dark tbody tr:hover {
  background-color: #3a3a50;
}

.product-link {
  color: #93c5fd;
  text-decoration: underline;
  transition: color .18s ease;
}

.product-link:hover {
  color: #bfdbfe;
}
</style>
