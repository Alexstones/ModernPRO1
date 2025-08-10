<script setup>
import { RouterView } from "vue-router";
import NavBar from "./views/NavBar.vue";
</script>

<template>
  <v-app>
    <div class="page-container">
      <NavBar />
      <div class="content-wrapper mt-16">
        <div class="router-view-card mt-4">
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
  background-color: #008DCE; /* Fondo azul como en tu imagen */
}

.content-wrapper {
  flex: 1;
  display: flex;
  justify-content: center;
  padding: 32px 16px;
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