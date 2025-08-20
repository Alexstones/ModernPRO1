<template>
  <!-- Desktop -->
  <v-app-bar
    v-if="isDesktop"
    height="100"
    scroll-behavior="hide"
    elevation="0"
    class="navbar-cool-gradient"
  >
    <v-card class="navbar-card-glow mx-auto" elevation="8">
      <v-card-title class="d-flex align-center text-h5 font-weight-bold text-white me-8">
        MODELPRO
      </v-card-title>

      <v-divider vertical class="mx-4" />

      <!-- ✅ Links principales -->
      <router-link to="/home" class="nav-link" aria-label="Ir a Inicio">
        <v-btn text class="nav-btn">Inicio</v-btn>
      </router-link>

      <router-link to="/clientes" class="nav-link" aria-label="Ir a Clientes">
        <v-btn text class="nav-btn">Clientes</v-btn>
      </router-link>

      <router-link to="/generar" class="nav-link" aria-label="Ir a Generador PDF">
        <v-btn text class="nav-btn">Generador PDF</v-btn>
      </router-link>

      <router-link to="/perfil" class="nav-link" aria-label="Ir a Perfil Producto PDF">
        <v-btn text class="nav-btn">Perfil Producto PDF</v-btn>
      </router-link>

      <router-link to="/moldes" class="nav-link" aria-label="Ir a Moldes">
        <v-btn text class="nav-btn">Moldes</v-btn>
      </router-link>

      <!-- ✅ Moldería -->
      <router-link to="/molderia" class="nav-link" aria-label="Ir a Moldería">
        <v-btn text class="nav-btn">Moldería</v-btn>
      </router-link>

      <router-link to="/perfil_tipo" class="nav-link" aria-label="Ir a Perfil tipografía">
        <v-btn text class="nav-btn">Perfil tipografía</v-btn>
      </router-link>

      <router-link to="/tallas" class="nav-link" aria-label="Ir a Tallas">
        <v-btn text class="nav-btn">Tallas</v-btn>
      </router-link>

      <router-link to="/config" class="nav-link" aria-label="Ir a Configuración">
        <v-btn text class="nav-btn">Config</v-btn>
      </router-link>

      <!-- ✅ Nueva pestaña Diseño -->
      <router-link to="/diseno" class="nav-link" aria-label="Ir a Diseño">
        <v-btn text class="nav-btn">Diseño</v-btn>
      </router-link>
    </v-card>
  </v-app-bar>

  <!-- Mobile -->
  <v-app-bar
    v-else
    height="64"
    elevation="0"
    class="navbar-cool-gradient"
  >
    <v-card-title class="text-h6 text-white">MODELPRO</v-card-title>
  </v-app-bar>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const width = ref(1024)
const getWidth = () => (typeof window !== 'undefined' ? window.innerWidth : 1024)
const handleResize = () => { width.value = getWidth() }

onMounted(() => {
  width.value = getWidth()
  window.addEventListener('resize', handleResize, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})

const isDesktop = computed(() => width.value > 700)
</script>

<style scoped>
.navbar-cool-gradient {
  background: linear-gradient(90deg, #1f2937 0%, #374151 100%) !important;
  border-bottom: 2px solid #5a647d;
  padding: 0 !important;
  display: flex;
  align-items: center;
  justify-content: center;
}

.navbar-card-glow {
  display: flex;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.05) !important;
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 8px 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
  transition: all .3s ease-in-out;
}
.navbar-card-glow:hover {
  box-shadow: 0 0 25px rgba(121, 194, 255, 0.3), 0 0 50px rgba(121, 194, 255, 0.1);
}

.nav-link { text-decoration: none; }

.nav-btn {
  font-weight: 600;
  text-transform: none;
  font-size: 1rem;
  letter-spacing: 0.5px;
  color: white !important;
  background-color: transparent;
  border-radius: 8px;
  padding: 0 16px;
  height: 48px !important;
  transition: all .3s ease-in-out;
}
.nav-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,.2);
}

/* Botón activo (router-link-active) */
.router-link-active .nav-btn {
  background: linear-gradient(45deg, #79c2ff, #4784ff) !important;
  color: #fff !important;
  transform: translateY(-2px);
  box-shadow: 0 0 15px rgba(71, 132, 255, .6);
  font-weight: 700;
}
</style>
