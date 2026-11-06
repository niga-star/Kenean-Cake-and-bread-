<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans selection:bg-[#E8D8C4] selection:text-[#553B2D]">
    <!-- Header with Continue Shopping -->
    <div class="flex items-center justify-between border-b border-[#E6DED3] pb-4">
      <div class="flex items-center space-x-3">
        <span class="text-3xl">🛒</span>
        <h1 class="text-3xl font-serif font-black text-[#553B2D]">Your Artisan Bag</h1>
      </div>

      <router-link
        to="/shop"
        class="text-xs font-bold text-[#A87552] hover:text-[#553B2D] flex items-center space-x-1"
      >
        <span>← Continue Shopping</span>
      </router-link>
    </div>

    <!-- Active Branch Notice -->
    <div class="bg-white border border-[#E6DED3] px-5 py-3.5 rounded-2xl flex flex-col sm:flex-row items-center justify-between text-xs text-[#29231F] shadow-2xs gap-3">
      <div class="flex items-center space-x-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-[#387A54] animate-pulse shrink-0"></span>
        <span>Shopping at <strong class="text-[#553B2D]">{{ currentBranchName }} Branch</strong>. Quantities are strictly reserved from this bakery's on-hand inventory.</span>
      </div>
      <router-link to="/branches" class="font-bold text-[#A87552] hover:text-[#553B2D] underline text-xs shrink-0">
        Change branch →
      </router-link>
    </div>

    <!-- Free Delivery Progress Bar -->
    <div class="bg-white rounded-2xl border border-[#E6DED3] p-4 shadow-2xs space-y-2">
      <div class="flex justify-between items-center text-xs">
        <span v-if="subtotal >= 1000" class="font-bold text-[#387A54] flex items-center space-x-1">
          <span>🎉</span>
          <span>Congratulations! You qualify for Free Delivery across Addis Ababa.</span>
        </span>
        <span v-else class="text-[#786B60]">
          Add <strong class="text-[#553B2D]">ETB {{ (1000 - subtotal).toLocaleString() }}</strong> more to unlock <strong>Free Delivery</strong>!
        </span>
        <span class="font-bold text-[#553B2D]">{{ Math.min(100, Math.round((subtotal / 1000) * 100)) }}%</span>
      </div>
      <div class="w-full h-2 bg-[#F8F3EA] rounded-full overflow-hidden border border-[#E6DED3]">
        <div
          class="h-full bg-[#A87552] transition-all duration-500 rounded-full"
          :style="{ width: `${Math.min(100, (subtotal / 1000) * 100)}%` }"
        ></div>
      </div>
    </div>

    <!-- Revalidation Warnings Banner -->
    <div
      v-if="cartStore.revalidationWarnings.length > 0"
      class="bg-[#FAF6EC] border-2 border-[#B98235] rounded-3xl p-5 shadow-xs space-y-3"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs font-bold text-[#B98235]">
          <span class="text-base">⚠️</span>
          <span>Stock &amp; Price Revalidation for {{ currentBranchName }} Branch</span>
        </div>
        <button
          @click="cartStore.clearWarnings"
          class="text-xs font-bold text-[#B98235] hover:underline cursor-pointer"
        >
          Dismiss All
        </button>
      </div>

      <div class="space-y-3">
        <div
          v-for="(warn, wIdx) in cartStore.revalidationWarnings"
          :key="wIdx"
          class="bg-white/95 rounded-2xl p-4 border border-[#E8D8B8] text-xs space-y-2 shadow-2xs"
        >
          <div class="flex items-start justify-between">
            <div class="text-[#553B2D] font-semibold leading-relaxed">
              {{ warn.message }}
            </div>
            <button
              @click="cartStore.dismissWarning(wIdx)"
              class="text-gray-400 hover:text-gray-600 text-xs ml-2 cursor-pointer"
            >
              ✕
            </button>
          </div>

          <div v-if="warn.suggestions && warn.suggestions.length > 0" class="pt-2 space-y-1.5 border-t border-[#FAF6EC]">
            <div class="text-[11px] font-bold text-[#B98235]">
              Available at other Addis branches:
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <div
                v-for="alt in warn.suggestions"
                :key="alt.branch_id"
                class="inline-flex items-center space-x-2 bg-[#F8F3EA] border border-[#E6DED3] px-3 py-1 rounded-xl text-[11px]"
              >
                <span class="font-bold text-[#29231F]">{{ alt.branch_name }}</span>
                <span class="text-[#786B60]">({{ alt.available }} available &middot; ETB {{ Number(alt.price).toLocaleString() }})</span>
                <button
                  type="button"
                  @click="switchBranch(alt)"
                  class="px-2 py-0.5 bg-[#553B2D] text-white rounded-lg font-bold hover:bg-[#432E23] text-[10px] transition cursor-pointer"
                >
                  Switch to {{ alt.branch_name }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="cartStore.items.length === 0" class="bg-white rounded-3xl border border-[#E6DED3] p-16 text-center space-y-4 shadow-2xs">
      <div class="text-5xl">🥐</div>
      <h2 class="text-2xl font-serif font-bold text-[#553B2D]">Your shopping bag is empty</h2>
      <p class="text-xs sm:text-sm text-[#786B60] max-w-sm mx-auto leading-relaxed">
        Discover slow-fermented artisan sourdough breads, celebration cakes, and fresh Viennoiserie pastries.
      </p>
      <div class="pt-2">
        <router-link
          to="/shop"
          class="inline-block px-8 py-3.5 bg-[#553B2D] hover:bg-[#432E23] text-white text-xs font-bold uppercase tracking-wider rounded-full shadow-md transition"
        >
          Explore Collection →
        </router-link>
      </div>
    </div>

    <!-- Cart Layout Grid: Items Table & Summary -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Left Column: Items Table (8 cols) -->
      <div class="lg:col-span-8 space-y-6">
        <div class="bg-white rounded-3xl border border-[#E6DED3] overflow-hidden shadow-2xs">
          <!-- Table Header -->
          <div class="bg-[#F8F3EA] px-6 py-3.5 border-b border-[#E6DED3] grid grid-cols-12 text-[11px] font-bold uppercase tracking-wider text-[#786B60]">
            <div class="col-span-6">Bakery Item</div>
            <div class="col-span-2 text-center">Unit Price</div>
            <div class="col-span-2 text-center">Quantity</div>
            <div class="col-span-2 text-right">Subtotal</div>
          </div>

          <!-- Items Lines -->
          <div class="divide-y divide-[#E6DED3]">
            <div
              v-for="item in cartStore.items"
              :key="item.product_id"
              class="px-6 py-4 grid grid-cols-12 items-center gap-3 text-xs"
              :class="(item.available_stock ?? item.available ?? 1) <= 0 ? 'bg-[#FAF0F0]/50' : ''"
            >
              <!-- Product info (6 cols) -->
              <div class="col-span-6 flex items-center space-x-3.5">
                <img
                  :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160'"
                  :alt="item.product_name"
                  class="w-16 h-16 rounded-2xl object-cover shrink-0 border border-[#E6DED3] bg-[#E8D8C4]/30"
                  :class="(item.available_stock ?? item.available ?? 1) <= 0 ? 'grayscale-50' : ''"
                />
                <div>
                  <h3 class="font-serif font-bold text-sm text-[#29231F]">{{ item.product_name }}</h3>
                  <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="text-[11px] text-[#387A54] font-medium mt-0.5">
                    ✓ Available ({{ item.available_stock ?? item.available }} in stock)
                  </div>
                  <div v-else class="text-[11px] text-[#B84B42] font-bold mt-0.5">
                    Sold out at {{ currentBranchName }}
                  </div>
                </div>
              </div>

              <!-- Price (2 cols) -->
              <div class="col-span-2 text-center font-bold text-[#553B2D]">
                ETB {{ Number(item.price).toLocaleString() }}
              </div>

              <!-- Quantity Stepper (2 cols) -->
              <div class="col-span-2 flex items-center justify-center">
                <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="flex items-center border border-[#E6DED3] rounded-xl bg-white shadow-2xs">
                  <button
                    @click="decreaseQty(item)"
                    class="px-2.5 py-1 text-xs font-bold text-[#553B2D] hover:bg-[#F8F3EA] rounded-l-xl cursor-pointer"
                  >
                    &minus;
                  </button>
                  <span class="px-2.5 py-1 text-xs font-bold text-[#29231F] min-w-[28px] text-center">
                    {{ item.quantity }}
                  </span>
                  <button
                    @click="increaseQty(item)"
                    class="px-2.5 py-1 text-xs font-bold text-[#553B2D] hover:bg-[#F8F3EA] rounded-r-xl disabled:opacity-40 cursor-pointer"
                    :disabled="item.quantity >= (item.available_stock ?? item.available ?? 999)"
                  >
                    +
                  </button>
                </div>
                <div v-else class="text-[11px] font-bold text-[#B84B42]">
                  Unavailable
                </div>
              </div>

              <!-- Total & Trash Icon (2 cols) -->
              <div class="col-span-2 flex items-center justify-end space-x-3">
                <span class="font-black text-[#553B2D] text-sm">
                  ETB {{ (item.price * item.quantity).toLocaleString() }}
                </span>
                <button
                  @click="cartStore.removeItem(item.product_id)"
                  class="text-[#786B60] hover:text-[#B84B42] transition text-sm cursor-pointer p-1"
                  title="Remove item"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Database Promotions Engine Card -->
        <div class="relative rounded-3xl bg-gradient-to-r from-[#E8D8C4] via-[#F8F3EA] to-[#E8D8C4] border border-[#D5C2AD] p-6 shadow-xs space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
              <span class="text-3xl">🎁</span>
              <div>
                <h4 class="font-serif font-black text-base text-[#553B2D]">
                  Artisan Loyalty &amp; Celebration Coupons
                </h4>
                <p class="text-xs text-[#786B60] mt-0.5">
                  Enter an official Kenean promo code or select one of today's specials below.
                </p>
              </div>
            </div>

            <!-- Active Promo Status or Input Form -->
            <div v-if="appliedPromo" class="flex items-center space-x-2 bg-emerald-50 border border-emerald-300 rounded-2xl px-4 py-2 shrink-0">
              <div class="text-xs">
                <span class="font-mono font-bold text-emerald-900 uppercase">{{ appliedPromo.promotion_name }}</span>
                <span class="text-emerald-700 ml-1.5 font-semibold">
                  ({{ appliedPromo.discount_type === 'percentage' ? `${appliedPromo.discount_value}% OFF` : `ETB ${appliedPromo.discount_value} OFF` }})
                </span>
              </div>
              <button
                @click="handleRemovePromo"
                class="ml-2 text-xs font-bold text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 cursor-pointer"
                title="Remove Coupon"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Promo Input Form -->
          <div v-if="!appliedPromo" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-2 border-t border-[#D5C2AD]/50">
            <div class="relative flex-1">
              <input
                v-model="promoInput"
                @keyup.enter="handleApplyPromo()"
                type="text"
                placeholder="Enter promo code (e.g. KENEAN10, YETSOM15, WELCOME50)..."
                class="w-full bg-white border border-[#D5C2AD] rounded-xl px-3.5 py-2 text-xs font-mono font-semibold uppercase text-[#29231F] placeholder:normal-case placeholder:font-sans placeholder:font-normal focus:outline-none focus:border-[#A87552]"
              />
            </div>
            <button
              @click="handleApplyPromo()"
              :disabled="promoLoading || !promoInput.trim()"
              class="px-5 py-2 bg-[#553B2D] hover:bg-[#432E23] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer shrink-0"
            >
              {{ promoLoading ? 'Verifying...' : 'Apply Coupon' }}
            </button>
          </div>

          <div v-if="promoError" class="text-xs text-red-600 font-medium">
            ⚠️ {{ promoError }}
          </div>

          <!-- Quick Promo Suggestions -->
          <div v-if="!appliedPromo" class="flex flex-wrap items-center gap-2 pt-1 text-xs">
            <span class="text-[11px] text-[#786B60] font-semibold">Available Specials:</span>
            <button
              @click="handleApplyPromo('KENEAN10')"
              class="px-2.5 py-1 bg-white hover:bg-[#FAF6F0] border border-[#D5C2AD] text-[#553B2D] rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              KENEAN10 (10% Off)
            </button>
            <button
              @click="handleApplyPromo('YETSOM15')"
              class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              🌿 YETSOM15 (15% Off Fasting)
            </button>
            <button
              @click="handleApplyPromo('WELCOME50')"
              class="px-2.5 py-1 bg-white hover:bg-[#FAF6F0] border border-[#D5C2AD] text-[#553B2D] rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              WELCOME50 (ETB 50 Off)
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary (4 cols) -->
      <div class="lg:col-span-4 bg-white rounded-3xl border border-[#E6DED3] p-6 space-y-5 shadow-2xs">
        <h2 class="text-base font-serif font-bold text-[#553B2D] border-b border-[#E6DED3] pb-3">
          Order Summary
        </h2>

        <div class="space-y-3 text-xs">
          <div class="flex justify-between items-center text-[#786B60]">
            <span>Items Subtotal</span>
            <span class="font-bold text-[#29231F]">ETB {{ subtotal.toLocaleString() }}</span>
          </div>

          <div class="flex justify-between items-center text-[#786B60]">
            <span>Estimated Delivery Fee</span>
            <span class="font-bold" :class="deliveryFee === 0 ? 'text-[#387A54]' : 'text-[#29231F]'">
              {{ deliveryFee === 0 ? 'FREE' : `ETB ${deliveryFee}` }}
            </span>
          </div>

          <div v-if="discount > 0" class="flex justify-between items-center text-[#387A54]">
            <span class="flex items-center space-x-1">
              <span>Promo Discount</span>
              <span v-if="appliedPromo" class="font-mono text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-bold">
                {{ appliedPromo.promotion_name }}
              </span>
            </span>
            <span class="font-bold">- ETB {{ discount.toLocaleString() }}</span>
          </div>

          <div class="pt-3 border-t border-[#E6DED3] flex justify-between items-center text-base font-black text-[#553B2D]">
            <span>Grand Total</span>
            <span class="text-xl font-serif">ETB {{ total.toLocaleString() }}</span>
          </div>
        </div>

        <div class="space-y-2.5 pt-2">
          <!-- Checkout button: disabled if any sold out item present -->
          <div v-if="cartStore.hasSoldOutItems" class="space-y-2">
            <button
              @click="removeSoldOutItems"
              class="w-full py-2.5 bg-[#FAF0F0] border border-[#E8C4C4] text-[#B84B42] hover:bg-[#B84B42] hover:text-white text-xs font-bold rounded-2xl transition cursor-pointer"
            >
              Clear Sold-Out Items
            </button>
            <button
              disabled
              class="block w-full py-3.5 bg-gray-200 text-gray-500 text-xs font-bold uppercase tracking-wider rounded-2xl text-center cursor-not-allowed"
            >
              Remove Sold-Out Items to Proceed
            </button>
          </div>
          <router-link
            v-else
            to="/checkout"
            class="block w-full py-4 bg-[#A87552] hover:bg-[#916140] text-white text-xs font-bold uppercase tracking-wider rounded-2xl text-center shadow-md hover:shadow-lg transition active:scale-98"
          >
            Proceed to Checkout →
          </router-link>

          <router-link
            to="/shop"
            class="block w-full py-2.5 bg-white border border-[#E6DED3] hover:bg-[#F8F3EA] text-[#553B2D] text-xs font-bold rounded-2xl text-center shadow-2xs transition"
          >
            + Add More Items
          </router-link>
        </div>

        <div class="pt-2 text-[11px] text-[#786B60] text-center space-y-1">
          <div class="flex items-center justify-center space-x-1 text-[#387A54] font-semibold">
            <span>🛡️</span>
            <span>Safe &amp; Encrypted Checkout</span>
          </div>
          <div>Telebirr &middot; Chapa &middot; Pay on Pickup / Delivery</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useToastStore } from '../../stores/toast';

const branchStore = useBranchStore();
const cartStore = useCartStore();
const toastStore = useToastStore();

const promoInput = ref('');
const promoLoading = ref(false);
const promoError = ref('');

const appliedPromo = computed(() => cartStore.appliedPromo);

const currentBranchName = computed(() => branchStore.selectedBranch?.branch_name || 'Bole');

onMounted(async () => {
  if (cartStore.items.length > 0) {
    await cartStore.revalidateForBranch(branchStore.selectedBranchId || 2);
  }
});

const subtotal = computed(() => {
  return cartStore.items.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
});

const deliveryFee = computed(() => {
  return subtotal.value >= 1000 || subtotal.value === 0 ? 0 : 60;
});

const discount = computed(() => {
  return cartStore.discountAmount;
});

const total = computed(() => {
  return Math.max(0, subtotal.value + deliveryFee.value - discount.value);
});

function increaseQty(item) {
  const res = cartStore.updateQuantity(item.product_id, item.quantity + 1);
  if (res && !res.success && res.message) {
    toastStore.error(res.message);
  }
}

function decreaseQty(item) {
  cartStore.updateQuantity(item.product_id, item.quantity - 1);
}

function removeSoldOutItems() {
  cartStore.items = cartStore.items.filter(i => !i.isSoldOut && (i.available || 0) > 0);
  cartStore.save();
  toastStore.info('Sold-out items cleared from your bag.');
}

function switchBranch(alt) {
  branchStore.setSelectedBranch(alt.branch_id);
  toastStore.info(`Switched shopping branch to ${alt.branch_name}.`);
}

async function handleApplyPromo(codeToApply = null) {
  const code = (codeToApply || promoInput.value).trim().toUpperCase();
  if (!code) {
    promoError.value = 'Please enter a coupon code.';
    return;
  }
  promoLoading.value = true;
  promoError.value = '';
  try {
    const res = await api.post('/promotions/validate', {
      code,
      subtotal: subtotal.value,
      branch_id: branchStore.selectedBranchId || cartStore.branchId || 2,
    });
    if (res.data?.valid) {
      cartStore.setPromo(res.data);
      promoInput.value = '';
      toastStore.success(res.data.message || `Coupon ${code} applied successfully!`);
    } else {
      promoError.value = res.data?.message || 'Invalid promotion code.';
    }
  } catch (err) {
    promoError.value = err.response?.data?.message || 'Invalid or expired promotion code.';
  } finally {
    promoLoading.value = false;
  }
}

function handleRemovePromo() {
  cartStore.removePromo();
  toastStore.info('Promotion removed.');
}
</script>
