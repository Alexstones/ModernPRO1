<script setup>
import { ref } from 'vue';
import { anchoPantalla } from '@/Utils/Helpers';



</script>

<template>

</template>


<style scoped>
.background-container {
  background-image: url('/public/imagenes/home3.png');
  /* Ruta de tu imagen de fondo */
  background-size: cover;
  /* La imagen cubre todo el fondo */
  background-position: center;
  /* Centra la imagen */
  background-repeat: no-repeat;
  /* Evita que la imagen se repita */
  height: 100vh;
  /* Altura completa de la ventana */
  width: 100vw;
  /* Ancho completo de la ventana */
  display: flex;
  /* Permite centrar el contenido */
  align-items: center;
  /* Centra verticalmente */
  justify-content: center;
  /* Centra horizontalmente */
  position: relative;
  /* Asegura que el contenido se posicione relativo al contenedor */
}

.background-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  /* Capa semitransparente más opaca */
  z-index: 1;
  /* Asegura que la capa esté encima de la imagen */
}

.v-card {
  position: relative;
  z-index: 2;
  /* Asegura que la tarjeta esté encima de la capa semitransparente */
  padding: 20px;
  /* Espaciado interno */
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.5);
  /* Sombras para destacar */
  backdrop-filter: blur(1px);
  /* Efecto de desenfoque en el fondo de la tarjeta */
}

.title-shadow {
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
  /* Sombra para texto del título */
  font-weight: bold;
  /* Hace que el título sea más llamativo */
}

.subtitle-shadow {
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
  /* Sombra para subtítulos */
}

.paragraph-shadow {
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
  /* Sombra ligera para texto del párrafo */
}

.white--text {
  color: white !important;
}

.text-h1 {
  font-size: 2.8rem;
  /* Aumenta el tamaño del título */
}

.text-h2 {
  font-size: 2rem;
  /* Ajusta el tamaño del subtítulo */
}

.text-h5 {
  font-size: 1.2rem;
  /* Ajusta el tamaño del texto del párrafo */
  line-height: 1.6;
  /* Aumenta la legibilidad del párrafo */
}

.logo-item {
  border-radius: 10%;
  /* Fondo blanco para destacar el logo */
  padding: 20px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}
</style>
