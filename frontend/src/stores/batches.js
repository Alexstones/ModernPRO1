// src/stores/batches.js
import { defineStore } from 'pinia'
import axios from 'axios'

const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

export const useBatchesStore = defineStore('batches', {
  state: () => ({
    list: [],
    page: 1,
    last: 1,
    total: 0,
    perPage: 20,
    loading: false,
    pollHandle: null,
    filter: { status: 'all', q: '' },
  }),

  getters: {
    activeCount: (s) => s.list.filter(b => ['queued', 'processing', 'partial'].includes(b.status)).length,
  },

  actions: {
    async fetch(page = 1) {
      this.loading = true
      try {
        const params = {}
        if (this.filter.status && this.filter.status !== 'all') params.status = this.filter.status
        const { data } = await axios.get(`${API}/print/batches`, { params: { page, ...params }})
        this.list = data.data
        this.page = data.current_page
        this.last = data.last_page
        this.total = data.total
        this.perPage = data.per_page
      } finally {
        this.loading = false
      }
    },

    async fetchOne(id) {
      const { data } = await axios.get(`${API}/print/batches/${id}`)
      return data
    },

    download(id) {
      window.open(`${API}/print/batches/${id}/download`, '_blank')
    },

    async retryFailed(id) {
      // Requiere endpoint opcional POST /print/batches/{id}/retry-failed
      return axios.post(`${API}/print/batches/${id}/retry-failed`)
    },

    startPolling(ms = 2000) {
      if (this.pollHandle) return
      this.pollHandle = setInterval(async () => {
        try { await this.fetch(this.page) } catch {}
      }, ms)
    },

    stopPolling() {
      if (this.pollHandle) {
        clearInterval(this.pollHandle)
        this.pollHandle = null
      }
    },
  }
})
