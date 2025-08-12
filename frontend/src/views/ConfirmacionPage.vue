<template>
    <div class="confirmacion-container">
      <h1>¡Cuenta confirmada con éxito! ✅</h1>
      <p>Ahora puedes iniciar sesión en tu cuenta.</p>
      <router-link to="/login">
        <button>Ir a Iniciar Sesión</button>
      </router-link>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ConfirmacionPage'
  };
  </script>
  
  <style scoped>
  /* Estilos para la página de confirmación */
  .confirmacion-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
    color: #fff;
  }
  h1 {
    font-size: 2em;
    margin-bottom: 0.5em;
  }
  p {
    font-size: 1.2em;
    margin-bottom: 2em;
  }
  button {
    padding: 10px 20px;
    font-size: 1em;
    border: none;
    border-radius: 5px;
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
  }
  </style>