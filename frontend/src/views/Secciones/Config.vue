<template>
  <div class="config-container">
    <div v-if="!autenticado" class="login-container">
      <h2>Iniciar sesión</h2>
      <p><strong>ID de esta PC:</strong> {{ visitorId }}</p>
      <form @submit.prevent="iniciarSesion">
        <input
          v-model="email"
          type="email"
          placeholder="Correo electrónico"
          class="login-input"
        />
        <input
          v-model="password"
          type="password"
          placeholder="Contraseña"
          class="login-input"
        />
        <button type="submit" class="login-button">Entrar</button>
      </form>
    </div>

    <div v-else class="tallas-manager">
      <h2 class="text-3xl font-bold mb-2 text-center text-gray-800">
        Gestión de Tallas
      </h2>
      <p class="mb-6 text-gray-600 text-center">
        En esta sección puedes personalizar las medidas de cada talle.
      </p>

      <div class="form-container">
        <form
          @submit.prevent="handleFormSubmit"
          class="flex flex-col md:flex-row gap-4 mb-6"
        >
          <input
            v-model="nuevaTalla"
            type="text"
            placeholder="Añadir nueva talla (Ej: S, 150 cm)"
            class="input-field flex-grow"
            required
          />
          <button type="submit" class="action-button primary-btn">
            Añadir Talla
          </button>
        </form>
      </div>

      <div class="table-container">
        <table class="tabla-clara w-full">
          <thead>
            <tr>
              <th>Talla</th>
              <th class="text-center">Seleccionar</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="tallasStore.tallas.length === 0">
              <td colspan="3" class="text-center py-4 text-gray-500">
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
                  class="transform scale-125"
                />
              </td>
              <td class="text-center">
                <button class="btn-rojo" @click="tallasStore.eliminarTalla(index)">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-8 p-4 bg-gray-100 rounded-lg shadow-inner">
        <h3 class="font-bold text-lg mb-2 text-gray-800">
          Tallas seleccionadas:
        </h3>
        <p v-if="tallasStore.tallasSeleccionadas.length === 0" class="text-gray-500">
          Ninguna talla seleccionada.
        </p>
        <ul v-else class="list-disc list-inside text-gray-700">
          <li v-for="t in tallasStore.tallasSeleccionadas" :key="t">{{ t }} cm</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FingerprintJS from '@fingerprintjs/fingerprintjs'
import { useTallasStore } from '@/stores/tallas'

// Accede al store de Pinia
const tallasStore = useTallasStore()

// --- Estado y Lógica de Autenticación (Se mantiene en el componente) ---
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

// --- Estado y Lógica de CRUD de Tallas (Ahora usa el store) ---
const nuevaTalla = ref('')

const handleFormSubmit = () => {
  tallasStore.addTalla(nuevaTalla.value)
  nuevaTalla.value = ''
}

// Cargar FingerprintJS al montar el componente
onMounted(async () => {
  const fp = await FingerprintJS.load()
  const result = await fp.get()
  visitorId.value = result.visitorId
})
</script>

<style scoped>
.config-container {
  max-width: 900px;
  margin: 2rem auto;
  padding: 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  font-family: sans-serif;
}

/* Estilos para el Login */
.login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 350px;
  margin: 0 auto;
  gap: 1rem;
}

.login-container h2 {
  font-size: 1.75rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.login-container input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
}

.login-button {
  width: 100%;
  padding: 0.75rem;
  font-weight: bold;
  background-color: #3b82f6; /* blue-500 */
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.login-button:hover {
  background-color: #2563eb; /* blue-600 */
}

/* Estilos de la gestión de tallas */
.tallas-manager {
  /* Separador visual para el modo autenticado */
}

.form-container {
  max-width: 500px;
  margin: 0 auto;
}

.input-field {
  padding: 0.75rem;
  border: 1px solid #d1d5db; /* gray-300 */
  border-radius: 6px;
  background-color: white;
  color: #1f2937;
}

.action-button {
  padding: 0.75rem 1.5rem;
  font-weight: bold;
  border-radius: 6px;
  color: white;
  transition: background-color 0.2s;
}

.primary-btn {
  background-color: #10b981; /* emerald-500 */
}
.primary-btn:hover {
  background-color: #059669; /* emerald-600 */
}

.tabla-clara {
  width: 100%;
  border-collapse: collapse;
}

.tabla-clara th,
.tabla-clara td {
  border: 1px solid #d1d5db; /* gray-300 */
  padding: 0.75rem;
  text-align: center;
  color: #1f2937;
}

.tabla-clara th {
  background-color: #e5e7eb; /* gray-200 */
  color: #1f2937;
}

.btn-rojo {
  background: #ef4444; /* red-500 */
  padding: 0.5rem 1rem;
  border: none;
  color: white;
  font-weight: bold;
  border-radius: 4px;
  transition: background-color 0.2s;
}
.btn-rojo:hover {
  background-color: #dc2626; /* red-600 */
}
</style>