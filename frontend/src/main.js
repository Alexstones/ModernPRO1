import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import 'bootstrap-icons/font/bootstrap-icons.css';
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import '@mdi/font/css/materialdesignicons.css'
import { createI18n } from 'vue-i18n'
import axios from 'axios'
import env from './env'

axios.defaults.headers.common["Cache-Control"] = "no-cache";
axios.defaults.headers.common["Pragma"] = "no-cache";
axios.defaults.headers.common["Expires"] = "0";
axios.defaults.baseURL = env.API_URL


/*const savedLocale = localStorage.getItem('user-locale') || 'es';

const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'es',
  messages: Diccionario
})*/

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: '#eeeeee',
          secondary: '#1e1e1e',
          bg: '#eeeeee',
          hovermenu: '#BDBDBD',
          botones: '#455A64',
          sidevOscuro: '#c6833a',
          sidev: '#d99040',
          sidevMedio: '#E5A55F',
          sidevClaro: '#FFCD96',
          sibot: '#128b7e'
        }
      },
      dark: {
        light: false,
        colors: {
          primary: '#1e1e1e',
          secondary: '#f4f4f4',
          bg: '#1e1e1e',
          hovermenu: '#2d2d2d',
          botones: '#ECEFF1',
          sidevOscuro: '#9b672f',
          sidev: '#d99040',
          sidevMedio: '#E5A55F',
          sidevClaro: '#FFCD96',
          sibot: '#128b7e'
        }
      },
    },
  }
})
const app = createApp(App)

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)
app.use(router)
app.use(vuetify)
app.mount('#app')
