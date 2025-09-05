<!-- src/views/PerfilTipografia.vue -->
<template>
  <v-container fluid class="font-view">
    <v-theme-provider theme="dark">
      <div class="xp-panel">
        <h2 class="xp-title">Perfil de las fuentes</h2>

        <!-- ====== EDITOR (al estilo de "Diseño") ====== -->
        <div class="builder-card">
          <div class="builder-header">Crear un perfil de la Fuente:</div>

          <div class="builder-grid">
            <!-- Columna izquierda: controles -->
            <div class="left">
              <div class="lbl-strong mb-2">{{ fuenteSeleccionadaNombre || 'Selecciona una fuente' }}</div>

              <v-select
                v-model="fuenteId"
                :items="fuenteOptions"
                item-title="title"
                item-value="value"
                label="Fuente"
                hide-details
                class="xp-input"
                variant="outlined"
                density="comfortable"
              />

              <v-textarea
                v-model="texto"
                rows="5"
                auto-grow
                label="Texto de ejemplo"
                hide-details
                class="xp-input"
                variant="outlined"
                density="comfortable"
                placeholder="SOFT&#10;ESCALATOR&#10;PLUS"
              />

              <div class="row">
                <label class="lbl">Color letra</label>
                <input type="color" v-model="colorLetra" class="colorbox" />
                <v-text-field v-model="colorLetra" class="xp-input flex-1" hide-details variant="outlined" />
                <v-btn class="xp-btn" height="36" @click="pegar('#letra')">pegar</v-btn>
              </div>

              <div class="row">
                <label class="lbl">Color contorno</label>
                <input type="color" v-model="colorContorno" class="colorbox" />
                <v-text-field v-model="colorContorno" class="xp-input flex-1" hide-details variant="outlined" />
                <v-btn class="xp-btn" height="36" @click="pegar('#contorno')">pegar</v-btn>
              </div>

              <div class="row">
                <label class="lbl">Tamaño contorno</label>
                <v-select
                  v-model="contornoMm"
                  :items="contornos"
                  hide-details
                  class="xp-input w-44"
                  variant="outlined"
                  density="comfortable"
                />
              </div>

              <!-- Paleta (igual que en Diseño) -->
              <div class="lbl-strong mt-2 mb-1">Paleta</div>
              <div class="paleta">
                <button
                  v-for="(c, i) in palette"
                  :key="i"
                  class="swatch"
                  :style="{ backgroundColor: c }"
                  :title="c"
                  @click="applyFromPalette(c)"
                />
                <v-spacer />
                <v-btn size="x-small" variant="outlined" color="error" class="text-none" @click="palette = []">
                  Limpiar
                </v-btn>
              </div>

              <v-text-field
                v-model="perfilNombre"
                label="Nombre del perfil"
                hide-details
                class="xp-input"
                variant="outlined"
                density="comfortable"
              />

              <div class="buttons">
                <v-btn class="xp-save" height="40" :loading="saving" @click="guardarPerfil">
                  Guardar Perfil Fuente
                </v-btn>
              </div>
            </div>

            <!-- Columna derecha: Preview grande -->
            <div class="right">
              <div class="preview">
                <div class="preview__text" :style="previewStyle" v-html="textoHtml" />
              </div>
            </div>
          </div>
        </div>

        <!-- ====== ACCIONES Y TABLAS (estilo Escalator Plus) ====== -->
        <div class="xp-layout">
          <!-- Columna de acciones -->
          <div class="xp-actions">
            <v-btn class="xp-btn" height="36" @click="uploadFuente">Subir Fuente</v-btn>
            <v-btn class="xp-btn xp-btn-danger" height="36"
                   :disabled="!hasFuenteSelected || loading.fuentes"
                   @click="eliminarFuente">
              Eliminar Fuente
            </v-btn>

            <v-divider class="my-6" />

            <v-btn class="xp-btn xp-btn-danger" height="36"
                   :disabled="!hasPerfilSelected || loading.perfiles"
                   @click="eliminarPerfil">
              Eliminar Perfil de fuente
            </v-btn>
          </div>

          <!-- Columna de tablas -->
          <div class="xp-tables">
            <!-- Fuentes -->
            <div class="xp-table-card">
              <div class="table-title">Fuentes cargadas</div>
              <v-data-table
                v-model="fuenteSeleccionada"
                :return-object="true"
                show-select
                :headers="headersFuente"
                :items="fuentes"
                :loading="loading.fuentes"
                item-value="id"
                density="compact"
                class="xp-table elevation-4"
                fixed-header
                height="210"
                hide-default-footer
                :items-per-page="-1"
              >
                <template #item.path="{ item }">
                  <span class="xp-path" :title="item.path">{{ item.path }}</span>
                </template>
              </v-data-table>
            </div>

            <!-- Perfiles -->
            <div class="xp-table-card">
              <div class="table-title">Perfiles de fuente</div>
              <v-data-table
                v-model="perfilSeleccionado"
                :return-object="true"
                show-select
                :headers="headersPerfil"
                :items="perfiles"
                :loading="loading.perfiles"
                item-value="id"
                density="compact"
                class="xp-table elevation-4"
                fixed-header
                height="230"
                hide-default-footer
                :items-per-page="-1"
              >
                <template #item.cont="{ item }">
                  <span>{{ item.cont ? `${item.cont} mm` : '—' }}</span>
                </template>
              </v-data-table>
            </div>
          </div>
        </div>
      </div>

      <!-- input oculto para subir fuentes -->
      <input ref="fileInput" type="file" accept=".ttf,.otf" style="display:none" @change="handleFile" />
    </v-theme-provider>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api.js'

/* ===== Columnas de tablas ===== */
const headersFuente = [
  { title: 'Nombre Fuente', key: 'nombre' },
  { title: 'Path Fuente',   key: 'path' },
]
const headersPerfil = [
  { title: 'Perfil Fuente', key: 'perfil' },
  { title: 'Fuente',        key: 'fuente' },
  { title: 'cont.',         key: 'cont' },
  { title: 'dir. letra',    key: 'letra' },
  { title: 'dir contorno',  key: 'contorno' },
]

/* ===== Estado listas ===== */
const fuentes = ref([])
const perfiles = ref([])
const fuenteSeleccionada = ref([])
const perfilSeleccionado = ref([])
const loading = ref({ fuentes: false, perfiles: false })
const fileInput = ref(null)

const fuenteOptions = computed(() => fuentes.value.map(f => ({ title: f.nombre, value: f.id })))
const hasFuenteSelected = computed(() => fuenteSeleccionada.value.length > 0)
const hasPerfilSelected  = computed(() => perfilSeleccionado.value.length > 0)

/* ===== Editor (diseño dentro del perfil) ===== */
const fuenteId = ref(null)
const perfilNombre = ref('')
const texto = ref('SOFT\nESCALATOR\nPLUS')
const textoHtml = computed(() => (texto.value || '').replace(/\n/g, '<br/>'))

const colorLetra = ref('#ffffff')
const colorContorno = ref('#2b2b2b')
const contornos = [0, 0.5, 1, 1.5, 2, 3, 4, 5].map(v => ({ title: v ? `${v} mm` : 'Sin contorno', value: v }))
const contornoMm = ref(1)
const palette = ref(['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b'])
function applyFromPalette(c){ colorLetra.value = c }

/* font preview con FontFace si la API da url */
const fuenteSeleccionadaNombre = computed(() => (fuentes.value.find(f => f.id === fuenteId.value)?.nombre) || '')
const cssFamily = ref(null)
async function ensureFontFace(){
  const f = fuentes.value.find(x => x.id === fuenteId.value)
  if (!f?.url) { cssFamily.value = null; return }
  try{
    const fam = `CustomFont_${f.id}`
    const ff = new FontFace(fam, `url("${f.url}")`)
    await ff.load()
    document.fonts.add(ff)
    cssFamily.value = fam
  }catch{ cssFamily.value = null }
}
watch(fuenteId, ensureFontFace)

/* mm -> px (96 dpi) */
const mmToPx = mm => Math.round((mm || 0) * 3.78)
const previewStyle = computed(() => {
  const px = mmToPx(contornoMm.value)
  const s = {
    color: colorLetra.value || '#ffffff',
    fontWeight: 800,
    lineHeight: 1.05,
    fontSize: '92px',
    letterSpacing: '2px',
    textAlign: 'left',
    fontFamily: cssFamily.value ? `"${cssFamily.value}", system-ui, -apple-system, Segoe UI, Roboto` : 'system-ui, -apple-system, Segoe UI, Roboto',
    WebkitTextStroke: px > 0 ? `${px}px ${colorContorno.value}` : '0 transparent',
    textShadow: px > 0
      ? `-${px}px 0 ${colorContorno.value}, ${px}px 0 ${colorContorno.value},
         0 ${px}px ${colorContorno.value}, 0 -${px}px ${colorContorno.value},
         -${px}px -${px}px ${colorContorno.value}, ${px}px -${px}px ${colorContorno.value},
         -${px}px ${px}px ${colorContorno.value}, ${px}px ${px}px ${colorContorno.value}`
      : 'none'
  }
  return s
})

async function pegar(which){
  try{
    const txt = (await navigator.clipboard.readText())?.trim()
    if (!/^#?[0-9a-f]{3,8}$/i.test(txt)) return alert('El portapapeles no contiene un color HEX válido.')
    const hex = txt.startsWith('#') ? txt : `#${txt}`
    if (which === '#letra') colorLetra.value = hex
    else colorContorno.value = hex
  }catch{ alert('No se pudo leer del portapapeles.') }
}

/* ===== API listas ===== */
const fetchFuentes = async () => {
  loading.value.fuentes = true
  try { const { data } = await api.get('/fuentes'); fuentes.value = Array.isArray(data) ? data : [] ; if (!fuenteId.value && fuentes.value[0]) fuenteId.value = fuentes.value[0].id }
  catch (e) { console.error(e); alert('No se pudieron cargar las fuentes.') }
  finally { loading.value.fuentes = false }
}
const fetchPerfiles = async () => {
  loading.value.perfiles = true
  try { const { data } = await api.get('/perfiles'); perfiles.value = Array.isArray(data) ? data : [] }
  catch (e) { console.error(e); alert('No se pudieron cargar los perfiles.') }
  finally { loading.value.perfiles = false }
}

/* ===== Guardar/Eliminar ===== */
const saving = ref(false)
async function guardarPerfil(){
  if (!perfilNombre.value.trim()) return alert('Escribe el nombre del perfil.')
  if (!fuenteId.value) return alert('Selecciona una fuente.')
  saving.value = true
  try{
    // Mapeo para tu backend: guardo HEX en letra_dir/contorno_dir para que aparezcan en la tabla.
    const payload = {
      perfil: perfilNombre.value.trim(),
      fuente_id: fuenteId.value,
      cont: contornoMm.value,
      letra_dir: colorLetra.value,
      contorno_dir: colorContorno.value
    }
    await api.post('/perfiles', payload)
    perfilNombre.value = ''
    await fetchPerfiles()
    alert('Perfil guardado ✅')
  }catch(e){ console.error(e); alert('No se pudo guardar el perfil.') }
  finally{ saving.value = false }
}

const uploadFuente = () => fileInput.value?.click?.()
const handleFile = async (e) => {
  const f = e.target?.files?.[0]; if (!f) return
  if (!/\.ttf$|\.otf$/i.test(f.name)) { alert('Selecciona un .ttf o .otf'); e.target.value=''; return }
  const fd = new FormData(); fd.append('fuente', f)
  try { await api.post('/fuentes', fd); await fetchFuentes() }
  catch (err) { console.error(err); alert('Error al subir la fuente.') }
  finally { e.target.value = '' }
}

const eliminarFuente = async () => {
  const ids = fuenteSeleccionada.value.map(f => f.id)
  if (!ids.length) return
  if (!confirm(`¿Eliminar ${ids.length} fuente(s)?`)) return
  try { await Promise.all(ids.map(id => api.delete(`/fuentes/${id}`))); await fetchFuentes(); fuenteSeleccionada.value = [] }
  catch (err) { console.error(err); alert('Error al eliminar fuente(s).') }
}

const eliminarPerfil = async () => {
  const ids = perfilSeleccionado.value.map(p => p.id)
  if (!ids.length) return
  if (!confirm(`¿Eliminar ${ids.length} perfil(es)?`)) return
  try { await Promise.all(ids.map(id => api.delete(`/perfiles/${id}`))); await fetchPerfiles(); perfilSeleccionado.value = [] }
  catch (err) { console.error(err); alert('Error al eliminar el/los perfil(es).') }
}

/* init */
onMounted(async () => {
  await fetchFuentes()
  await fetchPerfiles()
})
</script>

<style scoped>
/* Fondo general como tu app */
.font-view{
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  min-height: 100vh;
  padding: 30px 16px;
}
.xp-panel{
  max-width: 1120px;
  margin: 0 auto;
  background: #f7fbff;
  border: 2px solid #2aa0bf;
  border-radius: 6px;
  box-shadow: 0 8px 26px rgba(0,0,0,.35);
  padding: 18px 18px 24px;
}
.xp-title{
  text-align: center;
  font-weight: 800;
  color: #0a3350;
  margin: 6px 0 18px;
}

/* ====== EDITOR ====== */
.builder-card{
  border: 1px solid #c7d7e0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 16px;
  background: #fff;
}
.builder-header{
  padding: 10px 14px;
  font-weight: 800;
  color: #0a3350;
  border-bottom: 1px solid #c7d7e0;
  background: #eef7fb;
}
.builder-grid{
  display: grid;
  grid-template-columns: 380px 1fr;
  gap: 18px;
  padding: 14px;
}
.left{ display:flex; flex-direction:column; gap: 12px; }
.right{
  background:#d7d7d7; border:1px solid #b8b8b8; border-radius:4px;
  display:flex; align-items:center; justify-content:center; min-height: 420px;
}
.preview{ width:100%; height:100%; display:grid; place-items:center; padding: 10px; }
.preview__text{ white-space: pre-line; }

.lbl{ min-width:110px; color:#0a3350; font-weight:700; }
.lbl-strong{ color:#0a3350; font-weight:800; }
.row{ display:flex; align-items:center; gap:10px; }
.colorbox{ width:42px; height:36px; border-radius:6px; border:1px solid #9fb6c4; background:#fff; }

.xp-input :deep(.v-field){ background:#fff !important; }
.xp-btn{
  background: #e9f6fb !important;
  color: #093342 !important;
  border: 1px solid #7cc3d3 !important;
  border-radius: 4px !important;
  text-transform: none !important;
  font-weight: 700 !important;
  padding: 0 14px !important;
}
.xp-save{
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color:#fff !important; font-weight:800 !important; text-transform:none !important;
  border-radius:6px !important;
}
.buttons{ margin-top: 4px; }

/* Paleta */
.paleta{ display:flex; align-items:center; gap:8px; }
.swatch{ width:32px; height:32px; border-radius:8px; border:1px solid #c7d7e0; }

/* ====== LAYOUT TABLAS ====== */
.xp-layout{
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 16px;
  margin-top: 16px;
}
.xp-actions{
  display: flex; flex-direction: column; gap: 10px; align-items: flex-start;
}
.xp-btn-danger{
  background: #ffeaea !important;
  color: #7a0d0d !important;
  border-color: #f3aaaa !important;
}
.xp-tables{ display:flex; flex-direction:column; gap:16px; }
.xp-table-card{
  background: #fff;
  border: 1px solid #c7d7e0;
  border-radius: 4px;
  overflow: hidden;
}
.table-title{
  padding:8px 12px;
  background:#eef7fb;
  border-bottom:1px solid #c7d7e0;
  color:#0a3350; font-weight:800;
}

/* Estilo tabla Escalator */
.xp-table :deep(.v-table){ background:#fff !important; color:#0a1b2b !important; font-size:13px; }
.xp-table :deep(thead){ background:#1aaec0 !important; color:#fff !important; }
.xp-table :deep(th), .xp-table :deep(td){ border-bottom:1px solid #cfe3eb !important; }
.xp-table :deep(tbody tr:hover){ background:#eef8fb !important; }
.xp-table :deep(.v-selection-control .v-icon), .xp-table :deep(.v-checkbox .v-icon){ color:#0e81a0 !important; }

/* Path elíptico */
.xp-path{ display:inline-block; max-width:520px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

@media (max-width: 1100px){
  .builder-grid{ grid-template-columns: 1fr; }
  .xp-layout{ grid-template-columns: 1fr; }
  .xp-path{ max-width: 300px; }
}
</style>
