<template>
  <v-container fluid class="product-form-container">
    <v-row>
      <!-- Formulario -->
      <v-col cols="12" md="6">
        <v-text-field v-model="form.nombre" label="Nombre Producto" class="mb-3" />

        <v-select
          v-model="form.tipo"
          :items="tiposProducto"
          label="Tipo producto"
          class="mb-3"
        />

        <v-row v-for="campo in camposAdjuntos" :key="campo.model" class="mb-2" align="center">
          <v-col cols="7">
            <v-text-field
              v-model="form[campo.model]"
              :label="campo.label"
              readonly
            />
          </v-col>
          <v-col cols="5">
            <v-btn color="primary" @click="adjuntarArchivo(campo.model)">Adjuntar</v-btn>
          </v-col>
        </v-row>

        <!-- Selects de perfil -->
        <v-select
          v-model="form.ptfeLetra"
          :items="fuentes"
          label="Pfte letra"
          class="mb-3"
        />
        <v-select
          v-model="form.ptfeNumero"
          :items="fuentes"
          label="Pfte numero"
          class="mb-5"
        />

        <!-- Botones de acción -->
        <v-row justify="space-between">
          <v-btn color="success" @click="añadirProducto">Añadir producto</v-btn>
          <v-btn color="info" @click="limpiarCampos">Limpiar campos</v-btn>
        </v-row>
      </v-col>

      <!-- Tabla de productos -->
      <v-col cols="12" md="6">
        <v-row justify="end" class="mb-2">
          <v-btn color="error" @click="eliminarProducto">Eliminar producto</v-btn>
        </v-row>

        <v-data-table
          :headers="headers"
          :items="productos"
          class="elevation-1"
          dense
          hide-default-footer
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue';

const tiposProducto = ['camisas', 'pantalones', 'chaquetas'];
const fuentes = ['Adidas 2014 letras negras', 'Nike Blanco', 'Arial Bold'];

const form = ref({
  nombre: '',
  tipo: '',
  diseñoDelante: '',
  diseñoPosterior: '',
  modeloDelante: '',
  modeloPosterior: '',
  disenoMangaDer: '',
  disenoMangaIzq: '',
  ptfeLetra: '',
  ptfeNumero: ''
});

const camposAdjuntos = [
  { label: 'Diseño delante', model: 'diseñoDelante' },
  { label: 'Diseño posterior', model: 'diseñoPosterior' },
  { label: 'Modelo delante', model: 'modeloDelante' },
  { label: 'Modelo posterior', model: 'modeloPosterior' },
  { label: 'Dsg manga der.', model: 'disenoMangaDer' },
  { label: 'Dsg manga izq.', model: 'disenoMangaIzq' }
];

const productos = ref([
  { nombre: 'SET CAMISETA 701', tipo: 'camisas' },
  { nombre: 'CAMISETA D71', tipo: 'camisas' },
]);

const headers = [
  { title: 'Nombre producto', key: 'nombre' },
  { title: 'Tipo producto', key: 'tipo' }
];

const añadirProducto = () => {
  productos.value.push({
    nombre: form.value.nombre,
    tipo: form.value.tipo
  });
  limpiarCampos();
};

const limpiarCampos = () => {
  for (let key in form.value) {
    form.value[key] = '';
  }
};

const eliminarProducto = () => {
  productos.value.pop();
};

const adjuntarArchivo = (campo) => {
  form.value[campo] = 'archivo_adjunto.png'; // Simulado
};
</script>

<style scoped>
.product-form-container {
  padding: 30px;
  background-color: #f0f4f8;
  min-height: 100vh;
}
</style>
