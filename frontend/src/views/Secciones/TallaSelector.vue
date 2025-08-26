<script setup>
import { ref, computed, watch } from "vue"

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  // Puedes sobreescribir las opciones si quieres
  options: {
    type: Array,
    default: () => ([
      "0","2","4","6","8","10","12","14","16",
      "S","M","L","XL","XXL","3XL","4XL","5XL"
    ])
  },
  placeholder: { type: String, default: "Añade una talla y presiona Enter" }
})

const emit = defineEmits(["update:modelValue", "added-custom"])
const open = ref(false)
const query = ref("")

// Normaliza: quita espacios y mayúsculas
const norm = (v) => (v ?? "").toString().trim().toUpperCase()
const selected = ref(props.modelValue.map(norm))

watch(() => props.modelValue, v => {
  selected.value = (v || []).map(norm)
})

const available = computed(() =>
  props.options.map(norm)
)

function toggleOption(opt) {
  const v = norm(opt)
  if (selected.value.includes(v)) {
    selected.value = selected.value.filter(o => o !== v)
  } else {
    selected.value = [...selected.value, v]
  }
  emit("update:modelValue", selected.value)
}

function addCustom() {
  const v = norm(query.value)
  if (!v) return
  if (!selected.value.includes(v)) {
    selected.value = [...selected.value, v]
    emit("update:modelValue", selected.value)
    emit("added-custom", v)
  }
  query.value = ""
}

function removeTag(v) {
  selected.value = selected.value.filter(o => o !== v)
  emit("update:modelValue", selected.value)
}
</script>

<template>
  <div class="w-full">
    <!-- Chips de seleccionadas -->
    <div class="flex flex-wrap gap-2 mb-2">
      <template v-if="selected.length">
        <span v-for="tag in selected" :key="tag"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-2xl bg-slate-700/70 text-white text-sm">
          {{ tag }}
          <button type="button" @click="removeTag(tag)"
            class="hover:bg-white/10 rounded-full px-1 leading-none">✕</button>
        </span>
      </template>
      <span v-else class="text-slate-400 text-sm">No hay tallas seleccionadas</span>
    </div>

    <!-- Dropdown -->
    <div class="relative">
      <button type="button" @click="open = !open"
        class="w-full justify-between inline-flex items-center px-4 py-2 rounded-xl bg-slate-800 text-white border border-slate-700">
        Seleccionar tallas
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
      </button>

      <div v-if="open"
        class="absolute z-20 mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 p-3 shadow-xl">
        <!-- Opciones predefinidas -->
        <div class="grid grid-cols-3 gap-2 max-h-56 overflow-auto">
          <label v-for="opt in available" :key="opt"
                 class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-slate-800 cursor-pointer">
            <input type="checkbox" class="accent-indigo-500"
                   :checked="selected.includes(opt)"
                   @change="toggleOption(opt)" />
            <span class="text-sm text-white">{{ opt }}</span>
          </label>
        </div>

        <div class="h-px bg-slate-700 my-3"></div>

        <!-- Agregar personalizada -->
        <div class="flex gap-2">
          <input
            v-model="query"
            :placeholder="placeholder"
            @keydown.enter.prevent="addCustom"
            class="flex-1 px-3 py-2 rounded-lg bg-slate-800 text-white border border-slate-700"
          />
          <button type="button" @click="addCustom"
                  class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white">
            Agregar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
