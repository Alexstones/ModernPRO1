<template>
  <v-container fluid class="font-manager-container">
    <v-row justify="center" class="mb-6">
      <h2 class="text-center font-weight-bold text-h4">Perfil de las fuentes</h2>
    </v-row>

    <v-row justify="start" class="mb-4" dense>
      <v-btn color="primary" class="mr-2" @click="handleUploadClick">Subir Fuente</v-btn>
      <v-btn color="error" :disabled="!fuenteSeleccionada.length" @click="handleEliminarFuente">Eliminar Fuente</v-btn>
    </v-row>

    <v-data-table
      v-model="fuenteSeleccionada"
      show-select
      :headers="headersFuente"
      :items="fuentesStore.fuentes"
      item-value="id"
      class="mb-8"
      dense
    />

    <v-row justify="start" class="mb-2">
      <v-btn color="error" :disabled="!perfilSeleccionado.length" @click="handleEliminarPerfil">Eliminar Perfil de fuente</v-btn>
    </v-row>

    <v-data-table
      v-model="perfilSeleccionado"
      show-select
      :headers="headersPerfil"
      :items="fuentesStore.perfiles"
      item-value="id"
      dense
    />

    <input ref="fileInput" type="file" accept=".ttf,.otf" class="hidden" @change="handleFile" />
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFuentesStore } from '@/stores/fuentes'

// Accede al store de Pinia
const fuentesStore = useFuentesStore()

// Encabezados de las tablas
const headersFuente = [
  { title: 'Nombre Fuente', key: 'nombre' },
  { title: 'Path Fuente', key: 'path' }
]

const headersPerfil = [
  { title: 'Perfil Fuente', key: 'perfil' },
  { title: 'Fuente', key: 'fuente' },
  { title: 'Cont', key: 'cont' },
  { title: 'Dir Letra', key: 'letra' },
  { title: 'Dir Contorno', key: 'contorno' }
]

// Estado local del componente
const fuenteSeleccionada = ref([])
const perfilSeleccionado = ref([])
const fileInput = ref(null)

const handleUploadClick = () => {
  fileInput.value.click()
}

const handleFile = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  await fuentesStore.uploadFuente(file)
  fileInput.value.value = ''
}

const handleEliminarFuente = async () => {
  if (!fuenteSeleccionada.value.length) return

  if (confirm('¿Eliminar las fuentes seleccionadas?')) {
    for (const id of fuenteSeleccionada.value) {
      await fuentesStore.eliminarFuente(id)
    }
    fuenteSeleccionada.value = []
  }
}

const handleEliminarPerfil = async () => {
  if (!perfilSeleccionado.value.length) return

  if (confirm('¿Eliminar los perfiles de fuente seleccionados?')) {
    for (const id of perfilSeleccionado.value) {
      await fuentesStore.eliminarPerfil(id)
    }
    perfilSeleccionado.value = []
  }
}

// Llamadas a la API al montar el componente para inicializar el estado
onMounted(() => {
  fuentesStore.fetchFuentes()
  fuentesStore.fetchPerfiles()
})
</script>

<style scoped>
.font-manager-container {
  background-color: #f8f8f8;
  min-height: 100vh;
  padding: 40px;
}
</style>