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
                      <template #bottom></template> </v-data-table-virtual>
                  </v-card-text>
                </v-card>
              </v-expand-transition>

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

              <v-card-actions class="d-flex justify-space-between">
                <v-btn
                  color="secondary"
                  variant="elevated"
                  size="large"
                  prepend-icon="mdi-refresh"
                  @click="resetForm"
                >
                  Restablecer
                </v-btn>
                <v-btn
                  type="submit"
                  color="primary"
                  variant="elevated"
                  size="large"
                  prepend-icon="mdi-file-pdf-box"
                  :loading="submitting"
                >
                  Generar PDF
                </v-btn>
              </v-card-actions>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { usePdfSettingsStore } from '@/stores/pdfSettings';
import axios from 'axios';
import * as XLSX from 'xlsx'; // Importa la librería XLSX

const store = usePdfSettingsStore();
const { fileName, jpegQuality, imageRes, colorImageRes, grayImageRes, compressPdf } = storeToRefs(store);

onMounted(() => { store.init(); });

const formRef = ref(null);
const submitting = ref(false);
const excelFile = ref(null);

// Nuevas variables para almacenar los datos de la vista previa
const previewData = ref({
  headers: [],
  rows: [],
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

// Autocompleta nombre desde Excel y genera la vista previa
watch(excelFile, (val) => {
  const file = getFirstFile(val);
  if (!file || !file.name) {
    fileName.value = ''; // Limpia el nombre si no hay archivo
    previewData.value = { headers: [], rows: [] }; // Limpia la vista previa
    return;
  }
  
  const base = sanitizePdfName(file.name);
  if (!base) return;
  if (!fileName.value || fileName.value === '') fileName.value = base;

  // Lógica para leer y generar la vista previa
  const reader = new FileReader();
  reader.onload = (e) => {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: 'array' });
    const firstSheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[firstSheetName];
    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

    if (json.length > 0) {
      const headers = json[0].map(h => ({ title: h, value: h, sortable: false }));
      const rows = json.slice(1).map(row => {
        const item = {};
        headers.forEach((header, index) => {
          item[header.value] = row[index];
        });
        return item;
      });

      previewData.value.headers = headers;
      // Muestra solo las primeras 10 filas para no sobrecargar el navegador
      previewData.value.rows = rows.slice(0, 10);
    } else {
      previewData.value = { headers: [], rows: [] };
    }
  };
  reader.readAsArrayBuffer(file);
}, { immediate: false });

async function parseAxiosBlobError(error) {
  // ... (el resto de tu función, no necesita cambios)
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
  // ... (el resto de tu función, no necesita cambios)
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
    if (!file) {
      alert('Debes subir un archivo Excel.');
      submitting.value = false;
      return;
    }
    formData.append('excelFile', file);
    formData.append('download', '1'); // descarga directa

    const apiUrl = 'http://127.0.0.1:8000/api/generate-pdf';

    const response = await axios.post(apiUrl, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = finalPdfName;
    document.body.appendChild(a);
    a.click(); a.remove();
    window.URL.revokeObjectURL(url);

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

const handleFormSubmit = async () => {
  const result = await formRef.value?.validate();
  if (result?.valid) await submitPdfSettings();
  else alert('Por favor, corrige los campos marcados antes de continuar.');
};

const resetForm = () => {
  store.reset();
  excelFile.value = null;
  // Limpia la vista previa al restablecer el formulario
  previewData.value = { headers: [], rows: [] };
  formRef.value?.resetValidation?.();
  alert('Formulario de configuración restablecido.');
};
</script>

<style scoped>
/* Agrega estos estilos a tu sección <style scoped> */
.preview-card {
  border-left: 5px solid #4CAF50; /* Un color que combine con el diseño */
  transition: all 0.3s ease-in-out;
}
.preview-table {
  background-color: #f9f9f9;
  border-radius: 8px;
}
</style>