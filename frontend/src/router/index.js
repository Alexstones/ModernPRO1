// src/router/index.js
import { createRouter, createWebHashHistory } from 'vue-router'

const routes = [
  // Home visible como /home
  {
    path: '/home',
    name: 'home',
    component: () => import('../views/Home.vue'),
    meta: { label: 'Inicio' }
  },

  // Raíz y cadena vacía redirigen a /home
  { path: '/', redirect: { name: 'home' } },
  { path: '', redirect: { name: 'home' } },

  // ✅ NUEVO: Planes (con alias para tolerar /Planes)
  {
    path: '/planes',
    alias: ['/Planes'],
    name: 'planes',
    component: () => import('../views/Secciones/Planes.vue'),
    meta: { label: 'Planes' }
  },

  // Secciones
  { path: '/clientes', name: 'clientes', component: () => import('../views/Secciones/Clientes.vue') },
  { path: '/generar',  name: 'generar',  component: () => import('../views/Secciones/GenerarPDF.vue') },
  { path: '/perfil',   name: 'perfilProducto', component: () => import('../views/Secciones/Perfil.vue') },
  { path: '/moldes',   name: 'moldes',   component: () => import('../views/Secciones/Moldes.vue') },

  // ✅ Moldería
  {
    path: '/molderia',
    name: 'molderia',
    component: () => import('../views/Secciones/Molderia.vue'),
    meta: { label: 'Moldería' }
  },

  { path: '/perfil_tipo', name: 'perfil_tipo', component: () => import('../views/Secciones/PerfilTipo.vue') },
  { path: '/tallas',   name: 'tallas',   component: () => import('../views/Secciones/Tallas.vue') },
  { path: '/config',   name: 'config',   component: () => import('../views/Secciones/Config.vue') },

  // ✅ NUEVO: Diseño
  {
    path: '/diseno',
    name: 'diseno',
    component: () => import('../views/Secciones/Diseno.vue'),
    meta: { label: 'Diseño' }
  },

  // Confirmación de email de Supabase (sign-up)
  {
    path: '/confirmacion',
    name: 'Confirmacion',
    component: () => import('../views/ConfirmacionPage.vue'),
  },

  // Recuperación de contraseña
  {
    path: '/reset-password',
    name: 'resetPassword',
    component: () => import('../views/ResetPassword.vue'),
  },

  // (opcional) 404 real
  // { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFound.vue') }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior () { return { top: 0 } }
})

/**
 * Fix para callbacks de Supabase…
 */
router.beforeEach((to, from, next) => {
  if (to.name === 'Confirmacion' || to.name === 'resetPassword') return next()

  const raw = window.location.hash || ''
  if (raw && !raw.startsWith('#/')) {
    const params = new URLSearchParams(raw.slice(1))
    const access = params.get('access_token')
    const type = params.get('type')

    if (access || type) {
      const target = type === 'recovery' ? 'resetPassword' : 'Confirmacion'
      const query = Object.fromEntries(params.entries())
      return next({ name: target, query })
    }
  }

  return next()
})

export default router
