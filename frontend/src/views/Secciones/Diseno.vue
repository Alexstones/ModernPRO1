<!-- src/views/Secciones/Diseno.vue -->
<template>
  <v-container class="py-6">
    <!-- Encabezado -->
    <div class="tab-title mb-6">
      <div class="tab-title__bar">
        <span class="tab-title__text">PESTAÑA DISEÑO</span>
      </div>
      <p class="tab-title__hint">
        Texto con contorno, selección de colores, galería de imágenes y subida de Excel/PDF.
      </p>
    </div>

    <!-- ====== CARD PRINCIPAL ====== -->
    <v-card class="section-card">
      <div class="section-card__header d-flex align-center justify-space-between">
        <span>Texto, contorno, colores y archivos</span>

        <div class="d-flex ga-2">
          <!-- Generar PDF (descarga directa por JSON/base64) -->
          <v-btn
            color="primary"
            class="text-none"
            :loading="loadingPdf"
            @click="generarPDF"
          >
            Generar PDF
          </v-btn>

          <!-- Subir Excel o PDF / más imágenes y generar moldería en el servidor -->
          <v-btn
            color="success"
            class="text-none"
            :loading="loadingMol"
            @click="subirYGenerarMolderia"
          >
            Subir y generar moldería
          </v-btn>

          <!-- Enlace de descarga cuando el backend devuelve URL -->
          <v-btn
            v-if="molderiaUrl"
            :href="molderiaUrl"
            target="_blank"
            class="text-none"
            variant="outlined"
            prepend-icon="mdi-download"
          >
            Descargar Moldería
          </v-btn>
        </div>
      </div>

      <v-card-text class="section-card__body">
        <!-- ====== ARCHIVO ORIGEN (EXCEL o PDF) ====== -->
        <v-card class="inner-card mb-6">
          <div class="inner-card__header">Archivo origen (Excel o PDF)</div>
          <div class="pa-4">
            <v-file-input
              v-model="sourceFile"
              label="Subir .xlsx, .xls o .pdf"
              accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf,.pdf"
              prepend-icon="mdi-file-upload"
              variant="outlined"
              density="comfortable"
              class="input-dark"
              show-size
              :rules="fileRules"
            />
            <div class="text-caption mt-2" style="color:#94a3b8">
              Si subes un <b>PDF</b>, se guarda tal cual. Si subes un <b>Excel</b>, se convierte a PDF con una tabla por hoja.
            </div>
          </div>
        </v-card>

        <!-- ====== TEXTO Y ESTILO ====== -->
        <v-card class="inner-card mb-6">
          <div class="inner-card__header">Texto y estilo</div>

          <div class="pa-4">
            <v-row dense>
              <v-col cols="12" md="8">
                <v-text-field
                  v-model="state.text"
                  label="Texto"
                  variant="outlined"
                  density="comfortable"
                  class="input-dark"
                  placeholder="Escribe aquí…"
                />
              </v-col>

              <v-col cols="6" md="2">
                <v-text-field
                  v-model.number="state.fontSize"
                  type="number"
                  min="6"
                  label="Tamaño"
                  variant="outlined"
                  density="comfortable"
                  class="input-dark"
                />
              </v-col>

              <v-col cols="6" md="2">
                <v-select
                  v-model="state.fontFamily"
                  :items="fonts"
                  label="Fuente"
                  variant="outlined"
                  density="comfortable"
                  class="input-dark"
                />
              </v-col>

              <v-col cols="12" md="6">
                <div class="label">Color de fuente</div>
                <div class="d-flex align-center ga-3">
                  <input type="color" v-model="state.fillColor" class="colorbox" />
                  <v-text-field
                    v-model="state.fillColor"
                    label="Hex"
                    variant="outlined"
                    density="comfortable"
                    class="input-dark flex-1"
                  />
                </div>
              </v-col>

              <v-col cols="12" md="6">
                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center ga-2">
                    <v-switch
                      v-model="state.stroke.enabled"
                      inset
                      color="primary"
                      hide-details
                      class="me-2"
                    />
                    <span class="label">Aplicar trazo</span>
                  </div>
                  <v-text-field
                    v-model.number="state.stroke.width"
                    type="number"
                    min="0"
                    step="0.5"
                    label="Grosor"
                    variant="outlined"
                    density="comfortable"
                    class="input-dark w-25"
                  />
                </div>

                <div class="label mt-2">Color contorno</div>
                <div class="d-flex align-center ga-3">
                  <input type="color" v-model="state.stroke.color" class="colorbox" />
                  <v-text-field
                    v-model="state.stroke.color"
                    label="Hex"
                    variant="outlined"
                    density="comfortable"
                    class="input-dark flex-1"
                  />
                </div>
              </v-col>

              <v-col cols="12">
                <div class="preview">
                  <div class="preview__area">
                    <div :style="previewStyle" class="preview__text">
                      {{ state.text || 'Vista previa de texto' }}
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>
        </v-card>

        <!-- ====== PALETA ====== -->
        <v-card class="inner-card mb-6">
          <div class="inner-card__header d-flex align-center justify-space-between">
            <span>Paleta de colores</span>
            <div class="d-flex ga-2">
              <v-btn size="small" variant="tonal" class="text-none" @click="guardarColor(state.fillColor)">
                + Guardar color de fuente
              </v-btn>
              <v-btn size="small" variant="tonal" class="text-none" @click="guardarColor(state.stroke.color)">
                + Guardar color de contorno
              </v-btn>
            </div>
          </div>

          <div class="pa-4 d-flex flex-wrap ga-2">
            <button
              v-for="(c, i) in palette"
              :key="i"
              class="swatch"
              :style="{ backgroundColor: c }"
              :title="c"
              @click="applyFromPalette(c)"
            />
            <v-spacer />
            <v-btn
              size="small"
              variant="outlined"
              color="error"
              class="text-none"
              @click="palette = []"
            >
              Limpiar paleta
            </v-btn>
          </div>
        </v-card>

        <!-- ====== IMÁGENES (opcionales) ====== -->
        <v-card class="inner-card">
          <div class="inner-card__header d-flex align-center justify-space-between">
            <span>Imágenes cargadas</span>

            <v-btn size="small" variant="tonal" class="text-none">
              <label class="cursor-pointer">
                + Agregar imágenes
                <input type="file" class="d-none" multiple accept="image/*" @change="onFiles" />
              </label>
            </v-btn>
          </div>

          <div class="pa-4">
            <v-row dense class="max-h-520 pr-2 overflow-y-auto">
              <template v-if="images.length">
                <v-col
                  v-for="(img, idx) in images"
                  :key="img.id"
                  cols="12"
                  md="6"
                  lg="4"
                >
                  <div class="image-item">
                    <img :src="img.url" class="image-thumb" />
                    <div class="image-meta">
                      <div class="image-name" :title="img.name">{{ img.name }}</div>
                      <div class="image-size">{{ img.width }}×{{ img.height }}px</div>
                      <div class="d-flex ga-2 mt-2">
                        <v-btn size="x-small" variant="outlined" class="text-none" @click="selectImage(img)">Usar</v-btn>
                        <v-btn size="x-small" variant="outlined" color="error" class="text-none" @click="removeImage(idx)">Eliminar</v-btn>
                      </div>
                    </div>
                  </div>
                </v-col>
              </template>

              <v-col cols="12" v-else>
                <div class="empty">No hay imágenes. Usa “Agregar imágenes”.</div>
              </v-col>
            </v-row>
          </div>
        </v-card>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'

/* ====== Estado ====== */
const fonts = ['Inter', 'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New', 'Impact']

const state = reactive({
  text: '',
  fontSize: 32,
  fontFamily: 'Inter',
  fillColor: '#111827',
  stroke: { enabled: true, width: 2, color: '#ffffff' }
})

const palette = ref(['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b'])
const images  = ref([]) // { id, url(blob), file(File), name, width, height }
const sourceFile = ref(null) // Excel o PDF

/* Botones */
const loadingPdf = ref(false)
const loadingMol = ref(false)
const molderiaUrl = ref(null)

const fileRules = [
  v => !v || (Array.isArray(v) ? v.length > 0 : true) || 'Selecciona un archivo',
  v => {
    if (!v) return true
    const f = Array.isArray(v) ? v[0] : v
    const name = (f?.name || '').toLowerCase()
    return /\.(xlsx|xls|pdf)$/.test(name) || 'Debe ser .xlsx, .xls o .pdf'
  }
]

/* Preview con contorno */
const previewStyle = computed(() => {
  const css = {
    fontFamily: state.fontFamily,
    fontSize: `${state.fontSize}px`,
    color: state.fillColor,
    lineHeight: '1.15',
    fontWeight: '700',
    wordBreak: 'break-word'
  }
  if (state.stroke.enabled) {
    const s = state.stroke.width || 0
    const c = state.stroke.color || '#000'
    css['-webkit-text-stroke'] = `${s}px ${c}`
    css['textShadow'] =
      `-${s}px 0 ${c}, ${s}px 0 ${c}, 0 ${s}px ${c}, 0 -${s}px ${c},
       -${s}px -${s}px ${c}, ${s}px -${s}px ${c}, -${s}px ${s}px ${c}, ${s}px ${s}px ${c}`
  }
  return css
})

/* Paleta */
function guardarColor(c) { if (c && !palette.value.includes(c)) palette.value.push(c) }
function applyFromPalette(c) { state.fillColor = c }

/* Imágenes locales */
function onFiles(e) {
  const input = e.target
  if (!input.files) return
  Array.from(input.files).forEach(file => {
    const url = URL.createObjectURL(file)
    const img = new Image()
    img.onload = () => {
      images.value.unshift({
        id: crypto.randomUUID(),
        url, file, name: file.name, width: img.width, height: img.height
      })
    }
    img.src = url
  })
  input.value = ''
}
function removeImage(idx) { images.value.splice(idx, 1) }
function selectImage(img) { console.log('Seleccionada:', img) }

/* Util para JSON/base64 (sólo para Generar PDF) */
function fileToDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}
async function buildPayloadForJson() {
  const imagesPayload = []
  for (const img of images.value) {
    const dataUrl = img.file ? await fileToDataUrl(img.file) : null
    imagesPayload.push({ name: img.name, width: img.width, height: img.height, dataUrl })
  }
  return {
    text: state.text,
    fontFamily: state.fontFamily,
    fontSize: state.fontSize,
    fillColor: state.fillColor,
    stroke: state.stroke,
    images: imagesPayload
  }
}

/* Generar PDF (descarga directa) */
async function generarPDF() {
  loadingPdf.value = true
  try {
    const API = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
    const payload = await buildPayloadForJson()
    const res = await fetch(`${API}/api/diseno/pdf`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/pdf' },
      body: JSON.stringify({ ...payload, page: { width_mm: 900, height_mm: 600 } })
    })

    const ct = res.headers.get('content-type') || ''
    if (!res.ok || !ct.includes('application/pdf')) {
      const body = await res.text().catch(() => '')
      throw new Error(`Falló PDF (HTTP ${res.status}). ${body || 'El servidor no devolvió PDF.'}`)
    }

    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `diseno-${Date.now()}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch (e) {
    console.error(e)
    alert(e.message || 'Error generando el PDF')
  } finally { loadingPdf.value = false }
}

/* Subir Excel o PDF (y opcionalmente imágenes) y generar en servidor */
async function subirYGenerarMolderia () {
  loadingMol.value = true
  try {
    const API = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
    const fd = new FormData()

    // meta
    fd.append('text', state.text || '')
    fd.append('fontFamily', state.fontFamily || 'Inter')
    fd.append('fontSize', String(state.fontSize || 32))
    fd.append('fillColor', state.fillColor || '#111827')
    fd.append('stroke[enabled]', state.stroke.enabled ? '1' : '0')
    fd.append('stroke[width]', String(state.stroke.width ?? 2))
    fd.append('stroke[color]', state.stroke.color || '#000000')
    fd.append('page[width_mm]', '900')
    fd.append('page[height_mm]', '600')
    fd.append('thumbs[width_mm]', '80')
    fd.append('thumbs[gap_mm]', '4')

    // archivo origen (Excel o PDF)
    const f = Array.isArray(sourceFile.value) ? sourceFile.value[0] : sourceFile.value
    if (f) fd.append('sourceFile', f, f.name)

    // imágenes adicionales
    images.value.forEach(img => { if (img.file) fd.append('images[]', img.file, img.name) })

    const res = await fetch(`${API}/api/diseno/molderia/upload`, { method: 'POST', body: fd })
    const json = await res.json().catch(() => null)
    if (!res.ok || !json?.url) throw new Error(`Error (${res.status}). ${json?.message || 'Respuesta inválida'}`)

    molderiaUrl.value = json.url

    // descarga automática
    const a = document.createElement('a')
    a.href = json.url
    a.download = json.filename || `molderia-${Date.now()}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
  } catch (e) {
    console.error(e)
    alert(e.message || 'Error generando/subiendo la moldería')
  } finally {
    loadingMol.value = false
  }
}
</script>

<style scoped>
/* ====== Encabezado ====== */
.tab-title__bar{
  background: linear-gradient(90deg, #ff7a18, #ffb86b);
  color:#0b1020;
  border-radius: 12px 12px 0 0;
  padding: 16px 24px;
  box-shadow: inset 0 -6px 0 rgba(0,0,0,.15);
}
.tab-title__text{ font-weight: 800; letter-spacing:.5px; }
.tab-title__hint{ margin:0; padding:10px 16px; color:#cbd5e1; }

/* ====== Card contenedor ====== */
.section-card{
  background: radial-gradient(80% 120% at 10% 0%, #0b1b2e 0%, #0b1324 40%, #0b1020 100%);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  overflow: hidden;
}
.section-card__header{
  padding: 12px 20px;
  background: linear-gradient(90deg, rgba(255,255,255,.06), transparent);
  color:#e5e7eb;
  font-weight: 700;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.section-card__body{ padding: 16px; }

/* ====== Subcards ====== */
.inner-card{
  background: #0f172a;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 14px;
}
.inner-card__header{
  padding: 12px 16px;
  font-weight: 700;
  color:#f8fafc;
  background: linear-gradient(90deg, rgba(255,255,255,.05), transparent);
  border-bottom: 1px solid rgba(255,255,255,.06);
}

/* Inputs oscuros */
.input-dark :deep(.v-field) { background: #0b1220 !important; border-radius: 10px; }
.input-dark :deep(.v-field__field) { color:#e5e7eb; }
.input-dark :deep(.v-label), .label{ color:#a5b4fc; font-size:.85rem; }

/* Color picker */
.colorbox{ width: 48px; height: 36px; border-radius: 8px; border:1px solid rgba(255,255,255,.12); background: transparent; padding:2px; }

/* Preview */
.preview{ border-top: 1px dashed rgba(255,255,255,.15); margin-top: 8px; padding-top: 16px; }
.preview__area{
  border: 1px dashed rgba(255,255,255,.15);
  border-radius: 12px;
  background:
    linear-gradient(45deg, rgba(255,255,255,.06) 25%, transparent 25%),
    linear-gradient(-45deg, rgba(255,255,255,.06) 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, rgba(255,255,255,.06) 75%),
    linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.06) 75%);
  background-size: 20px 20px;
  background-position: 0 0, 0 10px, 10px -10px, -10px 0;
  padding: 24px; min-height: 120px;
}
.preview__text{ color:#e5e7eb; }

/* Imágenes */
.max-h-520{ max-height:520px; }
.image-item{ display:flex; gap:12px; padding:10px; border:1px solid rgba(255,255,255,.08); border-radius:12px; background:#0b1220; }
.image-thumb{ width:86px; height:110px; object-fit:cover; border-radius:10px; border:1px solid rgba(255,255,255,.08); }
.image-meta{ min-width:0; color:#e5e7eb; }
.image-name{ font-size:.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.image-size{ font-size:.72rem; color:#94a3b8; }
.empty{ text-align:center; color:#94a3b8; padding:24px 0; }

/* Swatches */
.swatch{ width:36px; height:36px; border-radius:10px; border:1px solid rgba(255,255,255,.14); }

/* Util */
.text-none { text-transform: none !important; }
</style>

