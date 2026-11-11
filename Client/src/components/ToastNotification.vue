<template>
  <div class="fixed bottom-5 right-4 sm:right-6 z-50 flex flex-col space-y-2.5 pointer-events-none max-w-sm sm:max-w-md w-full px-2 sm:px-0" role="region" aria-live="polite" aria-label="Notifications">
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
        class="pointer-events-auto bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 dark:border-[#382318] p-3.5 sm:p-4 flex items-start space-x-3 transition-all relative overflow-hidden"
        :class="{
          'border-l-4 border-l-emerald-600 shadow-emerald-950/5': toast.type === 'success',
          'border-l-4 border-l-[#DE1B54] shadow-rose-950/5': toast.type === 'error',
          'border-l-4 border-l-[#BA5420] shadow-amber-950/5': toast.type === 'warning',
          'border-l-4 border-l-blue-600 shadow-blue-950/5': toast.type === 'info',
        }"
      >
        <!-- Crisp Professional SVG Icon Badge -->
        <div
          class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
          :class="{
            'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400': toast.type === 'success',
            'bg-rose-50 dark:bg-rose-950/50 text-[#DE1B54] dark:text-rose-400': toast.type === 'error',
            'bg-amber-50 dark:bg-amber-950/50 text-[#BA5420] dark:text-amber-400': toast.type === 'warning',
            'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400': toast.type === 'info',
          }"
        >
          <!-- Success Icon: Check Circle -->
          <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>

          <!-- Error Icon: Exclamation Circle -->
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.008v.008H12v-.008z" />
          </svg>

          <!-- Warning Icon: Alert Triangle -->
          <svg v-else-if="toast.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 18.75h.008v.008H12v-.008z" />
          </svg>

          <!-- Info Icon: Information Circle -->
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
          </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 pt-0.5 space-y-1">
          <div class="text-xs sm:text-[13px] font-semibold text-[#29231F] dark:text-[#FAF3EE] leading-snug break-words">
            {{ toast.message }}
          </div>

          <!-- Optional Interactive Action Button (e.g. "View Bag") -->
          <div v-if="toast.action" class="pt-0.5">
            <button
              @click="handleAction(toast)"
              class="text-xs font-bold text-[#DE1B54] hover:text-[#C51345] dark:text-rose-400 dark:hover:text-rose-300 underline flex items-center space-x-1 cursor-pointer"
            >
              <span>{{ toast.action.label || 'Action' }}</span>
              <span>&rarr;</span>
            </button>
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="toastStore.remove(toast.id)"
          class="text-[#786B60] dark:text-[#C5B7AF] hover:text-[#DE1B54] dark:hover:text-rose-400 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition cursor-pointer shrink-0"
          title="Dismiss notification"
          aria-label="Dismiss notification"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
