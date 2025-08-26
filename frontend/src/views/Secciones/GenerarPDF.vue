<template>
  <v-container class="pdf-settings-container-colorful" fluid>
    <v-row justify="center">
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
                class="mb-4 pdf-text-field"
                show-size
                :rules="excelRules"
              />

              <!-- PREVIEW DEL EXCEL -->
              <v-expand-transition>
                <v-card v-if="previewData.headers.length" class="mt-4 preview-card" flat>
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

              <!-- MAPEO DE COLUMNAS -->
              <v-card class="mt-6" variant="flat">
                <v-card-title>
                  <v-icon class="mr-2">mdi-format-list-bulleted</v-icon>
                  Mapeo de columnas → placeholders
                </v-card-title>
                <v-card-text>
                  <v-row>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="headerValues"
                        v-model="mapping.NOMBRE"
                        label="Columna → {{NOMBRE}}"
                        variant="outlined"
                        density="comfortable"
                        :disabled="!previewData.headers.length"
                        :rules="[v => !!v || 'Requerido']"
                      />
                    </v-col>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="headerValues"
                        v-model="mapping.NUMERO"
                        label="Columna → {{NUMERO}}"
                        variant="outlined"
                        density="comfortable"
                        :disabled="!previewData.headers.length"
                        :rules="[v => !!v || 'Requerido']"
                      />
                    </v-col>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="headerValues"
                        v-model="mapping.TALLE"
                        label="Columna → {{TALLE}}"
                        variant="outlined"
                        density="comfortable"
                        :disabled="!previewData.headers.length"
                        :rules="[v => !!v || 'Requerido']"
                      />
                    </v-col>
                  </v-row>

                  <!-- PEQUEÑA PREVIEW POR FILA MAPEADA -->
                  <v-data-table-virtual
                    class="mt-4"
                    :headers="[
                      { title: 'Fila', value: '__i', sortable: false },
                      { title: '{{NOMBRE}}', value: '__NOMBRE', sortable: false },
                      { title: '{{NUMERO}}', value: '__NUMERO', sortable: false },
                      { title: '{{TALLE}}',  value: '__TALLE',  sortable: false }
                    ]"
                    :items="mappedPreviewRows"
                    hide-default-footer
                  />
                </v-card-text>
              </v-card>

              <!-- PERFILES DE IMPOSICIÓN -->
              <v-card class="mt-6" variant="flat">
                <v-card-title>
                  <v-icon class="mr-2">mdi-view-grid</v-icon>
                  Perfiles de imposición
                </v-card-title>
                <v-card-text>
                  <v-row>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="[
                          { title: 'A4 1x1', value: 'A4_1x1' },
                          { title: 'A4 2x2', value: 'A4_2x2' },
                          { title: 'A4 2x3', value: 'A4_2x3' }
                        ]"
                        v-model="impose.profile"
                        label="Perfil"
                        variant="outlined"
                        density="comfortable"
                      />
                    </v-col>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="['A4', 'Letter']"
                        v-model="impose.sheet"
                        label="Hoja"
                        variant="outlined"
                        density="comfortable"
                      />
                    </v-col>
                    <v-col cols="12" sm="4">
                      <v-select
                        :items="[
                          { title: 'ZIP (varios PDFs)', value: 'zip' },
                          { title: 'PDF único (imposición)', value: 'single' }
                        ]"
                        v-model="impose.mergeMode"
                        label="Salida"
                        variant="outlined"
                        density="comfortable"
                      />
                    </v-col>
                  </v-row>

                  <v-row>
                    <v-col cols="6" sm="3">
                      <v-text-field v-model.number="impose.cols" label="Cols" type="number" variant="outlined" />
                    </v-col>
                    <v-col cols="6" sm="3">
                      <v-text-field v-model.number="impose.rows" label="Rows" type="number" variant="outlined" />
                    </v-col>
                    <v-col cols="6" sm="3">
                      <v-text-field v-model.number="impose.margin" label="Margen (mm)" type="number" variant="outlined" />
                    </v-col>
                    <v-col cols="6" sm="3">
                      <v-text-field v-model.number="impose.bleed" label="Sangrado (mm)" type="number" variant="outlined" />
                    </v-col>
                  </v-row>

                  <v-switch v-model="impose.cropMarks" color="primary" label="Marcas de corte" />
                </v-card-text>
              </v-card>

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

              <v-divider class="my-4" />

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
                </div>
              </v-card-actions>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
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

// === Vista previa de Excel y filas completas ===
const previewData = ref({ headers: [], rows: [] });
const excelRowsFull = ref([]);

// === Mapeo de columnas ===
const mapping = ref({ NOMBRE: null, NUMERO: null, TALLE: null });

// === Perfiles de imposición / salida ===
const impose = ref({
  mergeMode: 'zip',   // 'zip' | 'single'
  profile: 'A4_2x2',
  sheet: 'A4',
  cols: 2,
  rows: 2,
  margin: 10,  // mm
  bleed: 3,    // mm
  cropMarks: true
});

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

// heurística: autocompleta mapeo
function autoMapHeaders(headerTitles) {
  const norm = (s) => (s || '').toLowerCase().trim();
  const find = (alts) =>
    headerTitles.find((h) => alts.some((a) => norm(h).includes(a))) || null;

  mapping.value.NOMBRE = find(['nombre','name']);
  mapping.value.NUMERO = find(['numero','número','number','dorsal']);
  mapping.value.TALLE  = find(['talle','talla','size']);
}

const headerValues = computed(() => previewData.value.headers.map(h => h.value));

const mappedPreviewRows = computed(() => {
  return excelRowsFull.value.slice(0, 6).map((r, i) => ({
    __i: i + 1,
    __NOMBRE: mapping.value.NOMBRE ? r[mapping.value.NOMBRE] : '',
    __NUMERO: mapping.value.NUMERO ? r[mapping.value.NUMERO] : '',
    __TALLE:  mapping.value.TALLE  ? r[mapping.value.TALLE]  : '',
  }));
});

// Lee Excel y genera preview + autocompleta nombre + mapeo
watch(excelFile, (val) => {
  const file = getFirstFile(val);
  if (!file || !file.name) {
    fileName.value = '';
    previewData.value = { headers: [], rows: [] };
    excelRowsFull.value = [];
    mapping.value = { NOMBRE: null, NUMERO: null, TALLE: null };
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

      autoMapHeaders(headerTitles);
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
    const response = await axios.post(apiUrl, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = finalPdfName;
    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(url);

    alert(`✅ PDF "${finalPdfName}" generado y descargado.`);
  } catch (error) {
    console.error('AxiosError ->', {
      message: error?.message,
      status: error?.response?.status,
      statusText: error?.response?.statusText,
      url: error?.config?.url,
      headers: error?.response?.headers,
      dataType: Object.prototype.toString.call(error?.response?.data),
    });

    const parsed = await parseAxiosBlobError(error);
    if (parsed) { alert('❌ ' + parsed); }
    else if (error.response) {
      if (error.response.status === 404) alert('❌ Ruta no encontrada (404). Verifica /api/generate-pdf en Laravel.');
      else if (error.response.status === 422) alert('❌ Error de validación (422). Revisa que subes el Excel y el nombre.');
      else if (error.response.status === 413) alert('❌ Archivo demasiado grande (413). Aumenta límites del servidor.');
      else if (error.response.status === 419) alert('❌ CSRF/expirado (419). Usa routes/api.php para evitar CSRF.');
      else if (error.response.status >= 500) alert('❌ Error interno del servidor (5xx). Revisa logs en Laravel.');
      else alert('❌ Error del servidor: ' + (error.response.statusText || 'Inténtalo de nuevo.'));
    } else if (error.request) {
      alert('❌ Error de red: el servidor no respondió. ¿Laravel corre en 127.0.0.1:8000?');
    } else {
      alert('❌ Error al configurar la solicitud: ' + error.message);
    }
  } finally {
    submitting.value = false;
  }
};

const submitBatch = async () => {
  const result = await formRef.value?.validate();
  if (!result?.valid) { alert('Corrige los campos requeridos.'); return; }
  if (!mapping.value.NOMBRE || !mapping.value.NUMERO || !mapping.value.TALLE) {
    alert('Completa el mapeo de columnas.'); return;
  }

  submitting.value = true;
  try {
    const formData = new FormData();
    const safeBase = sanitizePdfName(fileName.value ?? 'documento');
    const finalBase = safeBase.replace(/\.pdf$/i, '');
    const finalZip  = `${finalBase}.zip`;

    const file = getFirstFile(excelFile.value); if (!file) throw new Error('Falta Excel');

    formData.append('fileName', finalBase);
    formData.append('excelFile', file);
    formData.append('mapping', JSON.stringify(mapping.value));
    formData.append('imposition', JSON.stringify(impose.value));
    formData.append('jpegQuality', String(jpegQuality.value ?? ''));
    formData.append('imageRes', String(imageRes.value ?? ''));
    formData.append('colorImageRes', String(colorImageRes.value ?? ''));
    formData.append('grayImageRes', String(grayImageRes.value ?? ''));
    formData.append('compressPdf', compressPdf.value ? '1' : '0');
    formData.append('download', '1');

    const apiUrl = 'http://127.0.0.1:8000/api/generate-pdf-batch';
    const response = await axios.post(apiUrl, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      responseType: 'blob',
    });

    const ctype = response.headers['content-type'] || '';
    const ext = ctype.includes('zip') ? 'zip' : 'pdf';
    const fname = ext === 'zip' ? finalZip : `${finalBase}.pdf`;

    const blob = new Blob([response.data], { type: ctype });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = fname;
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);

    alert(`✅ Archivo "${fname}" generado.`);
  } catch (e) {
    const parsed = await parseAxiosBlobError(e);
    alert('❌ ' + (parsed || e?.message || 'Error desconocido.'));
  } finally { submitting.value = false; }
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
  mapping.value = { NOMBRE: null, NUMERO: null, TALLE: null };
  formRef.value?.resetValidation?.();
  alert('Formulario de configuración restablecido.');
};
</script>

<style scoped>
.preview-card { border-left: 5px solid #4CAF50; transition: all .3s; }
.preview-table { background-color: #f9f9f9; border-radius: 8px; }

/* opcional: estiliza las cards */
.pdf-settings-title-colorful {
  background: linear-gradient(90deg, #FF7A18, #AF002D 70%);
  color: white;
  border-top-left-radius: 12px; border-top-right-radius: 12px;
}
.pdf-settings-card-colorful {
  border-radius: 12px;
  overflow: hidden;
}
</style>
