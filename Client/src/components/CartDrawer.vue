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
        class="fixed inset-y-0 right-0 max-w-full flex pl-2 sm:pl-10 z-50 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]"
      >
        <div class="w-screen max-w-md bg-white dark:bg-[#1E120B] border-l border-gray-200 dark:border-[#382318] shadow-2xl flex flex-col justify-between">
          <!-- Top Header (Matching Reference Design) -->
          <div class="px-5 sm:px-6 py-4.5 bg-[#FAF8F6] dark:bg-[#2A1810] border-b border-gray-100 dark:border-[#382318] flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <span class="text-2xl">🛍️</span>
              <div>
                <div class="font-serif font-black text-lg text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-2 leading-tight">
                  <span>{{ localeStore.t('your_bakery_bag') }}</span>
                  <span class="text-xs font-sans font-black bg-[#FCE7F0] text-[#DE1B54] px-2.5 py-0.5 rounded-full">
                    {{ cartStore.totalItems }}
                  </span>
                </div>
                <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] flex items-center space-x-1 mt-0.5">
                  <span>📍</span>
                  <span>{{ localeStore.t('fulfilling_at') }} <strong class="text-[#2A1810] dark:text-[#FAF3EE] font-bold">{{ localeStore.tBranch(currentBranchName) }}</strong></span>
                </div>
              </div>
            </div>

            <button
              @click="cartStore.closeDrawer()"
              class="w-9 h-9 rounded-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:bg-rose-50 dark:hover:bg-[#341822] text-gray-400 hover:text-[#DE1B54] flex items-center justify-center transition text-sm cursor-pointer shadow-2xs"
              :title="localeStore.t('close_bag')"
            >
              ✕
            </button>
          </div>

          <!-- Free Delivery Progress Meter (Exact Reference Layout) -->
          <div class="px-5 sm:px-6 py-3.5 bg-[#FAF8F6] dark:bg-[#170E0A] border-b border-gray-100 dark:border-[#382318] space-y-2">
            <div class="flex justify-between items-center text-xs text-[#2A1810] dark:text-[#FAF3EE]">
              <span v-if="subtotal >= 1000" class="font-bold text-[#387A54] dark:text-emerald-400 flex items-center space-x-1">
                <span>🎉</span>
                <span>{{ localeStore.t('free_delivery_unlocked') }}</span>
              </span>
              <span v-else class="text-xs text-[#52443C] dark:text-[#C5B7AF]">
                <template v-if="localeStore.isAmharic">
                  ነፃ ለማድረስ <strong class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ (1000 - subtotal).toLocaleString() }} ብር</strong> ይጨምሩ
                </template>
                <template v-else>
                  Add <strong class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">ETB {{ (1000 - subtotal).toLocaleString() }}</strong> for <strong class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">Free Delivery</strong>
                </template>
              </span>
              <span class="font-bold text-xs text-[#2A1810] dark:text-[#FAF3EE]">{{ Math.min(100, Math.round((subtotal / 1000) * 100)) }}%</span>
            </div>
            <div class="w-full h-2 bg-[#EAE3DC] dark:bg-[#382318] rounded-full overflow-hidden">
              <div
                class="h-full bg-[#DE1B54] transition-all duration-300 rounded-full"
                :style="{ width: `${Math.min(100, (subtotal / 1000) * 100)}%` }"
              ></div>
            </div>
          </div>

          <!-- Drawer Body / Items List -->
          <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 divide-y divide-gray-100 dark:divide-[#382318] bg-white dark:bg-[#1E120B]">
            <!-- Empty Bag State -->
            <div v-if="cartStore.items.length === 0" class="py-16 text-center space-y-4">
              <div class="text-5xl">🥐</div>
              <h3 class="font-serif font-bold text-lg text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.t('empty_bag_drawer_title') }}</h3>
              <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] max-w-xs mx-auto">
                {{ localeStore.t('empty_bag_drawer_desc') }}
              </p>
              <button
                @click="goToShop"
                class="px-6 py-3 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold uppercase tracking-wider rounded-full shadow-md transition cursor-pointer active:scale-95"
              >
                {{ localeStore.t('browse_shop_btn') }}
              </button>
            </div>

            <!-- Items -->
            <div
              v-for="item in cartStore.items"
              :key="item.product_id"
              class="pt-4 first:pt-0 flex items-start space-x-3.5 group"
            >
              <!-- Thumbnail: Square with 2xl Rounded Corners -->
              <img
                :src="item.image_url || '/mockup/shop_chocolate_cake.jpg'"
                :alt="item.product_name"
                loading="lazy"
                decoding="async"
                class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0 border border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] shadow-2xs"
              />

              <!-- Item details -->
              <div class="flex-1 min-w-0 space-y-1.5">
                <div class="flex items-start justify-between gap-1">
                  <h4 class="font-serif font-black text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE] truncate pr-1">
                    {{ localeStore.tProduct(item.product_name) }}
                  </h4>
                  <button
                    @click="cartStore.removeItem(item.product_id)"
                    class="text-gray-400 hover:text-[#DE1B54] text-sm p-0.5 transition cursor-pointer"
                    :title="localeStore.t('remove_item')"
                  >
                    ✕
                  </button>
                </div>

                <div class="text-xs sm:text-sm font-bold text-[#DE1B54]">
                  {{ localeStore.formatPrice(item.price) }}
                </div>

                <!-- Quantity Stepper Pill & Item Total -->
                <div class="flex items-center justify-between pt-1">
                  <!-- Stepper Pill Button: Rounded-full with [ - ] [ 1 ] [ + ] -->
                  <div class="inline-flex items-center border border-gray-200 dark:border-[#382318] rounded-full bg-white dark:bg-[#170E0A] px-3 py-1 space-x-3.5 shadow-2xs">
                    <button
                      @click="decrease(item)"
                      class="text-sm font-black text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] transition cursor-pointer"
                      title="Decrease"
                    >
                      &minus;
                    </button>
                    <span class="text-xs font-black text-[#2A1810] dark:text-[#FAF3EE] min-w-[16px] text-center">
                      {{ item.quantity }}
                    </span>
                    <button
                      @click="increase(item)"
                      :disabled="item.quantity >= (item.available_stock ?? item.available ?? 999)"
                      class="text-sm font-black text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] disabled:opacity-30 transition cursor-pointer"
                      title="Increase"
                    >
                      +
                    </button>
                  </div>

                  <!-- Total line price in SweetBakes Red -->
                  <span class="font-serif font-black text-sm sm:text-base text-[#DE1B54]">
                    {{ localeStore.formatPrice(item.price * item.quantity) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Drawer Footer (Subtotal & Actions matching reference image) -->
          <div v-if="cartStore.items.length > 0" class="p-5 sm:p-6 bg-[#FAF8F6] dark:bg-[#20130C] border-t border-gray-200/80 dark:border-[#382318] space-y-4">
            <div class="space-y-2.5 text-xs sm:text-sm">
              <div class="flex justify-between items-center text-[#6E5D53] dark:text-[#C5B7AF]">
                <span>{{ localeStore.t('items_subtotal') }}</span>
                <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.formatPrice(subtotal) }}</span>
              </div>
              <div class="flex justify-between items-center text-[#6E5D53] dark:text-[#C5B7AF]">
                <span>{{ localeStore.t('estimated_delivery') }}</span>
                <span class="font-bold" :class="estimatedDelivery === 0 ? 'text-[#387A54] dark:text-emerald-400' : 'text-[#2A1810] dark:text-[#FAF3EE]'">
                  {{ estimatedDelivery === 0 ? localeStore.t('free_label') : localeStore.formatPrice(estimatedDelivery) }}
                </span>
              </div>
              <div v-if="cartStore.discountAmount > 0" class="flex justify-between items-center text-[#387A54] dark:text-emerald-400">
                <span class="flex items-center space-x-1">
                  <span>{{ localeStore.t('promo_savings') }}</span>
                  <span v-if="cartStore.appliedPromo" class="font-mono text-[9px] bg-emerald-100 text-emerald-800 px-1 py-0.2 rounded font-bold">
                    {{ cartStore.appliedPromo.promotion_name }}
                  </span>
                </span>
                <span class="font-bold">- {{ localeStore.formatPrice(cartStore.discountAmount) }}</span>
              </div>
              <div class="pt-3 border-t border-gray-200 dark:border-[#382318] flex justify-between items-center text-sm font-black text-[#2A1810] dark:text-[#FAF3EE]">
                <span class="font-serif font-black text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.t('estimated_total') }}
                </span>
                <span class="text-xl sm:text-2xl font-serif font-black text-[#DE1B54]">
                  {{ localeStore.formatPrice(Math.max(0, subtotal - cartStore.discountAmount + estimatedDelivery)) }}
                </span>
              </div>
            </div>

            <!-- Action Buttons: Vibrant Red Primary Button + Clean White Secondary Button -->
            <div class="space-y-2.5 pt-1">
              <!-- Primary Proceed to Checkout: Signature Red Pill Button -->
              <button
                @click="goToCheckout"
                class="w-full py-4 min-h-[50px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-98 cursor-pointer flex items-center justify-center space-x-2"
              >
                <span>{{ localeStore.isAmharic ? 'ትዕዛዝ ያጠናቁ' : 'PROCEED TO CHECKOUT' }}</span>
                <span class="text-base font-black">&rarr;</span>
              </button>

              <!-- Secondary View Complete Bag Details -->
              <button
                @click="goToCart"
                class="w-full py-3.5 min-h-[46px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:bg-rose-50 dark:hover:bg-[#2A1710] text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] text-xs sm:text-sm font-bold rounded-2xl shadow-xs transition active:scale-98 text-center cursor-pointer"
              >
                {{ localeStore.isAmharic ? 'ሙሉ የቅርጫት ዝርዝር ይመልከቱ' : 'View Complete Bag Details' }}
              </button>
            </div>

            <!-- Security Badge Matching Reference Image -->
            <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] text-center flex items-center justify-center space-x-1.5 pt-1">
              <span>🔒</span>
              <span>{{ localeStore.isAmharic ? 'በቴሌብር፣ በቻፓ እና በጥሬ ገንዘብ የተጠበቀ ክፍያ' : 'Encrypted telebirr, Chapa & cash checkout' }}</span>
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
import { useAuthStore } from '../stores/auth';
import { useBranchStore } from '../stores/branch';
import { useToastStore } from '../stores/toast';
import { useLocaleStore } from '../stores/locale';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();
const branchStore = useBranchStore();
const toastStore = useToastStore();
const localeStore = useLocaleStore();

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
  if (!authStore.isAuthenticated) {
    toastStore.info(localeStore.isAmharic 
      ? 'ወደ ክፍያ ለመቀጠል እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to proceed to checkout.');
    router.push({ path: '/login', query: { redirect: '/checkout' } });
    return;
  }
  router.push('/checkout');
}
</script>
