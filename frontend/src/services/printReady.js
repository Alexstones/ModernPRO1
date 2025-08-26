// src/services/printReady.js
import axios from 'axios';

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '');

/**
 * Genera un ZIP con 1 PDF por ítem, agrupado por TALLE.
 * Nombres: {NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}.pdf
 * @param {File} excelFile - Archivo .xlsx/.xls
 * @param {Object} opts - Opcionales para sobreescribir valores por defecto
 */
export async function generarZipPorTalle(excelFile, opts = {}) {
  const {
    fileName = 'pedido_copa_2025',
    mapping  = { NOMBRE: 'Nombre', NUMERO: 'Numero', TALLE: 'Talle', CATEGORIA: 'Categoria' },
    imposition = {
      sheet: 'a4', cols: 2, rows: 3, margin: 10, gap: 4, bleed: 3,
      cropMarks: true, pageCrop: true, rotate: false, fit: 'contain', mergeMode: 'zip'
    },
    namingTemplate = '{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}',
    groupBy = 'TALLE', // none | TALLE | CATEGORIA
    outline = false,
    downloadName = `${fileName}.zip`,
  } = opts;

  const form = new FormData();
  form.append('excelFile', excelFile);
  form.append('fileName', fileName);
  form.append('mapping', JSON.stringify(mapping));
  form.append('imposition', JSON.stringify(imposition));
  form.append('namingTemplate', namingTemplate);
  form.append('groupBy', groupBy);
  form.append('outline', outline ? '1' : '0');
  form.append('download', '1');

  const res = await axios.post(`${API}/print/generate-batch`, form, { responseType: 'blob' });
  const blob = new Blob([res.data], { type: 'application/zip' });
  const href = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = href; a.download = downloadName;
  document.body.appendChild(a); a.click(); a.remove();
  URL.revokeObjectURL(href);
}

/**
 * Genera un único PDF impuesto (grilla).
 * @param {File} excelFile - Archivo .xlsx/.xls
 * @param {Object} opts - Opcionales para sobreescribir valores por defecto
 */
export async function generarPdfUnico(excelFile, opts = {}) {
  const {
    fileName = 'imposicion_a4',
    mapping  = { NOMBRE: 'Nombre', NUMERO: 'Numero', TALLE: 'Talle' },
    imposition = {
      sheet: 'a4', cols: 3, rows: 4, margin: 8, gap: 3, bleed: 2,
      cropMarks: true, pageCrop: true, rotate: true, fit: 'contain', mergeMode: 'single'
    },
    namingTemplate = '{NOMBRE}_{NUMERO}_{TALLE}_{IDX:2}', // no afecta al modo single
    groupBy = 'none',
    outline = false,
    downloadName = `${fileName}.pdf`,
  } = opts;

  const form = new FormData();
  form.append('excelFile', excelFile);
  form.append('fileName', fileName);
  form.append('mapping', JSON.stringify(mapping));
  form.append('imposition', JSON.stringify(imposition));
  form.append('namingTemplate', namingTemplate);
  form.append('groupBy', groupBy);
  form.append('outline', outline ? '1' : '0');
  form.append('download', '1');

  const res = await axios.post(`${API}/print/generate-batch`, form, { responseType: 'blob' });
  const blob = new Blob([res.data], { type: 'application/pdf' });
  const href = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = href; a.download = downloadName;
  document.body.appendChild(a); a.click(); a.remove();
  URL.revokeObjectURL(href);
}
