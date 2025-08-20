<template>
  <v-container class="design-container-colorful" fluid>
    <v-row justify="center">
      <v-col cols="12" md="10">
        <v-card class="design-card-colorful">
          <v-card-title class="design-title-colorful">
            <v-icon size="40" class="mr-3 text-white">mdi-shape-plus</v-icon>
            Diseño de Moldería — Carga y Metadatos
          </v-card-title>

          <v-card-text>
            <v-form ref="formRef" @submit.prevent="onSubmit">
              <!-- Datos generales de la colección -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.coleccion"
                    label="Nombre de la colección / proyecto"
                    variant="outlined"
                    class="mb-4 design-text-field"
                    :rules="[v => !!v || 'Requerido']"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="form.prenda"
                    :items="prendas"
                    label="Tipo de prenda"
                    variant="outlined"
                    class="mb-4 design-text-field"
                    :rules="[v => !!v || 'Selecciona una prenda']"
                    prepend-inner-icon="mdi-tshirt-crew"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="form.genero"
                    :items="generos"
                    label="Género"
                    variant="outlined"
                    class="mb-4 design-text-field"
                    prepend-inner-icon="mdi-account"
                  />
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" md="6">
                  <v-combobox
                    v-model="form.tallasColeccion"
                    :items="tallasDisponibles"
                    label="Tallas involucradas (puedes escribir nuevas)"
                    multiple chips clearable
                    variant="outlined"
                    class="mb-4 design-text-field"
                    prepend-inner-icon="mdi-format-size"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.notaGeneral"
                    label="Nota / comentario general (opcional)"
                    variant="outlined"
                    class="mb-4 design-text-field"
                    prepend-inner-icon="mdi-note-text"
                  />
                </v-col>
              </v-row>

              <!-- Carga de archivos de moldería -->
              <v-file-input
                v-model="selectedFiles"
                label="Subir archivos de moldería (SVG/PNG/JPG/PDF)"
                accept=".svg,.png,.jpg,.jpeg,.pdf"
                multiple
                show-size
                counter
                prepend-icon="mdi-upload"
                variant="outlined"
                class="mb-6 design-text-field"
                :rules="uploadRules"
                @change="onFilesChange"
              />

              <!-- Vista de elementos cargados -->
              <v-alert v-if="items.length === 0" type="info" variant="tonal" class="mb-4">
                Aún no hay piezas cargadas. Selecciona uno o más archivos arriba para ver la vista previa.
              </v-alert>

              <v-card v-else flat class="pa-3 rounded-lg mb-6" style="background:#fff">
                <v-row class="mb-2" dense>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="filtro"
                      label="Buscar en piezas"
                      variant="outlined"
                      density="compact"
                      prepend-inner-icon="mdi-magnify"
                      hide-details
                    />
                  </v-col>
                  <v-col cols="12" md="6" class="d-flex align-center justify-end">
                    <div class="mr-3 text-caption">Total piezas: <strong>{{ filteredItems.length }}</strong></div>
                    <v-btn size="small" variant="tonal" prepend-icon="mdi-broom" @click="limpiarMetadatos">Limpiar metadatos</v-btn>
                  </v-col>
                </v-row>

                <v-data-table
                  :headers="headers"
                  :items="filteredItems"
                  item-key="id"
                  density="comfortable"
                  fixed-header
                  height="520"
                  class="elevation-1"
                >
                  <template #item.preview="{ item }">
                    <div class="d-flex align-center" style="gap:8px;">
                      <template v-if="item.tipo === 'image'">
                        <v-img :src="item.url" width="56" height="56" cover class="rounded" />
                      </template>
                      <template v-else>
                        <v-icon size="40">mdi-file-pdf-box</v-icon>
                      </template>
                      <div class="text-truncate" style="max-width:240px">{{ item.nombreOriginal }}</div>
                    </div>
                  </template>

                  <template #item.nombrePieza="{ item }">
                    <v-text-field v-model="item.nombrePieza" variant="outlined" density="compact" hide-details />
                  </template>

                  <template #item.lado="{ item }">
                    <v-select v-model="item.lado" :items="lados" variant="outlined" density="compact" hide-details />
                  </template>

                  <template #item.talla="{ item }">
                    <v-combobox v-model="item.talla" :items="tallasDisponibles" variant="outlined" density="compact" hide-details />
                  </template>

                  <template #item.cantidad="{ item }">
                    <v-text-field v-model.number="item.cantidad" type="number" min="1" variant="outlined" density="compact" hide-details />
                  </template>

                  <template #item.notas="{ item }">
                    <v-text-field v-model="item.notas" variant="outlined" density="compact" hide-details />
                  </template>

                  <template #item.size="{ item }">
                    {{ (item.size / 1024).toFixed(1) }} KB
                  </template>

                  <template #item.acciones="{ item }">
                    <v-btn icon="mdi-arrow-up" size="small" variant="text" @click="mover(item.id, -1)" />
                    <v-btn icon="mdi-arrow-down" size="small" variant="text" @click="mover(item.id, +1)" />
                    <v-btn icon="mdi-delete" size="small" color="error" variant="text" @click="eliminar(item.id)" />
                  </template>
                </v-data-table>
              </v-card>

              <!-- Acciones -->
              <v-card-actions class="d-flex justify-space-between">
                <v-btn color="secondary" variant="elevated" size="large" prepend-icon="mdi-refresh" @click="resetForm">
                  Restablecer
                </v-btn>
                <v-btn type="submit" color="primary" variant="elevated" size="large" prepend-icon="mdi-content-save" :loading="submitting">
                  Guardar moldería
                </v-btn>
              </v-card-actions>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Overlay durante el guardado -->
    <v-overlay :model-value="uploading" class="d-flex align-center justify-center" persistent>
      <v-card class="pa-6" style="min-width:320px;">
        <div class="text-h6 mb-2">Subiendo moldería…</div>
        <div class="text-caption mb-4">No cierres esta ventana.</div>
        <v-progress-linear :model-value="uploadProgress" :indeterminate="uploadProgress === null" />
        <div class="text-caption mt-2" v-if="uploadProgress !== null">
          Progreso: {{ Math.round(uploadProgress) }}%
        </div>
      </v-card>
    </v-overlay>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import axios from 'axios'

// === Estado principal ===
const formRef = ref(null)
const submitting = ref(false)
const uploading = ref(false)
const uploadProgress = ref(null)

const form = reactive({
  coleccion: '',
  prenda: '',
  genero: 'Unisex',
  tallasColeccion: [],
  notaGeneral: ''
})

const prendas = ['Camiseta', 'Short', 'Sudadera', 'Pantalón', 'Chaqueta', 'Accesorio']
const generos = ['Unisex', 'Hombre', 'Mujer']
// Si ya tienes un Pinia store de tallas, puedes sustituir por tu store.
const tallasDisponibles = ref(['XS', 'S', 'M', 'L', 'XL', 'XXL'])
const lados = ['Centro', 'Izquierda', 'Derecha', 'Espalda']

// Archivos seleccionados y filas normalizadas
const selectedFiles = ref([])
const items = ref([])
const filtro = ref('')

const headers = [
  { title: 'Archivo', key: 'preview', sortable: false },
  { title: 'Nombre de pieza', key: 'nombrePieza' },
  { title: 'Lado', key: 'lado' },
  { title: 'Talla', key: 'talla' },
  { title: 'Cantidad', key: 'cantidad' },
  { title: 'Notas', key: 'notas' },
  { title: 'Tamaño', key: 'size', width: 120 },
  { title: 'Acciones', key: 'acciones', sortable: false, width: 140 }
]

const uploadRules = [
  v => (v && v.length > 0) || 'Debes subir al menos un archivo',
  v => {
    const list = Array.isArray(v) ? v : [v]
    const ok = list.every(f => /\.(svg|png|jpe?g|pdf)$/i.test(f.name || ''))
    return ok || 'Formatos permitidos: SVG, PNG, JPG, PDF'
  }
]

const filteredItems = computed(() => {
  const q = (filtro.value || '').trim().toLowerCase()
  if (!q) return items.value
  return items.value.filter(it =>
    [it.nombreOriginal, it.nombrePieza, it.lado, it.talla, it.notas]
      .some(v => String(v ?? '').toLowerCase().includes(q))
  )
})

function guessFromName(name) {
  const base = name.replace(/\.[^.]+$/, '')
  const lower = base.toLowerCase()
  const lado = /izq|left/.test(lower) ? 'Izquierda' : /der|right/.test(lower) ? 'Derecha' : /espalda|back/.test(lower) ? 'Espalda' : 'Centro'
  const tallaMatch = lower.match(/(?:^|_|-)(xxl|xl|xs|s|m|l)(?:$|_|-)/)
  const talla = tallaMatch ? tallaMatch[1].toUpperCase() : ''
  return { nombrePieza: base.replace(/[_-]/g, ' '), lado, talla }
}

function isImage(file) {
  const n = file.name?.toLowerCase() || ''
  return /\.(svg|png|jpe?g)$/.test(n)
}

function onFilesChange() {
  const list = Array.isArray(selectedFiles.value) ? selectedFiles.value : [selectedFiles.value]
  const normalized = list.filter(Boolean).map((file, idx) => {
    const { nombrePieza, lado, talla } = guessFromName(file.name)
    return {
      id: `${Date.now()}_${idx}_${Math.random().toString(36).slice(2, 7)}`,
      file,
      url: isImage(file) ? URL.createObjectURL(file) : null,
      tipo: isImage(file) ? 'image' : 'pdf',
      nombreOriginal: file.name,
      nombrePieza,
      lado,
      talla,
      cantidad: 1,
      notas: '',
      size: file.size
    }
  })
  // Evitar duplicados por nombre exacto
  const existingNames = new Set(items.value.map(i => i.nombreOriginal))
  const toAdd = normalized.filter(i => !existingNames.has(i.nombreOriginal))
  items.value = items.value.concat(toAdd)
}

function limpiarMetadatos() {
  items.value.forEach(i => { i.lado = 'Centro'; i.talla = ''; i.cantidad = 1; i.notas = '' })
}

function eliminar(id) {
  const idx = items.value.findIndex(i => i.id === id)
  if (idx !== -1) {
    try { if (items.value[idx].url) URL.revokeObjectURL(items.value[idx].url) } catch {}
    items.value.splice(idx, 1)
  }
}

function mover(id, dir) {
  const i = items.value.findIndex(x => x.id === id)
  const j = i + dir
  if (i < 0 || j < 0 || j >= items.value.length) return
  const swap = items.value[j]
  items.value[j] = items.value[i]
  items.value[i] = swap
}

function resetForm() {
  form.coleccion = ''
  form.prenda = ''
  form.genero = 'Unisex'
  form.tallasColeccion = []
  form.notaGeneral = ''
  selectedFiles.value = []
  items.value.forEach(i => { try { if (i.url) URL.revokeObjectURL(i.url) } catch {} })
  items.value = []
}

async function onSubmit() {
  const result = await formRef.value?.validate?.()
  if (!result?.valid) {
    alert('Por favor completa los campos requeridos.')
    return
  }
  if (items.value.length === 0) {
    alert('Debes cargar al menos una pieza de moldería.')
    return
  }

  submitting.value = true
  uploading.value = true
  uploadProgress.value = 0

  try {
    const meta = items.value.map((i, order) => ({
      nombreOriginal: i.nombreOriginal,
      nombrePieza: i.nombrePieza,
      prenda: form.prenda,
      genero: form.genero,
      lado: i.lado,
      talla: i.talla,
      cantidad: i.cantidad,
      notas: i.notas,
      order
    }))

    const fd = new FormData()
    fd.append('coleccion', form.coleccion)
    fd.append('notaGeneral', form.notaGeneral)
    fd.append('tallasColeccion', JSON.stringify(form.tallasColeccion))
    fd.append('meta', JSON.stringify(meta))

    items.value.forEach(i => fd.append('files[]', i.file, i.nombreOriginal))

    // Cambia esta URL si tu API es distinta (p.ej., '/api/moldes')
    const API_URL = 'http://127.0.0.1:8000/api/moldes'

    const res = await axios.post(API_URL, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (evt) => {
        if (evt && evt.total) uploadProgress.value = (evt.loaded / evt.total) * 100
        else uploadProgress.value = null
      }
    })

    alert('✅ Moldería guardada correctamente.')
    // Si el backend responde con errores de pieza, podrías resaltarlas aquí.
    resetForm()
  } catch (err) {
    console.error('Error subiendo moldería:', err)
    const status = err?.response?.status
    if (status === 422) alert('❌ Error de validación. Revisa los campos y formatos.')
    else if (status === 413) alert('❌ Archivos demasiado grandes. Ajusta límites del servidor (post_max_size/upload_max_filesize).')
    else if (status >= 500) alert('❌ Error interno del servidor. Revisa logs en Laravel.')
    else alert('❌ Error al subir. Verifica la conexión o la URL del API.')
  } finally {
    submitting.value = false
    uploading.value = false
    uploadProgress.value = null
  }
}
</script>

<style scoped>
.design-container-colorful {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  padding: 64px 16px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.design-card-colorful {
  border-radius: 16px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}
.design-title-colorful {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: white;
  padding: 20px 24px;
  font-size: 1.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
}
.design-text-field:deep(.v-field__input) { color: #000 !important; font-weight: 500; }
.design-text-field:deep(.v-field__label) { color: #555 !important; opacity: 1 !important; }
.design-text-field:deep(.v-field__outline) { border-color: #ddd; border-width: 2px; border-radius: 8px; }
.design-text-field:deep(.v-field--variant-outlined) { background-color: white; }
.design-text-field:deep(.v-messages__message) { color: #e53935 !important; font-size: 0.8rem; font-weight: 600; margin-top: 4px; }
.v-btn { font-weight: 700; border-radius: 10px; text-transform: none; letter-spacing: 0.5px; transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
.v-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); }
</style>

<!--
INTEGRACIÓN RÁPIDA:
- Coloca este componente en tu vista de administración o dentro de una pestaña existente.
- Si usas <v-tabs>, agrega: <v-tab value="diseno">Diseño</v-tab> y muestra <MolderiaDesign v-if="currentTab==='diseno'" />.
- Backend esperado (Laravel):
  POST /api/moldes  multipart/form-data
    - coleccion: string
    - notaGeneral: string
    - tallasColeccion: JSON string (array)
    - meta: JSON string (array de objetos por pieza)
    - files[]: archivos reales (orden corresponde al meta.order/ nombreOriginal)
  Devuelve 200/201 si ok.
-->
