import { ref } from "vue";
import { defineStore } from "pinia";
import axios from "axios";
import { showErrorDialogTemprary } from '@/Utils/Dialogs';

export default defineStore(
  "UserStore",
  () => {
    const user = ref({
      token: "",
      user: "",
      id: 0,
      isLogged: false,
      isAdmin: "",
      permisos: [],
      time: 0, 
    });
    const modo = ref(true);
    const permisos = ref([]);

    const loadUserData = () => {
      const local = localStorage.getItem("user");

      if (local) {
        user.value = JSON.parse(local);

        const currentTime = Date.now();


        if (currentTime > user.value.time) {
          destroy();
          showErrorDialogTemprary("La sesión ha expirado, por favor inicia sesión de nuevo.");
          localStorage.clear()
          return;
        }

        user.value.isLogged = true;
       

        axios.defaults.headers.common["API-KEY"] = user.value.token;
      }
    };

   
    const createUserData = (data) => {
      const currentTime = Date.now();
      const expirationTime = currentTime + 3 * 60 * 60 * 1000;  // Hora actual + 3 horas en milisegundos

      user.value = {
        ...data,
        isLogged: true,
        time: expirationTime,  
      };

      axios.defaults.headers.common["API-KEY"] = user.value.token;
      localStorage.setItem("user", JSON.stringify(user.value)); 
    };

    const setApiKey = (token) => {
      axios.defaults.headers.common["API-KEY"] = user.value.token;
    };

    const destroy = () => {
      localStorage.clear();
      user.value = {
        token: "",
        user: "",
        id: 0,
        isLogged: false,
        isAdmin: false,
        time: 0,
      };

      axios.defaults.headers.common["API-KEY"] = "";
    };

    const getPermisos = async () => {
      const data = JSON.parse(localStorage.getItem('user'));

      if (!data || !data.id) return;

      const payload = {
        id: data.id,
      };

      try {
        const res = await UsuariosService.getPermissions(payload);

        permisos.value = res.data.dataset;
        user.value.permisos = res.data.dataset;
        return res;
      } catch (error) {
        console.log(error);
      }
    };

    return {
      user,
      permisos,
      loadUserData,
      createUserData,
      destroy,
      modo,
      setApiKey
    };
  },
  {
    persist: {
      key: "user-store",
      storage: localStorage,
      paths: ["user", "modo"],
    },
  }
);
