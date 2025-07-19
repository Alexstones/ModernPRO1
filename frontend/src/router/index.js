import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
   {
    path: '/',
    name: 'home',
    component: () => import('../views/Home.vue'),
  },
  {
    path: '/generar',
    name: 'generar',
    component: () => import('../views/Secciones/GenerarPDF.vue'),
  },
  {
    path: '/perfil',
    name: 'perfilProducto',
    component: () => import('../views/Secciones/Perfil.vue'),
  },
  {
    path: '/moldes',
    name: 'moldes',
    component: () => import('../views/Secciones/Moldes.vue'),
  },
  {
    path: '/perfil_tipo',
    name: 'perfil_tipo',
    component: () => import('../views/Secciones/PerfilTipo.vue'),
  },
   {
    path: '/tallas',
    name: 'tallas',
    component: () => import('../views/Secciones/Tallas.vue'),
  },
   {
    path: '/config',
    name: 'config',
    component: () => import('../views/Secciones/Config.vue'),
  },

];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Siempre vuelve al inicio de la página
    return { top: 0 };
  }
});

export default router;
