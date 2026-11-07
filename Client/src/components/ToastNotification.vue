<template>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full px-4">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto bg-white dark:bg-[#1E120B] rounded-2xl shadow-xl border border-gray-200 dark:border-[#382318] p-4 flex items-start space-x-3 transition-all"
        :class="{
          'border-l-4 border-l-[#387A54]': toast.type === 'success',
          'border-l-4 border-l-[#B84B42]': toast.type === 'error',
          'border-l-4 border-l-[#DE1B54]': toast.type === 'info',
        }"
      >
        <span class="text-lg shrink-0">
          <span v-if="toast.type === 'success'">✨</span>
          <span v-else-if="toast.type === 'error'">⚠️</span>
          <span v-else>💡</span>
        </span>
        <div class="flex-1 text-xs font-medium text-[#29231F] dark:text-[#FAF3EE] pt-0.5 leading-relaxed">
          {{ toast.message }}
        </div>
        <button
          @click="toastStore.remove(toast.id)"
          class="text-[#786B60] dark:text-[#C5B7AF] hover:text-[#DE1B54] text-sm leading-none shrink-0 cursor-pointer p-0.5"
        >
          ✕
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast';

const toastStore = useToastStore();
</script>
