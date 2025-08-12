import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../views/Home.vue'),
    },
    {
        path: '/clientes',
        name: 'clientes',
        component: () => import('../views/Secciones/Clientes.vue'),
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
    // Añade la ruta para la página de confirmación con la ruta corregida
    {
        path: '/confirmacion',
        name: 'Confirmacion',
        component: () => import('../views/ConfirmacionPage.vue'),
    },
    // Esta es la ruta clave que manejará la redirección de Supabase.
    // Es importante que esté al final para que no interfiera con las demás rutas.
    {
        path: '/:catchAll(.*)',
        redirect: to => {
            const hash = to.hash.substring(1);
            const params = new URLSearchParams(hash);
            const accessToken = params.get('access_token');
            
            if (accessToken) {
                // Si el token de acceso existe, redirige a la página de confirmación.
                return { name: 'Confirmacion' };
            }

            // Si no hay token, redirige a la página principal.
            return { name: 'home' };
        }
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0 };
    }
});

export default router;