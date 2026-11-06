<template>
  <div>
    <!-- Backdrop Blur Overlay -->
    <transition
      enter-active-class="transition-opacity ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="cartStore.isDrawerOpen"
        @click="cartStore.closeDrawer()"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 transition-opacity"
      ></div>
    </transition>

    <!-- Slide-Out Drawer Panel -->
    <transition
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transform transition ease-in duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="cartStore.isDrawerOpen"
        class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-50 font-sans selection:bg-[#E8D8C4] selection:text-[#553B2D]"
      >
        <div class="w-screen max-w-md bg-white border-l border-[#E6DED3] shadow-2xl flex flex-col justify-between">
          <!-- Top Header -->
          <div class="px-6 py-4 bg-[#F8F3EA] border-b border-[#E6DED3] flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
              <span class="text-2xl">🛍️</span>
              <div>
                <div class="font-serif font-black text-base text-[#553B2D] flex items-center space-x-2">
                  <span>Your Bakery Bag</span>
                  <span class="text-xs font-sans font-bold bg-[#E8D8C4] text-[#553B2D] px-2 py-0.5 rounded-full">
                    {{ cartStore.totalItems }}
                  </span>
                </div>
                <div class="text-[11px] text-[#786B60] flex items-center space-x-1 mt-0.5">
                  <span>📍</span>
                  <span>Fulfilling at <strong>{{ currentBranchName }} Branch</strong></span>
                </div>
              </div>
            </div>

            <button
              @click="cartStore.closeDrawer()"
              class="w-8 h-8 rounded-full bg-white border border-[#E6DED3] hover:bg-[#FAF6F0] text-[#786B60] hover:text-[#553B2D] flex items-center justify-center transition text-sm cursor-pointer"
              title="Close Bag"
            >
              ✕
            </button>
          </div>

          <!-- Free Delivery Progress Meter -->
          <div class="px-6 py-3 bg-[#FAF6F0] border-b border-[#E6DED3] space-y-1.5">
            <div class="flex justify-between items-center text-xs">
              <span v-if="subtotal >= 1000" class="font-bold text-[#387A54] flex items-center space-x-1">
                <span>🎉</span>
                <span>Free Delivery Unlocked!</span>
              </span>
              <span v-else class="text-[#786B60] text-[11px]">
                Add <strong class="text-[#553B2D]">ETB {{ (1000 - subtotal).toLocaleString() }}</strong> for <strong>Free Delivery</strong>
              </span>
              <span class="font-bold text-[11px] text-[#553B2D]">{{ Math.min(100, Math.round((subtotal / 1000) * 100)) }}%</span>
            </div>
            <div class="w-full h-1.5 bg-[#E8D8C4]/60 rounded-full overflow-hidden">
              <div
                class="h-full bg-[#A87552] transition-all duration-300 rounded-full"
                :style="{ width: `${Math.min(100, (subtotal / 1000) * 100)}%` }"
              ></div>
            </div>
          </div>

          <!-- Drawer Body / Items List -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4 divide-y divide-[#E6DED3]">
            <!-- Empty Bag State -->
            <div v-if="cartStore.items.length === 0" class="py-16 text-center space-y-4">
              <div class="text-5xl">🥐</div>
              <h3 class="font-serif font-bold text-lg text-[#553B2D]">Your bag is empty</h3>
              <p class="text-xs text-[#786B60] max-w-xs mx-auto">
                Explore our fresh sourdough loaves, celebratory multi-tier cakes, and Parisian pastries.
              </p>
              <button
                @click="goToShop"
                class="px-6 py-3 bg-[#553B2D] hover:bg-[#432E23] text-white text-xs font-bold uppercase tracking-wider rounded-full shadow-md transition cursor-pointer"
              >
                Browse Bakery Shop →
              </button>
            </div>

            <!-- Items -->
            <div
              v-for="item in cartStore.items"
              :key="item.product_id"
              class="pt-4 first:pt-0 flex items-start space-x-3.5 group"
            >
              <!-- Thumbnail -->
              <img
                :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160'"
                :alt="item.product_name"
                class="w-16 h-16 rounded-2xl object-cover shrink-0 border border-[#E6DED3] bg-[#E8D8C4]/30"
              />

              <!-- Item details -->
              <div class="flex-1 min-w-0 space-y-1">
                <div class="flex items-start justify-between gap-1">
                  <h4 class="font-serif font-bold text-xs sm:text-sm text-[#29231F] truncate pr-1">
                    {{ item.product_name }}
                  </h4>
                  <button
                    @click="cartStore.removeItem(item.product_id)"
                    class="text-[#786B60] hover:text-[#B84B42] text-xs p-0.5 cursor-pointer"
                    title="Remove item"
                  >
                    ✕
                  </button>
                </div>

                <div class="text-xs font-bold text-[#A87552]">
                  ETB {{ Number(item.price).toLocaleString() }}
                </div>

                <!-- Quantity Stepper -->
                <div class="flex items-center justify-between pt-1">
                  <div class="flex items-center border border-[#E6DED3] rounded-xl bg-white shadow-2xs">
                    <button
                      @click="decrease(item)"
                      class="px-2 py-0.5 text-xs font-bold text-[#553B2D] hover:bg-[#F8F3EA] rounded-l-xl cursor-pointer"
                    >
                      &minus;
                    </button>
                    <span class="px-2.5 py-0.5 text-xs font-bold text-[#29231F] min-w-[24px] text-center">
                      {{ item.quantity }}
                    </span>
                    <button
                      @click="increase(item)"
                      class="px-2 py-0.5 text-xs font-bold text-[#553B2D] hover:bg-[#F8F3EA] rounded-r-xl disabled:opacity-40 cursor-pointer"
                      :disabled="item.quantity >= (item.available_stock ?? item.available ?? 999)"
                    >
                      +
                    </button>
                  </div>

                  <span class="font-black text-xs text-[#553B2D]">
                    ETB {{ (item.price * item.quantity).toLocaleString() }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Drawer Footer (Subtotal & Actions) -->
          <div v-if="cartStore.items.length > 0" class="p-6 bg-[#F8F3EA] border-t border-[#E6DED3] space-y-4">
            <div class="space-y-2 text-xs">
              <div class="flex justify-between items-center text-[#786B60]">
                <span>Items Subtotal</span>
                <span class="font-bold text-[#29231F]">ETB {{ subtotal.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between items-center text-[#786B60]">
                <span>Estimated Delivery</span>
                <span class="font-bold" :class="estimatedDelivery === 0 ? 'text-[#387A54]' : 'text-[#29231F]'">
                  {{ estimatedDelivery === 0 ? 'FREE' : `ETB ${estimatedDelivery}` }}
                </span>
              </div>
              <div v-if="cartStore.discountAmount > 0" class="flex justify-between items-center text-[#387A54]">
                <span class="flex items-center space-x-1">
                  <span>Promo Savings</span>
                  <span v-if="cartStore.appliedPromo" class="font-mono text-[9px] bg-emerald-100 text-emerald-800 px-1 py-0.2 rounded font-bold">
                    {{ cartStore.appliedPromo.promotion_name }}
                  </span>
                </span>
                <span class="font-bold">- ETB {{ cartStore.discountAmount.toLocaleString() }}</span>
              </div>
              <div class="pt-2 border-t border-[#E6DED3] flex justify-between items-center text-sm font-black text-[#553B2D]">
                <span>Estimated Total</span>
                <span class="text-base font-serif font-black">ETB {{ Math.max(0, subtotal - cartStore.discountAmount + estimatedDelivery).toLocaleString() }}</span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
              <button
                @click="goToCheckout"
                class="w-full py-3.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer flex items-center justify-center space-x-2"
              >
                <span>Proceed to Checkout</span>
                <span>→</span>
              </button>

              <button
                @click="goToCart"
                class="w-full py-2.5 bg-white border border-[#E6DED3] hover:bg-[#FAF6F0] text-[#553B2D] text-xs font-bold rounded-2xl shadow-2xs transition cursor-pointer text-center"
              >
                View Complete Bag Details
              </button>
            </div>

            <div class="text-[10px] text-[#786B60] text-center flex items-center justify-center space-x-1">
              <span>🔒</span>
              <span>Encrypted telebirr, Chapa &amp; cash checkout</span>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useBranchStore } from '../stores/branch';
import { useToastStore } from '../stores/toast';

const router = useRouter();
const cartStore = useCartStore();
const branchStore = useBranchStore();
const toastStore = useToastStore();

const currentBranchName = computed(() => {
  return branchStore.selectedBranch?.branch_name || 'Bole';
});

const subtotal = computed(() => {
  return cartStore.items.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
});

const estimatedDelivery = computed(() => {
  return subtotal.value >= 1000 || subtotal.value === 0 ? 0 : 60;
});

function increase(item) {
  const res = cartStore.updateQuantity(item.product_id, item.quantity + 1);
  if (res && !res.success && res.message) {
    toastStore.warning(res.message);
  }
}

function decrease(item) {
  cartStore.updateQuantity(item.product_id, item.quantity - 1);
}

function goToShop() {
  cartStore.closeDrawer();
  router.push('/shop');
}

function goToCart() {
  cartStore.closeDrawer();
  router.push('/cart');
}

function goToCheckout() {
  cartStore.closeDrawer();
  router.push('/checkout');
}
</script>
