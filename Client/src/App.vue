<template>
  <div class="relative min-h-screen">
    <!-- Slim SweetBakes Berry Pink Top Loading Progress Bar -->
    <div v-if="isLoading" class="sweet-top-loader">
      <div
        class="sweet-top-loader-bar"
        :style="{ width: `${progress}%`, opacity: progress >= 100 ? 0 : 1 }"
      ></div>
    </div>

    <!-- Router View with Smooth Page Fade Transition -->
    <router-view v-slot="{ Component }">
      <transition name="page-fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const isLoading = ref(false);
const progress = ref(0);
let progressTimer = null;

router.beforeEach((to, from, next) => {
  if (to.path !== from.path) {
    isLoading.value = true;
    progress.value = 25;
    clearInterval(progressTimer);
    progressTimer = setInterval(() => {
      if (progress.value < 85) {
        progress.value += Math.floor(Math.random() * 15) + 5;
      }
    }, 120);
  }
  next();
});

router.afterEach(() => {
  progress.value = 100;
  clearInterval(progressTimer);
  setTimeout(() => {
    isLoading.value = false;
    progress.value = 0;
  }, 220);
});
</script>
