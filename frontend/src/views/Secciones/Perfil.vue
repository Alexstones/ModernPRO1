<template>
  <v-container fluid class="product-form-container">
    <v-row>
      <v-col cols="12" md="6">
        <h2 class="text-h5 mb-4">Añadir/Editar Producto</h2>
        <v-form ref="productForm" @submit.prevent="handleProductSubmit">
          <v-text-field
            v-model="form.nombre"
            label="Nombre Producto"
            class="mb-3"
            :rules="[v => !!v || 'El nombre es obligatorio']"
          />

          <v-select
            v-model="form.tipo"
            :items="tiposProducto"
            label="Tipo de Producto"
            class="mb-3"
            :rules="[v => !!v || 'El tipo es obligatorio']"
          />

          <v-row v-for="campo in camposAdjuntos" :key="campo.model" class="mb-2" align="center">
            <v-col cols="7">
              <v-text-field
                :model-value="form[campo.model] ? form[campo.model].name : 'Ningún archivo seleccionado'"
                :label="campo.label"
                readonly
              />
            </v-col>
            <v-col cols="5">
              <v-btn color="primary" @click="adjuntarArchivo(campo.model)">Adjuntar</v-btn>
              <input
                type="file"
                :ref="el => fileInputs[campo.model] = el"
                style="display: none"
                @change="onFileSelected($event, campo.model)"
              />
            </v-col>
          </v-row>

          <v-select
            v-model="form.ptfeLetra"
            :items="fuentes"
            label="Fuente Letras"
            class="mb-3"
          />
          <v-select
            v-model="form.ptfeNumero"
            :items="fuentes"
            label="Fuente Números"
            class="mb-5"
          />

          <v-row justify="end" class="gap-2">
            <v-btn color="info" @click="resetForm">Limpiar</v-btn>
            <v-btn type="submit" color="success">{{ editingIndex === -1 ? 'Añadir Producto' : 'Actualizar Producto' }}</v-btn>
          </v-row>
        </v-form>
      </v-col>

      <v-col cols="12" md="6">
        <h2 class="text-h5 mb-4">Lista de Productos</h2>
        <v-data-table
          :headers="headers"
          :items="productosStore.productos"
          class="elevation-1"
          item-key="nombre"
          @click:row="editProduct"
          show-select
          v-model="selectedProducts"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              small
              @click.stop="productosStore.deleteProduct(item)"
              color="error"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
        <v-row justify="end" class="mt-4">
          <v-btn color="error" @click="handleDeleteSelected" :disabled="!selectedProducts.length">
            Eliminar seleccionados
          </v-btn>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useProductosStore } from '@/stores/productos';

const productosStore = useProductosStore();

const tiposProducto = ['camisas', 'pantalones', 'chaquetas'];
const fuentes = ['Adidas 2014 letras negras', 'Nike Blanco', 'Arial Bold'];

const defaultForm = {
  nombre: '',
  tipo: '',
  diseñoDelante: null,
  diseñoPosterior: null,
  modeloDelante: null,
  modeloPosterior: null,
  disenoMangaDer: null,
  disenoMangaIzq: null,
  ptfeLetra: '',
  ptfeNumero: ''
};

const productForm = ref(null);
const form = reactive({ ...defaultForm });
const editingIndex = ref(-1);
const selectedProducts = ref([]);
const fileInputs = ref({});

const headers = [
  { title: 'Nombre producto', key: 'nombre' },
  { title: 'Tipo producto', key: 'tipo' },
];
const camposAdjuntos = [
  { label: 'Diseño delante', model: 'diseñoDelante' },
  { label: 'Diseño posterior', model: 'diseñoPosterior' },
  { label: 'Modelo delante', model: 'modeloDelante' },
  { label: 'Modelo posterior', model: 'modeloPosterior' },
  { label: 'Dsg manga der.', model: 'disenoMangaDer' },
  { label: 'Dsg manga izq.', model: 'disenoMangaIzq' }
];

const handleProductSubmit = async () => {
  const { valid } = await productForm.value.validate();
  if (valid) {
    if (editingIndex.value > -1) {
      productosStore.updateProduct(editingIndex.value, { ...form });
    } else {
      productosStore.addProduct({ ...form });
    }
    resetForm();
  }
};

const resetForm = () => {
  Object.assign(form, defaultForm);
  editingIndex.value = -1;
  productForm.value.reset();
};

const editProduct = (event, { item, index }) => {
  Object.assign(form, { ...item });
  editingIndex.value = index;
};

const handleDeleteSelected = () => {
  productosStore.deleteSelectedProducts(selectedProducts.value);
  selectedProducts.value = [];
};

const adjuntarArchivo = (campo) => {
  fileInputs.value[campo].click();
};

const onFileSelected = (event, campo) => {
  const file = event.target.files[0];
  if (file) {
    form[campo] = file;
  }
};
</script>

<style scoped>
.product-form-container {
  padding: 30px;
  background-color: #f0f4f8;
  min-height: 100vh;
}

.gap-2 {
  gap: 8px; /* Espacio entre los botones */
}
</style>