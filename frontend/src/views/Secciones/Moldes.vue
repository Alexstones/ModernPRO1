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
            Sube los moldes del conjunto: camiseta (frente/espalda), short (izquierda/derecha), mangas (izquierda/derecha)
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
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Nombre del conjunto -->
              <div class="md:col-span-2">
                <label class="block mb-2 text-lg font-medium text-white">Nombre del conjunto</label>
                <input
                  v-model="molde.nombre"
                  placeholder="Ej. Set CAMISETA 701"
                  class="product-input w-full"
                />
              </div>

              <!-- Talla para auto-mapeo -->
              <div>
                <label class="block mb-2 text-lg font-medium text-white">Talla</label>
                <input
                  v-model="tallaAuto"
                  inputmode="numeric"
                  placeholder="8"
                  class="product-input w-full"
                />
              </div>
            </div>

            <!-- Atajo: cargar carpeta y auto-asignar por talla -->
            <div class="mb-6">
              <input
                ref="dirInput"
                type="file"
                style="display:none"
                webkitdirectory
                directory
                multiple
                accept=".svg,.png,.jpg,.jpeg,.pdf"
                @change="handleFolder"
              />
              <button class="product-btn-secondary" @click="openFolderPicker" :disabled="uploading">
                Cargar carpeta (moldería de esta talla)
              </button>
              <p class="text-sm text-gray-300 mt-2">
                Elige la <span class="font-semibold">carpeta</span> que contiene los archivos. Intentaremos asignarlos
                automáticamente a cada pieza según el nombre y la talla (ej. <code>...T8...</code>, <code>_8_</code>, <code>-8</code>, <code>t8</code>).
              </p>
              <p v-if="autoMapMsg" class="text-sm mt-1" :class="autoMapOk ? 'text-green-300' : 'text-yellow-300'">
                {{ autoMapMsg }}
              </p>
            </div>

            <!-- Campos de archivo (edición manual si quieres cambiar alguno) -->
            <div class="grid grid-cols-1 gap-4">
              <div v-for="(campo, index) in camposArchivo" :key="index" class="flex flex-col gap-2">
                <label class="text-white font-medium">{{ campo.label }}</label>
                <div class="flex items-center gap-2">
                  <input
                    type="file"
                    accept=".svg,.png,.jpg,.jpeg,.pdf"
                    style="display:none"
                    @change="handleFileUpload($event, campo.key)"
                    :id="'file-'+campo.key"
                  />
                  <label
                    :for="'file-'+campo.key"
                    class="product-input flex-1 cursor-pointer truncate"
                    :title="molde.archivos[campo.key]?.name || 'Elegir archivo'"
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

            <!-- Progreso de subida -->
            <div v-if="uploading" class="mt-6 text-sm text-gray-200">
              Subiendo…
              <div class="w-full bg-gray-700 rounded h-2 mt-2 overflow-hidden">
                <div
                  class="bg-green-500 h-2 transition-all"
                  :style="{ width: (progress === null ? '100%' : Math.round(progress) + '%') }"
                ></div>
              </div>
              <div v-if="progress !== null" class="mt-1">{{ Math.round(progress) }}%</div>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 mt-8 justify-end">
              <button @click="limpiarCampos" class="product-btn-secondary" :disabled="uploading">Limpiar Campos</button>
              <button @click="addMolde" class="product-btn-primary" :disabled="uploading || !isValidNombre || !alMenosUnArchivo">
                {{ uploading ? 'Guardando…' : 'Guardar Moldes' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Card: Tabla -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark flex items-center justify-between">
            <h2 class="text-h5 text-white m-0">Moldes Registrados</h2>
            <span v-if="store.loading" class="text-sm text-gray-200">Cargando…</span>
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
                    <th class="px-6 py-3">Camiseta frente</th>
                    <th class="px-6 py-3">Camiseta espalda</th>
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

                  <tr v-for="(item, index) in moldes" :key="item.id || index">
                    <td class="px-6 py-4 font-semibold text-white">{{ item.nombre }}</td>

                    <td class="px-6 py-4">
                      <a v-if="item.camiseta_frente" :href="item.camiseta_frente.url" target="_blank" class="product-link">Ver</a>
                      <span v-else class="text-gray-500 italic">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="item.camiseta_espalda" :href="item.camiseta_espalda.url" target="_blank" class="product-link">Ver</a>
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
import { ref, computed, onMounted } from 'vue'
import { useMoldesStore } from '@/stores/moldes'

const camposArchivo = [
  { key: 'camiseta_frente',  label: 'Molde Camiseta frente' },
  { key: 'camiseta_espalda', label: 'Molde Camiseta espalda' },
  { key: 'short_izq',        label: 'Molde Short Izquierda' },
  { key: 'short_der',        label: 'Molde Short Derecha' },
  { key: 'manga_izq',        label: 'Molde Manga Izquierda' },
  { key: 'manga_der',        label: 'Molde Manga Derecha' }
]

const store = useMoldesStore()

const molde = ref({
  nombre: '',
  archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
})

const tallaAuto   = ref('')     // ej. "8"
const dirInput    = ref(null)
const uploading   = ref(false)
const progress    = ref(null)   // null = indeterminado
const autoMapMsg  = ref('')
const autoMapOk   = ref(false)

const moldes = computed(() => store.items)
const isValidNombre = computed(() => (molde.value.nombre || '').trim().length > 0)
const alMenosUnArchivo = computed(() => Object.values(molde.value.archivos).some(f => !!f))

// === Cargar carpeta completa y auto-mapear por talla ===
const openFolderPicker = () => dirInput.value?.click?.()

const handleFolder = (e) => {
  const files = Array.from(e.target.files || [])
  if (!files.length) return

  const talla = (tallaAuto.value || '').toString().trim()
  const asignadas = autoMapFromFolder(files, talla)

  const keys = Object.keys(asignadas).filter(k => asignadas[k])
  const faltantes = camposArchivo.map(c => c.key).filter(k => !asignadas[k])

  // asignar al estado
  for (const k of keys) molde.value.archivos[k] = asignadas[k]

  // si no hay nombre y se detectó carpeta, sugerir uno
  if (!molde.value.nombre && files[0].webkitRelativePath) {
    const parts = files[0].webkitRelativePath.split('/')
    const carpeta = parts.length > 1 ? parts[parts.length - 2] : 'Conjunto'
    molde.value.nombre = `${carpeta}${talla ? ` - T${talla}` : ''}`
  }

  // mensaje
  if (keys.length) {
    autoMapOk.value = faltantes.length === 0
    autoMapMsg.value = `Asignados: ${keys.length}/6. ${faltantes.length ? `Faltan: ${faltantes.join(', ')}` : '¡Todo listo!'}`
  } else {
    autoMapOk.value = false
    autoMapMsg.value = 'No se encontraron archivos que coincidan con esta talla o patrones. Revisa los nombres o sube manualmente.'
  }

  // limpia input carpeta para permitir re-selección
  e.target.value = ''
}

function autoMapFromFolder(files, talla) {
  const allowed = /\.(svg|png|jpe?g|pdf)$/i
  const clean = (s) => (s || '').toLowerCase()

  const patterns = {
    camiseta_frente:  [/(camiseta|playera|remera).*(frente|front)/i, /(frente|front).*?(camiseta|playera|remera)/i],
    camiseta_espalda: [/(camiseta|playera|remera).*(espalda|back)/i, /(espalda|back).*?(camiseta|playera|remera)/i],
    short_izq:        [/(short|pantal(o|ó)n|bermuda|shorts).*?(izq|left)/i],
    short_der:        [/(short|pantal(o|ó)n|bermuda|shorts).*?(der|right)/i],
    manga_izq:        [/(manga).*?(izq|left)/i],
    manga_der:        [/(manga).*?(der|right)/i]
  }

  const tallaRegex = talla
    ? new RegExp(`(^|[^a-z0-9])(?:t(?:alla)?\\s*)?${talla}(?=[^a-z0-9]|$)`, 'i')
    : null

  // score por archivo y campo
  const scoreFor = (name, field) => {
    let s = 0
    for (const rx of patterns[field]) if (rx.test(name)) { s += 100; break }
    if (tallaRegex && tallaRegex.test(name)) s += 50
    // bonus por palabras típicas
    if (/frente|front|espalda|back|izq|left|der|right|manga|short|camiseta|playera|remera/.test(name)) s += 10
    return s
  }

  // elegir mejor candidato por campo
  const out = Object.fromEntries(Object.keys(patterns).map(k => [k, null]))
  const used = new Set()

  const candidates = files
    .filter(f => allowed.test(f.name))
    .map(f => ({ file: f, name: clean(f.name) }))

  for (const field of Object.keys(patterns)) {
    let best = null
    let bestScore = 0
    for (const c of candidates) {
      if (used.has(c.file)) continue
      const sc = scoreFor(c.name, field)
      if (sc > bestScore) { best = c.file; bestScore = sc }
    }
    if (best) { out[field] = best; used.add(best) }
  }

  return out
}

// === Manejo manual por archivo ===
const handleFileUpload = (event, campo) => {
  const file = event.target.files?.[0]
  if (!file) return
  if (!/\.(svg|png|jpe?g|pdf)$/i.test(file.name)) {
    alert('Formato no permitido. Usa SVG/PNG/JPG/PDF.')
    event.target.value = ''
    return
  }
  molde.value.archivos[campo] = file
}

const removeFile = (campo) => {
  molde.value.archivos[campo] = null
  const el = document.getElementById(`file-${campo}`)
  if (el) el.value = ''
}

// === Guardado ===
const addMolde = async () => {
  const nombre = (molde.value.nombre || '').trim()
  if (!nombre) {
    alert('Debes ingresar un nombre para el conjunto')
    return
  }
  if (!alMenosUnArchivo.value) {
    alert('Agrega al menos un archivo (o usa la carga por carpeta).')
    return
  }

  uploading.value = true
  progress.value   = 0
  try {
    await store.addOne({
      nombre,
      archivos: molde.value.archivos
    }, (evt) => {
      if (evt?.total) progress.value = (evt.loaded / evt.total) * 100
      else progress.value = null
    })
    limpiarCampos()
    autoMapMsg.value = ''
  } catch (e) {
    console.error('addMolde error:', e)
    const msg = e?.response?.data?.message || e.message || 'No se pudo guardar los moldes.'
    alert('❌ ' + msg)
  } finally {
    uploading.value = false
    progress.value = null
  }
}

const eliminarMolde = async (indexOrId) => {
  const item = store.items[indexOrId]
  if (!item) return
  if (!confirm(`¿Eliminar el conjunto "${item.nombre}"?`)) return
  try {
    await store.deleteOne(item.id)
  } catch (e) {
    console.error(e)
    alert('No se pudo eliminar. Revisa el servidor.')
  }
}

const limpiarCampos = () => {
  molde.value = {
    nombre: '',
    archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null]))
  }
  // Limpia inputs file del DOM
  camposArchivo.forEach(c => {
    const el = document.getElementById(`file-${c.key}`)
    if (el) el.value = ''
  })
}

onMounted(() => {
  store.fetchAll()
})
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
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
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
