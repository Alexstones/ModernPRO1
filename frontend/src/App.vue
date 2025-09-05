<script setup>
import { RouterView } from "vue-router";
import NavBar from "./views/NavBar.vue";
</script>

<template>
  <v-app>
    <div class="page-container">
      <!-- barra -->
      <NavBar />

      <!-- contenido -->
      <div class="content-wrapper">
        <div class="router-view-card">
          <RouterView />
        </div>
      </div>
    </div>
  </v-app>
</template>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #008DCE; /* fondo */
}

/* Empuje top para que no se solape con el app-bar alto de escritorio */
.content-wrapper {
  flex: 1;
  display: flex;
  justify-content: center;
  padding: 32px 16px;
  /* si tu app-bar es alto en desktop, deja un respiro adicional */
  margin-top: 84px; /* equivalente aprox. a tu app-bar/espaciado */
}

.router-view-card {
  background-color: white;
  width: 100%;
  max-width: 1100px;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
