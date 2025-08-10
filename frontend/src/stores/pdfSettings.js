// src/stores/pdfSettings.js
import { defineStore } from 'pinia'

const STORAGE_KEY = 'pdf_settings_v1'

const defaultSettings = {
  fileName: 'documento.pdf',
  jpegQuality: 85,
  imageRes: 200,
  colorImageRes: 120,
  grayImageRes: 120,
  compressPdf: true,
}

export const usePdfSettingsStore = defineStore('pdfSettings', {
  state: () => ({
    // planos para facilitar v-model directo
    fileName: defaultSettings.fileName,
    jpegQuality: defaultSettings.jpegQuality,
    imageRes: defaultSettings.imageRes,
    colorImageRes: defaultSettings.colorImageRes,
    grayImageRes: defaultSettings.grayImageRes,
    compressPdf: defaultSettings.compressPdf,
  }),

  getters: {
    settings(state) {
      // objeto completo (útil para enviar a servicio/API cuando toque)
      return {
        fileName: state.fileName,
        jpegQuality: state.jpegQuality,
        imageRes: state.imageRes,
        colorImageRes: state.colorImageRes,
        grayImageRes: state.grayImageRes,
        compressPdf: state.compressPdf,
      }
    },
    isDefault(state) {
      return Object.keys(defaultSettings).every(
        k => state[k] === defaultSettings[k]
      )
    }
  },

  actions: {
    reset() {
      Object.assign(this, defaultSettings)
      this.saveToStorage()
    },

    update(partial) {
      Object.assign(this, partial)
      this.saveToStorage()
    },

    loadFromStorage() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (!raw) return
        const data = JSON.parse(raw)
        if (data && typeof data === 'object') {
          Object.assign(this, defaultSettings, data)
        }
      } catch (e) {
        console.warn('No se pudieron cargar ajustes de PDF del storage.', e)
      }
    },

    saveToStorage() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.settings))
      } catch (e) {
        console.warn('No se pudieron guardar ajustes de PDF en storage.', e)
      }
    },

    init() {
      this.loadFromStorage()
    }
  }
})
