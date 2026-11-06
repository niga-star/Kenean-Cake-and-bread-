<template>
  <div class="group bg-white rounded-2xl border border-[#E6DED3] overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col h-full hover:border-[#A87552]/50">
    <!-- Image Container with Hover Zoom -->
    <div class="relative aspect-4/3 overflow-hidden bg-[#E8D8C4]/40">
      <img
        :src="productImage"
        :alt="product.product_name"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
        @error="onImageError"
      />
      <!-- Category Badge -->
      <span class="absolute top-3 left-3 bg-[#F8F3EA]/90 backdrop-blur-xs text-[#553B2D] text-[11px] font-bold px-2.5 py-1 rounded-full border border-[#E6DED3] shadow-xs">
        {{ product.category?.category_name || product.category_name || 'Artisan' }}
      </span>

      <!-- Availability Badge -->
      <span
        class="absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded-full border shadow-2xs"
        :class="stockStatusClasses"
      >
        {{ stockStatusText }}
      </span>

      <!-- Ethiopian Fasting (Ye'Tsom) Badge -->
      <span
        v-if="product.is_fasting"
        class="absolute bottom-3 left-3 bg-emerald-800/95 backdrop-blur-xs text-white text-[10px] font-semibold tracking-wide px-2.5 py-0.5 rounded-full border border-emerald-600/50 shadow-xs flex items-center space-x-1"
        title="100% Ethiopian Fasting (Ye'Tsom / ጾም) - Plant-Based & Dairy Free"
      >
        <span class="text-xs">🌿</span>
        <span>Ye'Tsom / ጾም</span>
      </span>
    </div>

    <!-- Content -->
    <div class="p-4 flex flex-col flex-1 justify-between space-y-3">
      <div>
        <div class="flex items-baseline justify-between gap-2 mb-1">
          <router-link :to="`/product/${product.product_id}`" class="text-sm font-serif font-bold text-[#29231F] group-hover:text-[#DE1B54] transition-colors line-clamp-1">
            {{ product.product_name }}
          </router-link>
        </div>

        <!-- Star rating row -->
        <div class="flex items-center space-x-1.5 text-xs mb-2">
          <span class="text-amber-400 tracking-tighter">★★★★★</span>
          <span class="text-gray-700 text-[11px] font-bold">4.8</span>
          <span class="text-gray-400 text-[10px]">(90+ reviews)</span>
        </div>

        <p class="text-xs text-[#786B60] line-clamp-2 leading-relaxed">
          {{ product.description || 'Handcrafted daily with premium unbleached flour, natural sourdough yeast, and finest ingredients.' }}
        </p>
      </div>

      <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
        <div>
          <div class="text-[9px] uppercase tracking-wider text-gray-400 font-semibold">Price</div>
          <div class="text-base font-black text-[#DE1B54]">
            ETB {{ displayPrice.toLocaleString() }}
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <router-link
            :to="`/product/${product.product_id}`"
            class="p-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-rose-50 hover:text-[#DE1B54] hover:border-[#DE1B54] transition cursor-pointer"
            title="View Details"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </router-link>

          <button
            @click="handleAddToCart"
            :disabled="isOutOfStock || isAdding"
            class="flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
            :class="isOutOfStock 
              ? 'bg-gray-200 text-gray-500 cursor-not-allowed' 
              : 'bg-[#DE1B54] hover:bg-[#C51345] text-white active:scale-95'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>{{ isAdding ? 'Added' : (isOutOfStock ? 'Sold Out' : 'Add') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useCartStore } from '../stores/cart';
import { useToastStore } from '../stores/toast';

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

const cartStore = useCartStore();
const toastStore = useToastStore();
const isAdding = ref(false);

const displayPrice = computed(() => {
  return Number(props.product.resolved_price ?? props.product.price ?? props.product.base_price ?? 0);
});

const stockNumber = computed(() => {
  if (props.availableStock !== null) return props.availableStock;
  return Number(props.product.available_stock ?? props.product.quantity_available ?? 25);
});

const isOutOfStock = computed(() => {
  return stockNumber.value <= 0;
});

const stockStatusClasses = computed(() => {
  if (isOutOfStock.value) return 'bg-[#FAF0F0] text-[#B84B42] border-[#E8C4C4]';
  if (stockNumber.value <= 5) return 'bg-[#FAF6EC] text-[#B98235] border-[#E8D8B8]';
  return 'bg-[#F2F7F4] text-[#387A54] border-[#C8DFD2]';
});

const stockStatusText = computed(() => {
  if (isOutOfStock.value) return 'Out of Stock';
  if (stockNumber.value <= 5) return `Only ${stockNumber.value} left`;
  return `${stockNumber.value} in stock`;
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

  isAdding.value = true;
  const res = cartStore.addItem({
    product_id: props.product.product_id,
    product_name: props.product.product_name,
    price: displayPrice.value,
    image_url: productImage.value,
    quantity: 1,
    available: stockNumber.value,
    available_stock: stockNumber.value,
  });

  if (res && res.reason === 'capped') {
    toastStore.warning(res.message);
  } else if (res && res.success === false) {
    toastStore.error(res.message || 'Could not add to cart.');
  } else {
    toastStore.success(`Added "${props.product.product_name}" to your bag.`);
  }

  setTimeout(() => {
    isAdding.value = false;
  }, 800);
}
</script>
