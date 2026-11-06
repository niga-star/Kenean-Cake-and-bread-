<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans selection:bg-[#E8D8C4] selection:text-[#553B2D]">
    <div class="flex items-center justify-between border-b border-[#E6DED3] pb-4">
      <div>
        <h1 class="text-3xl font-serif font-black text-[#553B2D]">Checkout</h1>
        <p class="text-xs text-[#786B60] mt-1">Please enter your fulfillment and payment details to place your order.</p>
      </div>

      <router-link to="/cart" class="text-xs font-bold text-[#A87552] hover:text-[#553B2D] flex items-center space-x-1">
        <span>← Back to Bag</span>
      </router-link>
    </div>

    <!-- Active Branch Notice -->
    <div class="bg-white border border-[#E6DED3] px-5 py-3.5 rounded-2xl flex flex-col sm:flex-row items-center justify-between text-xs text-[#29231F] shadow-2xs gap-3">
      <div class="flex items-center space-x-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-[#387A54] animate-pulse shrink-0"></span>
        <span>Order will be fulfilled strictly by <strong class="text-[#553B2D]">{{ currentBranchName }} Branch</strong>. Stock and prices are locked to this location.</span>
      </div>
      <router-link to="/branches" class="font-bold text-[#A87552] hover:text-[#553B2D] underline text-xs shrink-0">
        Switch branch →
      </router-link>
    </div>

    <!-- Checkout Grid -->
    <form @submit.prevent="handlePlaceOrder" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Left Column: Fulfillment Details (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        <div class="bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-8 space-y-6 shadow-2xs">
          <h2 class="text-base font-serif font-bold text-[#553B2D] border-b border-[#E6DED3] pb-3">
            1. Fulfillment Method
          </h2>

          <!-- Fulfillment Toggle -->
          <div class="flex items-center space-x-3">
            <button
              type="button"
              @click="setMethod('Delivery')"
              class="flex-1 py-3.5 px-4 rounded-2xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer"
              :class="fulfillmentMethod === 'Delivery' 
                ? 'bg-[#553B2D] text-white shadow-xs' 
                : 'bg-[#F8F3EA] border border-[#E6DED3] text-[#786B60] hover:bg-[#E8D8C4]'"
            >
              <span>🚚</span>
              <span>Citywide Doorstep Delivery</span>
            </button>

            <button
              type="button"
              @click="setMethod('Pickup')"
              class="flex-1 py-3.5 px-4 rounded-2xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer"
              :class="fulfillmentMethod === 'Pickup' 
                ? 'bg-[#553B2D] text-white shadow-xs' 
                : 'bg-[#F8F3EA] border border-[#E6DED3] text-[#786B60] hover:bg-[#E8D8C4]'"
            >
              <span>🏪</span>
              <span>Shop Pickup ({{ currentBranchName }})</span>
            </button>
          </div>

          <!-- Preferred Time Window (Pickup or Delivery) -->
          <div class="space-y-2 pt-1">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D]">
              Preferred {{ fulfillmentMethod === 'Pickup' ? 'Pickup' : 'Delivery' }} Window
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                type="button"
                v-for="slot in timeSlots"
                :key="slot.value"
                @click="selectedTimeSlot = slot.value"
                class="py-2.5 px-3 rounded-xl text-xs font-bold transition border text-center cursor-pointer"
                :class="selectedTimeSlot === slot.value 
                  ? 'bg-[#553B2D] text-white border-[#553B2D] shadow-xs' 
                  : 'bg-[#F8F3EA] border-[#E6DED3] text-[#553B2D] hover:bg-[#E8D8C4]'"
              >
                <div class="text-[10px] opacity-75">{{ slot.period }}</div>
                <div class="text-xs mt-0.5">{{ slot.time }}</div>
              </button>
            </div>
          </div>

          <!-- Pickup Details Card if Shop Pickup -->
          <div v-if="fulfillmentMethod === 'Pickup'" class="p-4 bg-[#F8F3EA] rounded-2xl border border-[#E6DED3] space-y-2 text-xs">
            <div class="font-bold text-[#553B2D] flex items-center space-x-2">
              <span class="text-base">📍</span>
              <span>Pickup Counter: {{ currentBranchName }} Branch</span>
            </div>
            <div class="text-[#786B60] pl-6">
              {{ branchDetails?.address || 'Bole Medhanialem Area, Addis Ababa' }}
            </div>
            <div class="text-[#A87552] font-semibold pl-6">
              🕒 Daily Hours: {{ branchDetails?.opening_hours || 'Mon - Sun: 07:00 - 21:00' }}
            </div>
            <div class="text-[#786B60] pl-6">
              📞 Branch Phone: {{ branchDetails?.phone || '+251 911 123 456' }}
            </div>
            <div class="text-[11px] text-[#387A54] font-bold pl-6 pt-1">
              ✓ Free Shop Pickup (ETB 0 fulfillment fee)
            </div>
          </div>

          <!-- Form Fields -->
          <div class="space-y-4 pt-2">
            <h2 class="text-base font-serif font-bold text-[#553B2D] border-b border-[#E6DED3] pb-3">
              2. Contact Information
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
                  Full Name
                </label>
                <input
                  v-model="customer.name"
                  type="text"
                  placeholder="e.g. Abebe Kebede"
                  class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
                  Phone Number (Ethiopian format)
                </label>
                <input
                  v-model="customer.phone"
                  type="text"
                  placeholder="+251 9XX XXX XXX or 09XX XXX XXX"
                  class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
                  required
                />
              </div>
            </div>

            <!-- Home Delivery: Zone & Address -->
            <div v-if="fulfillmentMethod === 'Delivery'" class="space-y-4 pt-2">
              <h2 class="text-base font-serif font-bold text-[#553B2D] border-b border-[#E6DED3] pb-3">
                3. Delivery Address
              </h2>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
                  Select Delivery Zone ({{ currentBranchName }} Coverage Area)
                </label>
                <select
                  v-model="selectedZoneId"
                  @change="onZoneChange"
                  class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none font-medium"
                  required
                >
                  <option value="" disabled>-- Select your delivery zone in Addis Ababa --</option>
                  <option
                    v-for="zone in activeZones"
                    :key="zone.zone_id"
                    :value="zone.zone_id"
                  >
                    {{ zone.zone_name }} — ETB {{ subtotal >= 1000 ? '0 (Free over ETB 1,000)' : zone.delivery_fee }}
                  </option>
                  <option value="outside">Address is outside these active zones</option>
                </select>
              </div>

              <!-- Warning if outside zone -->
              <div v-if="selectedZoneId === 'outside'" class="p-4 bg-[#FAF0F0] border border-[#E8C4C4] rounded-2xl text-xs text-[#B84B42] space-y-2">
                <div class="font-bold flex items-center space-x-1.5">
                  <span>⚠️</span>
                  <span>Outside Delivery Coverage</span>
                </div>
                <p>
                  {{ currentBranchName }} branch does not deliver to addresses outside its designated coverage zone.
                </p>
                <div class="pt-1 flex items-center space-x-3">
                  <button
                    type="button"
                    @click="setMethod('Pickup')"
                    class="px-3 py-1.5 bg-[#B84B42] text-white rounded-xl font-bold text-xs shadow-xs cursor-pointer"
                  >
                    Switch to Shop Pickup (ETB 0)
                  </button>
                  <router-link to="/branches" class="font-bold text-[#B84B42] underline">
                    Switch to another branch
                  </router-link>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
                  Specific Street Address / Building / House No.
                </label>
                <input
                  v-model="customer.address"
                  type="text"
                  placeholder="e.g. Bole Medhanialem, Street 3, Edna Mall Area, House 42"
                  class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
                  required
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
                Special Delivery Instructions / Gate Code (Optional)
              </label>
              <textarea
                v-model="customer.notes"
                rows="2"
                placeholder="Gate code, gift card greeting note, or landmark instructions..."
                class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl p-3 text-xs text-[#29231F] focus:outline-none transition"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary & Payment (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-8 space-y-5 shadow-2xs">
          <h2 class="text-base font-serif font-bold text-[#553B2D] border-b border-[#E6DED3] pb-3">
            Order Review &amp; Payment
          </h2>

          <!-- Purchased Items Summary -->
          <div class="space-y-3 divide-y divide-[#E6DED3] max-h-56 overflow-y-auto">
            <div
              v-for="item in cartItems"
              :key="item.product_id"
              class="pt-3 first:pt-0 flex items-center justify-between text-xs"
            >
              <div class="flex items-center space-x-3">
                <img
                  :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=100'"
                  :alt="item.product_name"
                  class="w-12 h-12 rounded-xl object-cover border border-[#E6DED3] bg-[#E8D8C4]/30"
                />
                <div>
                  <div class="font-bold text-[#29231F]">{{ item.product_name }}</div>
                  <div class="text-[11px] text-[#786B60]">ETB {{ Number(item.price).toLocaleString() }} &times; {{ item.quantity }}</div>
                </div>
              </div>

              <div class="font-black text-[#553B2D]">
                ETB {{ (item.price * item.quantity).toLocaleString() }}
              </div>
            </div>
          </div>

          <!-- Price Calculations -->
          <div class="space-y-2.5 pt-3 border-t border-[#E6DED3] text-xs">
            <div class="flex justify-between items-center text-[#786B60]">
              <span>Subtotal</span>
              <span class="font-bold text-[#29231F]">ETB {{ subtotal.toLocaleString() }}</span>
            </div>

            <div class="flex justify-between items-center text-[#786B60]">
              <span>Delivery Fee</span>
              <span class="font-bold" :class="actualDeliveryFee === 0 ? 'text-[#387A54]' : 'text-[#29231F]'">
                {{ fulfillmentMethod === 'Delivery' ? (actualDeliveryFee === 0 ? 'FREE (Over ETB 1,000)' : `ETB ${actualDeliveryFee}`) : 'ETB 0 (Pickup)' }}
              </span>
            </div>

            <div v-if="discount > 0" class="flex justify-between items-center text-[#387A54]">
              <span class="flex items-center space-x-1.5">
                <span>Promo Discount</span>
                <span v-if="appliedPromo" class="font-mono text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-bold">
                  {{ appliedPromo.promotion_name }}
                </span>
              </span>
              <span class="font-bold">- ETB {{ discount.toLocaleString() }}</span>
            </div>

            <div class="pt-3 border-t border-[#E6DED3] flex justify-between items-center text-base font-black text-[#553B2D]">
              <span>Grand Total</span>
              <span class="text-xl font-serif">ETB {{ grandTotal.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Checkout Promo Code Box -->
          <div class="p-3 bg-[#F8F3EA] rounded-2xl border border-[#E6DED3] space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-[#553B2D] flex items-center space-x-1">
                <span>🎟️</span>
                <span>Coupon Code</span>
              </span>
              <span v-if="appliedPromo" class="font-mono font-bold text-emerald-800 text-[11px]">
                {{ appliedPromo.promotion_name }} Active
              </span>
            </div>
            <div v-if="!appliedPromo" class="flex items-center space-x-2">
              <input
                v-model="checkoutPromoInput"
                @keyup.enter="applyCheckoutPromo"
                type="text"
                placeholder="Code (KENEAN10, YETSOM15...)"
                class="flex-1 bg-white border border-[#D5C2AD] rounded-xl px-2.5 py-1.5 text-xs font-mono uppercase focus:outline-none focus:border-[#A87552]"
              />
              <button
                @click="applyCheckoutPromo"
                :disabled="checkoutPromoLoading || !checkoutPromoInput.trim()"
                class="px-3.5 py-1.5 bg-[#553B2D] hover:bg-[#432E23] text-white text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-50 shrink-0"
              >
                {{ checkoutPromoLoading ? '...' : 'Apply' }}
              </button>
            </div>
            <div v-else class="flex items-center justify-between text-[11px] text-emerald-800 font-semibold bg-white p-2 rounded-xl border border-emerald-200">
              <span>Savings: - ETB {{ discount.toLocaleString() }}</span>
              <button @click="removeCheckoutPromo" class="text-red-600 hover:text-red-800 text-xs font-bold cursor-pointer">
                Remove
              </button>
            </div>
            <div v-if="checkoutPromoError" class="text-[11px] text-red-600 font-medium">
              ⚠️ {{ checkoutPromoError }}
            </div>
          </div>

          <!-- Payment Options -->
          <div class="space-y-3 pt-3 border-t border-[#E6DED3]">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#553B2D]">Select Payment Method</h3>

            <div class="space-y-2">
              <label
                class="flex items-center space-x-3 p-3.5 rounded-2xl border cursor-pointer transition"
                :class="paymentMethod === 'cod' ? 'border-[#A87552] bg-[#F8F3EA]' : 'border-[#E6DED3] bg-white'"
              >
                <input
                  type="radio"
                  value="cod"
                  v-model="paymentMethod"
                  class="text-[#A87552] focus:ring-[#A87552]"
                />
                <div>
                  <span class="text-xs font-bold text-[#29231F]">
                    💵 {{ fulfillmentMethod === 'Pickup' ? 'Pay on Pickup' : 'Pay on Delivery (Cash)' }}
                  </span>
                  <div class="text-[10px] text-[#786B60]">
                    {{ fulfillmentMethod === 'Pickup' ? 'Pay upon collecting your order at the counter' : 'Pay cash to driver upon doorstep arrival' }}
                  </div>
                </div>
              </label>

              <label
                class="flex items-center space-x-3 p-3.5 rounded-2xl border cursor-pointer transition"
                :class="paymentMethod === 'online' ? 'border-[#A87552] bg-[#F8F3EA]' : 'border-[#E6DED3] bg-white'"
              >
                <input
                  type="radio"
                  value="online"
                  v-model="paymentMethod"
                  class="text-[#A87552] focus:ring-[#A87552]"
                />
                <div class="text-xs">
                  <div class="font-bold text-[#29231F]">💳 Online Payment (Telebirr / Chapa)</div>
                  <div class="text-[10px] text-[#786B60]">Instant encrypted digital payment confirmation</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Error Alert Banner -->
          <div v-if="orderError" class="p-3 bg-[#FAF0F0] border border-[#E8C4C4] text-xs text-[#B84B42] rounded-xl">
            {{ orderError }}
          </div>

          <!-- Place Order Button -->
          <button
            type="submit"
            :disabled="isSubmitting || (fulfillmentMethod === 'Delivery' && selectedZoneId === 'outside')"
            class="w-full py-4 bg-[#A87552] hover:bg-[#916140] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md transition active:scale-98 cursor-pointer"
          >
            {{ isSubmitting ? 'Confirming with bakery...' : `Place Order &middot; ETB ${grandTotal.toLocaleString()} →` }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();

const fulfillmentMethod = ref(cartStore.fulfillmentMethod || 'Delivery');
const paymentMethod = ref('online');
const baseDeliveryFee = ref(60);
const selectedZoneId = ref(null);
const activeZones = ref([]);
const branchDetails = ref(null);
const isSubmitting = ref(false);
const orderError = ref(null);

const timeSlots = [
  { period: '🌅 Morning', time: '08:00 – 11:00 AM', value: 'Morning (08:00 - 11:00 AM)' },
  { period: '☀️ Midday', time: '11:00 AM – 02:00 PM', value: 'Midday (11:00 AM - 02:00 PM)' },
  { period: '🌇 Afternoon', time: '02:00 – 05:00 PM', value: 'Afternoon (02:00 - 05:00 PM)' },
  { period: '🌙 Evening', time: '05:00 – 08:00 PM', value: 'Evening (05:00 - 08:00 PM)' },
];
const selectedTimeSlot = ref('Morning (08:00 - 11:00 AM)');

const customer = ref({
  name: '',
  phone: '',
  address: '',
  notes: '',
});

const currentBranchName = computed(() => branchStore.selectedBranch?.branch_name || 'Bole');

const cartItems = computed(() => cartStore.items);

const checkoutPromoInput = ref('');
const checkoutPromoLoading = ref(false);
const checkoutPromoError = ref('');

const appliedPromo = computed(() => cartStore.appliedPromo);
const discount = computed(() => cartStore.discountAmount);

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
});

const actualDeliveryFee = computed(() => {
  if (fulfillmentMethod.value !== 'Delivery') return 0;
  if (subtotal.value >= 1000) return 0; // Free delivery promotion
  return baseDeliveryFee.value;
});

const grandTotal = computed(() => {
  return Math.max(0, (subtotal.value - discount.value) + actualDeliveryFee.value);
});

async function applyCheckoutPromo() {
  const code = checkoutPromoInput.value.trim().toUpperCase();
  if (!code) {
    checkoutPromoError.value = 'Please enter a valid coupon code.';
    return;
  }
  checkoutPromoLoading.value = true;
  checkoutPromoError.value = '';
  try {
    const res = await api.post('/promotions/validate', {
      code,
      subtotal: subtotal.value,
      branch_id: branchStore.selectedBranchId || cartStore.branchId || 2,
    });
    if (res.data?.valid) {
      cartStore.setPromo(res.data);
      checkoutPromoInput.value = '';
      toastStore.success(res.data.message || `Coupon ${code} applied successfully!`);
    } else {
      checkoutPromoError.value = res.data?.message || 'Invalid promotion code.';
    }
  } catch (err) {
    checkoutPromoError.value = err.response?.data?.message || 'Invalid or expired promotion code.';
  } finally {
    checkoutPromoLoading.value = false;
  }
}

function removeCheckoutPromo() {
  cartStore.removePromo();
  toastStore.info('Promotion removed.');
}

function setMethod(method) {
  fulfillmentMethod.value = method;
}

function onZoneChange() {
  if (selectedZoneId.value && selectedZoneId.value !== 'outside') {
    const zone = activeZones.value.find((z) => z.zone_id === selectedZoneId.value);
    if (zone) {
      baseDeliveryFee.value = parseFloat(zone.delivery_fee) || 60;
    }
  }
}

onMounted(async () => {
  if (authStore.user) {
    customer.value.name = authStore.user.full_name || '';
    customer.value.phone = authStore.user.phone || '';
  }

  const branchId = branchStore.selectedBranchId || 2;

  // Load branch delivery zones
  try {
    const zoneRes = await api.get(`/branches/${branchId}/delivery-zones`);
    const zones = zoneRes.data || [];
    activeZones.value = zones.filter((z) => z.status === 'Active');

    if (activeZones.value.length > 0) {
      selectedZoneId.value = activeZones.value[0].zone_id;
      baseDeliveryFee.value = parseFloat(activeZones.value[0].delivery_fee) || 60;
    }
  } catch (err) {
    activeZones.value = [
      { zone_id: 1, zone_name: 'Bole Medhanialem & Edna Mall Area', delivery_fee: 60, status: 'Active' },
      { zone_id: 2, zone_name: 'Atlas & Rwanda Area', delivery_fee: 50, status: 'Active' },
      { zone_id: 3, zone_name: 'Olympia & Meskel Flower', delivery_fee: 60, status: 'Active' },
    ];
    selectedZoneId.value = 1;
  }

  try {
    const branchRes = await api.get(`/branches/${branchId}`);
    branchDetails.value = branchRes.data;
  } catch (err) {
    console.error('Failed to load branch details', err);
  }
});

async function handlePlaceOrder() {
  orderError.value = null;

  if (cartItems.value.length === 0) {
    orderError.value = 'Your shopping bag is empty. Please add items before checking out.';
    return;
  }

  if (fulfillmentMethod.value === 'Delivery' && selectedZoneId.value === 'outside') {
    orderError.value = 'Home delivery is only available within designated active delivery zones for this branch. Please choose an active zone or select Shop Pickup.';
    return;
  }

  isSubmitting.value = true;
  try {
    const mappedPayment = paymentMethod.value === 'online'
      ? 'Chapa'
      : (fulfillmentMethod.value === 'Pickup' ? 'Pay on pickup' : 'Pay on delivery');

    const idempotencyKey = 'order_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7);

    const payload = {
      branch_id: branchStore.selectedBranchId || 2,
      fulfillment_method: fulfillmentMethod.value,
      payment_method: mappedPayment,
      delivery_address: customer.value.address || 'Shop Counter Pickup',
      promo_code: appliedPromo.value?.promotion_name || null,
      order_notes: (selectedTimeSlot.value ? `[Slot: ${selectedTimeSlot.value}] ` : '') + (customer.value.notes || ''),
      zone_id: fulfillmentMethod.value === 'Delivery' ? selectedZoneId.value : null,
      idempotency_key: idempotencyKey,
      items: cartItems.value.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
      })),
    };

    const res = await api.post('/orders', payload, {
      headers: {
        'Idempotency-Key': idempotencyKey,
      }
    });

    const orderNumber = res.data?.order_number || 'ORD-1001';
    cartStore.clearCart();
    toastStore.success(`Order ${orderNumber} placed successfully!`);
    router.push(`/orders/${orderNumber}`);
  } catch (err) {
    orderError.value = err.response?.data?.message || 'Failed to place order. Please review your stock or address.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>
