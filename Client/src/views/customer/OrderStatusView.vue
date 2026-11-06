<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
    <!-- Status Alert Header Banner -->
    <div
      class="p-5 rounded-3xl text-xs space-y-1.5 shadow-2xs border transition"
      :class="{
        'bg-[#387A54]/10 border-[#387A54]/30 text-[#387A54]': order?.order_status === 'Confirmed' || order?.order_status === 'Completed',
        'bg-[#B98235]/10 border-[#B98235]/30 text-[#B98235]': order?.order_status === 'Pending',
        'bg-[#A87552]/10 border-[#A87552]/30 text-[#553B2D]': order?.order_status === 'Preparing',
        'bg-[#553B2D]/10 border-[#553B2D]/30 text-[#553B2D]': order?.order_status === 'Out for delivery' || order?.order_status === 'Ready',
        'bg-[#B84B42]/10 border-[#B84B42]/30 text-[#B84B42]': order?.order_status === 'Cancelled'
      }"
    >
      <div class="font-bold text-sm sm:text-base flex items-center space-x-2">
        <span>{{ statusIcon }}</span>
        <span>{{ statusHeadline }}</span>
      </div>
      <div class="text-xs text-[#786B60]">
        Fulfilling bakery: <strong class="text-[#29231F]">{{ order?.branch?.branch_name || 'Bole Flagship' }} ({{ currentBranchCode }})</strong>. Stock was reserved and items are fulfilled directly from this kitchen.
      </div>
    </div>

    <!-- Order Header Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E6DED3] pb-6">
      <div>
        <div class="text-[11px] font-bold uppercase tracking-widest text-[#786B60]">Order Confirmation</div>
        <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#29231F] mt-0.5">
          {{ order?.order_number || orderNumberParam }}
        </h1>
        <div class="text-xs text-[#786B60] mt-1">
          Placed on {{ orderDateFormatted }} &middot; Estimated fulfillment: 45–60 mins
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <span
          class="px-4 py-1.5 rounded-full text-xs font-bold capitalize shadow-2xs"
          :class="statusBadgeClass(order?.order_status || 'Pending')"
        >
          {{ order?.order_status || 'Pending' }}
        </span>

        <button
          @click="printReceipt"
          class="px-4 py-2 bg-white border border-[#E6DED3] hover:border-[#A87552] text-xs font-bold text-[#553B2D] rounded-xl transition shadow-2xs flex items-center space-x-1.5"
        >
          <span>🖨️</span>
          <span>Print Receipt</span>
        </button>
      </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
      <!-- Left Column: Stepper Timeline (5 cols) -->
      <div class="md:col-span-5 bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-7 shadow-xs space-y-6">
        <div class="flex items-center justify-between border-b border-[#E6DED3] pb-3">
          <h2 class="text-base font-serif font-bold text-[#29231F]">Preparation Progress</h2>
          <span class="text-[11px] text-[#A87552] font-bold">Live Status</span>
        </div>

        <!-- Vertical Stepper Steps -->
        <div class="relative space-y-7 pl-2">
          <!-- Step 1: Order Placed -->
          <div class="flex items-start space-x-3.5 relative">
            <div class="w-8 h-8 rounded-full bg-[#387A54] text-white flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs">
              ✓
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">1 &middot; Order Placed</div>
              <div class="text-[11px] text-[#786B60]">Stock reserved at {{ order?.branch?.branch_name || 'Bole' }}</div>
            </div>
            <!-- Connecting Line -->
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(2) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 2: Confirmed by Branch -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(2) ? 'bg-[#387A54] text-white' : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(2) ? '✓' : '2' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">2 &middot; Confirmed by Branch</div>
              <div class="text-[11px] text-[#786B60]">
                {{ isStepActiveOrDone(2) ? 'Payment verified & order accepted' : `Awaiting kitchen confirmation` }}
              </div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(3) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 3: Preparing -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(3) ? (isStepActiveOrDone(4) ? 'bg-[#387A54] text-white' : 'bg-[#A87552] text-white') : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(4) ? '✓' : '3' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">3 &middot; Artisan Baking &amp; Packing</div>
              <div class="text-[11px] text-[#786B60]">Ovens firing, decorating, and boxing</div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(4) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 4: Out for Delivery / Ready -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(4) ? (isStepActiveOrDone(5) ? 'bg-[#387A54] text-white' : 'bg-[#553B2D] text-white') : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(5) ? '✓' : '4' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">
                4 &middot; {{ order?.fulfillment_method === 'Pickup' ? 'Ready for Pickup' : 'Dispatched / Out for Delivery' }}
              </div>
              <div class="text-[11px] text-[#786B60]">
                {{ order?.fulfillment_method === 'Pickup' ? 'Ready at bakery counter' : 'Courier en route to your address' }}
              </div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(5) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 5: Completed -->
          <div class="flex items-start space-x-3.5">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(5) ? 'bg-[#387A54] text-white' : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(5) ? '✓' : '5' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">
                5 &middot; {{ order?.fulfillment_method === 'Pickup' ? 'Picked Up' : 'Delivered' }}
              </div>
              <div class="text-[11px] text-[#786B60]">Order complete. Fresh bakery enjoyed!</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details & Items Card (7 cols) -->
      <div class="md:col-span-7 bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-7 shadow-xs space-y-6">
        <h2 class="text-base font-serif font-bold text-[#29231F] border-b border-[#E6DED3] pb-3">
          Order Summary &amp; Receipt
        </h2>

        <!-- Metadata Grid -->
        <div class="space-y-3 text-xs border-b border-[#E6DED3] pb-4">
          <div class="flex justify-between items-center">
            <span class="text-[#786B60]">Fulfilling Branch</span>
            <span class="font-bold text-[#29231F]">
              {{ order?.branch?.branch_name || 'Bole Flagship' }} ({{ currentBranchCode }})
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-[#786B60]">Fulfillment Mode</span>
            <span class="font-bold text-[#553B2D]">
              {{ order?.fulfillment_method === 'Delivery' ? '🚀 Doorstep Delivery' : '🛍️ In-Store Pickup' }}
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-[#786B60]">Payment Method</span>
            <span class="font-bold text-[#387A54]">
              {{ order?.payment?.payment_method || 'Online (Chapa)' }} &middot; {{ order?.payment?.payment_status || 'Paid' }}
            </span>
          </div>

          <div class="flex justify-between items-start">
            <span class="text-[#786B60]">{{ order?.fulfillment_method === 'Pickup' ? 'Pickup Location' : 'Delivery Address' }}</span>
            <span class="font-semibold text-[#29231F] text-right max-w-[280px]">
              {{ order?.fulfillment_method === 'Pickup' ? (order?.branch?.address || 'Cameroon St, Bole, Addis Ababa') : (order?.address?.street || order?.delivery_address || 'Customer Address, Addis Ababa') }}
            </span>
          </div>
        </div>

        <!-- Items Breakdown -->
        <div class="space-y-3 text-xs">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#786B60]">
            Bakes in this Order ({{ displayItems.length }})
          </div>

          <div class="space-y-2 divide-y divide-[#F8F3EA]">
            <div
              v-for="item in displayItems"
              :key="item.order_item_id || item.product_name"
              class="pt-2 first:pt-0 flex justify-between items-center"
            >
              <div class="flex items-center space-x-2.5">
                <span class="w-6 h-6 rounded-lg bg-[#F8F3EA] flex items-center justify-center text-xs font-bold text-[#553B2D]">
                  {{ item.quantity }}×
                </span>
                <span class="text-[#29231F] font-medium">{{ item.product?.product_name || item.product_name }}</span>
              </div>
              <span class="font-bold text-[#553B2D]">
                ETB {{ ((item.unit_price || item.price) * item.quantity).toLocaleString() }}
              </span>
            </div>
          </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-4 border-t border-[#E6DED3] space-y-2 text-xs">
          <div class="flex justify-between items-center text-[#786B60]">
            <span>Subtotal</span>
            <span class="font-semibold text-[#29231F]">ETB {{ itemsSubtotal.toLocaleString() }}</span>
          </div>

          <div v-if="Number(order?.delivery_fee) > 0" class="flex justify-between items-center text-[#786B60]">
            <span>Delivery Fee</span>
            <span class="font-semibold text-[#29231F]">ETB {{ Number(order?.delivery_fee).toLocaleString() }}</span>
          </div>

          <div class="pt-2 border-t border-[#E6DED3] flex justify-between items-center text-base font-bold text-[#29231F]">
            <span>Grand Total</span>
            <span class="text-[#553B2D] text-lg font-black">ETB {{ Number(order?.total_amount || itemsSubtotal).toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Questions Box -->
    <div class="bg-[#F8F3EA] rounded-3xl border border-[#E6DED3] p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="space-y-1">
        <h3 class="text-xs font-bold text-[#29231F] uppercase tracking-wider flex items-center space-x-1.5">
          <span>📞</span>
          <span>Questions about this bake order?</span>
        </h3>
        <p class="text-xs text-[#786B60] leading-relaxed">
          Call {{ order?.branch?.branch_name || 'Bole Flagship' }} directly at
          <strong class="text-[#553B2D]">{{ order?.branch?.phone || '+251 911 123 456' }}</strong>.
          Every stock item and order is scoped strictly to this branch.
        </p>
      </div>

      <a
        :href="`tel:${order?.branch?.phone || '+251911123456'}`"
        class="px-5 py-2.5 bg-white border border-[#E6DED3] hover:border-[#A87552] text-xs font-bold text-[#553B2D] rounded-xl shadow-2xs transition shrink-0"
      >
        Call Bakery
      </a>
    </div>

    <!-- Bottom Buttons Row -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
      <router-link
        to="/shop"
        class="px-6 py-3 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl shadow-xs transition"
      >
        ← Continue Shopping
      </router-link>

      <!-- Cancel Order Button with rules -->
      <div class="flex items-center space-x-3">
        <button
          v-if="canCustomerCancel"
          @click="cancelOrder"
          :disabled="cancelling"
          class="px-5 py-2.5 bg-white border border-[#B84B42]/40 hover:bg-[#B84B42]/10 text-[#B84B42] text-xs font-bold rounded-xl transition"
        >
          {{ cancelling ? 'Cancelling Order...' : 'Cancel Order' }}
        </button>

        <span
          v-else-if="order?.order_status !== 'Cancelled' && order?.order_status !== 'Completed'"
          class="text-xs text-[#786B60] italic"
        >
          Cancellation is locked once artisan baking begins.
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const route = useRoute();
const toast = useToastStore();
const orderNumberParam = computed(() => route.params.order_number || route.params.id || 'ORD-1001');

const order = ref(null);
const cancelling = ref(false);

const orderDateFormatted = computed(() => {
  const d = order.value?.placed_at || order.value?.created_at;
  if (!d) return 'Today';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
});

const currentBranchCode = computed(() => {
  const b = order.value?.branch;
  if (!b) return 'Branch 01';
  return b.branch_type === 'Main' ? 'Main HQ' : `Branch ${String(b.branch_id).padStart(2, '0')}`;
});

const statusIcon = computed(() => {
  const s = order.value?.order_status || 'Pending';
  switch (s) {
    case 'Confirmed': return '✅';
    case 'Pending': return '⏳';
    case 'Preparing': return '👨‍🍳';
    case 'Ready': return '🛍️';
    case 'Out for delivery': return '🚀';
    case 'Completed': return '🎉';
    case 'Cancelled': return '❌';
    default: return '📦';
  }
});

const statusHeadline = computed(() => {
  const s = order.value?.order_status || 'Pending';
  switch (s) {
    case 'Confirmed': return 'Order confirmed & payment verified!';
    case 'Pending': return 'Order placed. Stock reserved, awaiting confirmation.';
    case 'Preparing': return 'Master bakers are crafting your order in the kitchen.';
    case 'Ready': return order.value?.fulfillment_method === 'Pickup' ? 'Your order is ready for pickup at the counter!' : 'Order boxed and ready for delivery courier.';
    case 'Out for delivery': return 'Courier is en route to your doorstep!';
    case 'Completed': return 'Order completed. Fresh confectionery enjoyed!';
    case 'Cancelled': return 'This order was cancelled.';
    default: return 'Order In Progress';
  }
});

const canCustomerCancel = computed(() => {
  const s = order.value?.order_status;
  return s === 'Pending' || s === 'Confirmed';
});

const displayItems = computed(() => {
  if (order.value?.items && order.value.items.length > 0) {
    return order.value.items;
  }
  return [
    { product_name: 'Belgian Chocolate Cake', quantity: 1, unit_price: 1200 },
    { product_name: 'Artisan Sourdough Loaf', quantity: 2, unit_price: 225 },
  ];
});

const itemsSubtotal = computed(() => {
  return displayItems.value.reduce((acc, item) => acc + ((item.unit_price || item.price || 0) * (item.quantity || 1)), 0);
});

const loadOrder = async () => {
  try {
    const res = await api.get(`/orders/${orderNumberParam.value}`);
    order.value = res.data;
  } catch (err) {
    console.error('Failed to load order', err);
  }
};

onMounted(() => {
  loadOrder();
});

function isStepActiveOrDone(step) {
  const status = order.value?.order_status || 'Pending';
  if (status === 'Cancelled') return false;
  if (status === 'Pending') return step <= 1;
  if (status === 'Confirmed') return step <= 2;
  if (status === 'Preparing') return step <= 3;
  if (status === 'Out for delivery' || status === 'Ready') return step <= 4;
  if (status === 'Completed') return step <= 5;
  return false;
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Pending':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-[#553B2D]/10 text-[#553B2D] border border-[#553B2D]/20';
    case 'Preparing':
      return 'bg-[#A87552]/15 text-[#A87552] border border-[#A87552]/30';
    case 'Out for delivery':
    case 'Ready':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Completed':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-[#F8F3EA] text-[#553B2D] border border-[#E6DED3]';
  }
}

async function cancelOrder() {
  if (!confirm('Are you sure you want to cancel this order? Any payment made will be refunded and stock reservation released.')) {
    return;
  }

  cancelling.value = true;
  try {
    await api.post(`/orders/${order.value?.order_number || orderNumberParam.value}/cancel`, {
      reason: 'Cancelled by customer before preparation',
    });
    toast.success('Your order has been cancelled and stock reservation released.');
    await loadOrder();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to cancel order.');
  } finally {
    cancelling.value = false;
  }
}

function printReceipt() {
  window.print();
}
</script>
