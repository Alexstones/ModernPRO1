<script setup>
import { RouterView } from "vue-router";
import NavBar from "./views/NavBar.vue";

</script>

<template>
  <v-app>
    <div class="page-container">
      <NavBar />
      <div class="content mt-16">
        <RouterView />
      </div>
     
    </div>
  </v-app>
</template>

<style>
.page-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  /* Altura mínima de la ventana para garantizar que el footer esté al final */
}

.content {
  flex: 1;
  /* Toma el espacio disponible entre el header y el footer */
}
</style>
