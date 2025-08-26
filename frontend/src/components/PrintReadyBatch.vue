<template>
  <v-container fluid class="printready-container">
    <v-theme-provider theme="dark">
      <!-- Header -->
      <v-row justify="center" class="mb-6">
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-title-gradient">
              <h2 class="m-0 text-white text-h5">PDF listo para imprimir (Lote / Imposición)</h2>
            </div>
            <div class="p-4">
              <p class="m-0 text-gray-300">
                Mapea columnas del Excel, elige perfiles de imposición, sangrado y marcas, define nomenclatura y exporta ZIP o PDF único.
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Config principal -->
      <v-row justify="center" class="mb-6" dense>
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-cog-outline</v-icon>
              Archivo y mapeo
            </div>
            <div class="p-4 d-flex flex-column ga-4">
              <v-text-field
                v-model="fileName"
                label="Nombre base (sin extensión)"
                variant="outlined"
                density="comfortable"
                hide-details
                :rules="[v => !!v || 'Obligatorio']"
                class="product-field-dark"
              />
              <v-file-input
                v-model="excelFile"
                label="Subir Excel"
                accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                prepend-icon="mdi-file-excel"
                show-size
                variant="outlined"
                density="comfortable"
                hide-details
                class="product-field-dark"
              />

              <div v-if="headers.length" class="mt-2">
                <div class="mb-2 text-gray-300">Mapeo de columnas</div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                  <v-select
                    v-model="mapping.NOMBRE"
                    :items="headers"
                    label="Columna → NOMBRE"
                    variant="outlined" class="product-field-dark" hide-details
                  />
                  <v-select
                    v-model="mapping.NUMERO"
                    :items="headers"
                    label="Columna → NUMERO"
                    variant="outlined" class="product-field-dark" hide-details
                  />
                  <v-select
                    v-model="mapping.TALLE"
                    :items="headers"
                    label="Columna → TALLE"
                    variant="outlined" class="product-field-dark" hide-details
                  />
                  <v-select
                    v-model="mapping.CATEGORIA"
                    :items="['(ninguna)', ...headers]"
                    label="Columna → CATEGORIA (opcional)"
                    variant="outlined" class="product-field-dark" hide-details
                  />
                </div>
              </div>

              <!-- Preview -->
              <v-expand-transition>
                <div v-if="previewRows.length" class="mt-4">
                  <div class="card-subsubtitle mb-2">Vista previa (primeras {{ previewRows.length }} filas)</div>
                  <div class="table-wrapper">
                    <v-data-table
                      :headers="previewHeaders"
                      :items="previewRows"
                      density="comfortable"
                      class="table-dark elevation-8"
                    />
                  </div>
                </div>
              </v-expand-transition>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Imposición / perfiles -->
      <v-row justify="center" class="mb-6" dense>
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-grid</v-icon>
              Imposición y marcas
            </div>
            <div class="p-4 d-flex flex-column ga-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <v-select
                  v-model="presetKey"
                  :items="presetOptions"
                  label="Perfil de imposición"
                  variant="outlined" class="product-field-dark" hide-details
                  @update:modelValue="applyPreset"
                />
                <v-select
                  v-model="imposition.sheet"
                  :items="sheets"
                  label="Hoja"
                  variant="outlined" class="product-field-dark" hide-details
                />
                <v-select
                  v-model="imposition.fit"
                  :items="['contain','fill']"
                  label="Ajuste"
                  variant="outlined" class="product-field-dark" hide-details
                />
              </div>

              <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <v-text-field v-model.number="imposition.cols" type="number" min="1" label="Cols" variant="outlined" class="product-field-dark" hide-details />
                <v-text-field v-model.number="imposition.rows" type="number" min="1" label="Rows" variant="outlined" class="product-field-dark" hide-details />
                <v-text-field v-model.number="imposition.margin" type="number" min="0" label="Margen (mm)" variant="outlined" class="product-field-dark" hide-details />
                <v-text-field v-model.number="imposition.gap" type="number" min="0" label="Espacio (mm)" variant="outlined" class="product-field-dark" hide-details />
                <v-text-field v-model.number="imposition.bleed" type="number" min="0" label="Sangrado (mm)" variant="outlined" class="product-field-dark" hide-details />
                <v-switch v-model="imposition.rotate" inset label="Auto-rotar" color="primary" class="product-field-dark" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <v-switch v-model="imposition.cropMarks" inset label="Marcas de corte por pieza" color="primary" class="product-field-dark" />
                <v-switch v-model="imposition.pageCrop" inset label="Marcas de corte de página" color="primary" class="product-field-dark" />
                <v-switch v-model="outline" inset label="Convertir texto a curvas (GS)" color="primary" class="product-field-dark" />
              </div>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Salida / nomenclatura -->
      <v-row justify="center" class="mb-6" dense>
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-file-multiple</v-icon>
              Salida: ZIP o PDF único
            </div>
            <div class="p-4 d-flex flex-column ga-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <v-select
                  v-model="mergeMode"
                  :items="['zip','single']"
                  label="Modo de salida"
                  variant="outlined" class="product-field-dark" hide-details
                />
                <v-select
                  v-model="groupBy"
                  :items="['none','TALLE','CATEGORIA']"
                  label="Agrupar ZIP por…"
                  :disabled="mergeMode !== 'zip'"
                  variant="outlined" class="product-field-dark" hide-details
                />
                <v-text-field
                  v-model="namingTemplate"
                  label="Plantilla nombre archivo"
                  variant="outlined" class="product-field-dark" hide-details
                  :hint="hintTpl" persistent-hint
                />
              </div>

              <div class="flex flex-wrap gap-2">
                <v-btn color="secondary" class="btn" :loading="loading" :disabled="!canGenerate" @click="generateBatch('download')">
                  <v-icon start>mdi-file-export</v-icon> Generar y descargar
                </v-btn>
                <v-btn variant="tonal" class="btn" :disabled="!excelReady" @click="resetAll">
                  <v-icon start>mdi-refresh</v-icon> Limpiar
                </v-btn>
              </div>

              <small class="text-gray-300">
                Campos válidos en la plantilla: <code>{NOMBRE}</code>, <code>{NUMERO}</code>, <code>{TALLE}</code>, <code>{CATEGORIA}</code>, <code>{YYYY}</code>, <code>{MM}</code>, <code>{DD}</code>, <code>{IDX}</code>, <code>{IDX:n}</code>.
              </small>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-theme-provider>
  </v-container>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx'

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

/* ======= Estado ======= */
const fileName = ref('')
const excelFile = ref(null)
const headers = ref([])
const rows = ref([]) // array de objetos

const mapping = ref({ NOMBRE: '', NUMERO: '', TALLE: '', CATEGORIA: '(ninguna)' })

const mergeMode = ref('zip') // 'zip' | 'single'
const groupBy = ref('none')  // 'none' | 'TALLE' | 'CATEGORIA'
const namingTemplate = ref('{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}')
const outline = ref(false)

const imposition = ref({
  sheet: 'a4', cols: 2, rows: 3,
  margin: 10, gap: 4, bleed: 3,
  cropMarks: true, pageCrop: true,
  rotate: false, fit: 'contain'
})

/* ======= Perfiles ======= */
const presetKey = ref('A4_2x3')
const sheets = ['a4','letter','legal','tabloid','a3','a5']
const presets = {
  'A4_2x3': { sheet:'a4', cols:2, rows:3, margin:10, gap:4, bleed:3, cropMarks:true, pageCrop:true, rotate:false, fit:'contain' },
  'A4_3x4': { sheet:'a4', cols:3, rows:4, margin:8,  gap:3, bleed:2, cropMarks:true, pageCrop:true, rotate:true,  fit:'contain' },
  'Letter_2x2': { sheet:'letter', cols:2, rows:2, margin:12, gap:4, bleed:3, cropMarks:true, pageCrop:false, rotate:false, fit:'contain' },
}
const presetOptions = Object.keys(presets)
const applyPreset = () => { Object.assign(imposition.value, presets[presetKey.value]) }

/* ======= Preview ======= */
const previewHeaders = computed(() => ([
  { title: 'NOMBRE', key: 'NOMBRE' },
  { title: 'NUMERO', key: 'NUMERO' },
  { title: 'TALLE',  key: 'TALLE'  },
  { title: 'CATEGORIA', key: 'CATEGORIA' }
]))
const previewRows = computed(() => {
  const m = mapping.value
  if (!rows.value.length || !m.NOMBRE || !m.NUMERO || !m.TALLE) return []
  const take = rows.value.slice(0, 20)
  return take.map(r => ({
    NOMBRE: r[m.NOMBRE] ?? '',
    NUMERO: r[m.NUMERO] ?? '',
    TALLE:  r[m.TALLE]  ?? '',
    CATEGORIA: m.CATEGORIA && m.CATEGORIA !== '(ninguna)' ? (r[m.CATEGORIA] ?? '') : ''
  }))
})

/* ======= Flags ======= */
const excelReady = computed(() => !!excelFile?.value && headers.value.length > 0 && rows.value.length > 0)
const mappingReady = computed(() => !!mapping.value.NOMBRE && !!mapping.value.NUMERO && !!mapping.value.TALLE)
const canGenerate = computed(() => !!fileName.value && excelReady.value && mappingReady.value)

/* ======= Helpers ======= */
const hintTpl = computed(() => `Ej: ${fileName.value || 'pedido'} → "{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}.pdf"`)
const getFirstFile = v => (Array.isArray(v) ? v[0] : v) || null
const sanitizeBase = (name) => (name || '').replace(/\.[^.]+$/, '').replace(/[<>:"/\\|?*\x00-\x1F]/g, '').trim()

/* ======= Leer Excel ======= */
watch(excelFile, async (val) => {
  headers.value = []; rows.value = []
  const f = getFirstFile(val)
  if (!f) return
  if (!fileName.value) fileName.value = sanitizeBase(f.name)

  const buf = await f.arrayBuffer()
  const wb = XLSX.read(buf, { type: 'array' })
  const wsName = wb.SheetNames[0]
  const ws = wb.Sheets[wsName]
  const json = XLSX.utils.sheet_to_json(ws, { header: 1 })
  if (!json.length) return

  // encabezados
  const hdr = json[0].map(x => (typeof x === 'string' && x.trim() !== '' ? x.trim() : null))
  const seen = new Set(); const norm = []
  hdr.forEach((h, i) => {
    let key = h || `Col${i+1}`
    let base = key, k = 1
    while (seen.has(key)) { key = `${base}_${++k}` }
    seen.add(key); norm[i] = key
  })
  headers.value = norm

  // filas
  const body = json.slice(1).map(r => {
    const obj = {}
    norm.forEach((h, i) => { obj[h] = r[i] ?? '' })
    return obj
  }).filter(o => Object.values(o).some(v => v !== '' && v != null))
  rows.value = body

  // autoselect mapping si matchea por nombre
  const auto = (name) => headers.value.find(h => h.toLowerCase() === name.toLowerCase())
  mapping.value.NOMBRE = auto('Nombre') || mapping.value.NOMBRE
  mapping.value.NUMERO = auto('Numero') || mapping.value.NUMERO
  mapping.value.TALLE  = auto('Talle')  || mapping.value.TALLE
  mapping.value.CATEGORIA = auto('Categoria') || mapping.value.CATEGORIA
}, { immediate: false })

/* ======= Generar ======= */
async function generateBatch(mode = 'download') {
  if (!canGenerate.value) return alert('Completa nombre, Excel y mapeo obligatorio.')

  const form = new FormData()
  const base = sanitizeBase(fileName.value) || 'documento'
  form.append('excelFile', getFirstFile(excelFile.value))
  form.append('fileName', base)

  const map = {
    NOMBRE: mapping.value.NOMBRE,
    NUMERO: mapping.value.NUMERO,
    TALLE:  mapping.value.TALLE,
  }
  if (mapping.value.CATEGORIA && mapping.value.CATEGORIA !== '(ninguna)') map.CATEGORIA = mapping.value.CATEGORIA
  form.append('mapping', JSON.stringify(map))

  const impos = { ...imposition.value, mergeMode: mergeMode.value }
  form.append('imposition', JSON.stringify(impos))
  form.append('namingTemplate', namingTemplate.value || '{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}')
  form.append('groupBy', groupBy.value || 'none')
  form.append('outline', outline.value ? '1' : '0')
  form.append('download', mode === 'download' ? '1' : '0')

  loading.value = true
  try {
    const url = `${API}/print/generate-batch`
    const res = await axios.post(url, form, { responseType: 'blob' })

    // Nombre final según modo
    const isZip = (mergeMode.value === 'zip')
    const ext = isZip ? 'zip' : 'pdf'
    const outName = `${base}.${ext}`

    const blob = new Blob([res.data], { type: isZip ? 'application/zip' : 'application/pdf' })
    const href = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = href; a.download = outName
    document.body.appendChild(a); a.click(); a.remove()
    URL.revokeObjectURL(href)
  } catch (e) {
    console.error('generateBatch', e)
    alert(e?.response?.data?.message || e.message || 'Error al generar.')
  } finally {
    loading.value = false
  }
}

function resetAll() {
  fileName.value = ''
  excelFile.value = null
  headers.value = []; rows.value = []
  mapping.value = { NOMBRE:'', NUMERO:'', TALLE:'', CATEGORIA:'(ninguna)' }
  presetKey.value = 'A4_2x3'; applyPreset()
  mergeMode.value = 'zip'; groupBy.value = 'none'
  namingTemplate.value = '{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}'
  outline.value = false
}

/* ======= UI ======= */
const loading = ref(false)

</script>

<style scoped>
.printready-container {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  min-height: 100vh;
  padding: 40px 16px;
}
.card-dark {
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}
.card-title-gradient {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: #fff;
  padding: 18px 24px;
  font-weight: 800;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}
.card-subtitle {
  display: flex; align-items: center; gap: 8px;
  padding: 14px 18px; font-weight: 700; color: #fff;
  background: rgba(255,255,255,0.06);
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.card-subsubtitle { font-weight: 700; color: #fff; margin-bottom: 8px; }
.table-wrapper { padding: 10px 12px 16px; }

/* Campos oscuros Vuetify */
.product-field-dark:deep(.v-field) {
  background-color: #3e3e57 !important;
  color: #fff !important;
  border-radius: 10px !important;
  border: 1px solid #4f4f72 !important;
}
.product-field-dark:deep(.v-label),
.product-field-dark:deep(.v-field__input) { color: #e5e7eb !important; }
.btn { font-weight: 800; border-radius: 10px; text-transform: none; }
.table-dark :deep(.v-table) { background-color: #2c2c3e !important; color: #e5e7eb !important; }
.table-dark :deep(thead) { background-color: #3e3e57 !important; color: #fff !important; }
.table-dark :deep(th), .table-dark :deep(td) { border-bottom: 1px solid rgba(255,255,255,0.08) !important; }
</style>
