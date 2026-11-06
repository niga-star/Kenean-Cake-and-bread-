<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 space-y-16 lg:space-y-24 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]">
    <!-- Hero Header -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
      <div class="inline-flex items-center space-x-1.5 px-4 py-1.5 bg-rose-50 border border-rose-200 text-[#DE1B54] text-xs font-bold rounded-full">
        <span>🧁</span>
        <span>Since 2015 &middot; SweetBakes &middot; Addis Ababa</span>
      </div>
      <h1 class="text-4xl sm:text-6xl font-serif font-black text-[#2A1810] tracking-tight leading-[1.15]">
        A Bakery You Can Trust
      </h1>
      <p class="text-sm sm:text-base text-[#6E5D53] leading-relaxed">
        At SweetBakes, we believe in the joy of fresh-baked goodness. Our cakes and bread are made with love, using the finest ingredients and creative designs to make your moments sweeter.
      </p>
    </div>

    <!-- Story 2-Column Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-6 space-y-5 text-sm text-[#6E5D53] leading-relaxed">
        <span class="text-xs font-bold uppercase tracking-widest text-[#DE1B54]">About SweetBakes</span>
        <h2 class="text-3xl sm:text-4xl font-serif font-black text-[#2A1810] leading-tight">
          Freshly Baked with Love for Every Special Moment
        </h2>
        <p>
          Founded in 2015 in Addis Ababa, SweetBakes began with a dream to create delightful celebration cakes, warm golden sourdough bread, and flaky French butter croissants.
        </p>
        <p>
          Our bakers arrive every morning at dawn to handcraft each recipe from scratch using pure cocoa, Madagascar vanilla, stoneground whole wheat, and fresh seasonal berries.
        </p>

        <!-- 4 Core Pillars -->
        <div class="space-y-2.5 pt-2 text-xs sm:text-sm font-semibold text-[#2A1810]">
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Premium Quality</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Fresh Ingredients</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Custom Cake Orders</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Multi-Branch Service</span>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-3 gap-4 pt-4 text-center">
          <div class="p-4 bg-white rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#DE1B54]">4</div>
            <div class="text-[11px] font-bold text-[#2A1810] mt-1">Addis Branches</div>
          </div>
          <div class="p-4 bg-white rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#DE1B54]">35,000+</div>
            <div class="text-[11px] font-bold text-[#2A1810] mt-1">Cakes Delivered</div>
          </div>
          <div class="p-4 bg-white rounded-2xl border border-gray-200 shadow-2xs">
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#DE1B54]">100%</div>
            <div class="text-[11px] font-bold text-[#2A1810] mt-1">Pure Ingredients</div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-6 relative">
        <div class="rounded-3xl overflow-hidden shadow-xl border-4 border-white bg-amber-50">
          <img
            src="/mockup/baker.jpg"
            alt="SweetBakes Baker Chef"
            class="w-full h-88 sm:h-112 object-cover"
          />
        </div>
        <div class="absolute -bottom-6 -left-6 bg-white p-5 rounded-2xl border border-gray-200 shadow-lg hidden sm:block max-w-xs">
          <div class="text-xs font-bold text-[#2A1810] mb-1">“Making life sweeter, one cake and bread at a time.”</div>
          <div class="text-[10px] text-[#DE1B54] font-semibold">— SweetBakes Master Pastry Chef</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// SweetBakes About Page
</script>
