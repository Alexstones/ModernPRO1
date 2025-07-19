<template>
  <v-container fluid class="font-manager-container">
    <v-row justify="center" class="mb-6">
      <h2 class="text-center font-weight-bold text-h4">Perfil de las fuentes</h2>
    </v-row>

    <!-- Botones principales -->
    <v-row justify="start" class="mb-4" dense>
      <v-btn color="primary" class="mr-2">Subir Fuente</v-btn>
      <v-btn color="error">Eliminar Fuente</v-btn>
    </v-row>

    <!-- Tabla de fuentes -->
    <v-data-table
      :headers="headersFuente"
      :items="fuentes"
      class="mb-8"
      dense
      hide-default-footer
    />

    <!-- Botón y tabla de perfil -->
    <v-row justify="start" class="mb-2">
      <v-btn color="error">Eliminar Perfil de fuente</v-btn>
    </v-row>

    <v-data-table
      :headers="headersPerfil"
      :items="perfiles"
      dense
      hide-default-footer
    />
  </v-container>
</template>

<script setup>
const headersFuente = [
  { title: 'Nombre Fuente', key: 'nombre' },
  { title: 'Path Fuente', key: 'path' }
];

const headersPerfil = [
  { title: 'Perfil Fuente', key: 'perfil' },
  { title: 'Fuente', key: 'fuente' },
  { title: 'Cont', key: 'cont' },
  { title: 'Dir Letra', key: 'letra' },
  { title: 'Dir Contorno', key: 'contorno' }
];

// Datos simulados
const fuentes = [
  { nombre: 'Adidas 2014', path: 'C:/Fonts/ADIDAS2014.ttf' },
  { nombre: 'Arsenal2014ROY.TTF', path: 'C:/Fonts/ARSENAL2014ROY.TTF' },
  // más registros...
];

const perfiles = [
  {
    perfil: 'Adidas 2014 letras negras',
    fuente: 'Adidas2014',
    cont: '2 mm',
    letra: '255 255 255',
    contorno: '0 0 0'
  },
  {
    perfil: 'nike blanco',
    fuente: 'CorinthiansNike2018',
    cont: '2 mm',
    letra: '255 255 255',
    contorno: '0 0 0'
  }
  // más perfiles...
];
</script>

<style scoped>
.font-manager-container {
  background-color: #f8f8f8;
  min-height: 100vh;
  padding: 40px;
}
</style>
