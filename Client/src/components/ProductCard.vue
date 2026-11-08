<template>
  <div class="group bg-white dark:bg-[#1C120C] rounded-2xl border border-[#E6DED3] dark:border-[#3D2619] overflow-hidden hover:shadow-xl hover:shadow-rose-950/5 dark:hover:shadow-black/40 hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full hover:border-[#DE1B54]/50">
    <!-- Image Container with Hover Zoom & Shimmer Placeholder -->
    <div class="relative aspect-4/3 overflow-hidden bg-[#F0E8DF] dark:bg-[#25150E]" :class="{ 'shimmer-wave': !imageLoaded }">
      <img
        :src="productImage"
        :alt="product.product_name"
        class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-108 will-change-transform"
        :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
        loading="lazy"
        decoding="async"
        @load="imageLoaded = true"
        @error="onImageError"
      />
      <!-- Category Badge -->
      <span class="absolute top-3 left-3 bg-[#FAF7F5]/90 dark:bg-[#24150E]/90 backdrop-blur-xs text-[#2A1810] dark:text-[#E8DED8] text-xs font-bold px-2.5 py-1 rounded-full border border-gray-200 dark:border-[#3D2619] shadow-xs">
        {{ product.category?.category_name || product.category_name || 'Artisan' }}
      </span>

      <!-- Heart Wishlist Button (Top Right) -->
      <button
        type="button"
        @click.stop="handleToggleWishlist"
        class="absolute top-3 right-3 w-8 h-8 rounded-full bg-[#FAF7F5]/90 dark:bg-[#24150E]/90 backdrop-blur-xs flex items-center justify-center text-sm shadow-xs hover:scale-110 transition cursor-pointer z-10 border border-gray-200 dark:border-[#3D2619]"
        :class="isWishlisted ? 'text-[#DE1B54]' : 'text-gray-400 hover:text-[#DE1B54]'"
        :title="localeStore.isAmharic ? 'ወደ ምኞት ዝርዝር ጨምር' : 'Add to Wishlist'"
      >
        <span>{{ isWishlisted ? '♥' : '♡' }}</span>
      </button>

      <!-- Availability Badge (Bottom Right) -->
      <span
        class="absolute bottom-3 right-3 text-xs font-bold px-2.5 py-0.5 rounded-full border shadow-2xs backdrop-blur-xs"
        :class="stockStatusClasses"
      >
        {{ stockStatusText }}
      </span>

      <!-- Ethiopian Fasting (Ye'Tsom) Badge -->
      <span
        v-if="product.is_fasting"
        class="absolute bottom-3 left-3 bg-emerald-800/95 backdrop-blur-xs text-white text-xs font-semibold tracking-wide px-2.5 py-0.5 rounded-full border border-emerald-600/50 shadow-xs flex items-center space-x-1"
        :title="localeStore.isAmharic ? '100% የጾም ምግብ - ያለ ወተትና እንቁላል የተዘጋጀ' : '100% Ethiopian Fasting (Ye\'Tsom / ጾም) - Plant-Based & Dairy Free'"
      >
        <span class="text-xs">🌿</span>
        <span>Ye'Tsom / ጾም</span>
      </span>
    </div>

    <!-- Content -->
    <div class="p-3.5 sm:p-4 flex flex-col flex-1 justify-between space-y-3">
      <div>
        <div class="flex items-baseline justify-between gap-2 mb-1.5">
          <router-link :to="`/product/${product.product_id}`" class="text-base sm:text-[17px] font-product font-bold text-[#29231F] dark:text-[#FBF7F4] group-hover:text-[#DE1B54] transition-colors line-clamp-1">
            {{ localeStore.tProduct(product.product_name) }}
          </router-link>
        </div>

        <!-- Star rating row from real database reviews -->
        <div class="flex items-center space-x-1.5 text-xs sm:text-sm mb-2">
          <span class="text-amber-400 tracking-tighter">
            <span v-for="s in 5" :key="s" :class="s <= Math.round(ratingNumber) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'">★</span>
          </span>
          <span class="text-gray-700 dark:text-gray-300 text-xs font-bold">{{ ratingNumber > 0 ? ratingNumber.toFixed(1) : '—' }}</span>
          <span class="text-gray-400 dark:text-gray-500 text-[11px] sm:text-xs">({{ reviewCountNumber }} {{ localeStore.isAmharic ? 'አስተያየት' : (reviewCountNumber === 1 ? 'review' : 'reviews') }})</span>
        </div>

        <p class="text-[13px] sm:text-sm text-[#786B60] dark:text-[#B3A69C] line-clamp-2 leading-relaxed">
          {{ product.description || 'Handcrafted daily with premium unbleached flour, natural sourdough yeast, and finest ingredients.' }}
        </p>
      </div>

      <div class="pt-3 border-t border-gray-100 dark:border-[#331E14] flex items-center justify-between gap-2 sm:gap-3">
        <div>
          <div class="text-[10px] sm:text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500 font-semibold">{{ localeStore.t('price_label') }}</div>
          <div class="text-base sm:text-lg font-black text-[#DE1B54]">
            {{ localeStore.formatPrice(displayPrice) }}
          </div>
        </div>

        <div class="flex items-center space-x-1.5 sm:space-x-2">
          <router-link
            :to="`/product/${product.product_id}`"
            class="min-h-[42px] min-w-[42px] flex items-center justify-center p-2 rounded-xl border border-gray-200 dark:border-[#3D2619] text-gray-700 dark:text-[#E8DED8] hover:bg-rose-50 dark:hover:bg-[#341822] hover:text-[#DE1B54] hover:border-[#DE1B54] transition cursor-pointer"
            :title="localeStore.t('view_details')"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </router-link>

          <button
            @click="handleAddToCart"
            :disabled="isOutOfStock || isAdding"
            class="min-h-[42px] flex items-center space-x-1.5 px-3.5 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition shadow-xs cursor-pointer"
            :class="isOutOfStock 
              ? 'bg-gray-200 dark:bg-gray-800 text-gray-500 cursor-not-allowed' 
              : 'bg-[#DE1B54] hover:bg-[#C51345] text-white active:scale-95'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>{{ isAdding ? localeStore.t('added_btn') : (isOutOfStock ? localeStore.t('out_of_stock_btn') : localeStore.t('add_btn')) }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useBranchStore } from '../stores/branch';
import { useToastStore } from '../stores/toast';
import { useWishlistStore } from '../stores/wishlist';
import { useLocaleStore } from '../stores/locale';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  availableStock: {
    type: Number,
    default: null,
  },
});

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cartStore = useCartStore();
const branchStore = useBranchStore();
const toastStore = useToastStore();
const wishlistStore = useWishlistStore();
const localeStore = useLocaleStore();
const isAdding = ref(false);
const imageLoaded = ref(false);

const isWishlisted = computed(() => {
  return wishlistStore.isWishlisted(props.product.product_id);
});

async function handleToggleWishlist() {
  const res = await wishlistStore.toggleWishlist(props.product);
  if (res.wishlisted) {
    toastStore.success(`Added "${props.product.product_name}" to your favorites!`);
  } else {
    toastStore.info(`Removed "${props.product.product_name}" from favorites.`);
  }
}

const displayPrice = computed(() => {
  return Number(props.product.resolved_price ?? props.product.price ?? props.product.base_price ?? 0);
});

const ratingNumber = computed(() => {
  return Number(props.product.rating ?? 0);
});

const reviewCountNumber = computed(() => {
  return Number(props.product.reviews_count ?? props.product.reviews ?? 0);
});

const stockNumber = computed(() => {
  if (props.availableStock !== null && props.availableStock !== undefined) return Number(props.availableStock);
  if (props.product.available !== undefined && props.product.available !== null) return Number(props.product.available);
  if (props.product.available_stock !== undefined && props.product.available_stock !== null) return Number(props.product.available_stock);
  if (props.product.quantity_available !== undefined && props.product.quantity_available !== null) return Number(props.product.quantity_available);
  if (props.product.on_hand !== undefined && props.product.on_hand !== null) return Math.max(0, Number(props.product.on_hand) - Number(props.product.reserved || 0));
  return 0;
});

const isOutOfStock = computed(() => {
  return stockNumber.value <= 0 || props.product.stock_status === 'Sold out' || props.product.isSoldOut === true;
});

const stockStatusClasses = computed(() => {
  if (isOutOfStock.value) return 'bg-[#FAF0F0] text-[#B84B42] border-[#E8C4C4]';
  if (stockNumber.value <= 5) return 'bg-[#FAF6EC] text-[#B98235] border-[#E8D8B8]';
  return 'bg-[#F2F7F4] text-[#387A54] border-[#C8DFD2]';
});

const stockStatusText = computed(() => {
  if (isOutOfStock.value) return localeStore.t('out_of_stock_label');
  if (stockNumber.value <= 5) {
    return localeStore.isAmharic ? `${stockNumber.value} ብቻ ቀርቷል` : `Only ${stockNumber.value} left`;
  }
  return localeStore.isAmharic ? `${stockNumber.value} በክምችት አለ` : `${stockNumber.value} in stock`;
});

// Image fallback mapping
const defaultBakeryImages = [
  'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80',
  'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?w=600&q=80',
  'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80',
  'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80',
];

const productImage = computed(() => {
  if (props.product.image_url) return props.product.image_url;
  if (props.product.images && props.product.images.length > 0) return props.product.images[0].image_url;
  const hash = Math.abs((props.product.product_id || 1) % defaultBakeryImages.length);
  return defaultBakeryImages[hash];
});

function onImageError(e) {
  e.target.src = defaultBakeryImages[0];
}

function handleAddToCart() {
  if (isOutOfStock.value) return;

  if (!authStore.isAuthenticated) {
    toastStore.info(localeStore.isAmharic 
      ? 'ምርቶችን ወደ ቅርጫት ለመጨመር እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to add items to your cart.');

    const pendingItem = {
      product_id: props.product.product_id,
      product_name: props.product.product_name,
      price: displayPrice.value,
      image_url: productImage.value,
      quantity: 1,
      available: stockNumber.value,
      available_stock: stockNumber.value,
      isSoldOut: isOutOfStock.value,
      branch_id: branchStore.selectedBranchId,
    };
    try {
      sessionStorage.setItem('pending_cart_item', JSON.stringify(pendingItem));
    } catch (e) {}

    const returnUrl = route.fullPath || '/shop';
    router.push({
      path: '/login',
      query: {
        redirect: returnUrl,
        pending_add_id: props.product.product_id,
      },
    });
    return;
  }

  isAdding.value = true;
  const res = cartStore.addItem(
    {
      product_id: props.product.product_id,
      product_name: props.product.product_name,
      price: displayPrice.value,
      image_url: productImage.value,
      quantity: 1,
      available: stockNumber.value,
      available_stock: stockNumber.value,
      isSoldOut: isOutOfStock.value,
    },
    branchStore.selectedBranchId
  );

  if (res && res.reason === 'capped') {
    toastStore.warning(res.message);
  } else if (res && res.success === false) {
    toastStore.error(res.message || 'Could not add to cart.');
  } else {
    toastStore.success(`Added "${props.product.product_name}" to your bag.`, {
      label: localeStore.isAmharic ? 'ቦርሳ ይመልከቱ' : 'View Bag',
      onClick: () => cartStore.toggleDrawer(),
    });
  }

  setTimeout(() => {
    isAdding.value = false;
  }, 800);
}
</script>
