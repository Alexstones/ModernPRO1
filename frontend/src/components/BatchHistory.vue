<template>
  <v-container fluid class="hist-container">
    <v-row justify="center">
      <v-col cols="12" md="11" lg="10">

        <!-- Header -->
        <div class="card-dark overflow-hidden mb-6">
          <div class="card-title-gradient">
            <h2 class="m-0 text-white text-h5">Historial de Lotes de Impresión</h2>
          </div>
          <div class="p-4 text-gray-200">
            <div class="d-flex flex-wrap gap-3">
              <v-text-field v-model="q" variant="outlined" hide-details clearable
                            class="filter-field" label="Buscar por nombre…" prepend-inner-icon="mdi-magnify" />
              <v-select v-model="status" :items="statusOptions" variant="outlined" hide-details
                        class="filter-field" label="Estado" />
              <v-btn class="btn-secondary" @click="manualRefresh" :loading="store.loading">
                <v-icon start>mdi-refresh</v-icon> Actualizar
              </v-btn>
              <v-chip class="ms-auto" color="info" variant="flat" label>
                Activos: {{ store.activeCount }}
              </v-chip>
            </div>
          </div>
        </div>

        <!-- Tabla -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle d-flex align-center justify-space-between">
            <span class="font-bold">Lotes</span>
            <small class="text-gray-300">Total: {{ store.total }}</small>
          </div>

          <div class="table-wrapper">
            <v-data-table
              :headers="headers"
              :items="filtered"
              :loading="store.loading"
              item-key="id"
              density="comfortable"
              class="table-dark elevation-8"
            >
              <!-- Nombre -->
              <template #item.name="{ item }">
                <div class="d-flex align-center gap-2">
                  <v-icon size="18">mdi-file-document-outline</v-icon>
                  <div class="text-left">
                    <div class="font-bold">{{ item.name }}</div>
                    <div class="text-caption text-gray-400">
                      {{ formatDate(item.created_at) }}
                    </div>
                  </div>
                </div>
              </template>

              <!-- Estado -->
              <template #item.status="{ item }">
                <v-chip :color="statusColor(item.status)" size="small" label>
                  {{ statusLabel(item.status) }}
                </v-chip>
              </template>

              <!-- Progreso -->
              <template #item.progress="{ item }">
                <div>
                  <v-progress-linear
                    :model-value="progress(item)"
                    :color="progressColor(item)"
                    height="10"
                    rounded
                  />
                  <div class="text-caption text-gray-400 mt-1">
                    {{ item.items_done }}/{{ item.items_total }}
                    <span v-if="item.items_failed" class="text-error"> • {{ item.items_failed }} fallidos</span>
                  </div>
                </div>
              </template>

              <!-- Modo -->
              <template #item.merge_mode="{ item }">
                <v-chip size="small" color="primary" variant="tonal" label>
                  {{ item.merge_mode?.toUpperCase() }}
                </v-chip>
              </template>

              <!-- Agrupado -->
              <template #item.group_by="{ item }">
                <v-chip size="small" color="secondary" variant="tonal" label>
                  {{ item.group_by?.toUpperCase() }}
                </v-chip>
              </template>

              <!-- Excel normalizado -->
              <template #item.normalized="{ item }">
                <div class="d-flex gap-1 justify-center">
                  <v-btn
                    size="small"
                    variant="tonal"
                    prepend-icon="mdi-microsoft-excel"
                    :loading="isDownloading(item)"
                    :disabled="!item.source_excel_path || !item.mapping_json"
                    @click="downloadNormalized(item)"
                  >
                    Normalizado
                  </v-btn>
                  <v-btn
                    size="small"
                    icon
                    variant="text"
                    :loading="isDownloading(item)"
                    :disabled="!item.source_excel_path || !item.mapping_json"
                    @click="downloadNormalized(item, true)"
                    title="Regenerar normalizado"
                  >
                    <v-icon>mdi-refresh</v-icon>
                  </v-btn>
                </div>
              </template>

              <!-- Artefacto -->
              <template #item.artifact_path="{ item }">
                <v-btn size="small" :disabled="!item.artifact_path" class="btn-secondary"
                       @click="store.download(item.id)">
                  <v-icon start>mdi-download</v-icon> Descargar
                </v-btn>
              </template>

              <!-- Acciones -->
              <template #item.actions="{ item }">
                <div class="d-flex gap-2 justify-end">
                  <v-btn icon variant="text" @click="openDetail(item)">
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                  <v-btn icon variant="text" :disabled="!item.items_failed" @click="onRetry(item)">
                    <v-icon color="warning">mdi-reload-alert</v-icon>
                  </v-btn>
                </div>
              </template>

              <!-- Footer -->
              <template #bottom>
                <div class="d-flex align-center justify-space-between px-4 py-3">
                  <v-pagination v-model="page" :length="store.last" total-visible="7" />
                  <div class="text-gray-300 text-caption">
                    Página {{ store.page }} de {{ store.last }}
                  </div>
                </div>
              </template>
            </v-data-table>
          </div>
        </div>

      </v-col>
    </v-row>

    <!-- Drawer Detalle -->
    <v-navigation-drawer v-model="detailOpen" location="right" temporary width="520" class="drawer-dark">
      <div class="p-4">
        <div class="d-flex align-center justify-space-between mb-3">
          <div class="text-white text-h6">Detalle del Lote</div>
          <v-btn icon variant="text" @click="detailOpen = false"><v-icon>mdi-close</v-icon></v-btn>
        </div>

        <template v-if="detail">
          <div class="mb-3">
            <div class="text-h6 text-white">{{ detail.name }}</div>
            <div class="text-gray-300">{{ formatDate(detail.created_at) }}</div>
            <div class="mt-2">
              <v-chip :color="statusColor(detail.status)" label size="small">{{ statusLabel(detail.status) }}</v-chip>
              <v-chip class="ms-2" color="primary" label size="small">{{ (detail.merge_mode || '').toUpperCase() }}</v-chip>
              <v-chip class="ms-2" color="secondary" label size="small">{{ (detail.group_by || '').toUpperCase() }}</v-chip>
            </div>
          </div>

          <div class="mb-4">
            <div class="font-bold text-white mb-2">Progreso</div>
            <v-progress-linear :model-value="progress(detail)" :color="progressColor(detail)" height="12" rounded />
            <div class="text-caption text-gray-300 mt-1">
              {{ detail.items_done }}/{{ detail.items_total }}
              <span v-if="detail.items_failed" class="text-error"> • {{ detail.items_failed }} fallidos</span>
            </div>
          </div>

          <div class="mb-4">
            <div class="font-bold text-white mb-1">Mapping</div>
            <pre class="code-pre">{{ pretty(detail.mapping) }}</pre>
          </div>

          <div class="mb-4">
            <div class="font-bold text-white mb-1">Imposition</div>
            <pre class="code-pre">{{ pretty(detail.imposition) }}</pre>
          </div>

          <!-- Archivos del lote -->
          <div class="mb-4">
            <div class="font-bold text-white mb-2">Archivos</div>
            <div class="d-flex flex-wrap gap-2">
              <a
                v-if="detail.source_excel_path"
                :href="fileUrl(detail.source_excel_path)"
                target="_blank" rel="noopener"
                class="btn-secondary v-btn v-btn--elevated"
                style="padding:6px 12px;border-radius:10px;text-decoration:none"
              >
                <v-icon start>mdi-file-excel</v-icon> Excel fuente
              </a>

              <v-btn
                variant="tonal" color="success"
                :loading="isDownloading(detail)"
                :disabled="!detail.source_excel_path || !detail.mapping_json"
                @click="downloadNormalized(detail)"
              >
                <v-icon start>mdi-microsoft-excel</v-icon> Normalizado
              </v-btn>

              <v-btn
                variant="text" icon
                :loading="isDownloading(detail)"
                :disabled="!detail.source_excel_path || !detail.mapping_json"
                title="Regenerar normalizado"
                @click="downloadNormalized(detail, true)"
              >
                <v-icon>mdi-refresh</v-icon>
              </v-btn>

              <a
                v-if="detail.artifact_path"
                :href="fileUrl(detail.artifact_path)"
                target="_blank" rel="noopener"
                class="btn-secondary v-btn v-btn--elevated"
                style="padding:6px 12px;border-radius:10px;text-decoration:none"
              >
                <v-icon start>mdi-zip-box</v-icon> Artefacto
              </a>
            </div>
          </div>

          <div class="mb-4">
            <div class="font-bold text-white mb-2">Items</div>
            <div class="items-table">
              <table>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Número</th>
                    <th>Talle</th>
                    <th>Estado</th>
                    <th>PDF</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="it in detail.items" :key="it.id">
                    <td>{{ it.index }}</td>
                    <td>{{ it.payload?.NOMBRE }}</td>
                    <td>{{ it.payload?.NUMERO }}</td>
                    <td>{{ it.payload?.TALLE }}</td>
                    <td>
                      <v-chip :color="statusColor(it.status)" size="x-small" label>
                        {{ statusLabel(it.status) }}
                      </v-chip>
                      <v-tooltip v-if="it.error" location="top" activator="parent">
                        {{ it.error }}
                      </v-tooltip>
                    </td>
                    <td>
                      <a v-if="it.pdf_path" :href="fileUrl(it.pdf_path)" target="_blank" rel="noopener">
                        abrir
                      </a>
                      <span v-else>—</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="d-flex gap-2">
            <v-btn class="btn-secondary" :disabled="!detail.artifact_path" @click="store.download(detail.id)">
              <v-icon start>mdi-download</v-icon> Descargar artefacto
            </v-btn>
            <v-btn variant="tonal" color="warning" :disabled="!detail.items_failed" @click="onRetry(detail)">
              <v-icon start>mdi-reload-alert</v-icon> Reintentar fallidos
            </v-btn>
          </div>
        </template>

        <template v-else>
          <div class="text-gray-300">Cargando…</div>
        </template>
      </div>
    </v-navigation-drawer>
  </v-container>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue'
import axios from 'axios'
import { useBatchesStore } from '@/stores/batches'

const store = useBatchesStore()

const headers = [
  { title: 'Nombre', key: 'name', sortable: true },
  { title: 'Estado', key: 'status', sortable: true, width: 120 },
  { title: 'Progreso', key: 'progress', sortable: false, width: 240 },
  { title: 'Modo', key: 'merge_mode', sortable: false, width: 110 },
  { title: 'Agrupado', key: 'group_by', sortable: false, width: 110 },
  { title: 'Excel', key: 'normalized', sortable: false, width: 170 },     // ← NUEVA COLUMNA
  { title: 'Artefacto', key: 'artifact_path', sortable: false, width: 130 },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

const statusOptions = [
  { title: 'Todos', value: 'all' },
  { title: 'En cola', value: 'queued' },
  { title: 'Procesando', value: 'processing' },
  { title: 'Parcial', value: 'partial' },
  { title: 'Listo', value: 'done' },
  { title: 'Fallido', value: 'failed' },
]

const q = ref('')
const status = ref('all')
const page = ref(1)

watch(page, (p) => store.fetch(p))
watch(status, async (s) => { store.filter.status = s; page.value = 1; await store.fetch(1) })

const filtered = computed(() => {
  const qv = q.value.trim().toLowerCase()
  if (!qv) return store.list
  return store.list.filter(b => (b.name || '').toLowerCase().includes(qv))
})

const formatDate = (s) => {
  if (!s) return '—'
  const d = new Date(s)
  return isNaN(d) ? '—' : d.toLocaleString()
}

const progress = (b) => {
  const tot = b.items_total || 0
  if (!tot) return 0
  const pct = Math.round((b.items_done / tot) * 100)
  return Math.min(100, Math.max(0, pct))
}

const statusColor = (st) => {
  switch (st) {
    case 'queued': return 'grey'
    case 'processing': return 'info'
    case 'partial': return 'warning'
    case 'done': return 'success'
    case 'failed': return 'error'
    default: return 'default'
  }
}
const progressColor = (b) => statusColor(b.status)
const statusLabel = (st) => ({
  queued: 'En cola',
  processing: 'Procesando',
  partial: 'Parcial',
  done: 'Listo',
  failed: 'Fallido',
}[st] || st)

const manualRefresh = async () => store.fetch(page.value)

onMounted(async () => {
  await store.fetch(1)
  store.startPolling(2000) // polling cada 2s
})
onBeforeUnmount(() => store.stopPolling())

/* Drawer detalle */
const detailOpen = ref(false)
const detail = ref(null)

async function openDetail(batch) {
  detailOpen.value = true
  detail.value = null
  try { detail.value = await store.fetchOne(batch.id) } catch (e) { alert('No se pudo cargar el detalle') }
}

const pretty = (obj) => {
  try { return JSON.stringify(obj ?? {}, null, 2) } catch { return String(obj ?? '') }
}

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')
const fileUrl = (rel) => `${API.replace(/\/api$/, '')}/storage/${rel.replace(/^storage\//,'')}`

/* ==== Descarga de Excel normalizado ==== */
const downloading = ref({}) // { [id]: true }
const isDownloading = (b) => !!downloading.value[b.uuid || b.id]

async function downloadNormalized(batch, refresh = false) {
  const id = batch.uuid || batch.id
  downloading.value[id] = true
  try {
    const url = `${API}/print/normalized/${id}${refresh ? '?refresh=1' : ''}`
    const res = await axios.get(url, { responseType: 'blob' })
    const blob = new Blob([res.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const href = URL.createObjectURL(blob)
    const a = document.createElement('a')
    const base = (batch.file_name || batch.name || 'lote').replace(/[^A-Za-z0-9_\-]+/g,'_')
    a.href = href
    a.download = `${base}_normalized.xlsx`
    document.body.appendChild(a); a.click(); a.remove()
    URL.revokeObjectURL(href)
  } catch (e) {
    console.error(e)
    alert('No se pudo descargar el Excel normalizado.')
  } finally {
    delete downloading.value[id]
  }
}

async function onRetry(batch) {
  try {
    await store.retryFailed(batch.id)
    alert('Se reencolaron los fallidos (si el endpoint existe).')
    await manualRefresh()
    if (detailOpen.value) detail.value = await store.fetchOne(batch.id)
  } catch (e) {
    alert('El endpoint de reintento no está disponible (POST /print/batches/{id}/retry-failed).')
  }
}
</script>

<style scoped>
.hist-container { background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%); min-height: 100vh; padding: 32px 16px; }
.card-dark { border-radius: 16px; background: rgba(26,26,39,0.92); color: #e5e7eb; box-shadow: 0 10px 30px rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
.card-title-gradient { background: linear-gradient(45deg, #ff6b6b, #ffa500); color: #fff; padding: 18px 24px; font-weight: 800; border-top-left-radius: 16px; border-top-right-radius: 16px; }
.card-subtitle { padding: 14px 18px; font-weight: 700; color: #fff; background: rgba(255,255,255,0.06); border-bottom: 1px solid rgba(255,255,255,0.08); }
.table-wrapper { padding: 12px 12px 16px; }

.table-dark :deep(.v-table) { background-color: #2c2c3e !important; color: #e5e7eb !important; }
.table-dark :deep(thead) { background-color: #3e3e57 !important; color: #ffffff !important; }
.table-dark :deep(th), .table-dark :deep(td) { border-bottom: 1px solid rgba(255,255,255,0.08) !important; }

.filter-field :deep(.v-field) { background: #3e3e57 !important; color: #fff !important; border-radius: 10px; }
.btn-secondary { background: linear-gradient(135deg, #60a5fa, #3b82f6); color: #fff; font-weight: 800; border-radius: 10px; text-transform: none; }

.drawer-dark { background: rgba(26,26,39,0.98) !important; }
.code-pre {
  background: #111827; color: #e5e7eb; padding: 12px; border-radius: 8px; overflow: auto;
  border: 1px solid rgba(255,255,255,0.08); font-size: 12px; line-height: 1.3;
}
.items-table { overflow: auto; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; }
.items-table table { width: 100%; border-collapse: collapse; }
.items-table th, .items-table td { padding: 8px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; }
.items-table thead { background: #3e3e57; color: #fff; }
</style>
