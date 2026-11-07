// Custom Vue Directive for smooth, performant scroll-reveal animations
// Automatically disconnects each element once revealed to ensure zero ongoing CPU/memory overhead.

export const vReveal = {
  mounted(el, binding) {
    if (typeof window === 'undefined') return;

    // Respect user's accessibility reduced-motion preference
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
      el.classList.add('reveal-visible');
      return;
    }

    // Base styling
    el.classList.add('reveal-init');

    if (binding.arg) {
      el.classList.add(`reveal-${binding.arg}`);
    }

    const delay = typeof binding.value === 'number' ? binding.value : 0;
    if (delay > 0) {
      el.style.transitionDelay = `${delay}ms`;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.classList.add('reveal-visible');
            observer.unobserve(el);
          }
        });
      },
      {
        threshold: 0.08,
        rootMargin: '0px 0px -30px 0px',
      }
    );

    observer.observe(el);
    el._revealObserver = observer;
  },

  unmounted(el) {
    if (el._revealObserver) {
      el._revealObserver.disconnect();
      delete el._revealObserver;
    }
  },
};

