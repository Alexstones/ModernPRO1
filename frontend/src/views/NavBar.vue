<template>
  <v-app-bar v-if="anchoPantalla() > 700" color="transparent" height="100" scroll-behavior="hide" elevation="0"
    class="navbar-blur">
    <v-container class="d-flex justify-center">
      <router-link to="/generar" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Generador PDF</v-btn>
      </router-link>
      <router-link to="/perfil" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Perfil Producto PDF</v-btn>
      </router-link>
      <router-link to="/moldes" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Moldes</v-btn>
      </router-link>
      <router-link to="/perfil_tipo" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Perfil tipografía</v-btn>
      </router-link>
      <router-link to="/tallas" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Tallas</v-btn>
      </router-link>
      <router-link to="/config" class="nav-link">
        <v-btn text color="black" class="text-h5 nav-btn">Config</v-btn>
      </router-link>
    </v-container>
  </v-app-bar>


  <v-navigation-drawer v-if="anchoPantalla() <= 700" v-model="drawer" app temporary height="100vh" elevation="0"
    style="background-color: rgba(132, 189, 0, 0.8);" class=" ms-n4">
    <v-list>
      <!-- Inicio -->
      <v-list-item @click="navigateTo('/')">
        <v-btn text class="text-h5 nav-btn bg-transparent text-white" elevation="0">
          inicio
        </v-btn>
      </v-list-item>

      <!-- SmartGin -->
      <v-list-item @click="navigateTo('/smartgin')">
        <v-btn class="text-h5 nav-btn bg-transparent text-white" elevation="0">
          SmartGin
        </v-btn>
      </v-list-item>

      <!-- SISCO Industrial -->
      <v-list-item @click="navigateTo('/siscoIndustrial')">
        <v-btn class="text-h5 nav-btn bg-transparent text-white" elevation="0">
          Sisco Industrial
        </v-btn>
      </v-list-item>
      <!-- SISCO Fabricacion -->
      <v-list-item @click="navigateTo('/siscoFabricacion')">
        <v-btn class="text-h5 nav-btn bg-transparent text-white" elevation="0">
          Sisco Diseño
        </v-btn>
      </v-list-item>

      <!-- Contacto -->
      <v-list-item @click="navigateTo('/contacto')">
        <v-btn text class="text-h5 nav-btn bg-transparent text-white" elevation="0">
          contacto
        </v-btn>
      </v-list-item>


    </v-list>
  </v-navigation-drawer>
  <!-- Botón para activar/desactivar el drawer en vista móvil -->
  <v-app-bar v-if="anchoPantalla() <= 700" color="transparent" height="100" scroll-behavior="hide" elevation="0">
    <v-btn v-if="drawer == false" icon @click="drawer = !drawer"
      style="background-color: #84bd00; color: white; border-radius: 50%;">
      <v-icon>mdi-menu</v-icon>
    </v-btn>
    <v-spacer></v-spacer>
  </v-app-bar>



</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { anchoPantalla } from '@/Utils/Helpers';


// Variable para controlar el color del texto
const textColor = ref('white');
const drawer = ref(false);

// Obtener la ruta activa y el router
const route = useRoute();
const router = useRouter();



// Función para alternar idioma
const toggleLanguage = () => {
  locale.value = locale.value === 'es' ? 'en' : 'es';
};

// Función para actualizar el color del texto basado en la ruta
const updateTextColor = () => {
  if (route.path === '/contacto') {
    textColor.value = '#84bd00'; // Verde si está en "contacto"
  } else {
    textColor.value = 'white'; // Blanco para otras rutas
  }
};

// Función para detectar el scroll y cambiar el color de texto
const handleScroll = () => {
  const scrollPosition = window.scrollY;

  if (scrollPosition > 100 || route.path === '/contacto') {
    textColor.value = '#84bd00';
  } else {
    textColor.value = 'white';
  }
};

const navigateTo = (path) => {
  drawer.value = false; // Cerrar el drawer
  router.push(path);
};

// Escuchar el evento de scroll y cambios en el router
onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  router.beforeEach((to, from, next) => {
    if (to.path === '/contacto') {
      textColor.value = '#84bd00';
    } else {
      textColor.value = 'white';
    }
    next();
  });
  updateTextColor(); // Verificar el estado inicial al cargar
});

// Limpiar el evento de scroll y el listener del router
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.navbar-blur {
  backdrop-filter: blur(12px);
  background-color: rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.nav-btn {
  text-transform: none;
  font-weight: 500;
  font-size: 18px;
  color: white;
  transition: color 0.3s ease;
}

.nav-btn:hover {
  color: #84bd00;
}

.nav-link {
  text-decoration: none;
  margin: 0 16px;
}

.navbar {
  font-family: Arial, sans-serif;
  color: #84bd00;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
}

.logo-container {
  display: flex;
  gap: 10px;
}

.logo-item {
  border-radius: 10%;
  padding: 20px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.logo-item:hover {
  transform: translateY(-5px);
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

.nav-btn {
  text-transform: none;
  font-weight: 600;
  font-size: 16px;
  transition: color 0.3s ease;
}

.nav-btn:hover {
  color: #3900bd;
}

.nav-link {
  text-decoration: none;
}

.ms-auto {
  margin-left: auto;
}

.mt-n20 {
  margin-top: -6.5rem !important;
}
</style>
