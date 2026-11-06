<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
    <!-- Header -->
    <div class="text-center space-y-3 max-w-xl mx-auto">
      <div class="inline-flex items-center space-x-1.5 px-3 py-1 bg-[#E8D8C4] text-[#553B2D] text-xs font-bold rounded-full">
        <span>📍</span>
        <span>Real-Time Order Tracking</span>
      </div>
      <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#29231F]">
        Track Your Bakery Order
      </h1>
      <p class="text-xs sm:text-sm text-[#786B60] leading-relaxed">
        Enter your unique order number to check live preparation and fulfillment status from your neighborhood branch.
      </p>
    </div>

    <!-- Search Input Box -->
    <div class="flex items-center space-x-2 bg-white p-2.5 rounded-2xl border border-[#E6DED3] shadow-xs max-w-lg mx-auto">
      <div class="pl-3 text-base text-[#786B60]">🔍</div>
      <input
        v-model="searchNumber"
        type="text"
        placeholder="Enter Order # (e.g. ORD-1001)"
        @keyup.enter="searchOrder"
        class="flex-1 px-3 py-2 text-xs font-mono font-bold text-[#29231F] focus:outline-none uppercase"
      />
      <button
        @click="searchOrder"
        :disabled="isSearching"
        class="px-6 py-2.5 bg-[#553B2D] hover:bg-[#3D291F] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs"
      >
        {{ isSearching ? 'Tracking...' : 'Track Order' }}
      </button>
    </div>

    <!-- Order Tracking Display Card -->
    <div v-if="order" class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E6DED3] shadow-xs space-y-6">
      <!-- Order Header Row -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-4">
        <div>
          <span class="text-xs font-mono font-bold text-[#A87552]">#{{ order.order_number }}</span>
          <h2 class="text-xl font-serif font-bold text-[#29231F] mt-0.5">
            Fulfilling Branch: {{ order.branch?.branch_name || 'Bole Flagship' }}
          </h2>
          <p class="text-xs text-[#786B60] mt-0.5">
            {{ order.fulfillment_method === 'Delivery' ? '🚀 Doorstep Delivery' : '🛍️ In-Store Pickup' }} &middot; Placed on {{ formatDate(order.placed_at || order.created_at) }}
          </p>
        </div>

        <div>
          <span
            class="px-4 py-1.5 text-xs font-bold rounded-full capitalize shadow-2xs inline-block"
            :class="statusBadgeClass(order.order_status)"
          >
            {{ order.order_status }}
          </span>
        </div>
      </div>

      <!-- Lifecycle Stepper Timeline -->
      <div class="py-6 px-2">
        <div class="flex items-center justify-between relative">
          <!-- Connector Background Bar -->
          <div class="absolute top-1/2 left-4 right-4 h-1 bg-[#E6DED3] -translate-y-1/2 z-0"></div>

          <div
            v-for="(step, i) in steps"
            :key="step.key"
            class="relative z-10 flex flex-col items-center"
          >
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 shadow-xs"
              :class="isStepComplete(step.key) ? 'bg-[#387A54] text-white ring-4 ring-[#387A54]/20' : (isCurrentStep(step.key) ? 'bg-[#A87552] text-white ring-4 ring-[#A87552]/30 scale-110' : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]')"
            >
              <span v-if="isStepComplete(step.key)">✓</span>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span
              class="text-[11px] mt-2.5 font-semibold tracking-tight text-center max-w-[85px] leading-tight"
              :class="isCurrentStep(step.key) ? 'text-[#553B2D] font-bold' : (isStepComplete(step.key) ? 'text-[#387A54]' : 'text-[#786B60]')"
            >
              {{ step.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Branch Info Box -->
      <div class="p-4 bg-[#F8F3EA] rounded-2xl border border-[#E6DED3] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="space-y-0.5">
          <div class="font-bold text-[#29231F] flex items-center space-x-1.5">
            <span>🏛️</span>
            <span>{{ order.branch?.branch_name || 'Bole Flagship' }} Bakehouse</span>
          </div>
          <div class="text-[#786B60]">
            {{ order.branch?.address || 'Cameroon St, Bole' }} &middot; {{ order.branch?.phone || '+251 911 123 456' }}
          </div>
        </div>
        <a
          :href="`tel:${order.branch?.phone || '+251911123456'}`"
          class="px-4 py-2 bg-white border border-[#E6DED3] hover:border-[#A87552] text-[#553B2D] font-bold rounded-xl shadow-2xs transition text-center shrink-0"
        >
          Call Bakery
        </a>
      </div>

      <!-- Items Breakdown -->
      <div class="border-t border-[#E6DED3] pt-5 space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#786B60]">Bakes in this Order</h3>
        <div class="divide-y divide-[#F8F3EA]">
          <div
            v-for="item in (order.items || mockItems)"
            :key="item.order_item_id || item.product_name"
            class="py-2.5 flex justify-between text-xs"
          >
            <span class="font-medium text-[#29231F]">{{ item.product_name || item.product?.product_name }} × {{ item.quantity }}</span>
            <span class="font-bold text-[#553B2D]">ETB {{ Number(item.subtotal || item.unit_price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>

        <div v-if="Number(order.delivery_fee) > 0" class="flex justify-between text-xs text-[#786B60] pt-2">
          <span>Delivery Fee</span>
          <span class="font-semibold text-[#29231F]">ETB {{ Number(order.delivery_fee).toLocaleString() }}</span>
        </div>

        <div class="flex justify-between text-sm font-bold text-[#29231F] border-t border-[#E6DED3] pt-3">
          <span>Total Paid</span>
          <span class="text-[#553B2D] text-base font-black">ETB {{ Number(order.total_amount).toLocaleString() }}</span>
        </div>
      </div>

      <!-- Order Actions -->
      <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-[#E6DED3]">
        <router-link
          :to="`/orders/${order.order_number}`"
          class="text-xs font-bold text-[#A87552] hover:underline"
        >
          View Full Interactive Receipt →
        </router-link>

        <router-link
          to="/shop"
          class="px-5 py-2.5 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl transition shadow-xs"
        >
          Order More Bakes
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const route = useRoute();
const toast = useToastStore();

const searchNumber = ref(route.query.order || 'ORD-1001');
const order = ref(null);
const isSearching = ref(false);

const steps = [
  { key: 'Pending', label: 'Order Placed' },
  { key: 'Confirmed', label: 'Confirmed' },
  { key: 'Preparing', label: 'Baking' },
  { key: 'Ready', label: 'Ready / Dispatch' },
  { key: 'Completed', label: 'Completed' },
];

const mockItems = [
  { product_name: 'Belgian Chocolate Cake', quantity: 1, subtotal: 1200 },
  { product_name: 'Artisan Sourdough Loaf', quantity: 2, subtotal: 450 },
];

function formatDate(d) {
  if (!d) return 'Today';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Pending':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-[#553B2D]/10 text-[#553B2D] border border-[#553B2D]/20';
    case 'Preparing':
      return 'bg-[#A87552]/15 text-[#A87552] border border-[#A87552]/30';
    case 'Ready':
    case 'Out for delivery':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Completed':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-[#F8F3EA] text-[#553B2D] border border-[#E6DED3]';
  }
}

function getStepIndex(key) {
  const map = {
    'Pending': 0,
    'Confirmed': 1,
    'Preparing': 2,
    'Ready': 3,
    'Out for delivery': 3,
    'Completed': 4,
  };
  return map[key] ?? 0;
}

function isStepComplete(key) {
  const currentIdx = getStepIndex(order.value?.order_status);
  const stepIdx = getStepIndex(key);
  return currentIdx > stepIdx;
}

function isCurrentStep(key) {
  const currentIdx = getStepIndex(order.value?.order_status);
  const stepIdx = getStepIndex(key);
  return currentIdx === stepIdx;
}

async function searchOrder() {
  if (!searchNumber.value.trim()) {
    toast.error('Please enter an order number.');
    return;
  }

  isSearching.value = true;
  try {
    const res = await api.get(`/orders/${searchNumber.value.trim()}`);
    order.value = res.data;
    toast.success(`Loaded order #${order.value.order_number}`);
  } catch (err) {
    // If not found in API, load mock order for demo
    order.value = {
      order_number: searchNumber.value.trim().toUpperCase(),
      order_status: 'Preparing',
      fulfillment_method: 'Delivery',
      placed_at: new Date().toISOString(),
      branch: {
        branch_name: 'Bole Flagship',
        address: 'Cameroon St, Bole, Addis Ababa',
        phone: '+251 911 123 456',
      },
      items: mockItems,
      delivery_fee: 100,
      total_amount: 1750,
    };
    toast.info('Displaying live simulation for this order.');
  } finally {
    isSearching.value = false;
  }
}

onMounted(() => {
  searchOrder();
});
</script>
