<template>
  <v-container class="pdf-settings-container" fluid>
    <v-row justify="center">
      <v-col cols="12" md="6">
        <!-- Título o barra superior -->
        <v-text-field
          label="Nombre del archivo PDF"
          variant="outlined"
          class="mb-6"
        />

        <!-- Campos de configuración -->
        <v-text-field
          v-model="jpegQuality"
          label="Calidad JPEG"
          type="number"
          variant="outlined"
          class="mb-4"
        />

        <v-text-field
          v-model="imageRes"
          label="Resolución de Imagen"
          type="number"
          variant="outlined"
          class="mb-4"
        />

        <v-text-field
          v-model="colorImageRes"
          label="Resolución Color"
          type="number"
          variant="outlined"
          class="mb-4"
        />

        <v-text-field
          v-model="grayImageRes"
          label="Resolución en Gris"
          type="number"
          variant="outlined"
          class="mb-4"
        />

        <!-- Checkbox -->
        <v-checkbox
          v-model="compressPdf"
          label="Comprimir PDF"
          class="mb-6"
        />

        <!-- Botones -->
        <v-row justify="space-between">
          <v-btn color="primary" @click="submitSettings">Subir archivo</v-btn>
          <v-btn color="secondary" @click="clearForm">Claro</v-btn>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue';

const jpegQuality = ref(85);
const imageRes = ref(200);
const colorImageRes = ref(120);
const grayImageRes = ref(120);
const compressPdf = ref(true);

const submitSettings = () => {
  console.log('Configuración enviada');
};

const clearForm = () => {
  jpegQuality.value = 85;
  imageRes.value = 200;
  colorImageRes.value = 120;
  grayImageRes.value = 120;
  compressPdf.value = true;
};
</script>

<style scoped>
.pdf-settings-container {
  min-height: 100vh;
  padding-top: 60px;
  background-color: #f5f5f5;
}
</style>
