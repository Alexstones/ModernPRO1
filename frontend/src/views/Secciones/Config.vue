<template>
  <div class="config-container">
    <!-- LOGIN -->
    <div v-if="!autenticado" class="login-container">
      <h2>Iniciar sesión</h2>
      <input v-model="email" type="email" placeholder="Correo electrónico" />
      <input v-model="password" type="password" placeholder="Contraseña" />
      <button @click="iniciarSesion">Entrar</button>
      <p><strong>ID de esta PC:</strong> {{ visitorId }}</p>
    </div>

    <!-- TABLA DE TALLAS -->
    <div v-else class="tabla-tallas">
      <h2>Selecciona las tallas disponibles</h2>
      <table>
        <thead>
          <tr>
            <th>Talla</th>
            <th v-for="talla in tallas" :key="talla">{{ talla }} cm</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Seleccionada</td>
            <td v-for="talla in tallas" :key="talla">
              <input type="checkbox" :value="talla" v-model="tallasSeleccionadas" />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="resultado">
        <h3>Tallas seleccionadas:</h3>
        <p v-if="tallasSeleccionadas.length === 0">Ninguna talla seleccionada.</p>
        <ul v-else>
          <li v-for="t in tallasSeleccionadas" :key="t">{{ t }} cm</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FingerprintJS from '@fingerprintjs/fingerprintjs'

// Estado de login
const email = ref('')
const password = ref('')
const autenticado = ref(false)

// ID de visitante
const visitorId = ref('')

// ID autorizado (solo este dispositivo podrá iniciar sesión)
const idAutorizado = '12b1bbef440295fbd1a596d71ad7967d' // <-- reemplaza esto con tu ID válido

// Cargar FingerprintJS al montar el componente
onMounted(async () => {
  const fp = await FingerprintJS.load()
  const result = await fp.get()
  visitorId.value = result.visitorId
  console.log('Visitor ID:', visitorId.value)
})

// Función de login
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

// Tallas específicas disponibles
const tallas = [108, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160]
const tallasSeleccionadas = ref([])
</script>

<style lang="scss" scoped>
.config-container {
  margin: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.login-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 300px;
  width: 100%;

  input {
    padding: 0.5rem;
    font-size: 1rem;
  }

  button {
    padding: 0.6rem;
    font-weight: bold;
    background-color: #0077ff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
  }
}

.tabla-tallas {
  margin-top: 2rem;
  overflow-x: auto;
  width: 100%;

  h2 {
    margin-bottom: 1rem;
    text-align: center;
  }

  table {
    border-collapse: collapse;
    width: 100%;
    min-width: 800px;

    th,
    td {
      border: 1px solid #ccc;
      padding: 0.5rem;
      text-align: center;
      white-space: nowrap;
    }

    th {
      background-color: #f0f0f0;
    }

    input[type='checkbox'] {
      transform: scale(1.2);
    }
  }

  .resultado {
    margin-top: 1.5rem;

    h3 {
      font-weight: bold;
    }

    ul {
      list-style: disc;
      margin-left: 1.2rem;
    }
  }
}
</style>
