<template>
  <div class="tallas-container text-white">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="card-dark overflow-hidden mb-8">
        <div class="card-title-gradient">
          <h2 class="m-0 text-white text-h5">PESTAÑA TALLAS</h2>
        </div>
        <div class="p-4">
          <p class="m-0 text-gray-200">En esta sección puedes personalizar las medidas de cada talle.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card: Formulario -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle">
            <span class="font-bold">Añadir Talla</span>
          </div>

          <div class="p-6 space-y-4">
            <form @submit.prevent="handleFormSubmit" class="space-y-4">
              <select v-model="talla.categoria" class="product-input w-full" required>
                <option disabled value="">Seleccionar categoría</option>
                <option>CAMISETAS</option>
                <option>MANGAS</option>
                <option>SHORT</option>
              </select>

              <input
                v-model="talla.talle"
                placeholder="Talle (0, 2, S, M...)"
                class="product-input w-full"
                required
              />

              <input
                v-model.number="talla.ancho"
                type="number"
                min="0"
                placeholder="Ancho (cm)"
                class="product-input w-full"
                required
              />

              <input
                v-model.number="talla.alto"
                type="number"
                min="0"
                placeholder="Alto (cm)"
                class="product-input w-full"
                required
              />

              <div class="flex gap-3 pt-2">
                <button class="btn-primary" type="submit">Añadir Talla</button>
                <button type="button" class="btn-secondary" @click="limpiarCampos">Limpiar Campos</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Card: Tabla -->
        <div class="card-dark overflow-hidden">
          <div class="card-subtitle">
            <span class="font-bold">Tallas registradas</span>
          </div>

          <div class="p-0">
            <div class="overflow-x-auto">
              <table class="table-dark w-full">
                <thead>
                  <tr>
                    <th>Categoría</th>
                    <th>Talle</th>
                    <th>Ancho</th>
                    <th>Alto</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="tallasStore.tallas.length === 0">
                    <td colspan="5" class="text-center py-6 text-gray-300">No hay tallas registradas aún.</td>
                  </tr>

                  <tr v-for="item in tallasStore.tallas" :key="item.id">
                    <td>{{ item.categoria }}</td>
                    <td>{{ item.talle }}</td>
                    <td>{{ item.ancho }} cm</td>
                    <td>{{ item.alto }} cm</td>
                    <td>
                      <button class="btn-danger" @click="tallasStore.eliminarTalla(item.id)">Eliminar</button>
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
      </div> <!-- grid -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTallasStore } from '@/stores/tallas'

const tallasStore = useTallasStore()

const talla = ref({
  categoria: '',
  talle: '',
  ancho: '',
  alto: ''
})

const handleFormSubmit = async () => {
  await tallasStore.addTalla({ ...talla.value })
  limpiarCampos()
}

const limpiarCampos = () => {
  talla.value = { categoria: '', talle: '', ancho: '', alto: '' }
}

onMounted(() => {
  tallasStore.getTallas()
})
</script>

<style scoped>
/* Fondo general con degradado oscuro */
.tallas-container {
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

/* Header con degradado cálido */
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
  padding: 14px 18px;
  font-weight: 700;
  color: #fff;
  background: rgba(255,255,255,0.06);
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* Inputs con texto negro y buen contraste */
.product-input {
  background-color: #f9fafb;       /* fondo claro */
  border: 1px solid #d1d5db;
  color: #000 !important;          /* texto negro */
  border-radius: 10px;
  padding: 12px 14px;
  outline: none;
  transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease;
  width: 100%;
}
.product-input::placeholder {
  color: #000 !important;
}
.product-input:focus {
  border-color: #8b8bd6;
  box-shadow: 0 0 0 3px rgba(139, 139, 214, 0.35);
  transform: translateY(-1px);
}

/* Botones con gradiente */
.btn-primary {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
  border: 0;
}
.btn-primary:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

.btn-secondary {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
  border: 0;
}
.btn-secondary:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 8px 14px;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
  border: 0;
}
.btn-danger:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0,0,0,.22);
}

/* Tabla oscura */
.table-dark {
  border-collapse: separate;
  border-spacing: 0;
}
.table-dark thead {
  background-color: #3e3e57;
  color: #ffffff;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: .4px;
}
.table-dark th,
.table-dark td {
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 12px 16px;
  text-align: center;
}
.table-dark tbody tr {
  background-color: #2c2c3e;
  transition: background-color .18s ease;
}
.table-dark tbody tr:hover {
  background-color: #3a3a50;
}
</style>
