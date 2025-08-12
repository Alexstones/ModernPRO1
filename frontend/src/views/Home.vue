<template>
  <div class="page-bg flex min-h-screen flex-col text-gray-100">
    <!-- HEADER -->
    <header class="nav-glass sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <h1 class="logo">ModelPro</h1>
      </div>
    </header>

    <!-- MAIN -->
    <main class="flex-1 container mx-auto px-4 py-16">
      <!-- HERO -->
      <section class="text-center mb-16">
        <h2 class="hero-title">
          Diseño que inspira, <span class="text-amber-300">experiencias que conectan</span>.
        </h2>
        <p class="hero-sub max-w-3xl mx-auto">
          ModelPro te ofrece las herramientas de diseño más avanzadas para que tus ideas tomen vida. Accede a nuestra plataforma exclusiva y lleva tus proyectos al siguiente nivel.
        </p>
      </section>

      <section class="grid gap-12 md:grid-cols-2 items-start">
        <!-- CARD LOGIN -->
        <div class="card-dark glass">
          <h3 class="card-title mb-6">Acceso Exclusivo</h3>

          <p class="card-text mb-6">
            Inicia sesión para desbloquear tu espacio de trabajo y colaborar con la comunidad. Si ya eres miembro premium, podrás dejar comentarios.
          </p>

          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label for="email" class="label">Correo Electrónico</label>
              <input
                id="email"
                type="email"
                v-model="email"
                class="input-plain"
                required
              />
            </div>

            <div>
              <label for="password" class="label">Contraseña</label>
              <input
                id="password"
                type="password"
                v-model="password"
                class="input-plain"
                required
              />
            </div>

            <button type="submit" class="btn-green w-full py-3 rounded-xl font-semibold">
              Entrar
            </button>

            <p v-if="loginError" class="mt-2 text-sm text-red-300">{{ loginError }}</p>
          </form>
        </div>

        <!-- CARD COMENTARIOS (VISIBLE SOLO PREMIUM) -->
        <div v-if="isLoggedIn && isPayingUser" class="card-dark glass">
          <h3 class="card-title mb-6">Comentarios de la Comunidad</h3>

          <form @submit.prevent="addComment" class="mb-6 space-y-4">
            <div>
              <label for="new-comment" class="label">Deja tu comentario</label>
              <textarea
                id="new-comment"
                v-model="newComment"
                class="input-plain h-28 resize-none"
                placeholder="Escribe tu opinión..."
                required
              ></textarea>
            </div>
            <button type="submit" class="btn-blue rounded-xl px-6 py-3 font-semibold">
              Publicar
            </button>
          </form>

          <ul class="space-y-4">
            <li
              v-for="comment in comments"
              :key="comment.id"
              class="rounded-xl border border-white/10 bg-white/5 p-4"
            >
              <p class="text-sm font-semibold mb-1 text-amber-200">{{ comment.user }}</p>
              <p class="text-gray-100/90">{{ comment.text }}</p>
            </li>
            <li v-if="comments.length === 0" class="text-center text-gray-300/80 italic">
              Aún no hay comentarios. ¡Sé el primero en participar!
            </li>
          </ul>
        </div>

        <!-- CARD BLOQUEADA (NO PREMIUM) -->
        <div v-else class="card-dark glass">
          <h3 class="card-title mb-4">Comentarios de la Comunidad</h3>
          <p class="card-text">
            Inicia sesión y suscríbete para dejar tus comentarios y unirte a la conversación.
          </p>
        </div>
      </section>
    </main>

    <!-- FOOTER -->
    <footer class="nav-glass border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-6 text-center text-sm text-gray-200/80">
        © {{ new Date().getFullYear() }} MODELPRO <span class="text-rose-300"></span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const isLoggedIn = ref(false)
const isPayingUser = ref(false)
const email = ref('')
const password = ref('')
const loginError = ref('')
const newComment = ref('')
const comments = ref([
  { id: 1, user: 'Usuario A', text: '¡Me encanta el nuevo diseño! Super intuitivo.' },
  { id: 2, user: 'Usuario B', text: 'Gran herramienta para prototipos rápidos.' },
])

const handleLogin = () => {
  loginError.value = ''
  if (email.value === 'usuario@pago.com' && password.value === 'password123') {
    isLoggedIn.value = true
    isPayingUser.value = true
    email.value = ''
    password.value = ''
  } else if (email.value === 'usuario@gratis.com' && password.value === 'password123') {
    isLoggedIn.value = true
    isPayingUser.value = false
    email.value = ''
    password.value = ''
  } else {
    loginError.value = 'Credenciales inválidas. Intenta de nuevo.'
  }
}

const addComment = () => {
  if (newComment.value.trim() !== '') {
    const newId = comments.value.length ? Math.max(...comments.value.map(c => c.id)) + 1 : 1
    comments.value.push({ id: newId, user: 'Usuario Premium', text: newComment.value })
    newComment.value = ''
  }
}
</script>

<style scoped>
/* ===== Fondo general con degradado oscuro ===== */
.page-bg {
  background: linear-gradient(135deg, #1e3a8a 0%, #155e75 50%, #0f766e 100%);
}

/* ===== Navbar glass ===== */
.nav-glass {
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,255,255,0.12);
}

/* ===== Logo con gradiente ===== */
.logo {
  font-size: 1.875rem;
  font-weight: 900;
  letter-spacing: .2px;
  background: linear-gradient(90deg, #f472b6, #fbbf24, #fde68a);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* ===== Cards oscuras estilo glass ===== */
.card-dark {
  border-radius: 24px;
  padding: 2rem;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.10);
  box-shadow: 0 20px 45px rgba(0,0,0,.35);
}
.glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* ===== Títulos con degradado cálido ===== */
.card-title {
  font-size: 1.5rem;
  font-weight: 800;
  line-height: 1.2;
  background: linear-gradient(45deg, #ff6b6b, #ffa500);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-shadow: 0 0 24px rgba(255, 166, 0, .18);
}

/* ===== Tipografías auxiliares ===== */
.hero-title {
  font-size: 3rem;
  font-weight: 900;
  line-height: 1.2;
  margin-bottom: 1rem;
}
.hero-sub {
  font-size: 1.25rem;
  color: rgba(229,231,235,.9);
}
.card-text { color: rgba(229,231,235,.9); }
.label {
  font-size: .9rem;
  font-weight: 600;
  margin-bottom: .25rem;
  display: inline-block;
}

/* ===== Inputs claros con texto negro ===== */
.input-plain {
  width: 100%;
  padding: .85rem 1rem;
  border-radius: 14px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  color: #000;
  outline: none;
  transition: box-shadow .2s ease, border-color .2s ease, transform .1s ease;
}
.input-plain::placeholder { color: #000; }
.input-plain:focus {
  border-color: #8b8bd6;
  box-shadow: 0 0 0 3px rgba(139,139,214,.35);
  transform: translateY(-1px);
}

/* ===== Botones con gradiente ===== */
.btn-blue {
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  color: #fff;
  border: 0;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-blue:hover {
  filter: brightness(1.06);
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}

.btn-green {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #fff;
  border: 0;
  transition: transform .18s ease, box-shadow .28s ease, filter .2s ease;
}
.btn-green:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.25);
}
</style>
