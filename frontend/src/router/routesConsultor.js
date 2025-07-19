export default {
  path: "/consultor",
  name: "DashbaordConsultor",
  meta: {
    isAdmin: false,
    isConsultor: true,
  },
  component: () => import("../views/Consultor/Algonodera.vue"),
  children: [
    {
      path: "/consultor",
      name: "Graficas",
      component: () => import("../views/Admin/DashboardConsultor.vue"),
      meta: {
        icon: "mdi-view-dashboard",
        isConsultor: true,
      }, // Icono de ejemplo
    },
    {
      path: "/conversaciones",
      name: "conversaciones",
      component: () => import("../views/Admin/chat.vue"),
      meta: {
        icon: "mdi-chat",
        isConsultor: true,
      }, // Icono de ejemplo
    },
    {
      path: "/consultor/configuracion",
      name: "Configuracion Consultor",
      component: () => import("../views/Consultor/configuracion.vue"),
      meta: {
        icon: "mdi-cog",
        isConsultor: true,
      }, // Icono de ejemplo
    },
  ],
};
