<template>
  <div class="tallas-container text-white">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="card-dark overflow-hidden mb-8">
        <div class="card-title-gradient">
          <h2 class="m-0 text-white text-h5">PESTAÑA TALLAS</h2>
        </div>
        <div class="p-4">
          <p class="m-0 text-gray-200">
            Sube la moldería por talla (una o varias), agrega la marca de agua y exporta en PNG o PDF.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card: Formulario + preview grande -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle">
            <span class="font-bold">Añadir Talla</span>
          </div>

          <div class="p-6 space-y-4">
            <form @submit.prevent="handleFormSubmit" class="space-y-4">
              <select v-model="talla.categoria" class="product-input w-full" required>
                <option disabled value="">Seleccionar categoría</option>
                <option>CAMISETAS</option>
                <option>MANGAS</option>
                <option>SHORT</option>
              </select>

              <input
                v-model="talla.talle"
                placeholder="Talle (0, 2, S, M...)"
                class="product-input w-full"
                required
              />

              <div class="grid grid-cols-2 gap-3">
                <input
                  v-model.number="talla.ancho"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="Ancho (cm)"
                  class="product-input w-full"
                  required
                />
                <input
                  v-model.number="talla.alto"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="Alto (cm)"
                  class="product-input w-full"
                  required
                />
              </div>

              <!-- Subir múltiples molderías -->
              <div>
                <small class="block mb-1 text-gray-300">Molderías (una o varias imágenes)</small>
                <input type="file" accept="image/*" multiple class="product-input w-full"
                       @change="onFormFilesChange" />
                <p v-if="formFiles.length" class="text-sm mt-1 text-gray-300">
                  {{ formFiles.length }} archivo(s) seleccionado(s)
                </p>

                <!-- Preview grande con la primera imagen + watermark -->
                <div class="playera-preview mt-3" ref="formPreviewRef">
                  <div class="playera-base" :style="formBgStyle"></div>
                  <div v-if="preview.mostrar" class="marca-agua" :style="marcaAguaBoxStyle">
                    <span class="marca-agua__sello" :style="marcaAguaTextStyle">
                      {{ (talla.talle || 'L').toUpperCase() }}
                    </span>
                  </div>
                </div>

                <!-- Export buttons (usa la primera imagen como muestra) -->
                <div class="flex flex-wrap gap-2 mt-3">
                  <button type="button" class="btn-secondary"
                          :disabled="!formHasImage"
                          @click="exportForm('png')">
                    Descargar PNG (form)
                  </button>
                  <button type="button" class="btn-secondary"
                          :disabled="!formHasImage"
                          @click="exportForm('pdf')">
                    Descargar PDF (form)
                  </button>
                </div>
              </div>

              <div class="flex gap-3 pt-2">
                <button class="btn-primary" type="submit">Añadir Talla</button>
                <button type="button" class="btn-secondary" @click="limpiarCampos">Limpiar Campos</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Card: Controles de Marca de Agua / Paleta -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle">
            <span class="font-bold">Controles de Marca de Agua</span>
          </div>

          <div class="p-6 space-y-6">
            <!-- Presets / Custom -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <small class="block mb-1 text-gray-300">Paleta</small>
                <select v-model.number="colors.presetIndex" class="product-input w-full" @change="applyPreset">
                  <option v-for="(p, i) in palettePresets" :key="i" :value="i">{{ p.name }}</option>
                </select>
              </div>

              <div v-if="isCustom" class="grid grid-cols-2 gap-3">
                <div>
                  <small class="block mb-1 text-gray-300">Relleno</small>
                  <input type="color" v-model="colors.fill" class="product-input w-full h-10 p-1" />
                </div>
                <div>
                  <small class="block mb-1 text-gray-300">Borde</small>
                  <input type="color" v-model="colors.border" class="product-input w-full h-10 p-1" />
                </div>
                <div>
                  <small class="block mb-1 text-gray-300">Filete</small>
                  <input type="color" v-model="colors.outline" class="product-input w-full h-10 p-1" />
                </div>
                <div>
                  <small class="block mb-1 text-gray-300">Texto</small>
                  <input type="color" v-model="colors.text" class="product-input w-full h-10 p-1" />
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <label class="flex items-center gap-2">
                <input type="checkbox" v-model="preview.mostrar" />
                <span>Mostrar</span>
              </label>

              <div>
                <small class="block mb-1 text-gray-300">Posición</small>
                <select v-model="preview.posicion" class="product-input w-full">
                  <option value="top-left">Arriba izq.</option>
                  <option value="top-right">Arriba der.</option>
                  <option value="bottom-left">Abajo izq.</option>
                  <option value="bottom-right">Abajo der.</option>
                  <option value="center">Centro</option>
                </select>
              </div>

              <div>
                <small class="block mb-1 text-gray-300">Tamaño (px)</small>
                <input type="range" min="36" max="140" v-model.number="preview.tamano" class="w-full" />
              </div>

              <div>
                <small class="block mb-1 text-gray-300">Opacidad</small>
                <input type="range" min="0.2" max="1" step="0.05" v-model.number="preview.opacidad" class="w-full" />
              </div>
            </div>

            <p class="text-gray-300 text-sm">
              *El sello usa el <strong>Talle</strong> (S, M, L, 0, 2…) y estos controles.*
            </p>
          </div>
        </div>
      </div> <!-- /grid -->

      <!-- ===================== TABLA ABAJO ===================== -->
      <div class="card-dark overflow-hidden mt-6">
        <div class="card-subtitle flex items-center justify-between">
          <span class="font-bold">Tallas registradas</span>

          <button class="btn-secondary" :disabled="guardandoTodo" @click="guardarTodo">
            {{ guardandoTodo ? 'Guardando…' : 'Guardar cambios' }}
          </button>
        </div>

        <div class="p-0">
          <div class="overflow-x-auto">
            <table class="table-dark w-full">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th>Talle</th>
                  <th>Ancho</th>
                  <th>Alto</th>
                  <th style="min-width:320px">Molderías (galería)</th>
                  <th>Exportar</th>
                  <th>Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="tallasStore.tallas.length === 0">
                  <td colspan="7" class="text-center py-6 text-gray-300">
                    No hay tallas registradas aún.
                  </td>
                </tr>

                <tr v-for="item in tallasStore.tallas" :key="item.id">
                  <td>{{ item.categoria }}</td>
                  <td>{{ item.talle }}</td>

                  <!-- Editable: ANCHO -->
                  <td>
                    <div class="flex items-center gap-2">
                      <input
                        v-model.number="item.ancho"
                        type="number"
                        min="0"
                        step="0.01"
                        class="product-input w-full"
                        @keydown.enter.prevent="guardarFila(item)"
                        @blur="guardarFila(item)"
                      />
                      <small class="text-gray-400">cm</small>
                    </div>
                  </td>

                  <!-- Editable: ALTO -->
                  <td>
                    <div class="flex items-center gap-2">
                      <input
                        v-model.number="item.alto"
                        type="number"
                        min="0"
                        step="0.01"
                        class="product-input w-full"
                        @keydown.enter.prevent="guardarFila(item)"
                        @blur="guardarFila(item)"
                      />
                      <small class="text-gray-400">cm</small>
                    </div>
                  </td>

                  <!-- Subir/Reemplazar molderías + galería -->
                  <td>
                    <div class="flex flex-col gap-2">
                      <input type="file" accept="image/*" multiple class="product-input w-full"
                             @change="onRowFilesChange($event, item)" />

                      <!-- Galería -->
                      <div class="gallery-grid">
                        <div
                          v-for="(url, idx) in rowGallery(item)"
                          :key="url + idx"
                          class="gal-item"
                        >
                          <div class="thumb-playera">
                            <div class="thumb-base" :style="thumbBg(url)"></div>
                            <div class="thumb-sello"
                                 :style="thumbBoxStyle"
                                 title="Marca de agua">
                              <span class="thumb-sello__text" :style="thumbTextStyle">
                                {{ (item.talle || '?').toString().toUpperCase() }}
                              </span>
                            </div>
                          </div>

                          <div class="flex gap-1 mt-2 justify-center">
                            <button class="btn-secondary px-2 py-1" @click="exportSingle(url, item.talle || item.id, 'png')">PNG</button>
                            <button class="btn-secondary px-2 py-1" @click="exportSingle(url, item.talle || item.id, 'pdf')">PDF</button>
                            <button class="btn-danger px-2 py-1" @click="removeFromRow(item, url)">Quitar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Export masivo por fila -->
                  <td>
                    <div class="flex flex-col gap-2">
                      <button class="btn-secondary px-3 py-1"
                              :disabled="rowGallery(item).length === 0"
                              @click="exportRowAll(item, 'png')">
                        PNG (todas)
                      </button>
                      <button class="btn-secondary px-3 py-1"
                              :disabled="rowGallery(item).length === 0"
                              @click="exportRowAll(item, 'pdf')">
                        PDF (todas)
                      </button>
                    </div>
                  </td>

                  <td class="flex gap-2 justify-center">
                    <button class="btn-primary"
                            :disabled="guardandoFila[item.id] === true"
                            @click="guardarFila(item)">
                      {{ guardandoFila[item.id] ? 'Guardando…' : 'Guardar' }}
                    </button>

                    <button class="btn-danger" @click="tallasStore.eliminarTalla(item.id)">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 text-right">
            <span class="text-gray-300 text-sm">Total: {{ tallasStore.tallas.length }}</span>
          </div>
        </div>
      </div>
      <!-- =================== /TABLA =================== -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTallasStore } from '@/stores/tallas'

const tallasStore = useTallasStore()

/* ======== Estado formulario ======== */
const talla = ref({
  categoria: '',
  talle: '',
  ancho: '',
  alto: '',
  // Para múltiples: en BD usa array de URLs
  molderias: [] // string[]
})
const formFiles = ref([])          // File[]
const formPreviewRef = ref(null)

/* ======== Guardados ======== */
const guardandoFila = ref({})
const guardandoTodo = ref(false)

/* ======== Paleta de colores ======== */
const palettePresets = [
  { name: 'Naranja RG (default)', fill: '#ff6a00', border: '#232323', outline: '#ffffff99', text: '#111111' },
  { name: 'Rojo', fill: '#ef4444', border: '#7f1d1d', outline: '#ffffff8c', text: '#111111' },
  { name: 'Azul', fill: '#3b82f6', border: '#1e3a8a', outline: '#ffffff8c', text: '#0b1020' },
  { name: 'Verde', fill: '#22c55e', border: '#14532d', outline: '#ffffff8c', text: '#0b160b' },
  { name: 'Negro', fill: '#111827', border: '#000000', outline: '#ffffff73', text: '#ffffff' },
  { name: 'Personalizado', custom: true }
]
const colors = ref({
  presetIndex: 0,
  fill: palettePresets[0].fill,
  border: palettePresets[0].border,
  outline: palettePresets[0].outline,
  text: palettePresets[0].text
})
const isCustom = computed(() => palettePresets[colors.value.presetIndex]?.custom === true)
const applyPreset = () => {
  const p = palettePresets[colors.value.presetIndex]
  if (!p?.custom) {
    colors.value.fill = p.fill
    colors.value.border = p.border
    colors.value.outline = p.outline
    colors.value.text = p.text
  }
}

/* ======== Marca de agua ======== */
const preview = ref({
  mostrar: true,
  posicion: 'bottom-right',
  tamano: 72,
  opacidad: 0.85
})
const posStyle = (pos) => {
  switch (pos) {
    case 'top-left':     return { top: '10px', left: '10px', transform: 'none' }
    case 'top-right':    return { top: '10px', right: '10px', transform: 'none' }
    case 'bottom-left':  return { bottom: '10px', left: '10px', transform: 'none' }
    case 'bottom-right': return { bottom: '10px', right: '10px', transform: 'none' }
    case 'center':
    default:
      return { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }
  }
}
const marcaAguaGeomStyle = computed(() => {
  const base = posStyle(preview.value.posicion)
  return {
    ...base,
    width: preview.value.tamano + 'px',
    height: preview.value.tamano + 'px',
    opacity: preview.value.opacidad
  }
})
const marcaAguaBoxStyle = computed(() => ({
  ...marcaAguaGeomStyle.value,
  backgroundColor: colors.value.fill,
  borderColor: colors.value.border,
  outlineColor: colors.value.outline
}))
const marcaAguaTextStyle = computed(() => ({ color: colors.value.text }))

/* ======== Galerías (persistidas + locales) ======== */
// temp locales por fila: { [id]: string[] (objectURL) }
const tempMolderias = ref({})

// helpers: obtener URLs de fila (persistidas + temp)
const persistedUrls = (item) => Array.isArray(item.molderias) ? item.molderias : (item.molderiaUrl ? [item.molderiaUrl] : [])
const tempUrls = (item) => tempMolderias.value[item.id] || []
const rowGallery = (item) => [...persistedUrls(item), ...tempUrls(item)]

// primera imagen del form para preview
const firstFormUrl = computed(() => {
  if (formFiles.value.length === 0) return ''
  // si ya es objectURL, úsalo. Si es File nuevo, creamos uno.
  const f = formFiles.value[0]
  return typeof f === 'string' ? f : URL.createObjectURL(f)
})
const formHasImage = computed(() => !!firstFormUrl.value)
const formBgStyle = computed(() => firstFormUrl.value
  ? { backgroundImage: `url('${firstFormUrl.value}')`, backgroundSize: 'cover', backgroundPosition: 'center' }
  : {}
)
const thumbBg = (url) => ({ backgroundImage: `url('${url}')`, backgroundSize: 'cover', backgroundPosition: 'center' })

/* ======== Miniatura: estilos del sello ======== */
const thumbBoxStyle = computed(() => ({
  width: '40px',
  height: '40px',
  opacity: 0.9,
  right: '6px',
  bottom: '6px',
  backgroundColor: colors.value.fill,
  borderColor: colors.value.border,
  outlineColor: colors.value.outline
}))
const thumbTextStyle = computed(() => ({ color: colors.value.text }))

/* ======== Inputs de archivos ======== */
const onFormFilesChange = (e) => {
  const files = Array.from(e.target.files || [])
  formFiles.value = files
}
const onRowFilesChange = async (e, item) => {
  const files = Array.from(e.target.files || [])
  if (files.length === 0) return
  try {
    // Si hay método en store, subimos una por una y guardamos array
    if (typeof tallasStore.uploadMolderia === 'function') {
      const newUrls = []
      for (const file of files) {
        const u = await tallasStore.uploadMolderia(item.id, file)
        newUrls.push(u)
      }
      const merged = [...persistedUrls(item), ...newUrls]
      await tallasStore.updateTalla(item.id, { molderias: merged })
      item.molderias = merged
    } else {
      // Local preview: acumulamos objectURLs
      const prev = tempUrls(item)
      const news = files.map(f => URL.createObjectURL(f))
      tempMolderias.value[item.id] = [...prev, ...news]
      alert('Vista previa local añadida. Implementa tallasStore.uploadMolderia() para persistir.')
    }
    e.target.value = '' // reset input
  } catch (err) {
    console.error(err); alert('No se pudieron subir algunas molderías.')
  }
}
const removeFromRow = async (item, url) => {
  // Si está en temp → liberar y quitar
  const temps = tempUrls(item)
  if (temps.includes(url)) {
    URL.revokeObjectURL(url)
    tempMolderias.value[item.id] = temps.filter(u => u !== url)
    return
  }
  // Si está persistida → quítala de la lista y guarda
  const pers = persistedUrls(item)
  if (pers.includes(url)) {
    const merged = pers.filter(u => u !== url)
    try {
      await tallasStore.updateTalla(item.id, { molderias: merged })
      item.molderias = merged
    } catch (e) {
      console.error(e); alert('No se pudo quitar la moldería del servidor.')
    }
  }
}

/* ======== Canvas / Export ======== */
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
  const targetH = 1400
  const scale = targetH / img.height
  const targetW = Math.round(img.width * scale)
  const canvas = document.createElement('canvas')
  canvas.width = targetW; canvas.height = targetH
  const ctx = canvas.getContext('2d')
  ctx.drawImage(img, 0, 0, targetW, targetH)
  const margin = Math.round(Math.min(targetW, targetH) * 0.02)
  const boxSize = Math.round(sizePx * (targetH / 340))
  let x = margin, y = margin
  if (pos === 'top-right') { x = targetW - boxSize - margin; y = margin }
  if (pos === 'bottom-left') { x = margin; y = targetH - boxSize - margin }
  if (pos === 'bottom-right') { x = targetW - boxSize - margin; y = targetH - boxSize - margin }
  if (pos === 'center') { x = (targetW - boxSize)/2; y = (targetH - boxSize)/2 }
  ctx.save()
  ctx.globalAlpha = opacity
  ctx.shadowColor = 'rgba(0,0,0,0.35)'
  ctx.shadowBlur = Math.round(boxSize * 0.15)
  ctx.shadowOffsetX = 0
  ctx.shadowOffsetY = Math.round(boxSize * 0.06)
  drawRoundedRect(ctx, x, y, boxSize, boxSize, Math.round(boxSize * 0.12))
  ctx.fillStyle = colors.fill; ctx.fill()
  ctx.lineWidth = Math.max(2, Math.round(boxSize * 0.06))
  ctx.strokeStyle = colors.border; ctx.stroke()
  ctx.lineWidth = Math.max(2, Math.round(boxSize * 0.05))
  ctx.strokeStyle = colors.outline; ctx.stroke()
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

/* ---- Export (form: primera imagen) ---- */
async function exportForm(kind) {
  const url = firstFormUrl.value
  if (!url) return
  const canvas = await composeCanvas(url, talla.value.talle || 'L', {
    pos: preview.value.posicion, sizePx: preview.value.tamano, opacity: preview.value.opacidad, colors: colors.value
  })
  const png = canvas.toDataURL('image/png')
  if (kind === 'png') {
    downloadDataUrl(`molderia_${(talla.value.talle || 'talle')}.png`, png)
  } else {
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
    const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
    const img = await loadImage(png)
    let w = maxW, h = (img.height / img.width) * w
    if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
    const x = (pageW - w)/2, y = (pageH - h)/2
    pdf.addImage(png, 'PNG', x, y, w, h)
    pdf.save(`molderia_${(talla.value.talle || 'talle')}.pdf`)
  }
}

/* ---- Export UNO (desde galería) ---- */
async function exportSingle(url, label, kind) {
  const canvas = await composeCanvas(url, label || '?', {
    pos: preview.value.posicion, sizePx: preview.value.tamano, opacity: preview.value.opacidad, colors: colors.value
  })
  const png = canvas.toDataURL('image/png')
  if (kind === 'png') {
    downloadDataUrl(`molderia_${label}.png`, png)
  } else {
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
    const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
    const img = await loadImage(png)
    let w = maxW, h = (img.height / img.width) * w
    if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
    const x = (pageW - w)/2, y = (pageH - h)/2
    pdf.addImage(png, 'PNG', x, y, w, h)
    pdf.save(`molderia_${label}.pdf`)
  }
}

/* ---- Export TODAS por fila ---- */
async function exportRowAll(item, kind) {
  const urls = rowGallery(item)
  if (urls.length === 0) return
  if (kind === 'png') {
    // Descarga una por una
    for (let i = 0; i < urls.length; i++) {
      const canvas = await composeCanvas(urls[i], item.talle || `${item.id}`, {
        pos: preview.value.posicion, sizePx: preview.value.tamano, opacity: preview.value.opacidad, colors: colors.value
      })
      const png = canvas.toDataURL('image/png')
      downloadDataUrl(`molderia_${(item.talle || item.id)}_${i+1}.png`, png)
    }
  } else {
    // Un PDF con una página por imagen
    const jsPDF = await ensureJsPDF()
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
    for (let i = 0; i < urls.length; i++) {
      const canvas = await composeCanvas(urls[i], item.talle || `${item.id}`, {
        pos: preview.value.posicion, sizePx: preview.value.tamano, opacity: preview.value.opacidad, colors: colors.value
      })
      const png = canvas.toDataURL('image/png')
      const pageW = pdf.internal.pageSize.getWidth(), pageH = pdf.internal.pageSize.getHeight()
      const margin = 36, maxW = pageW - margin*2, maxH = pageH - margin*2
      const img = await loadImage(png)
      let w = maxW, h = (img.height / img.width) * w
      if (h > maxH) { h = maxH; w = (img.width / img.height) * h }
      const x = (pageW - w)/2, y = (pageH - h)/2
      if (i > 0) pdf.addPage('a4', 'portrait')
      pdf.addImage(png, 'PNG', x, y, w, h)
    }
    pdf.save(`molderias_${(item.talle || item.id)}.pdf`)
  }
}

/* ======== Acciones Store ======== */
const handleFormSubmit = async () => {
  // 1) Crear talla
  const created = await tallasStore.addTalla({
    categoria: talla.value.categoria,
    talle: talla.value.talle,
    ancho: Number(talla.value.ancho ?? 0),
    alto: Number(talla.value.alto ?? 0),
    molderias: [] // empezar vacío
  })

  // 2) Subir múltiples si el store lo soporta
  if (formFiles.value.length) {
    try {
      if (typeof tallasStore.uploadMolderia === 'function') {
        const newUrls = []
        for (const file of formFiles.value) {
          const u = await tallasStore.uploadMolderia(created.id, file)
          newUrls.push(u)
        }
        await tallasStore.updateTalla(created.id, { molderias: newUrls })
      } else {
        alert('Se creó la talla, pero no se subieron molderías (falta uploadMolderia).')
      }
    } catch (err) {
      console.error(err)
      alert('La talla se creó, pero falló la subida de algunas molderías.')
    }
  }

  limpiarCampos()
}

const limpiarCampos = () => {
  // liberar objectURLs creados para form
  formFiles.value.forEach(f => { if (typeof f !== 'string' && f?._objectURL) URL.revokeObjectURL(f._objectURL) })
  talla.value = { categoria: '', talle: '', ancho: '', alto: '', molderias: [] }
  formFiles.value = []
}

const guardarFila = async (item) => {
  if (guardandoFila.value[item.id]) return
  guardandoFila.value[item.id] = true
  try {
    await tallasStore.updateTalla(item.id, {
      ancho: Number(item.ancho ?? 0),
      alto:  Number(item.alto ?? 0),
    })
  } catch (e) {
    console.error(e); alert('No se pudo guardar la fila.')
  } finally {
    guardandoFila.value[item.id] = false
  }
}

const guardarTodo = async () => {
  if (guardandoTodo.value) return
  guardandoTodo.value = true
  try {
    if (typeof tallasStore.bulkUpdate === 'function') {
      const items = tallasStore.tallas.map(t => ({
        id: t.id,
        ancho: Number(t.ancho ?? 0),
        alto:  Number(t.alto ?? 0),
      }))
      await tallasStore.bulkUpdate(items)
    } else {
      for (const t of tallasStore.tallas) {
        await tallasStore.updateTalla(t.id, {
          ancho: Number(t.ancho ?? 0),
          alto:  Number(t.alto ?? 0),
        })
      }
    }
    alert('Cambios guardados ✅')
  } catch (e) {
    console.error(e); alert('No se pudieron guardar todos los cambios.')
  } finally {
    guardandoTodo.value = false
  }
}

onMounted(() => { tallasStore.getTallas() })
</script>

<style scoped>
/* Fondo general con degradado oscuro */
.tallas-container { background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%); min-height: 100vh; padding: 40px 16px; }

/* Tarjeta */
.card-dark { border-radius: 16px; background: rgba(26, 26, 39, 0.92); color: #e5e7eb; box-shadow: 0 10px 30px rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
.card-title-gradient { background: linear-gradient(45deg, #ff6b6b, #ffa500); color: #fff; padding: 18px 24px; font-weight: 800; border-top-left-radius: 16px; border-top-right-radius: 16px; }
.card-subtitle { padding: 14px 18px; font-weight: 700; color: #fff; background: rgba(255,255,255,0.06); border-bottom: 1px solid rgba(255,255,255,0.08); }

/* Inputs y botones */
.product-input { background-color: #f9fafb; border: 1px solid #d1d5db; color: #000 !important; border-radius: 10px; padding: 12px 14px; outline: none; transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease; width: 100%; }
.product-input::placeholder { color: #000 !important; }
.product-input:focus { border-color: #8b8bd6; box-shadow: 0 0 0 3px rgba(139, 139, 214, 0.35); transform: translateY(-1px); }
.btn-primary { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px; letter-spacing: .3px; transition: transform .18s ease, box-shadow .28s ease, filter .2s ease; border: 0; }
.btn-primary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }
.btn-secondary { background: linear-gradient(135deg, #60a5fa, #3b82f6); color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px; letter-spacing: .3px; transition: transform .18s ease, box-shadow .28s ease, filter .2s ease; border: 0; }
.btn-secondary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }
.btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; font-weight: 800; border-radius: 10px; padding: 8px 14px; letter-spacing: .3px; transition: transform .18s ease, box-shadow .28s ease, filter .2s ease; border: 0; }
.btn-danger:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.22); }

/* Tabla */
.table-dark { border-collapse: separate; border-spacing: 0; }
.table-dark thead { background-color: #3e3e57; color: #ffffff; text-transform: uppercase; font-weight: 800; letter-spacing: .4px; }
.table-dark th,.table-dark td { border-bottom: 1px solid rgba(255,255,255,0.08); padding: 12px 16px; text-align: center; }
.table-dark tbody tr { background-color: #2c2c3e; transition: background-color .18s ease; }
.table-dark tbody tr:hover { background-color: #3a3a50; }

/* Vista previa grande */
.playera-preview { position: relative; width: 100%; max-width: 420px; aspect-ratio: 3/4; margin-inline: auto; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 30px rgba(0,0,0,0.35); background: #0b1245; }
.playera-base { position: absolute; inset: 0; background: radial-gradient(120% 80% at 50% 0%, rgba(255,0,0,0.18), transparent 60%), radial-gradient(70% 50% at 50% 100%, rgba(255,255,255,0.06), transparent 60%), #0b1245; background-size: cover; background-position: center; }

/* Galería */
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
.gal-item { display: flex; flex-direction: column; align-items: center; }

/* Miniatura Preview */
.thumb-playera { position: relative; width: 120px; height: 160px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.12); background: #0b1245; box-shadow: 0 4px 12px rgba(0,0,0,.25); }
.thumb-base { position: absolute; inset: 0; background: radial-gradient(100% 70% at 50% 0%, rgba(255,0,0,0.22), transparent 60%), radial-gradient(60% 40% at 50% 100%, rgba(255,255,255,0.08), transparent 60%), #0b1245; background-size: cover; background-position: center; }
.thumb-sello { position: absolute; display: grid; place-items: center; border-radius: 5px; border-width: 2px; outline: 2px solid transparent; box-shadow: 0 4px 10px rgba(0,0,0,.28); right: 6px; bottom: 6px; width: 40px; height: 40px; }
.thumb-sello__text { font-weight: 900; font-size: 16px; line-height: 1; }

/* Sello grande */
.marca-agua { position: absolute; display: grid; place-items: center; border-radius: 6px; border-width: 3px; outline: 3px solid transparent; box-shadow: 0 6px 18px rgba(0,0,0,.35); user-select: none; }
.marca-agua__sello { font-weight: 900; font-size: clamp(18px, 2.2vw, 26px); letter-spacing: 1px; line-height: 1; }
</style>
