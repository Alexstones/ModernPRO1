<script setup>
import { onMounted, ref, computed } from 'vue'
import { useTallasStore } from '@/stores/tallas'

const store = useTallasStore()
const q = ref('')
const isEditing = computed(() => !!store.form.id)

onMounted(() => {
  store.fetch()
})

function startCreate() {
  store.resetForm()
}
function startEdit(item) {
  store.setFormFrom(item)
}
async function submit() {
  const payload = { nombre: store.form.nombre, codigo: store.form.codigo, activo: store.form.activo }
  if (store.form.id) await store.update(store.form.id, payload)
  else await store.create(payload)
  store.resetForm()
}
async function del(id) {
  if (confirm('¿Eliminar talla?')) await store.remove(id)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-2">
      <input v-model="q" @keyup.enter="store.fetch({ q })" placeholder="Buscar talla..." class="border rounded px-3 py-2 w-full" />
      <button @click="store.fetch({ q })" class="px-4 py-2 rounded bg-black text-white">Buscar</button>
      <button @click="startCreate" class="px-4 py-2 rounded border">Nueva</button>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <form @submit.prevent="submit" class="p-4 border rounded space-y-3">
        <h3 class="font-semibold">{{ isEditing ? 'Editar talla' : 'Crear talla' }}</h3>
        <div>
          <label class="text-sm">Nombre</label>
          <input v-model="store.form.nombre" required class="border rounded px-3 py-2 w-full" />
        </div>
        <div>
          <label class="text-sm">Código</label>
          <input v-model="store.form.codigo" class="border rounded px-3 py-2 w-full" />
        </div>
        <div class="flex items-center gap-2">
          <input id="activo" type="checkbox" v-model="store.form.activo" />
          <label for="activo">Activo</label>
        </div>
        <div class="flex gap-2">
          <button type="submit" :disabled="store.isLoading" class="px-4 py-2 rounded bg-green-600 text-white">
            {{ isEditing ? 'Guardar' : 'Crear' }}
          </button>
          <button type="button" @click="store.resetForm" class="px-4 py-2 rounded border">Limpiar</button>
        </div>
        <p v-if="store.error" class="text-sm text-red-600">{{ store.error }}</p>
      </form>

      <div class="md:col-span-2">
        <div class="border rounded">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50">
                <th class="text-left p-2">ID</th>
                <th class="text-left p-2">Nombre</th>
                <th class="text-left p-2">Código</th>
                <th class="text-left p-2">Estado</th>
                <th class="text-right p-2">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in store.items" :key="t.id" class="border-t">
                <td class="p-2">{{ t.id }}</td>
                <td class="p-2">{{ t.nombre }}</td>
                <td class="p-2">{{ t.codigo }}</td>
                <td class="p-2">
                  <span :class="t.activo ? 'text-green-600' : 'text-gray-400'">{{ t.activo ? 'Activo' : 'Inactivo' }}</span>
                </td>
                <td class="p-2 text-right space-x-2">
                  <button @click="startEdit(t)" class="px-2 py-1 border rounded">Editar</button>
                  <button @click="del(t.id)" class="px-2 py-1 rounded bg-red-600 text-white">Borrar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex justify-between items-center mt-3">
          <span class="text-sm">Total: {{ store.total }}</span>
          <div class="flex items-center gap-2">
            <button :disabled="store.page===1" @click="store.fetch({ q, page: store.page - 1, perPage: store.perPage })" class="px-3 py-1 border rounded">Prev</button>
            <span class="text-sm">Página {{ store.page }}</span>
            <button :disabled="store.items.length < store.perPage" @click="store.fetch({ q, page: store.page + 1, perPage: store.perPage })" class="px-3 py-1 border rounded">Next</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>