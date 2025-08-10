<template>
  <v-container fluid class="font-manager-container">
    <v-theme-provider theme="dark">
      <!-- Título -->
      <v-row justify="center" class="mb-6">
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-title-gradient">
              <h2 class="m-0 text-white text-h5">Perfil de las fuentes</h2>
            </div>
            <div class="p-4">
              <p class="m-0 text-gray-300">
                Administra tus fuentes (TTF/OTF) y sus perfiles para letras y contornos.
              </p>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Acciones fuentes -->
      <v-row justify="center" class="mb-4">
        <v-col cols="12" md="10" lg="8" class="d-flex ga-3">
          <v-btn class="btn-primary text-none" variant="flat" rounded="lg" size="large" @click="uploadFuente">
            Subir fuente
          </v-btn>
          <v-btn class="btn-danger text-none" variant="flat" rounded="lg" size="large"
                 :disabled="!fuenteSeleccionada?.length"
                 @click="eliminarFuente">
            Eliminar fuente
          </v-btn>
        </v-col>
      </v-row>

      <!-- Tabla de fuentes -->
      <v-row justify="center" class="mb-8">
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-format-font</v-icon>
              Fuentes cargadas
            </div>
            <div class="table-wrapper">
              <v-data-table
                v-model="fuenteSeleccionada"
                show-select
                :headers="headersFuente"
                :items="fuentes"
                item-value="id"
                density="comfortable"
                class="table-dark elevation-8"
              />
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Acciones perfiles -->
      <v-row justify="center" class="mb-2">
        <v-col cols="12" md="10" lg="8" class="d-flex">
          <v-btn class="btn-danger text-none" variant="flat" rounded="lg" size="large"
                 :disabled="!perfilSeleccionado?.length"
                 @click="eliminarPerfil">
            Eliminar perfil de fuente
          </v-btn>
        </v-col>
      </v-row>

      <!-- Tabla de perfiles -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-view-list</v-icon>
              Perfiles de fuente
            </div>
            <div class="table-wrapper">
              <v-data-table
                v-model="perfilSeleccionado"
                show-select
                :headers="headersPerfil"
                :items="perfiles"
                item-value="id"
                density="comfortable"
                class="table-dark elevation-8"
              />
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Input oculto (realmente oculto) -->
      <input ref="fileInput" type="file" accept=".ttf,.otf" style="display:none" @change="handleFile" />
    </v-theme-provider>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

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

const fuentes = ref([])
const perfiles = ref([])

const fuenteSeleccionada = ref([])
const perfilSeleccionado = ref([])

const fileInput = ref(null)

const fetchFuentes = async () => {
  const res = await axios.get('/api/fuentes')
  fuentes.value = res.data
}

const fetchPerfiles = async () => {
  const res = await axios.get('/api/perfiles')
  perfiles.value = res.data
}

const uploadFuente = () => {
  fileInput.value?.click()
}

const handleFile = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('fuente', file)

  try {
    await axios.post('/api/fuentes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    await fetchFuentes()
  } catch (err) {
    console.error('Error subiendo fuente:', err)
    alert('Error al subir fuente.')
  }

  if (fileInput.value) fileInput.value.value = ''
}

const eliminarFuente = async () => {
  if (!fuenteSeleccionada.value.length) return
  const id = fuenteSeleccionada.value[0].id
  if (!confirm('¿Eliminar esta fuente?')) return

  try {
    await axios.delete(`/api/fuentes/${id}`)
    await fetchFuentes()
    fuenteSeleccionada.value = []
  } catch (err) {
    console.error('Error eliminando fuente:', err)
    alert('Error al eliminar fuente.')
  }
}

const eliminarPerfil = async () => {
  if (!perfilSeleccionado.value.length) return
  const id = perfilSeleccionado.value[0].id
  if (!confirm('¿Eliminar este perfil de fuente?')) return

  try {
    await axios.delete(`/api/perfiles/${id}`)
    await fetchPerfiles()
    perfilSeleccionado.value = []
  } catch (err) {
    console.error('Error eliminando perfil:', err)
    alert('Error al eliminar perfil.')
  }
}

onMounted(() => {
  fetchFuentes()
  fetchPerfiles()
})
</script>

<style scoped>
/* Fondo general con degradado oscuro */
.font-manager-container {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  min-height: 100vh;
  padding: 40px 16px;
}

/* Tarjeta oscura estilo glass */
.card-dark {
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

/* Título principal con degradado cálido */
.card-title-gradient {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: #fff;
  padding: 18px 24px;
  font-weight: 800;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}

/* Subtítulo de cada bloque */
.card-subtitle {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 18px;
  font-weight: 700;
  color: #fff;
  background: rgba(255,255,255,0.06);
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* Botones con gradiente */
.btn-primary {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #fff !important;
  font-weight: 800;
  border-radius: 12px;
  padding: 10px 18px;
  letter-spacing: .3px;
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: #fff !important;
  font-weight: 800;
  border-radius: 12px;
  padding: 10px 18px;
  letter-spacing: .3px;
}

/* Wrapper de tablas */
.table-wrapper {
  padding: 10px 12px 16px;
}

/* Estilo oscuro para v-data-table (Vuetify 3) */
.table-dark :deep(.v-table) {
  background-color: #2c2c3e !important;
  color: #e5e7eb !important;
}

.table-dark :deep(.v-table thead) {
  background-color: #3e3e57 !important;
  color: #ffffff !important;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: .4px;
}

.table-dark :deep(th),
.table-dark :deep(td) {
  border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}

.table-dark :deep(tbody tr:hover) {
  background-color: #3a3a50 !important;
}

/* Paginación y footer de la tabla */
.table-dark :deep(.v-data-table-footer) {
  background: rgba(255,255,255,0.04) !important;
  color: #e5e7eb !important;
  border-top: 1px solid rgba(255,255,255,0.08) !important;
}

.table-dark :deep(.v-pagination .v-btn),
.table-dark :deep(.v-field),
.table-dark :deep(.v-select .v-field) {
  background: #3e3e57 !important;
  color: #fff !important;
  border-radius: 10px !important;
}

/* Estado "no data" */
.table-dark :deep(.v-data-table__empty-wrapper),
.table-dark :deep(.v-data-table__no-data) {
  color: #cbd5e1 !important;
}

/* Checkboxes */
.table-dark :deep(.v-selection-control .v-icon),
.table-dark :deep(.v-checkbox .v-icon) {
  color: #93c5fd !important;
}
</style>
