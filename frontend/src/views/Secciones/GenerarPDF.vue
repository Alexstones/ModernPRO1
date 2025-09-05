<template>
  <v-container class="pdf-settings-container-colorful" fluid>
    <v-row justify="center">
      <!-- IZQUIERDA: Formulario -->
      <v-col cols="12" md="6">
        <v-card class="pdf-settings-card-colorful">
          <v-card-title class="pdf-settings-title-colorful">
            <v-icon size="40" class="mr-3 text-white">mdi-cog-outline</v-icon>
            Configuración de PDF
          </v-card-title>

          <v-card-text>
            <v-form ref="formRef" @submit.prevent="handleFormSubmit">
              <v-text-field
                v-model="fileName"
                label="Nombre del archivo PDF"
                variant="outlined"
                class="mb-4 pdf-text-field"
                required
                :rules="[v => !!v || 'El nombre es obligatorio']"
              />

              <v-file-input
                v-model="excelFile"
                label="Subir archivo Excel"
                accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                prepend-icon="mdi-file-excel"
                variant="outlined"
                class="mb-2 pdf-text-field"
                show-size
                :rules="excelRules"
              />

              <!-- === NUEVO: Acciones de plantilla === -->
              <div class="d-flex flex-wrap ga-2 mb-4">
                <v-btn size="small" variant="tonal" prepend-icon="mdi-file-download" @click="downloadSampleExcel">
                  Descargar plantilla (.xlsx)
                </v-btn>
                <v-btn size="small" variant="tonal" prepend-icon="mdi-eye" @click="previewSample">
                  Ver ejemplo
                </v-btn>
                <v-btn size="small" variant="tonal" prepend-icon="mdi-file-plus" @click="useSampleAsInput">
                  Usar ejemplo en el input
                </v-btn>
              </div>

              <!-- === NUEVO: Ayuda de columnas esperadas === -->
              <v-alert type="info" variant="tonal" density="compact" class="mb-4">
                <div class="mb-1"><strong>Columnas esperadas en el Excel:</strong></div>
                <div class="d-flex flex-wrap ga-2">
                  <v-chip v-for="c in sampleColumns" :key="c" size="x-small" class="ma-0">{{ c }}</v-chip>
                </div>
              </v-alert>

              <!-- PREVIEW DEL EXCEL -->
              <v-expand-transition>
                <v-card v-if="previewData.headers.length" class="mt-2 preview-card" flat>
                  <v-card-title>
                    <v-icon class="mr-2">mdi-table</v-icon>
                    Vista Previa del Archivo Excel
                  </v-card-title>
                  <v-card-text>
                    <v-data-table-virtual
                      :headers="previewData.headers"
                      :items="previewData.rows"
                      class="preview-table"
                      :items-per-page="5"
                    >
                      <template #bottom></template>
                    </v-data-table-virtual>
                  </v-card-text>
                </v-card>
              </v-expand-transition>

              <!-- OPCIONES DE CALIDAD Y COMPRESIÓN -->
              <v-divider class="my-6" />
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="jpegQuality"
                    label="Calidad JPEG"
                    type="number"
                    variant="outlined"
                    class="mb-4 pdf-text-field"
                    :rules="[v => (v === null || (v >= 0 && v <= 100)) || 'Debe ser entre 0 y 100']"
                    suffix="%"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="imageRes"
                    label="Resolución de Imagen"
                    type="number"
                    variant="outlined"
                    class="mb-4 pdf-text-field"
                    suffix="dpi"
                  />
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="colorImageRes"
                    label="Resolución Color"
                    type="number"
                    variant="outlined"
                    class="mb-4 pdf-text-field"
                    suffix="dpi"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="grayImageRes"
                    label="Resolución en Gris"
                    type="number"
                    variant="outlined"
                    class="mb-4 pdf-text-field"
                    suffix="dpi"
                  />
                </v-col>
              </v-row>

              <v-switch
                v-model="compressPdf"
                label="Comprimir PDF para un tamaño más pequeño"
                color="primary"
                class="mb-6 pdf-switch"
                inset
              />

              <v-divider class="my-6" />

              <!-- Acciones -->
              <v-card-actions class="d-flex justify-space-between">
                <v-btn color="secondary" variant="elevated" size="large" prepend-icon="mdi-refresh" @click="resetForm">
                  Restablecer
                </v-btn>

                <div class="d-flex ga-3">
                  <v-btn
                    type="submit"
                    color="primary"
                    variant="elevated"
                    size="large"
                    prepend-icon="mdi-file-pdf-box"
                    :loading="submitting"
                  >
                    Generar PDF (simple)
                  </v-btn>

                  <v-btn
                    color="primary"
                    variant="tonal"
                    size="large"
                    prepend-icon="mdi-package-variant-closed"
                    :loading="submitting"
                    @click="submitBatch"
                  >
                    Generar Lote
                  </v-btn>

                  <v-btn
                    color="error"
                    variant="tonal"
                    size="large"
                    prepend-icon="mdi-stop"
                    :disabled="!submitting"
                    @click="stopGeneration"
                  >
                    Detener
                  </v-btn>
                </div>
              </v-card-actions>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- DERECHA: Panel de progreso/descargas + VISTA PREVIA PDF -->
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2">mdi-download</v-icon>
            Descargas
          </v-card-title>
          <v-card-text>
            <div class="text-medium-emphasis mb-2">Progreso</div>

            <v-progress-linear
              :model-value="progress"
              height="12"
              rounded
            />

            <div class="d-flex align-center justify-space-between mt-2">
              <span class="text-medium-emphasis">{{ statusText }}</span>
              <strong style="font-variant-numeric: tabular-nums">{{ percentText }}</strong>
            </div>

            <v-divider class="my-4" />

            <!-- Vista previa del PDF descargado -->
            <div class="text-medium-emphasis mb-2">Vista del PDF</div>

            <v-skeleton-loader
              v-if="previewLoading"
              type="image, image, image"
              class="mb-2"
            />
            <div v-else-if="previewThumbs.length" class="thumbs-grid">
              <div class="thumb" v-for="(src, idx) in previewThumbs" :key="idx">
                <v-img :src="src" class="rounded" cover />
                <div class="thumb-idx">Página {{ idx + 1 }}</div>
              </div>
            </div>
            <div v-else class="text-medium-emphasis">
              Se mostrará una vista previa cuando el PDF esté listo.
            </div>

            <v-divider class="my-4" />

            <div class="text-medium-emphasis mb-2">Archivos generados</div>
            <v-list density="comfortable" lines="two" v-if="downloads.length">
              <v-list-item
                v-for="(d, i) in downloads"
                :key="i"
                :title="d.name"
                :subtitle="`${formatBytes(d.size)} · ${new Date(d.when).toLocaleString()}`"
              >
                <template #prepend>
                  <v-icon>{{ d.type === 'zip' ? 'mdi-zip-box' : 'mdi-file-pdf-box' }}</v-icon>
                </template>
                <template #append>
                  <div class="d-flex ga-2">
                    <v-btn :href="d.url" :download="d.name" variant="text" prepend-icon="mdi-download">
                      Descargar
                    </v-btn>
                    <v-btn
                      v-if="d.type === 'pdf'"
                      variant="text"
                      prepend-icon="mdi-eye"
                      @click="showPreview(d)"
                    >
                      Ver
                    </v-btn>
                  </div>
                </template>
              </v-list-item>
            </v-list>
            <div v-else class="text-medium-emphasis">Aún no hay descargas.</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
/* Para la vista previa se usa pdfjs-dist (v3):
   npm i pdfjs-dist
*/
import { ref, onMounted, watch, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePdfSettingsStore } from '@/stores/pdfSettings';
import axios from 'axios';
import * as XLSX from 'xlsx';

const store = usePdfSettingsStore();
const { fileName, jpegQuality, imageRes, colorImageRes, grayImageRes, compressPdf } = storeToRefs(store);

onMounted(() => { store.init(); });

const formRef = ref(null);
const submitting = ref(false);
const excelFile = ref(null);

// === NUEVO: columnas y datos de ejemplo ===
const sampleColumns = [
  'Producto', 'Código', 'Talla', 'Color', 'Cantidad', 'Precio', 'Observaciones'
];
const sampleRows = [
  ['CAMISETA 701', 'CAM701', 'M', 'Azul', 12, 15.5, 'Urgente'],
  ['CAMISETA 701', 'CAM701', 'L', 'Blanco', 8, 15.5, ''],
  ['SHORT 210',    'SH210',  'S', 'Negro', 20, 11.9, 'Cliente VIP'],
];

// Construye Blob de la plantilla:
function buildSampleBlob() {
  const wb = XLSX.utils.book_new();
  const aoa = [sampleColumns, ...sampleRows];
  const ws = XLSX.utils.aoa_to_sheet(aoa);
  XLSX.utils.book_append_sheet(wb, ws, 'Plantilla');
  const buf = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
  return new Blob([buf], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
}

// Descarga la plantilla .xlsx
function downloadSampleExcel() {
  const blob = buildSampleBlob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'plantilla_modelpro.xlsx'; a.click();
  URL.revokeObjectURL(url);
}

// Carga el ejemplo sólo en la vista previa
function previewSample() {
  const headers = sampleColumns.map(h => ({ title: h, value: h, sortable: false }));
  const rows = sampleRows.map(r => {
    const item = {};
    sampleColumns.forEach((h, i) => (item[h] = r[i]));
    return item;
  });
  previewData.value.headers = headers;
  previewData.value.rows = rows.slice(0, 10);
  // También limpia el input para que se note que es “ejemplo”
  excelFile.value = null;
}

// Carga la plantilla en el input (como si la hubieras subido)
async function useSampleAsInput() {
  const blob = buildSampleBlob();
  const file = new File([blob], 'plantilla_modelpro.xlsx', { type: blob.type });
  excelFile.value = file; // dispara el watcher y se verá la preview real
  if (!fileName.value) fileName.value = 'plantilla_modelpro';
}

// === Progreso / descargas / cancelación / preview PDF ===
const progress = ref(0);             // 0..100
const statusText = ref('Listo para generar');
const percentText = computed(() => `${progress.value.toFixed(2)}%`);
const downloads = ref([]);           // {name, url, size, type: 'pdf'|'zip', when, blob?}
const abortCtrl = ref(null);
let fallbackTimer = null;

// preview PDF
const previewThumbs = ref([]);
const previewLoading = ref(false);

function startFallbackProgress() {
  clearInterval(fallbackTimer);
  fallbackTimer = setInterval(() => {
    progress.value = Math.min(95, progress.value + 1);
  }, 300);
}
function stopFallbackProgress() {
  clearInterval(fallbackTimer);
  fallbackTimer = null;
}
function resetProgress() {
  stopFallbackProgress();
  progress.value = 0;
  statusText.value = 'Listo para generar';
}
function stopGeneration() {
  if (abortCtrl.value) {
    abortCtrl.value.abort();
    submitting.value = false;
    statusText.value = 'Cancelado';
    stopFallbackProgress();
    progress.value = 0;
  }
}
function pushDownload({ blob, name, type }) {
  const urlForList = URL.createObjectURL(blob);
  downloads.value.unshift({
    name,
    url: urlForList,
    size: blob.size ?? 0,
    type,
    when: Date.now(),
    blob
  });
}
function formatBytes(bytes) {
  if (!bytes && bytes !== 0) return '';
  const k = 1024; const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${(bytes / Math.pow(k, i)).toFixed(2)} ${sizes[i]}`;
}

/* ============================
   pdfjs-dist v3 (core + worker)
   ============================ */
let pdfjsLib = null;
async function ensurePdfJs() {
  if (pdfjsLib) return pdfjsLib;
  const pdfjs = await import('pdfjs-dist');
  const workerUrl = (await import('pdfjs-dist/build/pdf.worker.js?url')).default;
  pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;
  pdfjsLib = pdfjs;
  return pdfjsLib;
}
async function renderPdfPreview(blob) {
  previewLoading.value = true;
  previewThumbs.value = [];
  try {
    const pdfjs = await ensurePdfJs();
    const data = await blob.arrayBuffer();
    const pdf = await pdfjs.getDocument({ data }).promise;
    const total = Math.min(pdf.numPages, 6);
    for (let i = 1; i <= total; i++) {
      const page = await pdf.getPage(i);
      const viewport = page.getViewport({ scale: 0.3 });
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      canvas.width = viewport.width; canvas.height = viewport.height;
      await page.render({ canvasContext: ctx, viewport }).promise;
      previewThumbs.value.push(canvas.toDataURL('image/png'));
    }
  } catch (err) {
    console.warn('No se pudo generar la vista previa del PDF:', err);
  } finally {
    previewLoading.value = false;
  }
}
async function showPreview(d) {
  try {
    const blob = d.blob || await fetch(d.url).then(r => r.blob());
    await renderPdfPreview(blob);
  } catch (e) {
    console.warn('Error al cargar vista previa:', e);
  }
}

// === Vista previa de Excel y filas completas ===
const previewData = ref({ headers: [], rows: [] });
const excelRowsFull = ref([]);

const excelRules = [
  v => !!v || 'Debes subir un archivo Excel',
  v => {
    const file = Array.isArray(v) ? v[0] : v;
    if (!file) return true;
    const okExt = /\.(xlsx|xls)$/i.test(file.name || '');
    const okMime = [
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ].includes(file.type || '');
    return (okExt || okMime) || 'El archivo debe ser .xlsx o .xls';
  }
];
const getFirstFile = (val) => (Array.isArray(val) ? val[0] : val) || null;
const sanitizePdfName = (name) =>
  (name || '')
    .replace(/\.[^.]+$/, '')
    .replace(/[<>:"/\\|?*\x00-\x1F]/g, '')
    .replace(/\s+/g, ' ')
    .trim();

// util: normaliza encabezados y los hace únicos
function normalizeHeaders(arr) {
  const out = [];
  const seen = new Map();
  arr.forEach((raw, i) => {
    let h = String(raw ?? '').trim();
    if (!h) h = `Col${i + 1}`;
    let base = h;
    let k = 1;
    while (seen.has(h)) { h = `${base}_${++k}`; }
    seen.set(h, true);
    out.push(h);
  });
  return out;
}

// Lee Excel y genera preview
watch(excelFile, (val) => {
  const file = getFirstFile(val);
  if (!file || !file.name) {
    fileName.value = '';
    previewData.value = { headers: [], rows: [] };
    excelRowsFull.value = [];
    return;
  }
  const base = sanitizePdfName(file.name);
  if (base && (!fileName.value || fileName.value === '')) fileName.value = base;

  const reader = new FileReader();
  reader.onload = (e) => {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: 'array' });
    const firstSheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[firstSheetName];
    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

    if (json.length > 0) {
      const headerTitles = normalizeHeaders(json[0]);
      const headers = headerTitles.map(h => ({ title: h, value: h, sortable: false }));
      const rows = json.slice(1).map(row => {
        const item = {};
        headerTitles.forEach((h, index) => { item[h] = row[index]; });
        return item;
      });

      excelRowsFull.value = rows;
      previewData.value.headers = headers;
      previewData.value.rows = rows.slice(0, 10);
    } else {
      excelRowsFull.value = [];
      previewData.value = { headers: [], rows: [] };
    }
  };
  reader.readAsArrayBuffer(file);
}, { immediate: false });

async function parseAxiosBlobError(error) {
  try {
    if (error?.response?.data instanceof Blob) {
      const text = await error.response.data.text();
      try {
        const json = JSON.parse(text);
        const msg = json?.message || (json?.errors && Object.values(json.errors).flat().join(' | '));
        return msg || text.slice(0, 500);
      } catch {
        return text?.slice(0, 500) || 'Error desconocido en servidor.';
      }
    }
  } catch {}
  return null;
}

// Config común para POST con progreso y cancelación
function buildAxiosConfig() {
  abortCtrl.value = new AbortController();
  progress.value = 0;
  statusText.value = 'Preparando…';
  startFallbackProgress();
  return {
    headers: { 'Content-Type': 'multipart/form-data' },
    responseType: 'blob',
    signal: abortCtrl.value.signal,
    onDownloadProgress: (e) => {
      if (e.total) {
        stopFallbackProgress();
        const pct = (e.loaded / e.total) * 100;progress.value = Math.max(0, Math.min(100, Number(pct.toFixed(2))));
statusText.value = progress.value < 100 ? 'Descargando…' : 'Completado';

      }
    }
  };
}

const submitPdfSettings = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    const safeBase = sanitizePdfName(fileName.value ?? '');
    const finalPdfName = safeBase.toLowerCase().endsWith('.pdf') ? safeBase : `${safeBase}.pdf`;
    formData.append('fileName', finalPdfName);

    if (jpegQuality.value !== null && jpegQuality.value !== undefined) formData.append('jpegQuality', String(jpegQuality.value));
    if (imageRes.value !== null && imageRes.value !== undefined) formData.append('imageRes', String(imageRes.value));
    if (colorImageRes.value !== null && colorImageRes.value !== undefined) formData.append('colorImageRes', String(colorImageRes.value));
    if (grayImageRes.value !== null && grayImageRes.value !== undefined) formData.append('grayImageRes', String(grayImageRes.value));
    formData.append('compressPdf', compressPdf.value ? '1' : '0');

    const file = getFirstFile(excelFile.value);
    if (!file) { alert('Debes subir un archivo Excel.'); submitting.value = false; return; }
    formData.append('excelFile', file);
    formData.append('download', '1');

    const apiUrl = 'http://127.0.0.1:8000/api/generate-pdf';
    const response = await axios.post(apiUrl, formData, buildAxiosConfig());

    stopFallbackProgress();
    progress.value = 100;
    statusText.value = 'Completado';

    const blob = new Blob([response.data], { type: 'application/pdf' });

    pushDownload({ blob, name: finalPdfName, type: 'pdf' });
    await renderPdfPreview(blob);

    const tempUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = tempUrl; a.download = finalPdfName;
    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(tempUrl);

    alert(`✅ PDF "${finalPdfName}" generado y descargado.`);
  } catch (error) {
    if (axios.isCancel?.(error) || error?.name === 'CanceledError') {
      alert('⚠️ Proceso cancelado.');
    } else {
      console.error('AxiosError ->', error);
      const parsed = await parseAxiosBlobError(error);
      alert('❌ ' + (parsed || error?.message || 'Error desconocido.'));
    }
  } finally {
    submitting.value = false;
    abortCtrl.value = null;
    if (progress.value === 95) progress.value = 100;
  }
};

const submitBatch = async () => {
  const result = await formRef.value?.validate();
  if (!result?.valid) { alert('Corrige los campos requeridos.'); return; }

  submitting.value = true;
  try {
    const formData = new FormData();
    const safeBase = sanitizePdfName(fileName.value ?? 'documento');
    const finalBase = safeBase.replace(/\.pdf$/i, '');
    const finalZip  = `${finalBase}.zip`;

    const file = getFirstFile(excelFile.value); if (!file) throw new Error('Falta Excel');

    formData.append('fileName', finalBase);
    formData.append('excelFile', file);
    formData.append('jpegQuality', String(jpegQuality.value ?? ''));
    formData.append('imageRes', String(imageRes.value ?? ''));
    formData.append('colorImageRes', String(colorImageRes.value ?? ''));
    formData.append('grayImageRes', String(grayImageRes.value ?? ''));
    formData.append('compressPdf', compressPdf.value ? '1' : '0');
    formData.append('download', '1');

    const apiUrl = 'http://127.0.0.1:8000/api/generate-pdf-batch';
    const response = await axios.post(apiUrl, formData, buildAxiosConfig());

    stopFallbackProgress();
    progress.value = 100;
    statusText.value = 'Completado';

    const ctype = response.headers['content-type'] || '';
    const isZip = ctype.includes('zip');
    const ext = isZip ? 'zip' : 'pdf';
    const fname = isZip ? finalZip : `${finalBase}.pdf`;
    const blob = new Blob([response.data], { type: ctype || (isZip ? 'application/zip' : 'application/pdf') });

    pushDownload({ blob, name: fname, type: ext });

    if (!isZip) {
      await renderPdfPreview(blob);
    } else {
      previewThumbs.value = [];
    }

    const tempUrl = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = tempUrl; a.download = fname;
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(tempUrl);

    alert(`✅ Archivo "${fname}" generado.`);
  } catch (e) {
    if (e?.name === 'CanceledError') {
      alert('⚠️ Proceso cancelado.');
    } else {
      const parsed = await parseAxiosBlobError(e);
      alert('❌ ' + (parsed || e?.message || 'Error desconocido.'));
    }
  } finally {
    submitting.value = false;
    abortCtrl.value = null;
  }
};

const handleFormSubmit = async () => {
  const result = await formRef.value?.validate();
  if (result?.valid) await submitPdfSettings();
  else alert('Por favor, corrige los campos marcados antes de continuar.');
};

const resetForm = () => {
  store.reset();
  excelFile.value = null;
  previewData.value = { headers: [], rows: [] };
  excelRowsFull.value = [];
  formRef.value?.resetValidation?.();
  resetProgress();
  previewThumbs.value = [];
  alert('Formulario de configuración restablecido.');
};
</script>

<style scoped>
.preview-card { border-left: 5px solid #4CAF50; transition: all .3s; }
.preview-table { background-color: #f9f9f9; border-radius: 8px; }

.pdf-settings-title-colorful {
  background: linear-gradient(90deg, #FF7A18, #AF002D 70%);
  color: white;
  border-top-left-radius: 12px; border-top-right-radius: 12px;
}
.pdf-settings-card-colorful {
  border-radius: 12px;
  overflow: hidden;
}

/* Grid de miniaturas del PDF */
.thumbs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 10px;
}
.thumb-idx {
  text-align: center;
  font-size: 12px;
  margin-top: 4px;
  opacity: .7;
}
</style>

