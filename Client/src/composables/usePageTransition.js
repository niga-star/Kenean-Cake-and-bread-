import { ref } from 'vue';

// Coordinates route page leave completion with Vue Router scrollBehavior
// so exiting views stay visually stable while fading out and entering views
// land smoothly at their target scroll position without flicker.

let leavePromise = null;
let leaveResolver = null;
let leaveTimeoutId = null;

export const isReducedMotion = ref(false);

if (typeof window !== 'undefined' && window.matchMedia) {
  const mql = window.matchMedia('(prefers-reduced-motion: reduce)');
  isReducedMotion.value = mql.matches;
  const updateMotionPref = (event) => {
    isReducedMotion.value = event.matches;
  };
  if (mql.addEventListener) {
    mql.addEventListener('change', updateMotionPref);
  } else if (mql.addListener) {
    mql.addListener(updateMotionPref);
  }
}

export function prefersReducedMotion() {
  if (typeof window !== 'undefined' && window.matchMedia) {
    isReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }
  return isReducedMotion.value;
}

export function beginPageTransition() {
  if (prefersReducedMotion()) {
    leavePromise = null;
    leaveResolver = null;
    return;
  }

  clearTimeout(leaveTimeoutId);
  if (leaveResolver) {
    leaveResolver();
    leaveResolver = null;
  }

  leavePromise = new Promise((resolve) => {
    leaveResolver = resolve;
    // Safety cap just above leave duration (220ms) so navigation never stalls
    leaveTimeoutId = setTimeout(() => {
      if (leaveResolver) {
        leaveResolver();
        leaveResolver = null;
      }
      leavePromise = null;
    }, 280);
  });
}

export function notifyPageLeft() {
  clearTimeout(leaveTimeoutId);
  if (leaveResolver) {
    leaveResolver();
    leaveResolver = null;
  }
  leavePromise = null;
}

export async function waitForPageLeave() {
  if (!leavePromise || prefersReducedMotion()) {
    return;
  }
  await leavePromise;
}
