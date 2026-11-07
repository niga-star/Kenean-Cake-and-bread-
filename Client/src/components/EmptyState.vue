<template>
  <div class="text-center py-12 sm:py-16 px-4 rounded-3xl bg-rose-50/40 dark:bg-[#1E120B]/80 border border-rose-100 dark:border-[#382318] max-w-lg mx-auto space-y-4 shadow-xs">
    <!-- Icon or Illustration Badge -->
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-3xl bg-white dark:bg-[#281810] border border-rose-100 dark:border-[#422518] shadow-md flex items-center justify-center text-3xl sm:text-4xl transform hover:scale-105 transition-transform">
      <span>{{ icon || '🧁' }}</span>
    </div>

    <!-- Title & Description -->
    <div class="space-y-1.5 px-2">
      <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
        {{ title || (isAmharic ? 'ምንም መረጃ አልተገኘም' : 'No Items Found') }}
      </h3>
      <p class="text-xs sm:text-sm text-[#786B60] dark:text-[#C5B7AF] leading-relaxed max-w-sm mx-auto">
        {{ description || (isAmharic ? 'ለተመረጠው መስፈርት የሚስማማ ምንም ነገር የለም።' : 'There are no items matching this criteria right now.') }}
      </p>
    </div>

    <!-- Action Button (Router Link or Click Event) -->
    <div v-if="actionLabel" class="pt-2">
      <router-link
        v-if="actionLink"
        :to="actionLink"
        class="inline-flex items-center space-x-2 px-6 py-2.5 min-h-[42px] text-xs sm:text-sm font-bold bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-full shadow-md hover:shadow-lg transition-all transform hover:scale-105 active:scale-95 cursor-pointer"
      >
        <span>{{ actionLabel }}</span>
        <span>&rarr;</span>
      </router-link>

      <button
        v-else
        @click="$emit('action')"
        class="inline-flex items-center space-x-2 px-6 py-2.5 min-h-[42px] text-xs sm:text-sm font-bold bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-full shadow-md hover:shadow-lg transition-all transform hover:scale-105 active:scale-95 cursor-pointer"
      >
        <span>{{ actionLabel }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineProps({
  icon: { type: String, default: '🧁' },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
  actionLink: { type: String, default: '' },
});

defineEmits(['action']);

const isAmharic = computed(() => {
  return typeof document !== 'undefined' && document.documentElement.lang === 'am';
});
</script>

