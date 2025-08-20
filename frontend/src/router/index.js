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

  // Raíz redirige a /home
  { path: '/', redirect: { name: 'home' } },

  // Secciones
  { path: '/clientes', name: 'clientes', component: () => import('../views/Secciones/Clientes.vue') },
  { path: '/generar',  name: 'generar',  component: () => import('../views/Secciones/GenerarPDF.vue') },
  { path: '/perfil',   name: 'perfilProducto', component: () => import('../views/Secciones/Perfil.vue') },
  { path: '/moldes',   name: 'moldes',   component: () => import('../views/Secciones/Moldes.vue') },

  // ✅ NUEVO: Moldería
  {
    path: '/molderia',
    name: 'molderia',
    // Opción A: si tienes la vista creada:
    component: () => import('../views/Secciones/Molderia.vue'),
    // Opción B (alternativa): usar el componente que te compartí
    // component: () => import('@/components/MolderiaDesign.vue'),
    meta: { label: 'Moldería' }
  },

  { path: '/perfil_tipo', name: 'perfil_tipo', component: () => import('../views/Secciones/PerfilTipo.vue') },
  { path: '/tallas',   name: 'tallas',   component: () => import('../views/Secciones/Tallas.vue') },
  { path: '/config',   name: 'config',   component: () => import('../views/Secciones/Config.vue') },

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
