
import env from "@/env";
import { showErrorDialog } from "./Dialogs";
import Swal from "sweetalert2";

/*
 * Convierte un objeto file a base64
 */
export function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
      resolve(reader.result.split(",")[1]); // Extract base64 string from data URL
      // resolve(reader.result); // Extract base64 string from data URL
    };
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

/*
 * Esta función permite realizar la validación de un arreglo de forms (refs)
 * cuando la validación falla, los tabs asociados a la función se pondrán de
 * color rojo, alertando que falta llenar campos
 */
export async function validator(formRefArr, tabs) {
  let isValid = true;
  const promises = [];

  formRefArr.forEach((form, i) => {
    const promise = form.value.validate().then(({ valid }) => {
      if (!valid) {
        isValid = false;
        tabs[i].value = "text-red";
      } else {
        tabs[i].value = "";
      }
    });

    promises.push(promise);
  });

  await Promise.all(promises);

  setTheme();
  const locale = localStorage.getItem("user-locale");
  if (!isValid && anchoPantalla() < 725) {
    if (locale === "es") {
      Swal.fire({
        background: background,
        color: foreground,
        icon: "error",
        title: "Error",
        text: "Complete correctamente todos los campos obligatorios.",
        showConfirmButton: false,
        timer: 2000, // Ocultar la alerta después de 1.5 segundos
      });
    } else {
      Swal.fire({
        background: background,
        color: foreground,
        icon: "error",
        title: "Error",
        text: "Correctly fill out all required fields.",
        showConfirmButton: false,
        timer: 2000, // Ocultar la alerta después de 1.5 segundos
      });
    }
  }

  return isValid;
}
let background = "";
let foreground = "";
const setTheme = () => {
  const modo = localStorage.getItem("modo");

  if (modo === "true") {
    background = "#212121";
    foreground = "#fff";
  } else {
    background = "#E0E0E0";
    foreground = "#000";
  }
};
/*
 * Esta función genera una clave aleatoria
 */
export const genClave = () => {
  return Date.now().toString(36).substring(2) + parseInt(Math.random() * 100);
};

/*
 * Esta funcion forza la descarga del pdf, por defecto recibe un string, que es el nombre de cual de los pdfs
 * se desea abrir usando un [string] arreglo
 * en los environments existe la url por defecto donde se guardan todos los pdfs, solamente hay qeu concatenarle
 * '"Empleados/" que es donde se encuentran los pdfs del modulo de empleados
 */
export const openPDF = (string) => {
  {
    if (informacionEmpleadosStore.Informacion[string].datos == "") {
      return;
    }
    const url =
      env.PDFs_URL +
      "Empleados/" +
      informacionEmpleadosStore.Informacion[string].datos.path;
    fetch(url)
      .then((response) => response.blob())
      .then((blob) => {
        const url = window.URL.createObjectURL(new Blob([blob]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
          "download",
          informacionEmpleadosStore.Informacion[string].datos.name + ".pdf"
        ); // Nombre del archivo a descargar
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
      })
      .catch((error) => {
        showErrorDialog(error);
      });
  }
};

/*
 * Esta funcion devuelve el tamaño de la pantalla
 */
export const anchoPantalla = () => {
  return window.innerWidth;
};

/*
 * esta funciones se usa para los tabs para que sean responsivos y si no se necesitan la informacion se muestre
 * debajo y no a un lado
 */
export const clases_responsive = () => {
  return anchoPantalla() > 725 ? "d-flex flex-row" : "";
};

/*
 * Esta funcion es la que establece el tamaño de los titulos dependiendo del tamaño de la pantalla
 */
export const tamañoTitulos = () => {
  if (anchoPantalla() > 725) return "pb-5 text-h3";
  return "text-h4";
};
