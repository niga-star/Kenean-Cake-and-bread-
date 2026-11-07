<template>
  <transition
    enter-active-class="transition-opacity duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-all duration-700 ease-in-out"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-105 pointer-events-none"
  >
    <div
      v-if="isVisible"
      class="fixed inset-0 z-[99999] bg-[#FAF7F5] dark:bg-[#150D09] flex flex-col items-center justify-center p-6 select-none overflow-hidden"
      role="status"
      aria-live="polite"
      aria-label="Loading SweetBakes Bakery"
      @click="dismissEarly"
    >
      <!-- Ambient Bakery Glow Rings -->
      <div class="absolute -top-32 -left-32 w-96 h-96 bg-rose-200/40 dark:bg-rose-950/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-amber-200/30 dark:bg-amber-950/20 rounded-full blur-3xl pointer-events-none animate-pulse" style="animation-delay: 1s;"></div>

      <div class="relative z-10 flex flex-col items-center max-w-sm w-full text-center space-y-6">
        <!-- Rotating Artisanal Seal / Badge with Cupcake Icon -->
        <div class="relative flex items-center justify-center">
          <!-- Outer Rotating Dashed Ring -->
          <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-2 border-dashed border-[#DE1B54]/40 dark:border-[#DE1B54]/60 animate-spin-slow"></div>

          <!-- Pulsing Inner Circle -->
          <div class="absolute inset-2 rounded-full bg-white dark:bg-[#20140E] shadow-xl border border-rose-100 dark:border-[#382318] flex items-center justify-center">
            <span class="text-3xl sm:text-4xl filter drop-shadow-sm transform hover:scale-110 transition-transform">🧁</span>
          </div>

          <!-- Little Sparkle Accents -->
          <span class="absolute -top-1 -right-1 text-sm animate-bounce text-[#DE1B54]">✨</span>
          <span class="absolute -bottom-1 -left-1 text-xs animate-bounce text-amber-500" style="animation-delay: 0.5s;">★</span>
        </div>

        <!-- Bakery Brand Name in Dancing Script -->
        <div class="space-y-1">
          <div class="text-4xl sm:text-5xl font-logo font-bold text-[#2A1810] dark:text-[#FAF4ED] tracking-normal leading-none drop-shadow-xs">
            Sweet<span class="text-[#DE1B54]">Bakes</span>
          </div>
          <div class="text-[10px] sm:text-[11px] uppercase tracking-[0.25em] text-[#8C7E77] dark:text-[#A8988E] font-medium pt-1">
            Cakes &middot; Bread &middot; Happiness
          </div>
        </div>

        <!-- Sleek Animated Berry Progress Indicator -->
        <div class="w-48 sm:w-56 space-y-2 pt-2">
          <div class="h-1 w-full bg-rose-100 dark:bg-[#2A1710] rounded-full overflow-hidden relative">
            <div
              class="h-full bg-gradient-to-r from-[#DE1B54] via-[#F78BA8] to-[#DE1B54] rounded-full transition-all duration-300 ease-out"
              :style="{ width: `${progress}%` }"
            ></div>
          </div>
          <div class="flex items-center justify-between text-[10px] font-semibold text-[#A09088] dark:text-[#887870] px-0.5">
            <span>{{ isAmharic ? 'በማዘጋጀት ላይ...' : 'Crafting Fresh Bakes...' }}</span>
            <span>{{ Math.min(100, Math.round(progress)) }}%</span>
          </div>
        </div>

        <!-- Subtle Skip Hint -->
        <button
          @click.stop="dismissEarly"
          class="text-[10px] text-[#A8988E] hover:text-[#DE1B54] transition-colors pt-2 cursor-pointer focus:outline-none"
        >
          {{ isAmharic ? 'ለመዝለል ጠቅ ያድርጉ' : 'Click anywhere to enter' }} &rarr;
        </button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const isVisible = ref(false);
const progress = ref(0);

const isAmharic = computed(() => {
  return typeof document !== 'undefined' && document.documentElement.lang === 'am';
});

function dismissEarly() {
  progress.value = 100;
  isVisible.value = false;
  try {
    sessionStorage.setItem('sweetbakes_welcomed', 'true');
  } catch {
    // ignore sessionStorage errors in restricted privacy modes
  }
}

onMounted(() => {
  // Check if visitor has already experienced the welcome animation in this browser session
  let alreadyWelcomed = false;
  try {
    alreadyWelcomed = sessionStorage.getItem('sweetbakes_welcomed') === 'true';
  } catch {
    alreadyWelcomed = false;
  }

  // Check prefers-reduced-motion
  const prefersReducedMotion = typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (alreadyWelcomed || prefersReducedMotion) {
    isVisible.value = false;
    return;
  }

  // First session entrance: display smoothly
  isVisible.value = true;
  const startTime = Date.now();
  const totalDuration = 950; // ms

  const interval = setInterval(() => {
    const elapsed = Date.now() - startTime;
    const pct = Math.min(100, (elapsed / totalDuration) * 100);
    progress.value = pct;

    if (pct >= 100) {
      clearInterval(interval);
      setTimeout(() => {
        dismissEarly();
      }, 150);
    }
  }, 30);

  // Safety fallback: ensure dismissal within 1.6s under any circumstances
  setTimeout(() => {
    if (isVisible.value) {
      dismissEarly();
    }
  }, 1600);
});
</script>

<style scoped>
@keyframes spinSlow {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin-slow {
  animation: spinSlow 14s linear infinite;
}

@media (prefers-reduced-motion: reduce) {
  .animate-spin-slow {
    animation: none;
  }
}
</style>

