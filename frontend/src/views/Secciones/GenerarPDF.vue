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
            <v-form @submit.prevent="handleFormSubmit">
              <v-text-field
                v-model="fileName"
                label="Nombre del archivo PDF"
                variant="outlined"
                class="mb-4 pdf-text-field"
                required
                :rules="[v => !!v || 'El nombre es obligatorio']"
              />

              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="jpegQuality"
                    label="Calidad JPEG"
                    type="number"
                    variant="outlined"
                    class="mb-4 pdf-text-field"
                    :rules="[v => (v >= 0 && v <= 100) || 'Debe ser entre 0 y 100']"
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
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { usePdfSettingsStore } from '@/stores/pdfSettings';
import axios from 'axios'; // Importa Axios para las solicitudes HTTP

const store = usePdfSettingsStore();
const {
  fileName,
  jpegQuality,
  imageRes,
  colorImageRes,
  grayImageRes,
  compressPdf
} = storeToRefs(store);

onMounted(() => {
  store.init();
});

// Se cambia el nombre de la función para mayor claridad
const submitPdfSettings = async () => {
  try {
    // 1. Preparamos los datos del formulario
    const settings = {
      fileName: fileName.value,
      jpegQuality: jpegQuality.value,
      imageRes: imageRes.value,
      colorImageRes: colorImageRes.value,
      grayImageRes: grayImageRes.value,
      compressPdf: compressPdf.value
    };

    // 2. Definimos la URL de la API de Laravel
    const apiUrl = 'http://127.0.0.1:8000/api/generate-pdf'; // <-- ¡Cambia esta URL según tu configuración!

    // 3. Hacemos la solicitud POST a la API
    const response = await axios.post(apiUrl, settings);

    // 4. Manejamos una respuesta exitosa
    console.log('Respuesta del servidor:', response.data);
    alert('✅ ¡Configuración enviada y PDF en proceso de generación!');
    
    // Si la API devuelve un PDF, podrías manejar la descarga aquí
    // const fileUrl = response.data.fileUrl;
    // window.open(fileUrl, '_blank');

  } catch (error) {
    // 5. Manejamos los errores
    console.error('Error al enviar la configuración:', error);
    if (error.response) {
      // El servidor respondió con un estado fuera del rango 2xx
      console.error('Datos del error:', error.response.data);
      console.error('Código de estado:', error.response.status);
      alert('❌ Error del servidor: ' + (error.response.data.message || 'Inténtalo de nuevo.'));
    } else if (error.request) {
      // La solicitud se hizo pero no hubo respuesta
      alert('❌ Error de red: El servidor no respondió. Asegúrate de que el backend de Laravel está funcionando.');
    } else {
      // Algo pasó al configurar la solicitud
      alert('❌ Error al configurar la solicitud: ' + error.message);
    }
  }
};

const handleFormSubmit = () => {
  // En lugar de guardar en el almacenamiento, ahora llamamos a la función que se comunica con el backend
  submitPdfSettings();
};

const resetForm = () => {
  store.reset();
  alert('Formulario de configuración restablecido.');
};
</script>

<style scoped>
/* Tu CSS se mantiene sin cambios */
.pdf-settings-container-colorful {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  padding: 64px 16px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pdf-settings-card-colorful {
  border-radius: 16px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.pdf-settings-title-colorful {
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

/* 🎨 Estilos para los campos de texto con 'variant="outlined"' */
.pdf-text-field:deep(.v-field__input) {
  color: #000 !important;
  font-weight: 500;
}

.pdf-text-field:deep(.v-field__label) {
  color: #555 !important;
  opacity: 1 !important;
}

.pdf-text-field:deep(.v-field__outline) {
  border-color: #ddd;
  border-width: 2px;
  border-radius: 8px;
}

.pdf-text-field:deep(.v-field--variant-outlined) {
    background-color: white;
}

/* 🎨 Estilo para el mensaje de error */
.pdf-text-field:deep(.v-messages__message) {
  color: #e53935 !important;
  font-size: 0.8rem;
  font-weight: 600;
  margin-top: 4px;
}


/* Estilo para la etiqueta del switch en negro */
.pdf-switch:deep(.v-label) {
  font-weight: 600;
  color: #333 !important;
}

.v-btn {
  font-weight: 700;
  border-radius: 10px;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.v-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}
</style>