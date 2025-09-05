<template>
  <div class="product-form-container-dark">
    <div class="max-w-6xl mx-auto">
      <!-- Encabezado -->
      <div class="product-card-dark overflow-hidden mb-6">
        <div class="product-title-dark">
          <h1 class="text-h5 text-white m-0">PESTAÑA MOLDES</h1>
        </div>
        <div class="p-6">
          <p class="text-gray-200 text-lg m-0">
            Administra moldes por talla/posición. Adjunta el archivo y añádelo a la lista del perfil.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ======= LADO IZQUIERDO: FORM ======= -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark">
            <h2 class="text-h5 text-white m-0">Añadir/Editar Moldes</h2>
          </div>

          <div class="p-6">
            <!-- Nombre Talla -->
            <div class="mb-4">
              <label class="block mb-2 text-white font-semibold">Nombre Talla</label>
              <input
                v-model.trim="form.nombreTalla"
                placeholder="talla4XL"
                class="product-input w-full"
              />
            </div>

            <!-- Tipo de molde -->
            <div class="mb-4">
              <label class="block mb-2 text-white font-semibold">Tipo de molde</label>
              <select v-model="form.tipoMolde" class="product-input w-full">
                <option disabled value="">Selecciona…</option>
                <option value="camisas">camisas</option>
                <option value="mangas">mangas</option>
                <option value="shorts">shorts</option>
              </select>
            </div>

            <!-- Perfil Molde -->
            <div class="mb-4">
              <label class="block mb-2 text-white font-semibold">Perfil Molde</label>
              <select v-model="form.perfil" class="product-input w-full">
                <option v-for="p in perfiles" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>

            <!-- Posición (depende del tipo) -->
            <div class="mb-4">
              <label class="block mb-2 text-white font-semibold">Posición</label>
              <select v-model="form.posicion" class="product-input w-full">
                <option disabled value="">Selecciona…</option>
                <option v-for="opt in posOptions" :key="opt" :value="opt">{{ opt }}</option>
              </select>
            </div>

            <!-- Archivo + botón Adjuntar -->
            <div class="mb-6">
              <label class="block mb-2 text-white font-semibold">Archivo</label>
              <div class="flex gap-3">
                <input
                  :value="fileLabel"
                  class="product-input flex-1"
                  placeholder="Ningún archivo seleccionado"
                  readonly
                />
                <input
                  ref="fileInput"
                  type="file"
                  accept=".svg,.png,.jpg,.jpeg,.pdf"
                  class="hidden"
                  @change="onFileChange"
                />
                <button class="product-btn-secondary" @click="fileInput?.click()">
                  Adjuntar archivo
                </button>
              </div>
              <p class="text-xs text-gray-300 mt-2">Formatos permitidos: SVG, PNG, JPG, PDF</p>
            </div>

            <!-- BOTONES (estilo Escalator: Añadir / Eliminar / Adjuntar ya está arriba) -->
            <div class="flex items-center gap-3">
              <button class="product-btn-primary" @click="handleAdd" :disabled="saving">
                {{ saving ? 'Guardando…' : 'Añadir' }}
              </button>
              <button class="product-btn-danger" @click="handleDelete" :disabled="!selectedRow">
                Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- ======= LADO DERECHO: TABLA ======= -->
        <div class="product-card-dark overflow-hidden">
          <div class="product-title-dark flex items-center justify-between">
            <h2 class="text-h5 text-white m-0">
              Moldes del perfil: <span class="font-bold">{{ form.perfil }}</span>
            </h2>
          </div>

          <div class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full product-table-escalator w-full">
                <thead>
                  <tr>
                    <th class="w-1/3">Nombre Talla</th>
                    <th class="w-1/3">Tipo Molde</th>
                    <th class="w-1/3">Posición</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-if="filtrados.length === 0">
                    <td colspan="3" class="text-center py-6 text-gray-300">
                      No hay moldes para este perfil.
                    </td>
                  </tr>

                  <tr
                    v-for="(row, i) in filtrados"
                    :key="row.id || i"
                    :class="[{ active: isSelected(row) }]"
                    @click="selectRow(row)"
                  >
                    <td>{{ row.nombreTalla }}</td>
                    <td>{{ row.tipoMolde }}</td>
                    <td class="capitalize">{{ row.posicion }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="p-4 text-right">
              <span class="text-gray-300 text-sm">Total: {{ filtrados.length }}</span>
            </div>
          </div>
        </div>
      </div> <!-- /grid -->
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useMoldesStore } from '@/stores/moldes'

/* ===== Store ===== */
const store = useMoldesStore()
// Estructura “simple” de cada fila, para que se parezca a la app de escritorio:
// { id, nombreTalla, tipoMolde, perfil, posicion, archivoUrl? }
const items = computed(() => store.items ?? [])

/* ===== Form ===== */
const perfiles = ['generic'] // si tienes más, agrégalos aquí o cárgalos del backend
const form = reactive({
  nombreTalla: '',
  tipoMolde: '',
  perfil: 'generic',
  posicion: '',
  archivo: null,
})

const saving = ref(false)
const fileInput = ref(null)
const selectedRow = ref(null)

const fileLabel = computed(() => form.archivo?.name ?? '')
const posOptions = computed(() => {
  switch (form.tipoMolde) {
    case 'camisas': return ['delante', 'espalda']
    case 'mangas':  return ['izquierda', 'derecha']
    case 'shorts':  return ['izquierda', 'derecha']
    default:        return []
  }
})
const filtrados = computed(() =>
  items.value.filter(r => (r.perfil || 'generic') === (form.perfil || 'generic'))
)

function onFileChange(e) {
  const f = e.target.files?.[0]
  if (!f) return
  if (!/\.(svg|png|jpe?g|pdf)$/i.test(f.name)) {
    alert('Formato no permitido. Usa SVG/PNG/JPG/PDF.')
    e.target.value = ''
    return
  }
  form.archivo = f
}

function resetForm(keepPerfil = true) {
  const perfil = keepPerfil ? form.perfil : 'generic'
  Object.assign(form, {
    nombreTalla: '',
    tipoMolde: '',
    perfil,
    posicion: '',
    archivo: null,
  })
  if (fileInput.value) fileInput.value.value = ''
}

function isSelected(row) {
  return selectedRow.value && (selectedRow.value.id ?? selectedRow.value) === (row.id ?? row)
}
function selectRow(row) {
  selectedRow.value = row
  // precargar en el form por si quiere editar
  form.nombreTalla = row.nombreTalla
  form.tipoMolde   = row.tipoMolde
  form.perfil      = row.perfil || 'generic'
  form.posicion    = row.posicion
}

async function handleAdd() {
  if (!form.nombreTalla.trim()) return alert('Escribe el nombre de la talla.')
  if (!form.tipoMolde)          return alert('Elige el tipo de molde.')
  if (!form.posicion)           return alert('Elige la posición.')
  if (!form.archivo)            return alert('Adjunta un archivo.')

  const payload = {
    nombreTalla: form.nombreTalla.trim(),
    tipoMolde: form.tipoMolde,
    perfil: form.perfil || 'generic',
    posicion: form.posicion,
    archivo: form.archivo, // File para subir
  }

  saving.value = true
  try {
    // Soporta tu store actual: si tienes createMoldeSimple úsalo,
    // si no, cae a addOne (compat), si tampoco existe, guarda local.
    if (typeof store.createMoldeSimple === 'function') {
      await store.createMoldeSimple(payload)
    } else if (typeof store.addOne === 'function') {
      await store.addOne(payload)
    } else {
      // Fallback local (sin backend): solo inserta en memoria
      const url = URL.createObjectURL(form.archivo)
      store.items = [...(store.items || []), { id: Date.now(), ...payload, archivoUrl: url }]
    }
    resetForm()
  } catch (e) {
    console.error(e)
    alert('No se pudo guardar el molde.')
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!selectedRow.value) return
  if (!confirm(`¿Eliminar "${selectedRow.value.nombreTalla}"?`)) return
  try {
    if (typeof store.deleteOne === 'function') {
      await store.deleteOne(selectedRow.value.id)
    } else if (typeof store.deleteMoldeSimple === 'function') {
      await store.deleteMoldeSimple(selectedRow.value.id)
    } else {
      // Fallback local
      store.items = (store.items || []).filter(x => x !== selectedRow.value)
    }
    selectedRow.value = null
  } catch (e) {
    console.error(e)
    alert('No se pudo eliminar.')
  }
}

onMounted(() => {
  if (typeof store.fetchAll === 'function') store.fetchAll()
})
</script>

<style scoped>
/* === contenedor y cards (tu mismo diseño) === */
.product-form-container-dark {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  min-height: 100vh;
  padding: 48px 16px;
}
.product-card-dark {
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}
.product-title-dark {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: white;
  padding: 18px 24px;
  font-weight: 700;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* === inputs y botones (tus clases) === */
.product-input {
  background-color: #3e3e57;
  border: 1px solid #4f4f72;
  color: #f1f5f9;
  border-radius: 10px;
  padding: 12px 14px;
  outline: none;
  transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease;
}
.product-input:focus {
  border-color: #8b8bd6;
  box-shadow: 0 0 0 3px rgba(139,139,214,0.35);
  transform: translateY(-1px);
}
.product-btn-primary{
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px;
}
.product-btn-secondary{
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px;
}
.product-btn-danger{
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color:#fff; font-weight: 800; border-radius: 10px; padding: 10px 18px;
}

/* === tabla look “Escalator Plus” pero dark === */
.product-table-escalator {
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}
.product-table-escalator thead tr {
  background-color: #1a96ad; /* franja turquesa como la captura */
  color: #fff;
  text-transform: none;
}
.product-table-escalator th,
.product-table-escalator td {
  padding: 10px 14px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: left;
}
.product-table-escalator tbody tr {
  background-color: #2c2c3e;
  transition: background-color .18s ease;
}
.product-table-escalator tbody tr:hover {
  background-color: #3a3a50;
}
.product-table-escalator tbody tr.active {
  background-color: #445; /* selección */
  outline: 2px solid rgba(255,255,255,0.12);
}
</style>
