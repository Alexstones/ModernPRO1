<!-- src/views/HomeView.vue -->
<template>
    <div class="min-h-screen flex flex-col">
      <!-- Header -->
      <header class="border-b">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <h1 class="text-xl font-bold">Mi App</h1>
          <nav class="flex items-center gap-4">
            <RouterLink to="/" class="hover:underline">Inicio</RouterLink>
            <RouterLink to="/about" class="hover:underline">Acerca</RouterLink>
          </nav>
        </div>
      </header>
  
      <!-- Main -->
      <main class="flex-1">
        <section class="max-w-6xl mx-auto px-4 py-10">
          <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
              <h2 class="text-3xl md:text-4xl font-extrabold mb-3">
                Bienvenido a tu Home
              </h2>
              <p class="text-gray-600 mb-6">
                Este es un template base en Vue 3. Úsalo como punto de partida
                para tu dashboard o landing.
              </p>
  
              <div class="flex items-center gap-3">
                <button
                  class="px-4 py-2 rounded-xl border"
                  @click="count++"
                >
                  Contar: {{ count }}
                </button>
                <RouterLink
                  to="/dashboard"
                  class="px-4 py-2 rounded-xl border"
                >
                  Ir al Dashboard
                </RouterLink>
              </div>
            </div>
  
            <div class="border rounded-2xl p-6">
              <h3 class="text-lg font-semibold mb-2">Estado rápido</h3>
              <ul class="space-y-2 text-sm">
                <li>Usuario: <strong>{{ userName }}</strong></li>
                <li>Fecha: <strong>{{ today }}</strong></li>
                <li>Sistema: <strong>{{ env }}</strong></li>
              </ul>
            </div>
          </div>
        </section>
  
        <section class="max-w-6xl mx-auto px-4 pb-12">
          <h3 class="text-xl font-bold mb-4">Características</h3>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <article class="border rounded-2xl p-4">
              <h4 class="font-semibold mb-1">Rutas listas</h4>
              <p class="text-sm text-gray-600">
                Usa <code>&lt;RouterLink&gt;</code> para navegar entre vistas.
              </p>
            </article>
            <article class="border rounded-2xl p-4">
              <h4 class="font-semibold mb-1">Estado reactivo</h4>
              <p class="text-sm text-gray-600">
                Maneja estado con <code>ref</code> y <code>computed</code>.
              </p>
            </article>
            <article class="border rounded-2xl p-4">
              <h4 class="font-semibold mb-1">Componentes</h4>
              <p class="text-sm text-gray-600">
                Divide tu UI en componentes reutilizables en <code>src/components</code>.
              </p>
            </article>
          </div>
        </section>
      </main>
  
      <!-- Footer -->
      <footer class="border-t">
        <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-500">
          © {{ new Date().getFullYear() }} Mi App — Hecho con Vue 3
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { RouterLink } from 'vue-router'
  
  const count = ref(0)
  const userName = ref('Invitado')
  
  const today = computed(() => {
    const d = new Date()
    return d.toLocaleDateString()
  })
  
  const env = import.meta.env.MODE || 'development'
  
  onMounted(() => {
    // Aquí puedes cargar datos iniciales si quieres
    // e.g., userName.value = (await api.getUser()).name
    console.log('Home montado')
  })
  </script>
  
  <style scoped>
  /* Estilos locales opcionales */
  </style>
  