<template>
  <v-container fluid class="font-manager-container">
    <v-theme-provider theme="dark">
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

      <v-row justify="center" class="mb-4">
        <v-col cols="12" md="10" lg="8" class="d-flex ga-3">
          <v-btn class="btn-primary text-none" variant="flat" rounded="lg" size="large" @click="uploadFuente">
            Subir fuente
          </v-btn>
          <v-btn
            class="btn-danger text-none"
            variant="flat"
            rounded="lg"
            size="large"
            :disabled="!hasFuenteSelected || loading.fuentes"
            @click="eliminarFuente"
          >
            Eliminar fuente
          </v-btn>
        </v-col>
      </v-row>

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
                :return-object="true"
                show-select
                :headers="headersFuente"
                :items="fuentes"
                :loading="loading.fuentes"
                item-value="id"
                density="comfortable"
                class="table-dark elevation-8"
              >
                <template #item.url="{ item }">
                  <a v-if="item.url" :href="item.url" target="_blank" rel="noopener noreferrer">Abrir</a>
                  <span v-else>—</span>
                </template>
                <template #item.created_at="{ item }">
                  <span>{{ formatDate(item.created_at) }}</span>
                </template>
              </v-data-table>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row justify="center" class="mb-6">
        <v-col cols="12" md="10" lg="8">
          <div class="card-dark overflow-hidden">
            <div class="card-subtitle">
              <v-icon size="20" class="mr-2">mdi-plus-box</v-icon>
              Crear perfil
            </div>
            <div class="p-4 d-flex flex-column ga-4">
              <v-text-field
                v-model="nuevoPerfil.perfil"
                label="Nombre del perfil"
                variant="outlined"
                density="comfortable"
                hide-details
                required
              />
              <v-select
                v-model="nuevoPerfil.fuente_id"
                :items="fuenteOptions"
                label="Fuente"
                item-title="title"
                item-value="value"
                variant="outlined"
                density="comfortable"
                hide-details
                :disabled="!fuentes.length"
                required
              />
              <v-text-field
                v-model.number="nuevoPerfil.cont"
                type="number"
                label="Cont (opcional)"
                variant="outlined"
                density="comfortable"
                hide-details
                min="0"
              />
              <v-text-field
                v-model="nuevoPerfil.letra_dir"
                label="Dir Letra (opcional)"
                variant="outlined"
                density="comfortable"
                hide-details
                placeholder="p. ej. letras/camiseta"
              />
              <v-text-field
                v-model="nuevoPerfil.contorno_dir"
                label="Dir Contorno (opcional)"
                variant="outlined"
                density="comfortable"
                hide-details
                placeholder="p. ej. contornos/camiseta"
              />
              <div class="d-flex ga-3">
                <v-btn
                  class="btn-primary text-none"
                  :loading="loading.crearPerfil"
                  :disabled="!puedeCrearPerfil"
                  @click="crearPerfil"
                >
                  Guardar perfil
                </v-btn>
                <v-btn variant="tonal" class="text-none" @click="resetPerfilForm">
                  Limpiar
                </v-btn>
              </div>
              <small class="text-gray-300">
                * Se enviarán <code>perfil</code>, <code>cont</code>, <code>fuente_id</code>, <code>letra_dir</code> y <code>contorno_dir</code>.
              </small>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-row justify="center" class="mb-2">
        <v-col cols="12" md="10" lg="8" class="d-flex">
          <v-btn
            class="btn-danger text-none"
            variant="flat"
            rounded="lg"
            size="large"
            :disabled="!hasPerfilSelected || loading.perfiles"
            @click="eliminarPerfil"
          >
            Eliminar perfil de fuente
          </v-btn>
        </v-col>
      </v-row>

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
                :return-object="true"
                show-select
                :headers="headersPerfil"
                :items="perfiles"
                :loading="loading.perfiles"
                item-value="id"
                density="comfortable"
                class="table-dark elevation-8"
              >
                <template #item.letra="{ item }">
                  <a v-if="item.letra" :href="item.letra" target="_blank" rel="noopener noreferrer">Ver</a>
                  <span v-else>—</span>
                </template>
                <template #item.contorno="{ item }">
                  <a v-if="item.contorno" :href="item.contorno" target="_blank" rel="noopener noreferrer">Ver</a>
                  <span v-else>—</span>
                </template>
              </v-data-table>
            </div>
          </div>
        </v-col>
      </v-row>

      <input ref="fileInput" type="file" accept=".ttf,.otf" style="display:none" @change="handleFile" />
    </v-theme-provider>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api.js'

const FILE_FIELD_NAME = 'fuente'

const headersFuente = [
  { title: 'Nombre Fuente', key: 'nombre' },
  { title: 'Path', key: 'path' },
  { title: 'URL', key: 'url' },
  { title: 'Creado', key: 'created_at' },
]

const headersPerfil = [
  { title: 'Perfil Fuente', key: 'perfil' },
  { title: 'Fuente', key: 'fuente' },
  { title: 'Cont', key: 'cont' },
  { title: 'Dir Letra', key: 'letra' },
  { title: 'Dir Contorno', key: 'contorno' },
]

const fuentes = ref([])
const perfiles = ref([])

const fuenteSeleccionada = ref([])
const perfilSeleccionado = ref([])

const fileInput = ref(null)
const loading = ref({ fuentes: false, perfiles: false, crearPerfil: false })

const nuevoPerfil = ref({
  perfil: '',
  fuente_id: null,
  cont: null,
  letra_dir: '',
  contorno_dir: '',
})

const fuenteOptions = computed(() => fuentes.value.map(f => ({ title: f.nombre, value: f.id })))
const puedeCrearPerfil = computed(() => !!nuevoPerfil.value.perfil && !!nuevoPerfil.value.fuente_id)

const hasFuenteSelected = computed(() => fuenteSeleccionada.value.length > 0)
const hasPerfilSelected = computed(() => perfilSeleccionado.value.length > 0)

const resetPerfilForm = () => {
  nuevoPerfil.value = { perfil: '', fuente_id: null, cont: null, letra_dir: '', contorno_dir: '' }
}

const showApiError = (err, fallback = 'Ocurrió un error') => {
  const msg =
    err?.response?.data?.message ||
    err?.response?.data?.error ||
    err?.message ||
    fallback
  alert(msg)
}

const formatDate = dt => {
  if (!dt) return '—'
  try {
    const d = new Date(dt)
    return isNaN(d.getTime()) ? '—' : d.toLocaleString()
  } catch { return '—' }
}

const fetchFuentes = async () => {
  loading.value.fuentes = true
  try {
    const res = await api.get('/fuentes')
    fuentes.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('fetchFuentes()', err)
    showApiError(err, 'No se pudieron cargar las fuentes.')
  } finally {
    loading.value.fuentes = false
  }
}

const fetchPerfiles = async () => {
  loading.value.perfiles = true
  try {
    const res = await api.get('/perfiles')
    perfiles.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('fetchPerfiles()', err)
    showApiError(err, 'No se pudieron cargar los perfiles.')
  } finally {
    loading.value.perfiles = false
  }
}

const uploadFuente = () => fileInput.value?.click()

const handleFile = async (event) => {
  const file = event.target?.files?.[0]
  if (!file) return
  const ok = /\.ttf$|\.otf$/i.test(file.name)
  if (!ok) {
    alert('Selecciona un archivo .ttf o .otf')
    if (fileInput.value) fileInput.value.value = ''
    return
  }
  const formData = new FormData()
  formData.append(FILE_FIELD_NAME, file)
  try {
    await api.post('/fuentes', formData)
    await fetchFuentes()
  } catch (err) {
    console.error('Error subiendo fuente:', err)
    showApiError(err, 'Error al subir la fuente.')
  } finally {
    if (fileInput.value) fileInput.value.value = ''
  }
}

const eliminarFuente = async () => {
  const ids = fuenteSeleccionada.value.map(f => f.id)
  if (!ids.length) return
  if (!confirm(`¿Eliminar ${ids.length} fuente(s)?`)) return

  try {
    await Promise.all(ids.map(id => api.delete(`/fuentes/${id}`)))
    await fetchFuentes()
    fuenteSeleccionada.value = []
  } catch (err) {
    console.error('Error eliminando fuente:', err)
    showApiError(err, 'Error al eliminar la(s) fuente(s).')
  }
}

const eliminarPerfil = async () => {
  const ids = perfilSeleccionado.value.map(p => p.id)
  if (!ids.length) return
  if (!confirm(`¿Eliminar ${ids.length} perfil(es)?`)) return

  try {
    await Promise.all(ids.map(id => api.delete(`/perfiles/${id}`)))
    await fetchPerfiles()
    perfilSeleccionado.value = []
  } catch (err) {
    console.error('Error eliminando perfil:', err)
    showApiError(err, 'Error al eliminar el/los perfil(es).')
  }
}

const crearPerfil = async () => {
  if (!puedeCrearPerfil.value) return
  loading.value.crearPerfil = true
  try {
    const payload = {
      perfil: nuevoPerfil.value.perfil,
      cont: nuevoPerfil.value.cont ?? null,
      fuente_id: nuevoPerfil.value.fuente_id,
      letra_dir: nuevoPerfil.value.letra_dir || null,
      contorno_dir: nuevoPerfil.value.contorno_dir || null,
    }
    await api.post('/perfiles', payload)
    resetPerfilForm()
    await fetchPerfiles()
  } catch (err) {
    console.error('crearPerfil()', err)
    showApiError(err, 'No se pudo crear el perfil.')
  } finally {
    loading.value.crearPerfil = false
  }
}

onMounted(async () => {
  await fetchFuentes()
  await fetchPerfiles()
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
.table-wrapper { padding: 10px 12px 16px; }

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