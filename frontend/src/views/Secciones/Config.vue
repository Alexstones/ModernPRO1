<template>
  <div class="config-container-dark">
    <!-- Registro -->
    <div v-if="!registrado" class="card-dark overflow-hidden login-card" role="form" aria-labelledby="titulo-registro">
      <div class="card-title-gradient">
        <h2 id="titulo-registro" class="m-0 text-white text-h5">Crear cuenta</h2>
      </div>

      <div class="p-6">
        <p class="mb-4 text-gray-200">
          <strong>ID de esta PC:</strong> {{ visitorId || 'Detectando…' }}
        </p>

        <form @submit.prevent="handleRegister" class="space-y-4 max-w-sm" novalidate>
          <div class="field">
            <label class="label" for="nombre">Nombre completo</label>
            <input
              id="nombre"
              v-model.trim="form.nombre"
              type="text"
              placeholder="Tu nombre y apellidos"
              class="product-input w-full"
              :aria-invalid="errors.nombre ? 'true' : 'false'"
              required
              autocomplete="name"
            />
            <p v-if="errors.nombre" class="err">{{ errors.nombre }}</p>
          </div>

          <div class="field">
            <label class="label" for="email">Correo electrónico</label>
            <input
              id="email"
              v-model.trim="form.email"
              type="email"
              placeholder="correo@dominio.com"
              class="product-input w-full"
              :aria-invalid="errors.email ? 'true' : 'false'"
              required
              autocomplete="email"
            />
            <p v-if="errors.email" class="err">{{ errors.email }}</p>
          </div>

          <div class="field">
            <label class="label" for="telefono">Teléfono (opcional)</label>
            <input
              id="telefono"
              v-model.trim="form.telefono"
              type="tel"
              placeholder="Ej: +52 55 1234 5678"
              class="product-input w-full"
              autocomplete="tel"
            />
          </div>

          <div class="field">
            <label class="label" for="password">Contraseña</label>
            <div class="relative">
              <input
                :type="showPass ? 'text' : 'password'"
                id="password"
                v-model="form.password"
                placeholder="Mínimo 8 caracteres"
                class="product-input w-full pr-12"
                :aria-invalid="errors.password ? 'true' : 'false'"
                required
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-eye"
                @click="showPass = !showPass"
                :aria-label="showPass ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              >
                {{ showPass ? '🙈' : '👁️' }}
              </button>
            </div>
            <p v-if="errors.password" class="err">{{ errors.password }}</p>
          </div>

          <div class="field">
            <label class="label" for="password2">Repite la contraseña</label>
            <input
              :type="showPass2 ? 'text' : 'password'"
              id="password2"
              v-model="form.password2"
              placeholder="Confirma tu contraseña"
              class="product-input w-full"
              :aria-invalid="errors.password2 ? 'true' : 'false'"
              required
              autocomplete="new-password"
            />
            <button
              type="button"
              class="toggle-eye-standalone"
              @click="showPass2 = !showPass2"
              :aria-label="showPass2 ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            >
              {{ showPass2 ? '🙈' : '👁️' }}
            </button>
            <p v-if="errors.password2" class="err">{{ errors.password2 }}</p>
          </div>

          <div class="field flex items-center gap-2">
            <input type="checkbox" id="acepto" v-model="form.acepto" class="check-lg" />
            <label class="label" for="acepto">Acepto los términos y condiciones</label>
            <p v-if="errors.acepto" class="err">{{ errors.acepto }}</p>
          </div>

          <button type="submit" class="btn-blue w-full mt-4" :disabled="submitting || !canSubmit">
            <span v-if="submitting">Creando cuenta…</span>
            <span v-else>Registrarme</span>
          </button>

          <p v-if="serverMsg" class="server-msg" :class="{ ok: serverOk, bad: !serverOk }">
            {{ serverMsg }}
          </p>
        </form>
      </div>
    </div>

    <!-- Gestión de Tallas (post-registro) -->
    <div v-else class="card-dark overflow-hidden">
      <div class="card-title-gradient">
        <h2 class="m-0 text-white text-h5 text-center">Gestión de Tallas</h2>
      </div>

      <div class="p-6">
        <p class="mb-2 text-gray-200 text-center">
          Cuenta creada para <strong>{{ form.nombre }}</strong> ({{ form.email }}).
        </p>
        <p class="mb-6 text-gray-300 text-center">
          ID de dispositivo registrado: <code>{{ visitorId }}</code>
        </p>

        <div class="form-container">
          <form @submit.prevent="handleFormSubmit" class="flex flex-col md:flex-row gap-4 mb-6">
            <input
              v-model.trim="nuevaTalla"
              type="text"
              placeholder="Añadir nueva talla (Ej: S, 150)"
              class="product-input flex-grow"
              required
            />
            <button type="submit" class="btn-primary">Añadir Talla</button>
            <button type="button" class="btn-secondary" @click="nuevaTalla = ''">Limpiar</button>
          </form>
        </div>

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
                  <button class="btn-danger" @click="tallasStore.eliminarTalla(index)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

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
import { ref, computed, onMounted } from 'vue'
import FingerprintJS from '@fingerprintjs/fingerprintjs'
import axios from 'axios'
import { useTallasStore } from '@/stores/tallas'

// ----- STORE -----
const tallasStore = useTallasStore()

// ----- REGISTRO -----
const registrado = ref(false)
const submitting = ref(false)
const serverMsg = ref('')
const serverOk = ref(false)
const visitorId = ref('')

const form = ref({
  nombre: '',
  email: '',
  telefono: '',
  password: '',
  password2: '',
  acepto: false
})

const errors = ref({ nombre: '', email: '', password: '', password2: '', acepto: '' })
const showPass = ref(false)
const showPass2 = ref(false)

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/

const canSubmit = computed(() => {
  return (
    form.value.nombre.trim().length >= 3 &&
    emailRegex.test(form.value.email) &&
    form.value.password.length >= 8 &&
    form.value.password === form.value.password2 &&
    form.value.acepto
  )
})

function validate() {
  errors.value = { nombre: '', email: '', password: '', password2: '', acepto: '' }
  if (form.value.nombre.trim().length < 3) errors.value.nombre = 'Ingresa al menos 3 caracteres.'
  if (!emailRegex.test(form.value.email)) errors.value.email = 'Correo inválido.'
  if (form.value.password.length < 8) errors.value.password = 'Mínimo 8 caracteres.'
  if (form.value.password !== form.value.password2) errors.value.password2 = 'Las contraseñas no coinciden.'
  if (!form.value.acepto) errors.value.acepto = 'Debes aceptar los términos.'
  return Object.values(errors.value).every(v => !v)
}

async function handleRegister() {
  if (submitting.value) return // evita doble click
  if (!validate()) return

  submitting.value = true
  serverMsg.value = ''
  serverOk.value = false

  const payload = {
    nombre: form.value.nombre,
    email: form.value.email,
    telefono: form.value.telefono || null,
    password: form.value.password,
    device_id: visitorId.value
  }

  try {
    const { data } = await axios.post('/api/register', payload)

    // Backend normalizado: ok=true en éxitos y en casos idempotentes (429/ya existe)
    if (data?.ok || data?.message) {
      serverMsg.value =
        data?.message || 'Te enviamos un correo de confirmación. Revisa tu bandeja.'
      serverOk.value = true
      registrado.value = true
      localStorage.setItem('registered_email', form.value.email)
      return
    }

    // Si no viene ok, forzamos error para caer al catch
    throw new Error(data?.message || 'No se pudo completar el registro.')
  } catch (e) {
    const status = e.response?.status
    const body = e.response?.data
    const errorCode = body?.error?.error_code

    // Si por alguna razón el proxy no normalizó:
    if (status === 429 && errorCode === 'over_email_send_rate_limit') {
      serverMsg.value = 'Ya enviamos un correo recientemente. Revisa tu bandeja.'
      serverOk.value = true
      registrado.value = true
      localStorage.setItem('registered_email', form.value.email)
    } else if (status === 400) {
      // Podría ser “already registered”
      const msg =
        (body?.error_description || body?.msg || body?.message || '').toString().toLowerCase()
      if (msg.includes('already registered') || msg.includes('user already exists')) {
        serverMsg.value =
          'Tu correo ya está registrado. Si no ves el correo, revisa spam o inténtalo más tarde.'
        serverOk.value = true
        registrado.value = true
        localStorage.setItem('registered_email', form.value.email)
      } else {
        serverMsg.value = 'Datos inválidos. Revisa tu información e intenta de nuevo.'
        serverOk.value = false
      }
    } else {
      console.error('API error:', status, body)
      serverMsg.value = 'Ocurrió un error al registrar. Intenta de nuevo.'
      serverOk.value = false
    }
  } finally {
    submitting.value = false
  }
}

// ----- TALLAS -----
const nuevaTalla = ref('')
function handleFormSubmit() {
  if (!nuevaTalla.value) return
  tallasStore.addTalla(nuevaTalla.value)
  nuevaTalla.value = ''
}

// ----- FINGERPRINT -----
onMounted(async () => {
  try {
    const fp = await FingerprintJS.load()
    const result = await fp.get()
    visitorId.value = result.visitorId
  } catch (e) {
    console.error('FingerprintJS error:', e)
  }

  // Restaurar si ya estaba registrado en esta máquina
  const saved = localStorage.getItem('registered_email')
  if (saved) registrado.value = true
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

/* Etiquetas y errores */
.label { display:block; font-size:.9rem; color:#e5e7eb; margin-bottom:.35rem; }
.err { color:#fecaca; font-size:.85rem; margin-top:.25rem; }
.server-msg { margin-top:.75rem; font-size:.9rem; }
.server-msg.ok { color:#bbf7d0; }
.server-msg.bad { color:#fecaca; }
.link { text-decoration: underline; color:#c7d2fe; }

/* Botón mostrar/ocultar password */
.toggle-eye { position:absolute; right:.5rem; top:50%; transform: translateY(-50%); background:transparent; border:0; font-size:1.15rem; cursor:pointer; }
.toggle-eye-standalone { margin-top:.35rem; background:transparent; border:0; font-size:1rem; cursor:pointer; color:#e5e7eb; }

/* Botones con gradiente */
.btn-blue {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff;
  font-weight: 800;
  border-radius: 10px;
  padding: 12px 18px;
  border: 0;
  letter-spacing: .3px;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease, opacity .2s ease;
}
.btn-blue[disabled] { opacity:.7; cursor:not-allowed; }
.btn-blue:hover:not([disabled]) { filter: brightness(1.05); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.25); }

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

/* utilidades */
.field { display:flex; flex-direction:column; }
</style>
