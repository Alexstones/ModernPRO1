// src/services/supabaseClient.js
import { createClient } from '@supabase/supabase-js'

// Lee de .env del frontend (VITE_*). No rompemos la app si faltan.
const url  = import.meta.env.VITE_SUPABASE_URL || ''
const anon = import.meta.env.VITE_SUPABASE_ANON_KEY || ''

if (!url || !anon) {
  console.warn('[Supabase] Faltan variables VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY')
}

export const supabase = (url && anon)
  ? createClient(url, anon, { auth: { persistSession: true, autoRefreshToken: true } })
  : null

export const getClientInfo = () => ({
  url: url || '(sin URL por env)',
  usingFallback: false
})
