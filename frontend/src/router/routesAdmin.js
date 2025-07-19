export default {
  path: "/dashboard",
  name: "dashboard",
  meta: {
    isAdmin: true,
    isConsultor: false,
  },
  component: () => import("../views/Admin/DashboardLayoutAdmin.vue"),
  children: [
    {
      path: "/panel",
      name: "Inicio",
      component: () => import("../views/Admin/Panel.vue"),
      meta: {
        icon: "mdi-view-dashboard",
        isAdmin: true,
      },
    },
    {
      path: "/consultores",
      name: "Consultores",
      component: () => import("../views/Admin/Consultores.vue"),
      meta: {
        icon: "mdi-account-group",
        isAdmin: true,
      },
    },
    {
      path: "/chat",
      name: "Chat",
      component: () => import("../views/Admin/Chat.vue"),
      meta: {
        icon: "mdi-chat",
        isAdmin: true,
      },
    },
    {
      path: "/campaigns",
      name: "Campañas",
      component: () => import("../views/Admin/Campanas.vue"),
      meta: {
        icon: "mdi-bullhorn",
        isAdmin: true,
      },
    },
    {
      path: "/contactos",
      name: "Contactos",
      component: () => import("../views/Admin/Contactos.vue"),
      meta: { icon: "mdi-card-account-phone", isAdmin: true },
      children: [
        {
          path: "/grupos",
          name: "Grupos",
          component: () => import("../views/Admin/GruposContactos.vue"),
          meta: {
            icon: "mdi-account-group",
            isAdmin: true,
          },
        },
        {
          path: "/importarContactos",
          name: "Importar contactos",
          component: () => import("../views/Admin/ImportarContactos.vue"),
          meta: {
            icon: "mdi-file-import",
            isAdmin: true,
          },
        },
      ],
    },
    {
      path: "/plantillas",
      name: "Plantillas",
      component: () => import("../views/Admin/Plantillas.vue"),
      meta: {
        icon: "mdi-file-document-outline",
        isAdmin: true,
      },
    },
    {
      path: "/reportes",
      name: "Reportes",
      component: () => import("../views/Admin/Reportes.vue"),
      meta: {
        icon: "mdi-chart-box",
        isAdmin: true,
      },
    },
    {
      path: "/configuracion",
      name: "Configuracion",
      component: () => import("../views/Admin/Configuracion.vue"),
      meta: {
        icon: "mdi-cog",
        isAdmin: true,
      },
    },
    // {
    //   path: '/sendCampaign',
    //   name: 'sendCampaign',
    //   component: () => import('../components/General/SendCampaign.vue')
    // },
  ],
};
