<template>
  <div class="fixed bottom-5 right-4 sm:right-6 z-50 flex flex-col space-y-2.5 pointer-events-none max-w-sm w-full px-2 sm:px-0">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-4 opacity-0 scale-95"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md rounded-2xl shadow-xl border border-rose-100/80 dark:border-[#382318] p-3.5 sm:p-4 flex items-start space-x-3 transition-all relative overflow-hidden"
        :class="{
          'border-l-4 border-l-emerald-600 shadow-emerald-950/5': toast.type === 'success',
          'border-l-4 border-l-[#B84B42] shadow-red-950/5': toast.type === 'error',
          'border-l-4 border-l-amber-500 shadow-amber-950/5': toast.type === 'warning',
          'border-l-4 border-l-[#DE1B54] shadow-rose-950/5': toast.type === 'info',
        }"
      >
        <!-- Icon badge -->
        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-base"
          :class="{
            'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600': toast.type === 'success',
            'bg-red-50 dark:bg-red-950/50 text-[#B84B42]': toast.type === 'error',
            'bg-amber-50 dark:bg-amber-950/50 text-amber-600': toast.type === 'warning',
            'bg-rose-50 dark:bg-rose-950/50 text-[#DE1B54]': toast.type === 'info',
          }"
        >
          <span v-if="toast.type === 'success'">🧁</span>
          <span v-else-if="toast.type === 'error'">⚠️</span>
          <span v-else-if="toast.type === 'warning'">⚡</span>
          <span v-else>💡</span>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 pt-0.5 space-y-1.5">
          <div class="text-xs sm:text-[13px] font-semibold text-[#29231F] dark:text-[#FAF3EE] leading-snug">
            {{ toast.message }}
          </div>

          <!-- Optional Interactive Action Button (e.g. "View Bag") -->
          <div v-if="toast.action" class="pt-0.5">
            <button
              @click="handleAction(toast)"
              class="text-xs font-bold text-[#DE1B54] hover:text-[#C51345] underline flex items-center space-x-1 cursor-pointer"
            >
              <span>{{ toast.action.label || 'View Bag' }}</span>
              <span>&rarr;</span>
            </button>
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="toastStore.remove(toast.id)"
          class="text-[#786B60] dark:text-[#C5B7AF] hover:text-[#DE1B54] p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition cursor-pointer shrink-0"
          title="Dismiss notification"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast';

const toastStore = useToastStore();

function handleAction(toast) {
  if (toast.action && typeof toast.action.onClick === 'function') {
    toast.action.onClick();
  }
  toastStore.remove(toast.id);
}
</script>
