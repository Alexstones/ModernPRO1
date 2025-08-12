<template>
  <div class="page-bg min-h-screen text-gray-100 flex flex-col">
    <header class="nav-glass sticky top-0 z-40">
      <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <h1 class="logo">ModelPro</h1>
        <div class="flex items-center gap-3">
          <span v-if="isLoggedIn" class="text-sm text-gray-200/90">{{ userEmail }}</span>
          <button v-if="isLoggedIn" class="btn-blue px-4 py-2 rounded-xl text-sm" @click="handleLogout">
            Cerrar sesión
          </button>
        </div>
      </div>
    </header>

    <main class="flex-1 container mx-auto px-4 py-12">
      <section class="grid gap-10 md:grid-cols-2 items-start">
        <!-- Login -->
        <div class="card-dark glass">
          <h3 class="card-title mb-6">Acceso Exclusivo</h3>
          <p class="card-text mb-6">
            Inicia sesión con tu cuenta. Si tienes confirmación por correo activada, verifica tu email.
          </p>

          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="label" for="email">Correo</label>
              <input id="email" type="email" v-model.trim="email" class="input-plain" required autocomplete="email" />
            </div>
            <div>
              <label class="label" for="password">Contraseña</label>
              <input id="password" type="password" v-model="password" class="input-plain" required autocomplete="current-password" />
            </div>

            <button type="submit" class="btn-green w-full py-3 rounded-xl font-semibold" :disabled="loggingIn">
              <span v-if="loggingIn">Entrando…</span>
              <span v-else>Entrar</span>
            </button>

            <button type="button" class="btn-blue w-full py-2 rounded-xl font-semibold" @click="sendReset" :disabled="sendingReset">
              <span v-if="sendingReset">Enviando enlace…</span>
              <span v-else>¿Olvidaste tu contraseña?</span>
            </button>

            <p v-if="loginOk" class="mt-2 text-sm text-green-300">{{ loginOk }}</p>
            <p v-if="loginError" class="mt-2 text-sm text-red-300">{{ loginError }}</p>
            <p v-if="resetOk" class="mt-2 text-sm text-green-300">{{ resetOk }}</p>
            <p v-if="resetError" class="mt-2 text-sm text-red-300">{{ resetError }}</p>

            <p class="mt-3 text-xs text-gray-300/70">
              Endpoint: <code>{{ supaInfo.url }}</code>
            </p>
          </form>
        </div>

        <!-- Comentarios -->
        <div v-if="isLoggedIn && isPayingUser" class="card-dark glass">
          <h3 class="card-title mb-6">Comentarios de la Comunidad</h3>
          <form @submit.prevent="addComment" class="mb-6 space-y-4">
            <div>
              <label for="new-comment" class="label">Deja tu comentario</label>
              <textarea id="new-comment" v-model="newComment" class="input-plain h-28 resize-none" required></textarea>
            </div>
            <button type="submit" class="btn-blue rounded-xl px-6 py-3 font-semibold">Publicar</button>
          </form>
          <ul class="space-y-4">
            <li v-for="c in comments" :key="c.id" class="rounded-xl border border-white/10 bg-white/5 p-4">
              <p class="text-sm font-semibold mb-1 text-amber-200">{{ c.user }}</p>
              <p class="text-gray-100/90">{{ c.text }}</p>
            </li>
            <li v-if="!comments.length" class="text-center text-gray-300/80 italic">Aún no hay comentarios.</li>
          </ul>
        </div>

        <!-- Bloqueado -->
        <div v-else class="card-dark glass">
          <h3 class="card-title mb-4">Comentarios de la Comunidad</h3>
          <p class="card-text">Inicia sesión y suscríbete para comentar.</p>
          <ul class="mt-4 space-y-2 text-sm text-gray-300/80">
            <li><strong>Estado:</strong> {{ isLoggedIn ? 'Conectado' : 'No conectado' }}</li>
            <li><strong>Plan:</strong> {{ isPayingUser ? 'Premium' : 'Gratuito' }}</li>
          </ul>
        </div>
      </section>
    </main>

    <footer class="nav-glass border-t border-white/10">
      <div class="max-w-6xl mx-auto px-4 py-6 text-center text-sm text-gray-200/80">
        © {{ new Date().getFullYear() }} MODELPRO
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { supabase, getClientInfo } from '../services/supabaseClient'

const supaInfo = getClientInfo()

const isLoggedIn = ref(false)
const isPayingUser = ref(false)
const userEmail = ref('')
const email = ref('')
const password = ref('')
const loggingIn = ref(false)
const loginError = ref('')
const loginOk = ref('')

const sendingReset = ref(false)
const resetOk = ref('')
const resetError = ref('')

const newComment = ref('')
const comments = ref([
  { id: 1, user: 'Usuario A', text: '¡Me encanta el nuevo diseño!' },
  { id: 2, user: 'Usuario B', text: 'Gran herramienta para prototipos rápidos.' }
])

async function handleLogin () {
  loginError.value = ''
  loginOk.value = ''
  if (loggingIn.value) return
  loggingIn.value = true

  if (!supabase) {
    loggingIn.value = false
    loginError.value = 'Falta configuración de Supabase (.env del frontend).'
    return
  }

  const { data, error } = await supabase.auth.signInWithPassword({
    email: email.value,
    password: password.value
  })

  loggingIn.value = false

  if (error) {
    const msg = (error.message || '').toLowerCase()
    if (msg.includes('not confirmed')) {
      loginError.value = 'Debes confirmar tu correo antes de iniciar sesión.'
    } else if (msg.includes('invalid') || msg.includes('credentials')) {
      loginError.value = 'Correo o contraseña inválidos.'
    } else {
      loginError.value = error.message || 'No se pudo iniciar sesión.'
    }
    return
  }

  // Sesión OK
  isLoggedIn.value = true
  userEmail.value = data.user?.email || ''
  email.value = ''
  password.value = ''
  loginOk.value = '¡Bienvenido!'

  const { data: me } = await supabase
    .from('users')
    .select('is_premium')
    .eq('id', data.user.id)
    .single()

  isPayingUser.value = !!me?.is_premium
}

async function sendReset () {
  resetOk.value = ''
  resetError.value = ''
  if (!email.value) {
    resetError.value = 'Escribe tu correo y luego pulsa el botón.'
    return
  }
  if (!supabase) {
    resetError.value = 'Falta configuración de Supabase.'
    return
  }
  try {
    sendingReset.value = true
    const { error } = await supabase.auth.resetPasswordForEmail(email.value, {
      redirectTo: window.location.origin + '/#/reset-password'
    })
    if (error) throw error
    resetOk.value = 'Te enviamos un enlace para restablecer tu contraseña.'
  } catch (e) {
    resetError.value = e.message || 'No se pudo enviar el enlace.'
  } finally {
    sendingReset.value = false
  }
}

async function handleLogout () {
  if (supabase) await supabase.auth.signOut()
  isLoggedIn.value = false
  isPayingUser.value = false
  userEmail.value = ''
}

function addComment () {
  if (!newComment.value.trim()) return
  const newId = comments.value.length ? Math.max(...comments.value.map(c => c.id)) + 1 : 1
  comments.value.push({ id: newId, user: userEmail.value || 'Usuario', text: newComment.value })
  newComment.value = ''
}

onMounted(async () => {
  if (!supabase) return

  const { data } = await supabase.auth.getSession()
  if (data.session) {
    isLoggedIn.value = true
    userEmail.value = data.session.user?.email || ''
    const { data: me } = await supabase
      .from('users')
      .select('is_premium')
      .eq('id', data.session.user.id)
      .single()
    isPayingUser.value = !!me?.is_premium
  }

  supabase.auth.onAuthStateChange((_event, session) => {
    isLoggedIn.value = !!session
    userEmail.value = session?.user?.email || ''
  })
})
</script>

<style scoped>
.page-bg { background: linear-gradient(135deg, #1e3a8a 0%, #155e75 50%, #0f766e 100%); }
.nav-glass { background: rgba(255,255,255,0.06); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.12); }
.logo { font-size: 1.75rem; font-weight: 900; background: linear-gradient(90deg, #f472b6, #fbbf24, #fde68a); -webkit-background-clip: text; background-clip: text; color: transparent; }
.card-dark { border-radius: 24px; padding: 2rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10); box-shadow: 0 20px 45px rgba(0,0,0,.35); }
.glass { backdrop-filter: blur(10px); }
.card-title { font-size: 1.5rem; font-weight: 800; background: linear-gradient(45deg, #ff6b6b, #ffa500); -webkit-background-clip: text; background-clip: text; color: transparent; }
.card-text { color: rgba(229,231,235,.9); }
.label { font-size:.9rem; font-weight:600; margin-bottom:.25rem; display:inline-block; }
.input-plain { width:100%; padding:.85rem 1rem; border-radius:14px; border:1px solid #d1d5db; background:#f9fafb; color:#000; }
.input-plain:focus { border-color:#8b8bd6; box-shadow:0 0 0 3px rgba(139,139,214,.35); }
.btn-blue { background: linear-gradient(135deg,#60a5fa,#3b82f6); color:#fff; border:0; }
.btn-green { background: linear-gradient(135deg,#22c55e,#16a34a); color:#fff; border:0; }
</style>
