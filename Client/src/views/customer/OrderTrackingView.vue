<template>
  <div class="sweetbakes-container py-8 sm:py-10 space-y-8 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <!-- Header -->
    <div class="text-center space-y-3 max-w-xl mx-auto">
      <div class="inline-flex items-center space-x-1.5 px-3 py-1 bg-rose-50 dark:bg-[#341822] text-[#DE1B54] border border-rose-200 dark:border-[#58263A] text-xs font-bold rounded-full">
        <span>📍</span>
        <span>{{ localeStore.t('realtime_tracking_badge') }}</span>
      </div>
      <h1 class="text-3xl sm:text-4xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
        {{ localeStore.t('tracking_title') }}
      </h1>
      <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
        {{ localeStore.t('tracking_hero_desc') }}
      </p>
    </div>

    <!-- Search Input Box -->
    <div class="flex items-center space-x-2 bg-white dark:bg-[#1E120B] p-2.5 rounded-2xl border border-gray-200 dark:border-[#382318] shadow-xs max-w-lg mx-auto">
      <div class="pl-3 text-base text-[#786B60] dark:text-[#C5B7AF]">🔍</div>
      <input
        v-model="searchNumber"
        type="text"
        :placeholder="localeStore.t('order_num_placeholder')"
        @keyup.enter="searchOrder"
        class="flex-1 px-3 py-2 text-xs font-mono font-bold text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none uppercase bg-transparent"
      />
      <button
        @click="searchOrder"
        :disabled="isSearching"
        class="px-6 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer active:scale-95"
      >
        {{ isSearching ? localeStore.t('tracking_btn_loading') : localeStore.t('track_now_btn') }}
      </button>
    </div>

    <!-- Order Tracking Display Card -->
    <div v-if="order" class="bg-white dark:bg-[#1E120B] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-[#382318] shadow-xs space-y-6">
      <!-- Order Header Row -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-[#382318] pb-4">
        <div>
          <span class="text-xs font-mono font-bold text-[#DE1B54]">#{{ order.order_number }}</span>
          <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE] mt-0.5">
            {{ localeStore.t('fulfilling_branch_label') }} {{ localeStore.tBranch(order.branch?.branch_name || 'Bole') }}
          </h2>
          <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
            {{ order.fulfillment_method === 'Delivery' ? ('🚀 ' + localeStore.t('doorstep_deliv_label')) : ('🛍️ ' + localeStore.t('instore_pickup_label')) }} &middot; {{ localeStore.t('placed_on_label') }} {{ formatDate(order.placed_at || order.created_at) }}
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
          <div class="absolute top-1/2 left-4 right-4 h-1 bg-gray-200 dark:bg-[#382318] -translate-y-1/2 z-0"></div>

          <div
            v-for="(step, i) in steps"
            :key="step.key"
            class="relative z-10 flex flex-col items-center"
          >
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 shadow-xs"
              :class="isStepComplete(step.key) ? 'bg-[#387A54] text-white ring-4 ring-[#387A54]/20' : (isCurrentStep(step.key) ? 'bg-[#DE1B54] text-white ring-4 ring-[#DE1B54]/30 scale-110' : 'bg-[#FAF7F5] dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] border border-gray-200 dark:border-[#382318]')"
            >
              <span v-if="isStepComplete(step.key)">✓</span>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span
              class="text-[11px] mt-2.5 font-semibold tracking-tight text-center max-w-[85px] leading-tight"
              :class="isCurrentStep(step.key) ? 'text-[#DE1B54] font-bold' : (isStepComplete(step.key) ? 'text-[#387A54] dark:text-emerald-400' : 'text-[#786B60] dark:text-[#C5B7AF]')"
            >
              {{ step.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Branch Info Box -->
      <div class="p-4 bg-[#F8F3EA] dark:bg-[#20120B] rounded-2xl border border-[#E6DED3] dark:border-[#382318] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="space-y-0.5">
          <div class="font-bold text-[#29231F] dark:text-[#FAF3EE] flex items-center space-x-1.5">
            <span>🏛️</span>
            <span>{{ localeStore.tBranch(order.branch?.branch_name || 'Bole') }} {{ localeStore.isAmharic ? 'ዳቦ ቤት' : 'Bakehouse' }}</span>
          </div>
          <div class="text-[#786B60] dark:text-[#C5B7AF]">
            {{ order.branch?.address || 'Cameroon St, Bole' }} &middot; {{ order.branch?.phone || '+251 911 123 456' }}
          </div>
        </div>
        <a
          :href="`tel:${order.branch?.phone || '+251911123456'}`"
          class="px-4 py-2 min-h-[40px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] font-bold rounded-xl shadow-2xs transition text-center shrink-0 flex items-center justify-center cursor-pointer"
        >
          {{ localeStore.t('call_bakery_btn') }}
        </a>
      </div>

      <!-- Items Breakdown -->
      <div class="border-t border-gray-100 dark:border-[#382318] pt-5 space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#786B60] dark:text-[#A8988E]">{{ localeStore.t('bakes_in_order') }}</h3>
        <div class="divide-y divide-gray-100 dark:divide-[#382318]">
          <div
            v-for="item in (order.items || mockItems)"
            :key="item.order_item_id || item.product_name"
            class="py-2.5 flex justify-between text-xs"
          >
            <span class="font-medium text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tProduct(item.product_name || item.product?.product_name) }} × {{ item.quantity }}</span>
            <span class="font-bold text-[#DE1B54]">{{ localeStore.formatPrice(item.subtotal || item.unit_price * item.quantity) }}</span>
          </div>
        </div>

        <div v-if="Number(order.delivery_fee) > 0" class="flex justify-between text-xs text-[#786B60] dark:text-[#A8988E] pt-2">
          <span>{{ localeStore.t('delivery_fee') }}</span>
          <span class="font-semibold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.formatPrice(order.delivery_fee) }}</span>
        </div>

        <div class="flex justify-between text-sm font-bold text-[#2A1810] dark:text-[#FAF3EE] border-t border-gray-100 dark:border-[#382318] pt-3">
          <span>{{ localeStore.t('total_amount') }}</span>
          <span class="text-[#DE1B54] text-base font-black">{{ localeStore.formatPrice(order.total_amount) }}</span>
        </div>
      </div>

      <!-- Order Actions -->
      <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 dark:border-[#382318]">
        <router-link
          :to="`/orders/${order.order_number}`"
          class="text-xs font-bold text-[#DE1B54] hover:underline"
        >
          {{ localeStore.isAmharic ? 'ሙሉ ደረሰኝ ይመልከቱ →' : 'View Full Interactive Receipt →' }}
        </router-link>

        <router-link
          to="/shop"
          class="px-5 py-2.5 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center cursor-pointer"
        >
          {{ localeStore.isAmharic ? 'ተጨማሪ ኬኮች ይዘዙ' : 'Order More Bakes' }}
        </router-link>
      </div>
    </div>

    <!-- Clean Empty State when No Order Loaded -->
    <div v-else class="text-center py-16 px-4 bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] max-w-lg mx-auto space-y-3">
      <div class="text-4xl">🔍</div>
      <h3 class="font-serif font-bold text-lg text-[#2A1810] dark:text-[#FAF3EE]">
        {{ hasSearched ? localeStore.t('order_not_found_title') : localeStore.t('tracking_title') }}
      </h3>
      <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] max-w-sm mx-auto">
        {{ hasSearched ? localeStore.t('order_not_found_desc') : localeStore.t('tracking_hero_desc') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const route = useRoute();
const toast = useToastStore();
const localeStore = useLocaleStore();

const searchNumber = ref(route.query.order || '');
const order = ref(null);
const isSearching = ref(false);
const hasSearched = ref(false);

const steps = computed(() => [
  { key: 'Pending', label: localeStore.isAmharic ? 'ትዕዛዝ ተቀብለናል' : 'Order Placed' },
  { key: 'Confirmed', label: localeStore.isAmharic ? 'ተረጋግጧል' : 'Confirmed' },
  { key: 'Preparing', label: localeStore.isAmharic ? 'በመጋገር ላይ' : 'Baking' },
  { key: 'Ready', label: localeStore.isAmharic ? 'ለመላክ ዝግጁ' : 'Ready / Dispatch' },
  { key: 'Completed', label: localeStore.isAmharic ? 'ተጠናቋል' : 'Completed' },
]);

function formatDate(d) {
  if (!d) return 'Today';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Pending':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-rose-50 text-[#DE1B54] border border-rose-200 dark:bg-[#341822] dark:border-[#58263A]';
    case 'Preparing':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Ready':
    case 'Out for delivery':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Completed':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-gray-100 text-[#2A1810] border border-gray-200 dark:bg-[#20120B] dark:text-[#FAF3EE] dark:border-[#382318]';
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
  hasSearched.value = true;
  try {
    const res = await api.get(`/orders/${searchNumber.value.trim()}`);
    order.value = res.data;
    toast.success(`Loaded order #${order.value.order_number}`);
  } catch (err) {
    order.value = null;
    toast.error('Order not found. Please verify your order number and try again.');
  } finally {
    isSearching.value = false;
  }
}

onMounted(() => {
  if (searchNumber.value) {
    searchOrder();
  }
});
</script>
