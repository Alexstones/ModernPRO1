<template>
  <div class="config-container-dark">
    <!-- LOGIN -->
    <div v-if="!autenticado" class="card-dark overflow-hidden login-card">
      <div class="card-title-gradient">
        <h2 class="m-0 text-white text-h5">Iniciar sesión</h2>
      </div>

      <div class="p-6">
        <p class="mb-4 text-gray-200">
          <strong>ID de esta PC:</strong> {{ visitorId }}
        </p>

        <form @submit.prevent="iniciarSesion" class="space-y-4 max-w-sm">
          <input
            v-model="email"
            type="email"
            placeholder="Correo electrónico"
            class="product-input w-full"
            required
          />
          <input
            v-model="password"
            type="password"
            placeholder="Contraseña"
            class="product-input w-full"
            required
          />
          <button type="submit" class="btn-blue w-full">Entrar</button>
        </form>
      </div>
    </div>

    <!-- GESTIÓN TALLAS -->
    <div v-else class="card-dark overflow-hidden">
      <div class="card-title-gradient">
        <h2 class="m-0 text-white text-h5 text-center">Gestión de Tallas</h2>
      </div>

      <div class="p-6">
        <p class="mb-6 text-gray-200 text-center">
          En esta sección puedes personalizar las medidas de cada talle.
        </p>

        <!-- Form -->
        <div class="form-container">
          <form @submit.prevent="handleFormSubmit" class="flex flex-col md:flex-row gap-4 mb-6">
            <input
              v-model="nuevaTalla"
              type="text"
              placeholder="Añadir nueva talla (Ej: S, 150 cm)"
              class="product-input flex-grow"
              required
            />
            <button type="submit" class="btn-primary">Añadir Talla</button>
            <button type="button" class="btn-secondary" @click="nuevaTalla = ''">Limpiar</button>
          </form>
        </div>

        <!-- Tabla -->
        <div class="table-container">
          <table class="table-dark w-full">
            <thead>
              <tr>
                <th>Talla</th>
                <th class="text-center">Seleccionar</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="tallasStore.tallas.length === 0">
                <td colspan="3" class="text-center py-6 text-gray-300">
                  No hay tallas disponibles.
                </td>
              </tr>

              <tr v-for="(item, index) in tallasStore.tallas" :key="index">
                <td>{{ item }}</td>
                <td class="text-center">
                  <input
                    type="checkbox"
                    :value="item"
                    v-model="tallasStore.tallasSeleccionadas"
                    class="check-lg"
                  />
                </td>
                <td class="text-center">
                  <button class="btn-danger" @click="tallasStore.eliminarTalla(index)">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Seleccionadas -->
        <div class="mt-8 p-4 info-box">
          <h3 class="font-bold text-lg mb-2 text-white">Tallas seleccionadas:</h3>
          <p v-if="tallasStore.tallasSeleccionadas.length === 0" class="text-gray-300">
            Ninguna talla seleccionada.
          </p>
          <ul v-else class="list-disc list-inside text-gray-100">
            <li v-for="t in tallasStore.tallasSeleccionadas" :key="t">{{ t }} cm</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FingerprintJS from '@fingerprintjs/fingerprintjs'
import { useTallasStore } from '@/stores/tallas'

const tallasStore = useTallasStore()

// Auth
const email = ref('')
const password = ref('')
const autenticado = ref(false)
const visitorId = ref('')
const idAutorizado = '9398492c7d09f97894abd41332fcecd5'

const iniciarSesion = () => {
  if (email.value === 'admin@gmail.com' && password.value === '123456') {
    if (visitorId.value !== idAutorizado) {
      alert(`Este dispositivo no está autorizado.\nTu ID es: ${visitorId.value}`)
      return
    }
    autenticado.value = true
  } else {
    alert('Credenciales incorrectas. Usa: admin@gmail.com / 123456')
  }
}

// Tallas (store)
const nuevaTalla = ref('')
const handleFormSubmit = () => {
  tallasStore.addTalla(nuevaTalla.value)
  nuevaTalla.value = ''
}

onMounted(async () => {
  const fp = await FingerprintJS.load()
  const result = await fp.get()
  visitorId.value = result.visitorId
})
</script>

<style scoped>
/* Fondo general degradado oscuro */
.config-container-dark {
  min-height: 100vh;
  padding: 40px 16px;
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  display: grid;
  place-items: start center;
  gap: 24px;
}

/* Cards oscuras estilo glass */
.card-dark {
  width: 100%;
  max-width: 900px;
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.login-card { max-width: 520px; }

/* Header con degradado cálido */
.card-title-gradient {
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  color: #fff;
  padding: 18px 24px;
  font-weight: 800;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}

/* Caja info final */
.info-box {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
}

/* Inputs claros con texto negro */
.product-input {
  background-color: #f9fafb;
  border: 1px solid #d1d5db;
  color: #000 !important;
  border-radius: 10px;
  padding: 12px 14px;
  outline: none;
  width: 100%;
  transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease;
}
.product-input::placeholder { color: #000 !important; }
.product-input:focus {
  border-color: #8b8bd6;
  box-shadow: 0 0 0 3px rgba(139,139,214,0.35);
  transform: translateY(-1px);
}

/* Botones con gradiente */
.btn-blue {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 12px 18px;
  border: 0;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-blue:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

.btn-primary {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  border: 0;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-primary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

.btn-secondary {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 10px 18px;
  border: 0;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-secondary:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 8px 14px;
  border: 0;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-danger:hover { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.22); }

/* Tabla oscura */
.table-dark { border-collapse: separate; border-spacing: 0; width: 100%; }
.table-dark thead {
  background-color: #3e3e57;
  color: #ffffff;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: .4px;
}
.table-dark th, .table-dark td {
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 12px 16px;
  text-align: center;
}
.table-dark tbody tr { background-color: #2c2c3e; transition: background-color .18s ease; }
.table-dark tbody tr:hover { background-color: #3a3a50; }

/* Checkbox visible en oscuro */
.check-lg { transform: scale(1.2); accent-color: #60a5fa; }
</style>
