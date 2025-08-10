<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

interface Cliente {
  id: number | string;
  nombre: string | null;
  email: string;
  plan: string | null;
  estado: 'activo' | 'inactivo' | 'trial' | 'suspendido';
  ultimo_pago_at: string | null;
  monto_ultimo_pago: number | null;
  moneda: string | null;
  total_pagado: number | null;
}
interface Pago {
  id: string | number;
  fecha: string;
  monto: number;
  moneda: string;
  metodo: string | null;
  status: 'paid' | 'refunded' | 'failed' | 'pending';
  referencia: string | null;
}

const cargando = ref(false);
const clientes = ref<Cliente[]>([]);
const query = ref('');
const plan = ref<string|undefined>();
const estado = ref<string|undefined>();
const page = ref(1);
const perPage = ref(10);

const mostrarModalPagos = ref(false);
const pagosCliente = ref<Pago[]>([]);
const clienteSeleccionado = ref<Cliente | null>(null);

const filtrados = computed(() => {
  let list = clientes.value;
  if (query.value) {
    const q = query.value.toLowerCase();
    list = list.filter(c =>
      (c.email || '').toLowerCase().includes(q) ||
      (c.nombre || '').toLowerCase().includes(q)
    );
  }
  if (plan.value) list = list.filter(c => (c.plan || '') === plan.value);
  if (estado.value) list = list.filter(c => c.estado === estado.value);
  return list;
});
const totalPages = computed(() => Math.max(1, Math.ceil(filtrados.value.length / perPage.value)));
const pageItems = computed(() => {
  const start = (page.value - 1) * perPage.value;
  return filtrados.value.slice(start, start + perPage.value);
});

function fmtMoney(n?: number|null, cur?: string|null) {
  if (n == null) return '—';
  try { return new Intl.NumberFormat(undefined, { style:'currency', currency: cur || 'USD' }).format(n); }
  catch { return `${n.toFixed(2)} ${cur || ''}`.trim(); }
}
function fmtDate(iso?: string|null) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString();
}

async function cargarClientes() {
  cargando.value = true;
  try {
    const { data } = await axios.get<Cliente[]>('/api/admin/clients');
    clientes.value = data;
  } catch (e) {
    console.error(e);
    clientes.value = [];
  } finally {
    cargando.value = false;
  }
}
async function verPagos(c: Cliente) {
  clienteSeleccionado.value = c;
  pagosCliente.value = [];
  mostrarModalPagos.value = true;
  try {
    const { data } = await axios.get<Pago[]>(`/api/admin/clients/${c.id}/payments`);
    pagosCliente.value = data;
  } catch (e) {
    console.error(e);
  }
}
function exportCSV() {
  const headers = ['ID','Nombre','Email','Plan','Estado','Último pago','Monto último','Moneda','Total pagado'];
  const rows = filtrados.value.map(c => [
    c.id, c.nombre ?? '', c.email, c.plan ?? '', c.estado, fmtDate(c.ultimo_pago_at),
    c.monto_ultimo_pago ?? '', c.moneda ?? '', c.total_pagado ?? ''
  ]);
  const csv = [headers, ...rows].map(r => r.map(x => `"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'clientes_modelpro.csv'; a.click();
  URL.revokeObjectURL(url);
}

onMounted(cargarClientes);
</script>

<template>
  <section class="wrap">
    <header class="bar">
      <h1>Clientes</h1>
      <div class="actions">
        <input v-model="query" placeholder="Buscar por nombre o email" />
        <select v-model="plan">
          <option :value="undefined">Plan</option>
          <option value="premium">Premium</option>
        </select>
        <select v-model="estado">
          <option :value="undefined">Todos los estados</option>
          <option value="activo">Activo</option>
          <option value="trial">Trial</option>
          <option value="inactivo">Inactivo</option>
          <option value="suspendido">Suspendido</option>
        </select>
        <button class="btn" @click="exportCSV">Exportar CSV</button>
      </div>
    </header>

    <div v-if="cargando" class="card">Cargando…</div>

    <div v-else class="card">
      <table class="grid">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Email</th>
            <th>Plan</th>
            <th>Estado</th>
            <th>Último pago</th>
            <th>Total pagado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in pageItems" :key="c.id">
            <td>{{ c.nombre ?? '—' }}</td>
            <td>{{ c.email }}</td>
            <td>{{ c.plan ?? '—' }}</td>
            <td><span :class="['pill', c.estado]">{{ c.estado }}</span></td>
            <td>
              <div>{{ fmtDate(c.ultimo_pago_at) }}</div>
              <small v-if="c.monto_ultimo_pago != null">{{ fmtMoney(c.monto_ultimo_pago, c.moneda) }}</small>
            </td>
            <td>{{ fmtMoney(c.total_pagado, c.moneda) }}</td>
            <td><button class="btn ghost" @click="verPagos(c)">Ver pagos</button></td>
          </tr>
          <tr v-if="!pageItems.length">
            <td colspan="7" class="empty">No hay resultados</td>
          </tr>
        </tbody>
      </table>

      <div class="pager">
        <button :disabled="page<=1" @click="page--">←</button>
        <span>Página {{ page }} / {{ totalPages }}</span>
        <button :disabled="page>=totalPages" @click="page++">→</button>
      </div>
    </div>

    <!-- Modal pagos -->
    <dialog v-if="mostrarModalPagos" open class="modal">
      <header>
        <h3>Pagos de {{ clienteSeleccionado?.email }}</h3>
        <button class="close" @click="mostrarModalPagos=false">×</button>
      </header>
      <div class="content">
        <table class="grid small">
          <thead><tr><th>Fecha</th><th>Monto</th><th>Estado</th><th>Método</th><th>Ref</th></tr></thead>
        <tbody>
          <tr v-for="p in pagosCliente" :key="p.id">
            <td>{{ fmtDate(p.fecha) }}</td>
            <td>{{ fmtMoney(p.monto, p.moneda) }}</td>
            <td><span :class="['pill', p.status]">{{ p.status }}</span></td>
            <td>{{ p.metodo ?? '—' }}</td>
            <td>{{ p.referencia ?? '—' }}</td>
          </tr>
          <tr v-if="!pagosCliente.length"><td colspan="5" class="empty">Sin pagos</td></tr>
        </tbody>
        </table>
      </div>
    </dialog>
  </section>
</template>

<style scoped>
.wrap { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
.bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
.bar h1 { margin: 0; }
.actions { display: flex; gap: 8px; flex-wrap: wrap; }
.actions input, .actions select { padding: 10px 12px; border-radius: 8px; border: 1px solid #444; background: #222; color: #fff; }
.btn { padding: 10px 14px; border-radius: 8px; background: #4CAF50; color: #fff; border: 0; cursor: pointer; }
.btn.ghost { background: transparent; border: 1px solid #555; }
.card { background: #222; color: #f0f0f0; border-radius: 12px; padding: 12px; box-shadow: 0 0 15px rgba(0,0,0,.25); }
.grid { width: 100%; border-collapse: collapse; }
.grid th, .grid td { padding: 12px; border-bottom: 1px solid #333; text-align: left; }
.grid.small th, .grid.small td { padding: 8px; }
.empty { text-align: center; color: #aaa; }
.pager { display: flex; justify-content: center; gap: 8px; padding: 12px; }
.pager button { padding: 6px 10px; }
.pill { padding: 3px 8px; border-radius: 999px; font-size: .8rem; background:#333; text-transform: capitalize; }
.pill.activo { background:#204d2e; }
.pill.trial { background:#314a7a; }
.pill.inactivo { background:#5a4a2c; }
.pill.suspendido { background:#5a2c2c; }
.pill.paid { background:#204d2e; }
.pill.failed { background:#5a2c2c; }
.pill.pending { background:#5a4a2c; }
.modal { width: min(900px, 96vw); border: 1px solid #333; border-radius: 12px; background:#1e1e1e; color:#fff; }
.modal header { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #333; }
.modal .content { padding: 12px 16px; }
.close { background: transparent; color: #fff; border: 0; font-size: 22px; cursor: pointer; }
</style>
