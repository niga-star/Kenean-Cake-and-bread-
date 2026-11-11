<template>
  <teleport to="body">
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="dialogStore.isOpen"
        class="fixed inset-0 z-50 overflow-y-auto bg-[#2A1810]/60 dark:bg-black/75 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="dialogTitleId"
        @click.self="dialogStore.handleCancel"
      >
        <transition
          enter-active-class="transition duration-250 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-3"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-3"
        >
          <div
            v-if="dialogStore.isOpen"
            class="relative w-full max-w-md bg-[#FAF7F2] dark:bg-[#1E120B] rounded-3xl border border-[#EFE8E1] dark:border-[#382318] shadow-2xl p-6 sm:p-7 space-y-5 text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]"
            @keydown.esc="dialogStore.handleCancel"
          >
            <!-- Header with Icon Badge -->
            <div class="flex items-start space-x-4">
              <!-- Icon Container -->
              <div
                class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-2xs"
                :class="{
                  'bg-rose-100 dark:bg-rose-950/60 text-[#DE1B54] dark:text-rose-400': dialogStore.variant === 'danger',
                  'bg-amber-100 dark:bg-amber-950/60 text-[#BA5420] dark:text-amber-400': dialogStore.variant === 'warning',
                  'bg-emerald-100 dark:bg-emerald-950/60 text-[#047857] dark:text-emerald-400': dialogStore.variant === 'success',
                  'bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400': dialogStore.variant === 'info',
                  'bg-rose-50 dark:bg-[#341822] text-[#DE1B54] dark:text-rose-400': dialogStore.variant === 'primary',
                }"
              >
                <!-- Danger Icon: Exclamation Triangle -->
                <svg v-if="dialogStore.variant === 'danger'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 18.75h.008v.008H12v-.008z" />
                </svg>

                <!-- Warning Icon -->
                <svg v-else-if="dialogStore.variant === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Success Icon -->
                <svg v-else-if="dialogStore.variant === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Prompt / Edit Icon -->
                <svg v-else-if="dialogStore.type === 'prompt'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>

                <!-- Information Icon (Default) -->
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
              </div>

              <!-- Title & Message Text -->
              <div class="flex-1 min-w-0 space-y-1.5 pt-0.5">
                <h3 :id="dialogTitleId" class="text-base sm:text-lg font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] leading-snug">
                  {{ dialogStore.title }}
                </h3>
                <p class="text-xs sm:text-[13px] text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed whitespace-pre-line break-words">
                  {{ dialogStore.message }}
                </p>
              </div>
            </div>

            <!-- Optional Prompt Input Field -->
            <div v-if="dialogStore.type === 'prompt'" class="pt-1">
              <textarea
                v-if="dialogStore.multiline"
                ref="inputRef"
                v-model="dialogStore.inputValue"
                rows="3"
                :placeholder="dialogStore.inputPlaceholder"
                class="w-full bg-white dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:ring-1 focus:ring-[#DE1B54] rounded-2xl px-3.5 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition leading-relaxed resize-none shadow-2xs"
                @keydown.enter.ctrl="dialogStore.handleConfirm"
              ></textarea>
              <input
                v-else
                ref="inputRef"
                v-model="dialogStore.inputValue"
                type="text"
                :placeholder="dialogStore.inputPlaceholder"
                class="w-full bg-white dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:ring-1 focus:ring-[#DE1B54] rounded-2xl px-3.5 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[42px] shadow-2xs"
                @keydown.enter="dialogStore.handleConfirm"
              />
            </div>

            <!-- Action Buttons Footer -->
            <div class="flex items-center justify-end space-x-3 pt-2">
              <button
                v-if="dialogStore.type !== 'alert' && dialogStore.cancelText"
                type="button"
                @click="dialogStore.handleCancel"
                class="px-4 py-2.5 min-h-[40px] bg-white dark:bg-[#2A1810] hover:bg-gray-100 dark:hover:bg-[#382318] text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE] font-bold text-xs rounded-xl border border-gray-200 dark:border-[#382318] transition cursor-pointer active:scale-98 shadow-2xs"
              >
                {{ dialogStore.cancelText }}
              </button>

              <button
                ref="confirmBtnRef"
                type="button"
                @click="dialogStore.handleConfirm"
                class="px-5 py-2.5 min-h-[40px] font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition active:scale-98 cursor-pointer flex items-center justify-center space-x-1.5"
                :class="{
                  'bg-[#DE1B54] hover:bg-[#C51345] text-white': dialogStore.variant === 'danger' || dialogStore.variant === 'primary',
                  'bg-[#BA5420] hover:bg-[#A34617] text-white': dialogStore.variant === 'warning',
                  'bg-[#047857] hover:bg-[#036045] text-white': dialogStore.variant === 'success',
                  'bg-blue-600 hover:bg-blue-700 text-white': dialogStore.variant === 'info',
                }"
              >
                <span>{{ dialogStore.confirmText }}</span>
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useDialogStore } from '../stores/dialog';

const dialogStore = useDialogStore();
const dialogTitleId = 'sweetbakes-dialog-title';
const inputRef = ref(null);
const confirmBtnRef = ref(null);

watch(
  () => dialogStore.isOpen,
  async (isOpen) => {
    if (isOpen) {
      await nextTick();
      if (dialogStore.type === 'prompt' && inputRef.value) {
        inputRef.value.focus();
        if (typeof inputRef.value.select === 'function') {
          inputRef.value.select();
        }
      } else if (confirmBtnRef.value) {
        confirmBtnRef.value.focus();
      }
    }
  }
);

function handleKeydown(e) {
  if (!dialogStore.isOpen) return;
  if (e.key === 'Escape') {
    e.preventDefault();
    dialogStore.handleCancel();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

