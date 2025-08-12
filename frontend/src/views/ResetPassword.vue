<!-- src/views/ResetPassword.vue -->
<template>
    <div class="page-bg min-h-screen flex items-center justify-center text-gray-100">
      <div class="card-dark glass max-w-md w-full">
        <h2 class="card-title mb-6">Restablecer contraseña</h2>
  
        <!-- Estado inicial (sin sesión activa aún) -->
        <p v-if="!canUpdate && !loading" class="mb-4 text-sm text-gray-200/90">
          Abre este enlace desde el correo de recuperación. Si expiró, vuelve a solicitarlo.
        </p>
  
        <!-- Cargando/validando tokens -->
        <p v-if="loading" class="text-sm text-gray-200/90">Validando enlace…</p>
  
        <!-- Formulario cuando la sesión quedó activa con el token del enlace -->
        <form v-else-if="canUpdate" @submit.prevent="updatePassword" class="space-y-4">
          <div>
            <label class="label" for="n1">Nueva contraseña</label>
            <input id="n1" type="password" v-model="p1" class="input-plain" required minlength="8" />
          </div>
          <div>
            <label class="label" for="n2">Confirmar contraseña</label>
            <input id="n2" type="password" v-model="p2" class="input-plain" required minlength="8" />
          </div>
          <button type="submit" class="btn-green w-full py-3 rounded-xl font-semibold" :disabled="saving">
            <span v-if="saving">Guardando…</span>
            <span v-else>Guardar</span>
          </button>
  
          <p v-if="ok" class="mt-2 text-sm text-green-300">{{ ok }}</p>
          <p v-if="err" class="mt-2 text-sm text-red-300">{{ err }}</p>
        </form>
  
        <!-- Error -->
        <p v-if="!canUpdate && errorMsg && !loading" class="mt-2 text-sm text-red-300">{{ errorMsg }}</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { supabase } from '../services/supabaseClient'
  
  const p1 = ref('')
  const p2 = ref('')
  const canUpdate = ref(false)
  const loading = ref(true)
  const saving = ref(false)
  const ok = ref('')
  const err = ref('')
  const errorMsg = ref('')
  
  /** Lee tokens aunque vengan como "#/reset-password#access_token=..." */
  function readTokensFromHash () {
    const hash = window.location.hash || ''
    // Buscamos el segundo '#', que suele preceder a los tokens
    const secondHashPos = hash.indexOf('#', 1)
    const rawParams = secondHashPos >= 0 ? hash.slice(secondHashPos + 1) : hash.slice(1)
    const params = new URLSearchParams(rawParams)
  
    // También soporta formato con ? (por si alguna config lo envía así)
    if (!params.get('access_token') && window.location.search) {
      const q = new URLSearchParams(window.location.search)
      q.forEach((v, k) => params.set(k, v))
    }
  
    return {
      access_token: params.get('access_token') || '',
      refresh_token: params.get('refresh_token') || '',
      type: params.get('type') || ''
    }
  }
  
  onMounted(async () => {
    try {
      if (!supabase) {
        errorMsg.value = 'Falta configuración de Supabase.'
        loading.value = false
        return
      }
  
      // 1) Si ya hay sesión, habilita el formulario
      const { data } = await supabase.auth.getSession()
      if (data.session) {
        canUpdate.value = true
        loading.value = false
        return
      }
  
      // 2) Si vienen tokens en el hash (doble #), actívalos manualmente
      const { access_token, refresh_token, type } = readTokensFromHash()
      if (access_token && refresh_token) {
        const { data: setData, error } = await supabase.auth.setSession({
          access_token,
          refresh_token
        })
        if (error) throw error
        canUpdate.value = !!setData.session
  
        // Limpia la URL para que quede simplemente "#/reset-password"
        const clean = window.location.origin + '/#/reset-password'
        window.history.replaceState({}, document.title, clean)
  
        loading.value = false
        return
      }
  
      // 3) Para enlaces con "code" (PKCE), por si tu proyecto lo usa:
      const url = new URL(window.location.href)
      const code = url.searchParams.get('code')
      if (code) {
        const { data: xData, error } = await supabase.auth.exchangeCodeForSession(code)
        if (error) throw error
        canUpdate.value = !!xData.session
        window.history.replaceState({}, document.title, window.location.origin + '/#/reset-password')
      }
    } catch (e) {
      errorMsg.value = e.message || 'No se pudo validar el enlace.'
    } finally {
      loading.value = false
    }
  })
  
  async function updatePassword () {
    ok.value = ''
    err.value = ''
  
    if (p1.value.length < 8) {
      err.value = 'La contraseña debe tener al menos 8 caracteres.'
      return
    }
    if (p1.value !== p2.value) {
      err.value = 'Las contraseñas no coinciden.'
      return
    }
    if (!supabase) {
      err.value = 'Falta configuración de Supabase.'
      return
    }
  
    try {
      saving.value = true
      const { error } = await supabase.auth.updateUser({ password: p1.value })
      if (error) throw error
      ok.value = 'Contraseña actualizada. Ya puedes iniciar sesión.'
    } catch (e) {
      err.value = e.message || 'No se pudo actualizar la contraseña.'
    } finally {
      saving.value = false
    }
  }
  </script>
  
  <style scoped>
  .page-bg { background: linear-gradient(135deg, #1e3a8a 0%, #155e75 50%, #0f766e 100%); }
  .card-dark { border-radius: 24px; padding: 2rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10); box-shadow: 0 20px 45px rgba(0,0,0,.35); }
  .glass { backdrop-filter: blur(10px); }
  .card-title { font-size: 1.5rem; font-weight: 800; background: linear-gradient(45deg, #ff6b6b, #ffa500); -webkit-background-clip: text; background-clip: text; color: transparent; }
  .label { font-size:.9rem; font-weight:600; margin-bottom:.25rem; display:inline-block; }
  .input-plain { width:100%; padding:.85rem 1rem; border-radius:14px; border:1px solid #d1d5db; background:#f9fafb; color:#000; }
  .btn-green { background: linear-gradient(135deg,#22c55e,#16a34a); color:#fff; border:0; }
  </style>
  