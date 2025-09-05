<template>
  <div class="config-container-dark">
    <div class="card-dark overflow-hidden" style="max-width: 980px; width: 100%;">
      <div class="card-title-gradient">
        <h2 class="m-0 text-white text-h5 text-center">Crear usuario</h2>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- IZQUIERDA: Formulario -->
          <form @submit.prevent="handleCreate" class="space-y-5" aria-label="Formulario crear usuario">
            <div>
              <label class="label">Usuario:</label>
              <input
                v-model.trim="form.username"
                type="text"
                class="product-input"
                placeholder="Nombre de usuario"
                required
                autocomplete="username"
              />
            </div>

            <div>
              <label class="label">Contraseña:</label>
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                class="product-input"
                placeholder="Mínimo 6 caracteres"
                required
                minlength="6"
                autocomplete="new-password"
              />
            </div>

            <div class="flex items-center gap-3">
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Creando…' : 'Crear' }}
              </button>

              <button type="button" class="btn-danger" :disabled="!selectedId || submitting" @click="handleDelete">
                Eliminar
              </button>
            </div>

            <p v-if="msg" class="server-msg" :class="{ ok: msgOk, bad: !msgOk }">{{ msg }}</p>
          </form>

          <!-- DERECHA: Tabla -->
          <div>
            <div class="table-wrapper">
              <table class="table-dark w-full" aria-label="Usuarios">
                <thead>
                  <tr>
                    <th style="width: 90px;">Id</th>
                    <th>Nombre Usuario</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="users.length === 0">
                    <td colspan="2" class="text-center py-6 text-gray-300">Aún no hay usuarios.</td>
                  </tr>

                  <tr
                    v-for="u in users"
                    :key="u.id"
                    :class="['row-selectable', { selected: u.id === selectedId }]"
                    @click="toggleSelect(u.id)"
                  >
                    <td>{{ u.id }}</td>
                    <td class="text-left">{{ u.username }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-3 text-gray-300 text-sm">
              <span>Seleccionado: </span>
              <strong v-if="selectedId">#{{ selectedId }}</strong>
              <span v-else>Ninguno</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

/* -------- estado -------- */
const USERS_KEY = 'ep_users'         // fallback localStorage
const submitting = ref(false)
const msg = ref('')
const msgOk = ref(false)

const form = ref({ username: '', password: '' })
const users = ref([])                // [{id, username}]
const selectedId = ref(null)
const showPass = ref(false)

/* -------- util: backend + fallback -------- */
async function backendList() {
  try {
    const { data } = await axios.get('/api/users') // si existe
    // normaliza
    return Array.isArray(data) ? data : (data?.users || [])
  } catch {
    // fallback local
    const raw = localStorage.getItem(USERS_KEY)
    return raw ? JSON.parse(raw) : []
  }
}

async function backendCreate(username, password) {
  try {
    const { data } = await axios.post('/api/users', { username, password })
    // espera {id, username}
    return data
  } catch {
    // fallback local: autoincrement
    const list = await backendList()
    const nextId = list.length ? Math.max(...list.map(u => Number(u.id))) + 1 : 1
    const created = { id: nextId, username }
    list.push(created)
    localStorage.setItem(USERS_KEY, JSON.stringify(list))
    return created
  }
}

async function backendDelete(id) {
  try {
    await axios.delete(`/api/users/${id}`)
  } catch {
    // fallback local
    const list = await backendList()
    const idx = list.findIndex(u => Number(u.id) === Number(id))
    if (idx >= 0) {
      list.splice(idx, 1)
      localStorage.setItem(USERS_KEY, JSON.stringify(list))
    }
  }
}

/* -------- acciones -------- */
async function loadUsers() {
  users.value = await backendList()
}

function toggleSelect(id) {
  selectedId.value = selectedId.value === id ? null : id
}

async function handleCreate() {
  if (!form.value.username || !form.value.password) return
  submitting.value = true
  msg.value = ''
  try {
    const created = await backendCreate(form.value.username, form.value.password)
    msg.value = `Usuario "${created.username}" creado.`
    msgOk.value = true
    form.value.username = ''
    form.value.password = ''
    await loadUsers()
  } catch (e) {
    msg.value = e?.response?.data?.message || e?.message || 'No se pudo crear el usuario.'
    msgOk.value = false
  } finally {
    submitting.value = false
  }
}

async function handleDelete() {
  if (!selectedId.value) return
  submitting.value = true
  msg.value = ''
  try {
    await backendDelete(selectedId.value)
    msg.value = `Usuario #${selectedId.value} eliminado.`
    msgOk.value = true
    selectedId.value = null
    await loadUsers()
  } catch (e) {
    msg.value = e?.response?.data?.message || e?.message || 'No se pudo eliminar.'
    msgOk.value = false
  } finally {
    submitting.value = false
  }
}

onMounted(loadUsers)
</script>

<style scoped>
/* ==== layout base (mantenemos tu look & feel) ==== */
.config-container-dark {
  min-height: 100vh;
  padding: 24px 16px;
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 100%);
  display: grid;
  place-items: start center;
}

.card-dark {
  border-radius: 16px;
  background: rgba(26, 26, 39, 0.92);
  color: #e5e7eb;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
  border: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
}

.card-title-gradient {
  background: linear-gradient(45deg, #00a3ff, #00c48c);
  color: #fff;
  padding: 16px 24px;
  font-weight: 800;
}

/* grid helpers */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: 1fr; }
.md\:grid-cols-2 { }
@media (min-width: 768px) {
  .md\:grid-cols-2 { grid-template-columns: 1fr 1fr; }
}

/* spacing utils */
.p-6 { padding: 24px; }
.gap-8 { gap: 32px; }
.space-y-5 > * + * { margin-top: 20px; }
.mt-3 { margin-top: 12px; }

/* inputs y labels (tus estilos) */
.label { display:block; font-size:.95rem; color:#e5e7eb; margin-bottom:.35rem; }
.product-input {
  background-color: #f9fafb;
  border: 1px solid #d1d5db;
  color: #000 !important;
  border-radius: 8px;
  padding: 10px 12px;
  outline: none;
  width: 100%;
}
.product-input::placeholder { color: #000 !important; }

/* botones (reuso de tu paleta) */
.btn-primary {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
  font-weight: 800;
  border-radius: 8px;
  padding: 10px 18px;
  border: 0;
}
.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  font-weight: 800;
  border-radius: 8px;
  padding: 10px 18px;
  border: 0;
}

/* tabla */
.table-wrapper { overflow: auto; }
.table-dark { border-collapse: separate; border-spacing: 0; width: 100%; }
.table-dark thead {
  background-color: #3e3e57;
  color: #ffffff;
  text-transform: none;
  font-weight: 800;
  letter-spacing: .2px;
}
.table-dark th, .table-dark td {
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 10px 12px;
}
.table-dark tbody tr { background-color: #2c2c3e; transition: background-color .15s ease; }
.table-dark tbody tr:hover { background-color: #3a3a50; }

/* selección fila */
.row-selectable { cursor: pointer; }
.row-selectable.selected { outline: 2px solid #60a5fa; }

/* mensajes */
.server-msg { font-size: .9rem; margin-top: .5rem; }
.server-msg.ok { color:#bbf7d0; }
.server-msg.bad { color:#fecaca; }
</style>
