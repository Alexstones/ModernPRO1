<template>
  <section class="pricing">
    <header class="pricing__header">
      <h1>Planes y Licencias</h1>
      <p>Elige el plan ideal para tu equipo: 1, 3 o 5 licencias.</p>

      <div class="billing-toggle" role="group" aria-label="Periodo de facturación">
        <button :class="{ active: billingPeriod === 'monthly' }" @click="billingPeriod = 'monthly'">
          Mensual
        </button>
        <button :class="{ active: billingPeriod === 'annual' }" @click="billingPeriod = 'annual'">
          Anual <span class="badge">Ahorra {{ Math.round(annualDiscount * 100) }}%</span>
        </button>
      </div>
    </header>

    <div class="cards">
      <article v-for="plan in plans" :key="plan.id" class="card" :class="plan.accent">
        <div class="card__header">
          <h2>{{ plan.title }}</h2>
          <p class="card__subtitle">{{ plan.subtitle }}</p>
        </div>

        <div class="price">
          <div class="price__main">
            <span class="price__currency">{{ currency }}</span>
            <span class="price__amount">{{ displayPrice(plan) }}</span>
            <span class="price__period">/{{ billingPeriod === 'monthly' ? 'mes' : 'año' }}</span>
          </div>
          <p v-if="billingPeriod === 'annual'" class="price__note">
            Facturación anual: {{ currency }}{{ annualBilled(plan) }} (descuento aplicado)
          </p>
        </div>

        <ul class="features">
          <li v-for="feat in plan.features" :key="feat">{{ feat }}</li>
        </ul>

        <button class="btn btn--primary" @click="buy(plan)">
          Comprar {{ plan.title }}
        </button>

        <p class="note">Incluye {{ plan.seats }} {{ plan.seats === 1 ? 'licencia' : 'licencias' }}.
          Soporte por correo y actualizaciones.
        </p>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

type BillingPeriod = 'monthly' | 'annual'

interface Plan {
  id: 'basic' | 'premium' | 'gold'
  title: string
  subtitle: string
  seats: number
  monthlyPrice: number // precio total del plan por mes
  features: string[]
  accent?: string
}

// ---- Props (puedes editar los valores por defecto desde el padre) ----
const props = defineProps<{
  currency?: string
  annualDiscount?: number // 0.20 = 20% dcto
  plans?: Plan[]
}>()

// Valores por defecto sensatos para iniciar rápido
const currency = computed(() => props.currency ?? 'MX$')
const annualDiscount = computed(() => props.annualDiscount ?? 0.20)

const defaultPlans: Plan[] = [
  {
    id: 'basic',
    title: 'Básico',
    subtitle: 'Para 1 persona',
    seats: 1,
    monthlyPrice: 149, // total por mes para 1 licencia
    features: [
      '1 licencia',
      'Acceso completo a la app',
      'Actualizaciones automáticas',
    ],
    accent: 'accent--basic',
  },
  {
    id: 'premium',
    title: 'Premium',
    subtitle: 'Para 3 personas',
    seats: 3,
    monthlyPrice: 399, // total por mes para 3 licencias
    features: [
      '3 licencias',
      'Todo de Básico',
      'Soporte prioritario',
    ],
    accent: 'accent--premium',
  },
  {
    id: 'gold',
    title: 'Gold',
    subtitle: 'Para 5 personas',
    seats: 5,
    monthlyPrice: 599, // total por mes para 5 licencias
    features: [
      '5 licencias',
      'Todo de Premium',
      'Onboarding asistido',
    ],
    accent: 'accent--gold',
  },
]

const plans = computed(() => props.plans ?? defaultPlans)

// ---- Estado ----
const billingPeriod = ref<BillingPeriod>('monthly')

// ---- Emitir evento de compra ----
const emit = defineEmits<{
  (e: 'buy', payload: {
    planId: Plan['id']
    seats: number
    period: BillingPeriod
    unitPriceMonthly: number
    totalDue: number // total a pagar según periodo actual
  }): void
}>()

function priceForPeriod(plan: Plan) {
  if (billingPeriod.value === 'monthly') return plan.monthlyPrice
  // anual: 12 meses con descuento
  const annualBase = plan.monthlyPrice * 12
  return Math.round(annualBase * (1 - annualDiscount.value))
}

function displayPrice(plan: Plan) {
  if (billingPeriod.value === 'monthly') return plan.monthlyPrice
  // mostrar costo anual total (ya con dcto)
  return priceForPeriod(plan)
}

function annualBilled(plan: Plan) {
  return priceForPeriod(plan)
}

function buy(plan: Plan) {
  emit('buy', {
    planId: plan.id,
    seats: plan.seats,
    period: billingPeriod.value,
    unitPriceMonthly: plan.monthlyPrice,
    totalDue: priceForPeriod(plan),
  })
}
</script>

<style scoped>
.pricing { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
.pricing__header { text-align: center; margin-bottom: 1rem; }
.pricing__header h1 { font-size: 2rem; margin: 0; }
.pricing__header p { color: #555; margin: .25rem 0 1rem; }

.billing-toggle { display: inline-flex; gap: .25rem; background: #f2f2f2; padding: .25rem; border-radius: .75rem; }
.billing-toggle button { border: 0; background: transparent; padding: .5rem .9rem; border-radius: .6rem; cursor: pointer; font-weight: 600; }
.billing-toggle button.active { background: white; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
.billing-toggle .badge { margin-left: .35rem; font-size: .75rem; background: #16a34a; color: white; padding: .1rem .4rem; border-radius: .4rem; font-weight: 700; }

.cards { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
@media (min-width: 768px) { .cards { grid-template-columns: repeat(3, 1fr); } }

.card { border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1rem; background: white; display: flex; flex-direction: column; gap: .75rem; transition: box-shadow .2s ease, transform .2s ease; }
.card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.06); transform: translateY(-2px); }
.card__header h2 { margin: 0; font-size: 1.35rem; }
.card__subtitle { margin: .15rem 0 0; color: #6b7280; }

.price { margin: .25rem 0 .25rem; }
.price__main { display: flex; align-items: baseline; gap: .25rem; }
.price__currency { font-weight: 700; }
.price__amount { font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; }
.price__period { color: #6b7280; }
.price__note { margin: .1rem 0 0; color: #374151; font-size: .9rem; }

.features { list-style: none; padding: 0; margin: .5rem 0; display: grid; gap: .35rem; }
.features li::before { content: '✔'; margin-right: .35rem; color: #16a34a; }

.btn { border: 0; cursor: pointer; border-radius: .75rem; padding: .65rem 1rem; font-weight: 700; }
.btn--primary { background: #111827; color: white; }
.btn--primary:hover { filter: brightness(1.07); }

.note { color: #6b7280; font-size: .9rem; margin: .25rem 0 0; }

/* Accents */
.accent--basic { border-color: #dbeafe; }
.accent--premium { border-color: #fde68a; }
.accent--gold { border-color: #fcd34d; }
</style>
