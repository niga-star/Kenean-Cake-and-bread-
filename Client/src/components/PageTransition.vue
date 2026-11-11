<template>
  <Transition
    :name="transitionName"
    mode="out-in"
    @after-leave="handleAfterLeave"
  >
    <div v-if="component" :key="transitionKey" class="sweet-page-shell w-full">
      <component :is="component" />
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { isReducedMotion, notifyPageLeft } from '../composables/usePageTransition';

const props = defineProps({
  component: {
    type: [Object, Function],
    default: null,
  },
  transitionKey: {
    type: String,
    required: true,
  },
  scrollContainerSelector: {
    type: String,
    default: '',
  },
});

const transitionName = computed(() => (isReducedMotion.value ? 'sweet-page-reduced' : 'sweet-page'));

function handleAfterLeave() {
  if (props.scrollContainerSelector && typeof document !== 'undefined') {
    const container = document.querySelector(props.scrollContainerSelector);
    if (container) {
      container.scrollTo({ top: 0, behavior: 'auto' });
    }
  }
  notifyPageLeft();
}
</script>
