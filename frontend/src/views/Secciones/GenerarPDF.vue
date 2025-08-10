<template>
  <v-container class="pdf-settings-container" fluid>
    <v-row justify="center">
      <v-col cols="12" md="6">
        <h2 class="text-h4 mb-6 text-center">Configuración del PDF</h2>

        <v-form @submit.prevent="handleFormSubmit">
          <v-text-field
            v-model="fileName"
            label="Nombre del archivo PDF"
            variant="outlined"
            class="mb-4"
            required
            :rules="[v => !!v || 'El nombre es obligatorio']"
          />

          <v-text-field
            v-model.number="jpegQuality"
            label="Calidad JPEG"
            type="number"
            variant="outlined"
            class="mb-4"
            :rules="[v => (v >= 0 && v <= 100) || 'Debe ser entre 0 y 100']"
            suffix="%"
          />

          <v-text-field
            v-model.number="imageRes"
            label="Resolución de Imagen"
            type="number"
            variant="outlined"
            class="mb-4"
            suffix="dpi"
          />

          <v-text-field
            v-model.number="colorImageRes"
            label="Resolución Color"
            type="number"
            variant="outlined"
            class="mb-4"
            suffix="dpi"
          />

          <v-text-field
            v-model.number="grayImageRes"
            label="Resolución en Gris"
            type="number"
            variant="outlined"
            class="mb-4"
            suffix="dpi"
          />

          <v-switch
            v-model="compressPdf"
            label="Comprimir PDF"
            color="primary"
            class="mb-6"
            inset
          />

          <v-row justify="end" class="gap-2">
            <v-btn color="secondary" @click="resetForm">
              Restablecer
            </v-btn>
            <v-btn type="submit" color="primary">
              Generar PDF
            </v-btn>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePdfSettingsStore } from '@/stores/pdfSettings'

// Store
const store = usePdfSettingsStore()
const {
  fileName,
  jpegQuality,
  imageRes,
  colorImageRes,
  grayImageRes,
  compressPdf
} = storeToRefs(store)

onMounted(() => {
  // Inicializa leyendo de localStorage (solo front)
  store.init()
})

const handleFormSubmit = () => {
  // Aquí seguimos en front: solo mostramos/guardamos
  console.log('Configuración enviada:', store.settings)
  store.saveToStorage()
  alert('¡Configuración guardada! Se puede iniciar la generación del PDF.')
  // Más adelante: aquí llamarías a tu servicio/API con store.settings
}

const resetForm = () => {
  store.reset()
}
</script>

<style scoped>
.pdf-settings-container {
  padding-top: 60px;
  background-color: #f0f2f5;
}
.gap-2 { gap: 8px; }
</style>
