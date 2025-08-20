<template>
  <div class="space-y-4">
    <!-- Texto y estilo -->
    <div class="bg-white rounded-2xl shadow p-4 border border-zinc-200">
      <div class="d-flex align-center justify-space-between mb-3">
        <h3 class="text-subtitle-2 font-weight-bold">Texto y estilo</h3>
        <div class="d-flex align-center ga-2">
          <label class="text-caption">Vista previa</label>
          <input type="checkbox" v-model="showPreview" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
          <label class="block text-xs text-zinc-500 mb-1">Texto</label>
          <input v-model="state.text" type="text" placeholder="Escribe aquí…"
                 class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm"/>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-zinc-500 mb-1">Tamaño</label>
            <input type="number" min="6" v-model.number="state.fontSize"
                   class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-zinc-500 mb-1">Fuente</label>
            <select v-model="state.fontFamily"
                    class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm">
              <option v-for="f in fonts" :key="f" :value="f">{{ f }}</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:col-span-3">
          <div class="flex items-center gap-3">
            <div>
              <label class="block text-xs text-zinc-500 mb-1">Color de fuente</label>
              <input type="color" v-model="state.fillColor"
                     class="h-10 w-14 p-1 bg-transparent rounded border border-zinc-300" />
            </div>
            <div class="grow">
              <label class="block text-xs text-zinc-500 mb-1">Hex</label>
              <input v-model="state.fillColor"
                     class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm" />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 mt-5">
              <input type="checkbox" v-model="state.stroke.enabled">
              <span class="text-xs text-zinc-600">Aplicar trazo</span>
            </div>
            <div>
              <label class="block text-xs text-zinc-500 mb-1">Grosor</label>
              <input type="number" min="0" step="0.5" v-model.number="state.stroke.width"
                     class="w-24 rounded-xl border border-zinc-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-xs text-zinc-500 mb-1">Color contorno</label>
              <input type="color" v-model="state.stroke.color"
                     class="h-10 w-14 p-1 bg-transparent rounded border border-zinc-300" />
            </div>
            <div class="grow">
              <label class="block text-xs text-zinc-500 mb-1">Hex</label>
              <input v-model="state.stroke.color"
                     class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm" />
            </div>
          </div>
        </div>

        <div v-if="showPreview" class="md:col-span-3">
          <div class="rounded-xl border border-dashed border-zinc-300 p-6 bg-[length:20px_20px]">
            <div class="select-none" :style="previewStyle">
              {{ state.text || 'Vista previa de texto' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Paleta -->
    <div class="bg-white rounded-2xl shadow p-4 border border-zinc-200">
      <div class="d-flex align-center justify-space-between mb-3">
        <h3 class="text-subtitle-2 font-weight-bold">Paleta de colores</h3>
        <div class="d-flex ga-2">
          <button @click="guardarColor(state.fillColor)"
                  class="px-3 py-1.5 text-xs rounded-xl bg-zinc-100 hover:bg-zinc-200">
            + Guardar color de fuente
          </button>
          <button @click="guardarColor(state.stroke.color)"
                  class="px-3 py-1.5 text-xs rounded-xl bg-zinc-100 hover:bg-zinc-200">
            + Guardar color de contorno
          </button>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <button v-for="(c, i) in palette" :key="i"
                @click="applyFromPalette(c)"
                :title="c"
                class="h-9 w-9 rounded-xl border border-zinc-200"
                :style="{ backgroundColor: c }"></button>

        <button @click="palette=[]"
                class="ml-auto px-3 py-1.5 text-xs rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100">
          Limpiar paleta
        </button>
      </div>
    </div>

    <!-- Imágenes -->
    <div class="bg-white rounded-2xl shadow p-4 border border-zinc-200">
      <div class="d-flex align-center justify-space-between mb-3">
        <h3 class="text-subtitle-2 font-weight-bold">Imágenes cargadas</h3>
        <label class="px-3 py-1.5 text-xs rounded-xl bg-zinc-100 hover:bg-zinc-200 cursor-pointer">
          + Agregar imágenes
          <input type="file" class="hidden" multiple accept="image/*" @change="onFiles" />
        </label>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 max-h-[520px] overflow-y-auto pr-1">
        <div v-for="(img, idx) in images" :key="img.id"
             class="flex gap-3 p-2 rounded-xl border border-zinc-200 bg-zinc-50">
          <img :src="img.url" class="h-24 w-20 object-cover rounded-lg border border-zinc-200" />
          <div class="min-w-0 grow">
            <div class="text-xs font-medium text-zinc-700 truncate">{{ img.name }}</div>
            <div class="text-[11px] text-zinc-500">{{ img.width }}×{{ img.height }}px</div>
            <div class="mt-2 flex gap-2">
              <button class="px-2 py-1 text-[11px] rounded-lg border border-zinc-300 hover:bg-zinc-100"
                      @click="$emit('select-image', img)">Usar</button>
              <button class="px-2 py-1 text-[11px] rounded-lg border border-zinc-300 hover:bg-zinc-100"
                      @click="removeImage(idx)">Eliminar</button>
            </div>
          </div>
        </div>

        <div v-if="images.length===0" class="col-span-full text-xs text-zinc-500 text-center py-8">
          No hay imágenes. Usa “Agregar imágenes”.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  initialPalette: { type: Array, default: () => [] },
  initialImages: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:modelValue', 'select-image', 'change'])

const fonts = ['Inter', 'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New', 'Impact']

const state = reactive({
  text: props.modelValue.text ?? '',
  fontSize: props.modelValue.fontSize ?? 32,
  fontFamily: props.modelValue.fontFamily ?? 'Inter',
  fillColor: props.modelValue.fillColor ?? '#111827',
  stroke: props.modelValue.stroke ?? { enabled: true, width: 2, color: '#ffffff' }
})

const showPreview = ref(true)
const palette = ref(props.initialPalette.length ? props.initialPalette : ['#111827','#ffffff','#ef4444','#3b82f6','#10b981','#f59e0b'])
const images = ref(props.initialImages)

const previewStyle = computed(() => {
  const css = {
    fontFamily: state.fontFamily,
    fontSize: `${state.fontSize}px`,
    color: state.fillColor,
    lineHeight: '1.15',
    fontWeight: '700',
    wordBreak: 'break-word',
  }
  if (state.stroke.enabled) {
    const s = state.stroke.width
    const c = state.stroke.color
    css['-webkit-text-stroke'] = `${s}px ${c}`
    css['textShadow'] =
      `-${s}px 0 ${c}, ${s}px 0 ${c}, 0 ${s}px ${c}, 0 -${s}px ${c},
       -${s}px -${s}px ${c}, ${s}px -${s}px ${c}, -${s}px ${s}px ${c}, ${s}px ${s}px ${c}`
  }
  return css
})

watch(state, () => {
  const val = { ...state }
  emit('update:modelValue', val)
  emit('change', val)
}, { deep: true })

function guardarColor(c) {
  if (!c) return
  if (!palette.value.includes(c)) palette.value.push(c)
}
function applyFromPalette(c) {
  state.fillColor = c
}
function onFiles(e) {
  const input = e.target
  if (!input.files) return
  Array.from(input.files).forEach(file => {
    const url = URL.createObjectURL(file)
    const img = new Image()
    img.onload = () => {
      images.value.unshift({
        id: crypto.randomUUID(),
        url,
        name: file.name,
        width: img.width,
        height: img.height
      })
    }
    img.src = url
  })
  input.value = ''
}
function removeImage(idx) {
  images.value.splice(idx, 1)
}
</script>
