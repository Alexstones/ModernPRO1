<template>
  <v-container fluid class="product-form-container-dark">
    <v-row justify="center">
      <v-col cols="12" md="6">
        <v-card class="product-card-dark" elevation="10">
          <v-card-title class="product-title-dark">
            <v-icon class="me-2 text-white">mdi-tshirt-crew</v-icon>
            <h2 class="text-h5 text-white">Añadir/Editar Producto</h2>
          </v-card-title>
          <v-card-text>
            <v-form ref="productForm" @submit.prevent="handleProductSubmit">
              <v-text-field
                v-model="form.nombre"
                label="Nombre Producto"
                variant="outlined"
                class="mb-3 product-field-dark"
                :rules="[v => !!v || 'El nombre es obligatorio']"
              />

              <v-select
                v-model="form.tipo"
                :items="tiposProducto"
                label="Tipo de Producto"
                variant="outlined"
                class="mb-3 product-field-dark"
                :rules="[v => !!v || 'El tipo es obligatorio']"
              />

              <v-row v-for="campo in camposAdjuntos" :key="campo.model" class="mb-2" align="center">
                <v-col cols="12" sm="7">
                  <v-text-field
                    :model-value="form[campo.model] ? form[campo.model].name : 'Ningún archivo seleccionado'"
                    :label="campo.label"
                    readonly
                    variant="outlined"
                    class="product-field-dark"
                  />
                </v-col>
                <v-col cols="12" sm="5">
                  <v-btn color="secondary" class="product-btn" block @click="adjuntarArchivo(campo.model)">
                    <v-icon left>mdi-paperclip</v-icon> Adjuntar
                  </v-btn>
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
                variant="outlined"
                class="mb-3 product-field-dark"
              />
              <v-select
                v-model="form.ptfeNumero"
                :items="fuentes"
                label="Fuente Números"
                variant="outlined"
                class="mb-5 product-field-dark"
              />

              <v-row justify="end" class="gap-2">
                <v-btn color="secondary" class="product-btn" @click="resetForm">
                  <v-icon left>mdi-refresh</v-icon> Limpiar
                </v-btn>
                <v-btn type="submit" color="primary" class="product-btn">
                  <v-icon left>mdi-plus</v-icon>
                  {{ editingIndex === -1 ? 'Añadir Producto' : 'Actualizar Producto' }}
                </v-btn>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card class="product-card-dark" elevation="10">
          <v-card-title class="product-title-dark">
            <v-icon class="me-2 text-white">mdi-format-list-bulleted</v-icon>
            <h2 class="text-h5 text-white">Lista de Productos</h2>
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="productosStore.productos"
              class="elevation-1 product-table-dark"
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
                  class="product-table-icon-btn"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>
            <v-row justify="end" class="mt-4">
              <v-btn color="error" class="product-btn" @click="handleDeleteSelected" :disabled="!selectedProducts.length">
                <v-icon left>mdi-delete</v-icon> Eliminar seleccionados
              </v-btn>
            </v-row>
          </v-card-text>
        </v-card>
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
  if (productForm.value) {
    productForm.value.reset();
  }
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
.product-form-container-dark {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  padding: 64px 16px;
  min-height: 100vh;
}

.product-card-dark {
  border-radius: 16px;
  background-color: #2c2c3e;
  color: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.product-title-dark {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: white;
  padding: 24px;
  font-weight: 700;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.product-field-dark:deep(.v-label),
.product-field-dark:deep(.v-field__input) {
  color: #ccc !important;
  opacity: 1 !important;
}

.product-field-dark:deep(.v-field) {
  background-color: #3e3e57 !important;
  color: white !important;
  border-radius: 8px;
  border: 1px solid #4f4f72;
}

.product-field-dark:deep(.v-field__outline) {
  display: none;
}

.product-field-dark:deep(.v-select__selection) {
  color: white !important;
}

.product-btn {
  font-weight: 700;
  border-radius: 8px;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-btn.v-btn--active {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
}

.product-table-dark {
  background-color: #2c2c3e !important;
  border-radius: 12px;
}

.product-table-dark:deep(.v-table) {
  color: white;
  background-color: #2c2c3e !important;
}

.product-table-dark:deep(thead) {
  background-color: #3e3e57 !important;
  color: white !important;
}

.product-table-dark:deep(th) {
  color: white !important;
  font-weight: bold;
}

.product-table-dark:deep(tbody tr:hover) {
  background-color: #3e3e57 !important;
}

.product-table-dark:deep(tbody tr:hover .v-btn) {
  opacity: 1 !important;
}

.product-table-dark:deep(.v-btn) {
  opacity: 0.8;
}

.product-table-icon-btn {
  color: #ef5350;
}

.text-white {
  color: white !important;
}
</style>