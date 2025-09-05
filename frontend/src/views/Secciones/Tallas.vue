<template>
  <div class="tallas-container text-white">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="card-dark overflow-hidden mb-8">
        <div class="card-title-gradient">
          <h2 class="m-0 text-white text-h5">
            Tallas de perfil de molde:
            <span class="font-bold">{{ form.perfil || 'generic' }}</span>
          </h2>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Formulario (mismo look) -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle"><span class="font-bold">Añadir Talla</span></div>

          <div class="p-6 grid gap-4">
            <div>
              <label class="block mb-1 text-sm text-white">Nombre talla</label>
              <input v-model="form.talle" class="product-input w-full" placeholder="tallaXL" />
            </div>

            <div>
              <label class="block mb-1 text-sm text-white">Tipo talla</label>
              <select v-model="form.categoria" class="product-input w-full">
                <option value="camisas">camisas</option>
                <option value="mangas">mangas</option>
                <option value="short">short</option>
              </select>
            </div>

            <!-- NUEVO: Perfil Molde editable -->
            <div>
              <label class="block mb-1 text-sm text-white">Perfil Molde</label>
              <input v-model="form.perfil" class="product-input w-full" placeholder="generic" />
              <small class="text-gray-300">Este texto también aparece arriba.</small>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block mb-1 text-sm text-white">Ancho Cm</label>
                <input v-model.number="form.ancho" type="number" min="0" step="0.01" class="product-input w-full" />
              </div>
              <div>
                <label class="block mb-1 text-sm text-white">Alto Cm</label>
                <input v-model.number="form.alto" type="number" min="0" step="0.01" class="product-input w-full" />
              </div>
            </div>

            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-primary" @click="handleAdd">Añadir</button>
              <button type="button" class="btn-danger" :disabled="!seleccion.size" @click="handleDelete">
                Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- TABLA con el look anterior -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle"><span class="font-bold">Tallas registradas</span></div>

          <div class="p-0">
            <div class="overflow-x-auto">
              <table class="table-dark w-full">
                <thead>
                  <tr>
                    <th style="width:42px"></th>
                    <th>Nombre Talla</th>
                    <th>Tipo Molde</th>
                    <th>ancho</th>
                    <th>alto</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-if="tallasStore.tallas.length === 0">
                    <td colspan="5" class="text-center py-6 text-gray-300">No hay datos</td>
                  </tr>

                  <tr v-for="row in tallasStore.tallas" :key="row.id" @click="toggleRow(row)" class="cursor-pointer">
                    <td>
                      <input type="checkbox" :checked="seleccion.has(row.id)" @change.stop="toggleRow(row)" />
                    </td>
                    <td class="mono">{{ row.talle }}</td>
                    <td class="mono">{{ row.categoria }}</td>
                    <td>
                      <input
                        class="product-input cell-input"
                        type="number" min="0" step="0.01"
                        v-model.number="row.ancho"
                        @blur="saveRow(row)" @keydown.enter.prevent="saveRow(row)"
                      />
                    </td>
                    <td>
                      <input
                        class="product-input cell-input"
                        type="number" min="0" step="0.01"
                        v-model.number="row.alto"
                        @blur="saveRow(row)" @keydown.enter.prevent="saveRow(row)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="p-4 text-right">
              <span class="text-gray-300 text-sm">Total: {{ tallasStore.tallas.length }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTallasStore } from '@/stores/tallas'

const tallasStore = useTallasStore()

// estado del form
const form = ref({
  talle: '',
  categoria: 'camisas',
  perfil: 'generic',
  ancho: null,
  alto:  null
})

// selección simple por id (para eliminar)
const seleccion = ref(new Set())

function toggleRow(row){
  const s = new Set(seleccion.value)
  if (s.has(row.id)) s.delete(row.id); else s.add(row.id)
  seleccion.value = s
}

// === FIX: botón Añadir ===
const handleAdd = async () => {
  const talle = (form.value.talle || '').trim()
  if (!talle) return alert('Ingresa el nombre de la talla')

  const payload = {
    talle,
    categoria: form.value.categoria,
    perfil: form.value.perfil || 'generic',
    ancho: Number(form.value.ancho ?? 0),
    alto:  Number(form.value.alto  ?? 0),
    molderias: [],
    composiciones: []
  }

  let created = null
  try {
    if (typeof tallasStore.addTalla === 'function') {
      created = await tallasStore.addTalla(payload)
    }
    // si el store no empuja a la lista, lo agregamos nosotros
    if (!created) {
      created = { id: Date.now(), ...payload }
      // Pinia: mutación segura
      tallasStore.tallas.push(created)
    }
  } catch (e) {
    console.error(e)
    return alert('No se pudo crear la talla.')
  }

  // limpiar
  form.value = { talle: '', categoria: 'camisas', perfil: form.value.perfil || 'generic', ancho: null, alto: null }
}

// guardar ancho/alto inline
const saveRow = async (row) => {
  try {
    if (typeof tallasStore.updateTalla === 'function') {
      await tallasStore.updateTalla(row.id, { ancho: Number(row.ancho ?? 0), alto: Number(row.alto ?? 0) })
    }
  } catch (e) {
    console.error(e); alert('No se pudo guardar la fila.')
  }
}

const handleDelete = async () => {
  if (!seleccion.value.size) return
  if (!confirm(`¿Eliminar ${seleccion.value.size} fila(s)?`)) return
  try {
    for (const id of seleccion.value) {
      if (typeof tallasStore.eliminarTalla === 'function') {
        await tallasStore.eliminarTalla(id)
      } else {
        const i = tallasStore.tallas.findIndex(t => t.id === id)
        if (i !== -1) tallasStore.tallas.splice(i, 1)
      }
    }
    seleccion.value = new Set()
  } catch (e) { console.error(e); alert('No se pudieron eliminar algunas filas.') }
}

onMounted(async () => {
  if (typeof tallasStore.getTallas === 'function') {
    try { await tallasStore.getTallas() } catch {}
  }
})
</script>

<style scoped>
/* === MISMO LOOK QUE TENÍAS === */
.tallas-container { background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%); min-height: 100vh; padding: 40px 16px; }
.card-dark { border-radius: 16px; background: rgba(26,26,39,0.92); color: #e5e7eb; box-shadow: 0 10px 30px rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
.card-title-gradient { background: linear-gradient(45deg, #ff6b6b, #ffa500); color: #fff; padding: 18px 24px; font-weight: 800; border-top-left-radius: 16px; border-top-right-radius: 16px; }
.card-subtitle { padding: 14px 18px; font-weight: 700; color: #fff; background: rgba(255,255,255,0.06); border-bottom: 1px solid rgba(255,255,255,0.08); }

/* Inputs/botones (mismo estilo) */
.product-input { background-color: #f9fafb; border: 1px solid #d1d5db; color: #000 !important; border-radius: 10px; padding: 12px 14px; outline: none; transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease; width: 100%; }
.product-input::placeholder { color: #000 !important; }
.product-input:focus { border-color: #8b8bd6; box-shadow: 0 0 0 3px rgba(139,139,214,0.35); transform: translateY(-1px); }
.btn-primary { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px; letter-spacing: .3px; transition: transform .18s ease, box-shadow .28s ease, filter .2s ease; border: 0; }
.btn-primary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }
.btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; font-weight: 800; border-radius: 10px; padding: 10px 18px; letter-spacing: .3px; transition: transform .18s ease, box-shadow .28s ease, filter .2s ease; border: 0; }
.btn-danger:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.22); }

/* Tabla */
.table-dark { border-collapse: separate; border-spacing: 0; width: 100%; }
.table-dark thead { background-color: #3e3e57; color: #ffffff; text-transform: uppercase; font-weight: 800; letter-spacing: .4px; }
.table-dark th,.table-dark td { border-bottom: 1px solid rgba(255,255,255,0.08); padding: 12px 16px; text-align: center; }
.table-dark tbody tr { background-color: #2c2c3e; transition: background-color .18s ease; }
.table-dark tbody tr:hover { background-color: #3a3a50; }
.cell-input{ width:120px; padding:8px 10px; }

/* tipografía mono para columnas de texto */
.mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; }
</style>
