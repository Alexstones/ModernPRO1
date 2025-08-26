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
                <input v-model="molde.nombre" placeholder="Ej. Set CAMISETA 701" class="product-input w-full" />
              </div>

              <!-- Talla para auto-mapeo -->
              <div>
                <label class="block mb-2 text-lg font-medium text-white">Talla</label>
                <input v-model="tallaAuto" inputmode="numeric" placeholder="8" class="product-input w-full" />
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

            <!-- Campos de archivo (edición manual) + Previews -->
            <div class="grid grid-cols-1 gap-5">
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

                <!-- Preview con watermark si es imagen -->
                <div v-if="formPreviewUrl(campo.key)" class="form-thumb">
                  <div class="thumb-base" :style="bgFrom(formPreviewUrl(campo.key))"></div>
                  <div v-if="wm.mostrar" class="thumb-sello" :style="thumbBoxStyle">
                    <span class="thumb-sello__text" :style="thumbTextStyle">{{ tallaAutoDisplay }}</span>
                  </div>
                </div>

                <!-- Exportar del form por pieza -->
                <div class="flex flex-wrap gap-2" v-if="formPreviewUrl(campo.key)">
                  <button class="product-btn-secondary px-3 py-2" @click="exportFormPiece(campo.key, 'png')">PNG</button>
                  <button class="product-btn-secondary px-3 py-2" @click="exportFormPiece(campo.key, 'pdf')">PDF</button>
                </div>

                <p class="text-sm text-gray-400 m-0">
                  {{ molde.archivos[campo.key] ? '' : 'No se eligió ningún archivo' }}
                </p>

                <hr class="sep" />
              </div>
            </div>

            <!-- Progreso de subida -->
            <div v-if="uploading" class="mt-6 text-sm text-gray-200">
              Subiendo…
              <div class="w-full bg-gray-700 rounded h-2 mt-2 overflow-hidden">
                <div class="bg-green-500 h-2 transition-all" :style="{ width: (progress === null ? '100%' : Math.round(progress) + '%') }"></div>
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

        <!-- Card: Controles de Marca de Agua -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark">
            <h2 class="text-h5 text-white m-0">Marca de Agua</h2>
          </div>

          <div class="p-6 space-y-6">
            <!-- Paleta -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <small class="block mb-1 text-gray-300">Paleta</small>
                <select v-model.number="colors.presetIndex" class="product-input w-full" @change="applyPreset">
                  <option v-for="(p, i) in palettePresets" :key="i" :value="i">{{ p.name }}</option>
                </select>
              </div>
              <div v-if="isCustom" class="grid grid-cols-2 gap-3">
                <div><small class="block mb-1 text-gray-300">Relleno</small><input type="color" v-model="colors.fill" class="product-input w-full h-10 p-1" /></div>
                <div><small class="block mb-1 text-gray-300">Borde</small>  <input type="color" v-model="colors.border" class="product-input w-full h-10 p-1" /></div>
                <div><small class="block mb-1 text-gray-300">Filete</small> <input type="color" v-model="colors.outline" class="product-input w-full h-10 p-1" /></div>
                <div><small class="block mb-1 text-gray-300">Texto</small>  <input type="color" v-model="colors.text" class="product-input w-full h-10 p-1" /></div>
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <label class="flex items-center gap-2"><input type="checkbox" v-model="wm.mostrar" /><span>Mostrar</span></label>
              <div>
                <small class="block mb-1 text-gray-300">Posición</small>
                <select v-model="wm.posicion" class="product-input w-full">
                  <option value="top-left">Arriba izq.</option>
                  <option value="top-right">Arriba der.</option>
                  <option value="bottom-left">Abajo izq.</option>
                  <option value="bottom-right">Abajo der.</option>
                  <option value="center">Centro</option>
                </select>
              </div>
              <div><small class="block mb-1 text-gray-300">Tamaño (px)</small><input type="range" min="36" max="140" v-model.number="wm.tamano" class="w-full" /></div>
              <div><small class="block mb-1 text-gray-300">Opacidad</small><input type="range" min="0.2" max="1" step="0.05" v-model.number="wm.opacidad" class="w-full" /></div>
            </div>
          </div>
        </div>
      </div> <!-- grid -->

      <!-- ===================== TABLA ABAJO ===================== -->
      <div class="product-card-dark overflow-hidden mt-6">
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
                  <th colspan="6" class="px-6 py-3 text-center">Piezas / Preview</th>
                  <th rowspan="2" class="px-6 py-3">Exportar</th>
                  <th rowspan="2" class="px-6 py-3">Guardar / Descargar</th>
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
                  <td colspan="10" class="text-center text-gray-400 py-6">
                    No hay moldes registrados. ¡Sube tu primer conjunto!
                  </td>
                </tr>

                <tr v-for="(item, idx) in moldes" :key="item.id || idx">
                  <td class="px-6 py-4 font-semibold text-white align-top">
                    {{ item.nombre }}
                  </td>

                  <!-- 6 celdas de piezas: preview + acciones -->
                  <td v-for="c in camposArchivo" :key="c.key" class="px-6 py-4 align-top">
                    <div v-if="pieceUrl(item, c.key)">
                      <div class="thumb-playera mb-2">
                        <div class="thumb-base" :style="bgFrom(pieceUrl(item, c.key))"></div>
                        <div v-if="wm.mostrar" class="thumb-sello" :style="thumbBoxStyle">
                          <span class="thumb-sello__text" :style="thumbTextStyle">{{ tallaFromName(item.nombre) }}</span>
                        </div>
                      </div>
                      <div class="flex flex-wrap gap-1">
                        <button class="product-btn-secondary px-2 py-1" @click="exportPiece(item, c.key, 'png')">PNG</button>
                        <button class="product-btn-secondary px-2 py-1" @click="exportPiece(item, c.key, 'pdf')">PDF</button>
                        <a :href="pieceUrl(item, c.key)" target="_blank" class="product-link px-2 py-1">Ver base</a>
                      </div>

                      <div class="mt-2 flex flex-col gap-1">
                        <button class="product-btn-primary px-2 py-1" @click="savePieceComposition(item, c.key)">Guardar comp.</button>
                        <a v-if="getSavedPiece(item, c.key)" :href="getSavedPiece(item, c.key)" target="_blank" class="product-btn-secondary inline-block px-2 py-1 text-center">Ver/Descargar guardada</a>
                      </div>
                    </div>
                    <span v-else class="text-gray-500 italic">-</span>
                  </td>

                  <!-- Exportar todas -->
                  <td class="px-6 py-4 align-top">
                    <div class="flex flex-col gap-2">
                      <button class="product-btn-secondary px-3 py-1" @click="exportAll(item, 'png')" :disabled="!hasAnyPiece(item)">PNG (todas)</button>
                      <button class="product-btn-secondary px-3 py-1" @click="exportAll(item, 'pdf')" :disabled="!hasAnyPiece(item)">PDF (todas)</button>
                    </div>
                  </td>

                  <!-- Guardar / abrir todas -->
                  <td class="px-6 py-4 align-top">
                    <div class="flex flex-col gap-2">
                      <button class="product-btn-primary px-3 py-1" @click="saveAll(item)" :disabled="!hasAnyPiece(item)">Guardar comp. (todas)</button>
                      <button v-if="hasAnySaved(item)" class="product-btn-secondary px-3 py-1" @click="openAllSaved(item)">Abrir guardadas</button>
                    </div>
                  </td>

                  <td class="px-6 py-4 align-top">
                    <button @click="eliminarMolde(idx)" class="product-btn-danger">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 text-right">
            <span class="text-gray-300 text-sm">Total: {{ moldes.length }}</span>
          </div>
        </div>
      </div>
      <!-- =================== /TABLA =================== -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMoldesStore } from '@/stores/moldes'

/* ===== Config piezas ===== */
const camposArchivo = [
  { key: 'camiseta_frente',  label: 'Molde Camiseta frente' },
  { key: 'camiseta_espalda', label: 'Molde Camiseta espalda' },
  { key: 'short_izq',        label: 'Molde Short Izquierda' },
  { key: 'short_der',        label: 'Molde Short Derecha' },
  { key: 'manga_izq',        label: 'Molde Manga Izquierda' },
  { key: 'manga_der',        label: 'Molde Manga Derecha' }
]

/* ===== Store ===== */
const store = useMoldesStore()
const moldes = computed(() => store.items)

/* ===== Form ===== */
const molde = ref({
  nombre: '',
  archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null])) // File | null
})
const tallaAuto   = ref('')     // ej. "8"
const dirInput    = ref(null)
const uploading   = ref(false)
const progress    = ref(null)
const autoMapMsg  = ref('')
const autoMapOk   = ref(false)

const isValidNombre = computed(() => (molde.value.nombre || '').trim().length > 0)
const alMenosUnArchivo = computed(() => Object.values(molde.value.archivos).some(f => !!f))
const tallaAutoDisplay = computed(() => (tallaAuto.value || '').toString().trim().toUpperCase() || 'T')

/* ===== Watermark global ===== */
const palettePresets = [
  { name: 'Naranja RG (default)', fill: '#ff6a00', border: '#232323', outline: '#ffffff99', text: '#111111' },
  { name: 'Rojo', fill: '#ef4444', border: '#7f1d1d', outline: '#ffffff8c', text: '#111111' },
  { name: 'Azul', fill: '#3b82f6', border: '#1e3a8a', outline: '#ffffff8c', text: '#0b1020' },
  { name: 'Verde', fill: '#22c55e', border: '#14532d', outline: '#ffffff8c', text: '#0b160b' },
  { name: 'Negro', fill: '#111827', border: '#000', outline: '#ffffff73', text: '#fff' },
  { name: 'Personalizado', custom: true }
]
const colors = ref({ presetIndex: 0, fill: palettePresets[0].fill, border: palettePresets[0].border, outline: palettePresets[0].outline, text: palettePresets[0].text })
const isCustom = computed(() => palettePresets[colors.value.presetIndex]?.custom === true)
const applyPreset = () => {
  const p = palettePresets[colors.value.presetIndex]
  if (!p?.custom) { colors.value.fill = p.fill; colors.value.border = p.border; colors.value.outline = p.outline; colors.value.text = p.text }
}
const wm = ref({ mostrar: true, posicion: 'bottom-right', tamano: 72, opacidad: 0.85 })
const posStyle = (pos) => {
  switch (pos) {
    case 'top-left': return { top: '10px', left: '10px', transform: 'none' }
    case 'top-right': return { top: '10px', right: '10px', transform: 'none' }
    case 'bottom-left': return { bottom: '10px', left: '10px', transform: 'none' }
    case 'bottom-right': return { bottom: '10px', right: '10px', transform: 'none' }
    default: return { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }
  }
}
const thumbBoxStyle = computed(() => ({
  width: '40px', height: '40px', opacity: 0.9, right: '6px', bottom: '6px',
  backgroundColor: colors.value.fill, borderColor: colors.value.border, outlineColor: colors.value.outline
}))
const thumbTextStyle = computed(() => ({ color: colors.value.text }))

/* ===== Helpers preview ===== */
const isImageName = (name = '') => /\.(png|jpe?g|svg)$/i.test(name)
const isPdfName   = (name = '') => /\.pdf$/i.test(name)

const bgFrom = (url) => ({ backgroundImage: `url('${url}')`, backgroundSize: 'cover', backgroundPosition: 'center' })

// Previews del form (File -> objectURL)
const objectUrlCache = new Map()
const formPreviewUrl = (key) => {
  const f = molde.value.archivos[key]
  if (!f) return ''
  if (isImageName(f.name)) {
    if (!objectUrlCache.has(f)) objectUrlCache.set(f, URL.createObjectURL(f))
    return objectUrlCache.get(f)
  }
  return '' // PDFs no se renderizan en canvas (se pueden abrir con "Ver base" al guardar)
}

/* ====== Carpeta / auto-mapeo ====== */
const openFolderPicker = () => dirInput.value?.click?.()
const handleFolder = (e) => {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  const talla = (tallaAuto.value || '').toString().trim()
  const asignadas = autoMapFromFolder(files, talla)

  const keys = Object.keys(asignadas).filter(k => asignadas[k])
  const faltantes = camposArchivo.map(c => c.key).filter(k => !asignadas[k])

  for (const k of keys) molde.value.archivos[k] = asignadas[k]

  if (!molde.value.nombre && files[0].webkitRelativePath) {
    const parts = files[0].webkitRelativePath.split('/')
    const carpeta = parts.length > 1 ? parts[parts.length - 2] : 'Conjunto'
    molde.value.nombre = `${carpeta}${talla ? ` - T${talla}` : ''}`
  }

  if (keys.length) {
    autoMapOk.value = faltantes.length === 0
    autoMapMsg.value = `Asignados: ${keys.length}/6. ${faltantes.length ? `Faltan: ${faltantes.join(', ')}` : '¡Todo listo!'}`}
  else {
    autoMapOk.value = false
    autoMapMsg.value = 'No se encontraron archivos que coincidan con esta talla. Revisa los nombres o sube manualmente.'
  }

  e.target.value = ''
}
function autoMapFromFolder(files, talla) {
  const allowed = /\.(svg|png|jpe?g|pdf)$/i
  const clean = (s) => (s || '').toLowerCase()
  const patterns = {
    camiseta_frente:  [/(camiseta|playera|remera).*(frente|front)/i, /(frente|front).*?(camiseta|playera|remera)/i],
    camiseta_espalda: [/(camiseta|playera|remera).*(espalda|back)/i, /(espalda|back).*?(camiseta|playera|remera)/i],
    short_izq:        [/(short|bermuda|pantal(?:o|ó)n|shorts).*?(izq|left)/i],
    short_der:        [/(short|bermuda|pantal(?:o|ó)n|shorts).*?(der|right)/i],
    manga_izq:        [/(manga).*?(izq|left)/i],
    manga_der:        [/(manga).*?(der|right)/i]
  }
  const tallaRegex = talla ? new RegExp(`(^|[^a-z0-9])(?:t(?:alla)?\\s*)?${talla}(?=[^a-z0-9]|$)`, 'i') : null
  const scoreFor = (name, field) => {
    let s = 0
    for (const rx of patterns[field]) if (rx.test(name)) { s += 100; break }
    if (tallaRegex && tallaRegex.test(name)) s += 50
    if (/frente|front|espalda|back|izq|left|der|right|manga|short|camiseta|playera|remera/.test(name)) s += 10
    return s
  }
  const out = Object.fromEntries(Object.keys(patterns).map(k => [k, null]))
  const used = new Set()
  const candidates = files.filter(f => allowed.test(f.name)).map(f => ({ file: f, name: clean(f.name) }))
  for (const field of Object.keys(patterns)) {
    let best = null, bestScore = 0
    for (const c of candidates) {
      if (used.has(c.file)) continue
      const sc = scoreFor(c.name, field)
      if (sc > bestScore) { best = c.file; bestScore = sc }
    }
    if (best) { out[field] = best; used.add(best) }
  }
  return out
}

/* ===== Manejo manual por archivo (form) ===== */
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
  const f = molde.value.archivos[campo]
  if (f && objectUrlCache.has(f)) { try { URL.revokeObjectURL(objectUrlCache.get(f)) } catch {} ; objectUrlCache.delete(f) }
  molde.value.archivos[campo] = null
  const el = document.getElementById(`file-${campo}`)
  if (el) el.value = ''
}

/* ===== Export/Canvas ===== */
function drawRoundedRect(ctx, x, y, w, h, r) {
  const radius = Math.min(r, w/2, h/2)
  ctx.beginPath()
  ctx.moveTo(x + radius, y)
  ctx.arcTo(x + w, y, x + w, y + h, radius)
  ctx.arcTo(x + w, y + h, x, y + h, radius)
  ctx.arcTo(x, y + h, x, y, radius)
  ctx.arcTo(x, y, x + w, y, radius)
  ctx.closePath()
}
async function loadImage(url) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.crossOrigin = 'anonymous'
    img.onload = () => resolve(img)
    img.onerror = reject
    img.src = url
  })
}
async function composeCanvas(bgUrl, selloText, opts) {
  const { pos, sizePx, opacity, colors } = opts
  const img = await loadImage(bgUrl)
  const targetH = 1400, scale = targetH / img.height, targetW = Math.round(img.width * scale)
  const canvas = document.createElement('canvas'); canvas.width = targetW; canvas.height = targetH
  const ctx = canvas.getContext('2d')
  ctx.drawImage(img, 0, 0, targetW, targetH)

  const margin = Math.round(Math.min(targetW, targetH) * 0.02)
  const boxSize = Math.round(sizePx * (targetH / 340))
  let x = margin, y = margin
  if (pos === 'top-right')    { x = targetW - boxSize - margin; y = margin }
  if (pos === 'bottom-left')  { x = margin; y = targetH - boxSize - margin }
  if (pos === 'bottom-right') { x = targetW - boxSize - margin; y = targetH - boxSize - margin }
  if (pos === 'center')       { x = (targetW - boxSize)/2; y = (targetH - boxSize)/2 }

  ctx.save()
  ctx.globalAlpha = opacity
  ctx.shadowColor = 'rgba(0,0,0,0.35)'
  ctx.shadowBlur = Math.round(boxSize * 0.15)
  ctx.shadowOffsetX = 0
  ctx.shadowOffsetY = Math.round(boxSize * 0.06)

  drawRoundedRect(ctx, x, y, boxSize, boxSize, Math.round(boxSize * 0.12))
  ctx.fillStyle = colors.fill; ctx.fill()
  ctx.lineWidth = Math.max(2, Math.round(boxSize * 0.06)); ctx.strokeStyle = colors.border; ctx.stroke()
  ctx.lineWidth = Math.max(2, Math.round(boxSize * 0.05)); ctx.strokeStyle = colors.outline; ctx.stroke()

  ctx.shadowColor = 'transparent'
  ctx.fillStyle = colors.text
  ctx.font = `bold ${Math.round(boxSize * 0.42)}px system-ui, -apple-system, Segoe UI, Roboto`
  ctx.textAlign = 'center'; ctx.textBaseline = 'middle'
  ctx.fillText((selloText || '').toString().toUpperCase(), x + boxSize/2, y + boxSize/2)
  ctx.restore()

  return canvas
}
function downloadDataUrl(filename, dataUrl) {
  const a = document.createElement('a')
  a.href = dataUrl; a.download = filename
  document.body.appendChild(a); a.click(); a.remove()
}
async function ensureJsPDF() {
  if (window.jspdf?.jsPDF) return window.jspdf.jsPDF
  const mod = await import('https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js')
  return mod.jsPDF || window.jspdf.jsPDF
}

/* ---- Export pieza (form) ---- */
async function exportFormPiece(key, kind) {
  const url = formPreviewUrl(key)
  if (!url) { alert('Esta pieza no es imagen o no tiene archivo.'); return }
  const canvas = await composeCanvas(url, tallaAutoDisplay.value, { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
  const png = canvas.toDataURL('image/png')
  if (kind === 'png') downloadDataUrl(`molde_${key}.png`, png)
  else {
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
    const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
    const img = await loadImage(png)
    let w = maxW, h = (img.height / img.width) * w
    if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
    pdf.addImage(png, 'PNG', (pageW - w)/2, (pageH - h)/2, w, h)
    pdf.save(`molde_${key}.pdf`)
  }
}

/* ---- Tabla: helpers pieza ---- */
const pieceUrl = (item, key) => {
  const p = item[key]
  if (!p) return ''
  // si tu API guarda como { url, name } o string simple:
  return typeof p === 'string' ? p : (p.url || '')
}
const hasAnyPiece = (item) => camposArchivo.some(c => !!pieceUrl(item, c.key))
const hasAnySaved = (item) => !!item.composiciones && Object.values(item.composiciones).some(Boolean)
const tallaFromName = (nombre = '') => {
  const m = /T(\d+)/i.exec(nombre) || /(?:talla|t)\s*(\d+)/i.exec(nombre)
  return m ? m[1] : 'T'
}

/* ---- Export pieza (tabla) ---- */
async function exportPiece(item, key, kind) {
  const url = pieceUrl(item, key)
  if (!url) return
  if (!isImageName(url)) { window.open(url, '_blank'); return } // PDF u otros: abrir
  const canvas = await composeCanvas(url, tallaFromName(item.nombre), { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
  const png = canvas.toDataURL('image/png')
  if (kind === 'png') downloadDataUrl(`molde_${item.nombre}_${key}.png`, png)
  else {
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
    const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
    const img = await loadImage(png)
    let w = maxW, h = (img.height / img.width) * w
    if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
    pdf.addImage(png, 'PNG', (pageW - w)/2, (pageH - h)/2, w, h)
    pdf.save(`molde_${item.nombre}_${key}.pdf`)
  }
}

/* ---- Export todas las piezas ---- */
async function exportAll(item, kind) {
  const urls = camposArchivo.map(c => ({ key: c.key, url: pieceUrl(item, c.key) })).filter(x => !!x.url)
  if (!urls.length) return
  if (kind === 'png') {
    for (const { key, url } of urls) {
      if (!isImageName(url)) continue
      const canvas = await composeCanvas(url, tallaFromName(item.nombre), { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
      const png = canvas.toDataURL('image/png')
      downloadDataUrl(`molde_${item.nombre}_${key}.png`, png)
    }
  } else {
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    let first = true
    for (const { url } of urls) {
      if (!isImageName(url)) continue
      const canvas = await composeCanvas(url, tallaFromName(item.nombre), { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
      const png = canvas.toDataURL('image/png')
      const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
      const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
      const img = await loadImage(png)
      let w = maxW, h = (img.height / img.width) * w
      if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
      if (!first) pdf.addPage('a4', 'portrait'); first = false
      pdf.addImage(png, 'PNG', (pageW - w)/2, (pageH - h)/2, w, h)
    }
    pdf.save(`moldes_${item.nombre}.pdf`)
  }
}

/* ---- Guardar composiciones en servidor ---- */
function getSavedPiece(item, key) {
  return item.composiciones?.[key] || null
}
async function savePieceComposition(item, key) {
  if (typeof store.uploadComposicion !== 'function') {
    alert('Falta store.uploadComposicion(id, key, blob) para guardar en servidor.')
    return
  }
  const url = pieceUrl(item, key)
  if (!url) return
  if (!isImageName(url)) { alert('Pieza no es imagen (PDF/SVG remoto bloqueado).'); return }
  try {
    const canvas = await composeCanvas(url, tallaFromName(item.nombre), { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
    const pngData = canvas.toDataURL('image/png'); const blob = await (await fetch(pngData)).blob()
    const remoteUrl = await store.uploadComposicion(item.id, key, blob)
    const curr = { ...(item.composiciones || {}) }
    curr[key] = remoteUrl
    await store.updateOne(item.id, { composiciones: curr })
    item.composiciones = curr
  } catch (e) { console.error(e); alert('No se pudo guardar la composición.') }
}
async function saveAll(item) {
  if (typeof store.uploadComposicion !== 'function') {
    alert('Falta store.uploadComposicion(id, key, blob) para guardar en servidor.')
    return
  }
  try {
    const curr = { ...(item.composiciones || {}) }
    for (const c of camposArchivo) {
      const url = pieceUrl(item, c.key)
      if (!url || !isImageName(url)) continue
      const canvas = await composeCanvas(url, tallaFromName(item.nombre), { pos: wm.value.posicion, sizePx: wm.value.tamano, opacity: wm.value.opacidad, colors: colors.value })
      const pngData = canvas.toDataURL('image/png'); const blob = await (await fetch(pngData)).blob()
      const remoteUrl = await store.uploadComposicion(item.id, c.key, blob)
      curr[c.key] = remoteUrl
    }
    await store.updateOne(item.id, { composiciones: curr })
    item.composiciones = curr
    alert('Composiciones guardadas ✅')
  } catch (e) { console.error(e); alert('No se pudieron guardar todas las composiciones.') }
}
function openAllSaved(item) {
  Object.values(item.composiciones || {}).forEach(u => { if (u) window.open(u, '_blank') })
}

/* ===== Guardado inicial, borrar y limpiar ===== */
const addMolde = async () => {
  const nombre = (molde.value.nombre || '').trim()
  if (!nombre) { alert('Debes ingresar un nombre para el conjunto'); return }
  if (!alMenosUnArchivo.value) { alert('Agrega al menos un archivo (o usa la carga por carpeta).'); return }

  uploading.value = true; progress.value = 0
  try {
    await store.addOne({ nombre, archivos: molde.value.archivos }, (evt) => {
      if (evt?.total) progress.value = (evt.loaded / evt.total) * 100
      else progress.value = null
    })
    limpiarCampos(); autoMapMsg.value = ''
  } catch (e) {
    console.error('addMolde error:', e)
    const msg = e?.response?.data?.message || e.message || 'No se pudo guardar los moldes.'
    alert('❌ ' + msg)
  } finally { uploading.value = false; progress.value = null }
}
const eliminarMolde = async (indexOrId) => {
  const item = store.items[indexOrId]
  if (!item) return
  if (!confirm(`¿Eliminar el conjunto "${item.nombre}"?`)) return
  try { await store.deleteOne(item.id) }
  catch (e) { console.error(e); alert('No se pudo eliminar. Revisa el servidor.') }
}
const limpiarCampos = () => {
  Object.values(molde.value.archivos).forEach(f => {
    if (f && objectUrlCache.has(f)) { try { URL.revokeObjectURL(objectUrlCache.get(f)) } catch {} ; objectUrlCache.delete(f) }
  })
  molde.value = { nombre: '', archivos: Object.fromEntries(camposArchivo.map(c => [c.key, null])) }
  camposArchivo.forEach(c => { const el = document.getElementById(`file-${c.key}`); if (el) el.value = '' })
}

/* ===== Init ===== */
onMounted(() => { store.fetchAll() })
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
.product-input::placeholder { color: #a7a7bf; }
.product-input:focus { border-color: #8b8bd6; box-shadow: 0 0 0 3px rgba(139,139,214,0.35); transform: translateY(-1px); }

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
.product-btn-primary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

.product-btn-secondary {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: white;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.product-btn-secondary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

.product-btn-danger {
  background: rgba(239, 83, 80, 0.18);
  color: #ef5350;
  font-weight: 800;
  border-radius: 10px;
  padding: 8px 14px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, background .2s ease, color .2s ease;
}
.product-btn-danger:hover { background: rgba(239, 83, 80, 0.28); color: #ff6b6b; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,.2); }

/* Tabla oscura */
.product-table-dark { border-collapse: separate; border-spacing: 0; width: 100%; }
.product-table-dark thead { background-color: #3e3e57; color: #ffffff; text-transform: uppercase; font-weight: 800; letter-spacing: .4px; }
.product-table-dark th, .product-table-dark td { border-bottom: 1px solid rgba(255,255,255,0.08); }
.product-table-dark tbody tr { background-color: #2c2c3e; transition: background-color .18s ease; }
.product-table-dark tbody tr:hover { background-color: #3a3a50; }
.product-link { color: #93c5fd; text-decoration: underline; transition: color .18s ease; }
.product-link:hover { color: #bfdbfe; }

/* Previews */
.thumb-playera { position: relative; width: 120px; height: 160px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.12); background: #0b1245; box-shadow: 0 4px 12px rgba(0,0,0,.25); }
.thumb-base { position: absolute; inset: 0; background: radial-gradient(100% 70% at 50% 0%, rgba(255,0,0,0.18), transparent 60%), radial-gradient(60% 40% at 50% 100%, rgba(255,255,255,0.06), transparent 60%), #0b1245; background-size: cover; background-position: center; }
.thumb-sello { position: absolute; display: grid; place-items: center; border-radius: 5px; border-width: 2px; outline: 2px solid transparent; box-shadow: 0 4px 10px rgba(0,0,0,.28); right: 6px; bottom: 6px; width: 40px; height: 40px; }
.thumb-sello__text { font-weight: 900; font-size: 16px; line-height: 1; }

/* Preview en formulario */
.form-thumb { position: relative; width: 180px; height: 240px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.12); background: #0b1245; box-shadow: 0 4px 12px rgba(0,0,0,.25); }
.sep { border: none; border-top: 1px dashed rgba(255,255,255,0.1); margin: 14px 0; }
</style>
