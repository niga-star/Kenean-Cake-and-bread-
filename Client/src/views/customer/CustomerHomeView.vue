<template>
  <div class="space-y-16 sm:space-y-20 selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO SECTION: Full-Bleed Natural Bakery Background with Delivery Badge -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] min-h-[440px] sm:min-h-[500px] lg:min-h-[560px] flex items-center border-b border-[#F2E5DD]">
      <!-- Background Bakery Imagery on Right -->
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[60%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/hero_right_hires.jpg"
          alt="SweetBakes Artisan Cake and Fresh Pastries"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <!-- Soft gradient mask smoothly merging into blush peach on the left -->
        <div class="absolute inset-y-0 left-0 w-32 sm:w-56 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/85 to-transparent hidden lg:block"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/70 lg:hidden"></div>
      </div>

      <!-- Hero Text Content on Left -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-12 sm:py-16 relative z-10">
        <div class="max-w-xl space-y-5 text-center lg:text-left">
          <!-- Cursive Subtitle with Heart -->
          <div class="inline-flex items-center space-x-2 font-script text-2xl sm:text-3xl lg:text-4xl text-[#DE1B54]">
            <span>Freshly Baked with Love</span>
            <span class="text-3xl font-bold">♡</span>
          </div>

          <!-- Large Bold Serif Title -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-black text-[#2A1810] tracking-tight leading-[1.1]">
            <span>Cakes &amp; Bread</span>
            <br />
            <span class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-[#422216]">
              for Every Special Moment
            </span>
          </h1>

          <!-- Body Description -->
          <p class="text-xs sm:text-sm lg:text-base text-[#6E5D53] leading-relaxed max-w-md mx-auto lg:mx-0">
            Delicious cakes, fresh bread and more... crafted with premium ingredients. Handcrafted daily for Addis Ababa celebrations.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
            <!-- Shop Now: Vivid Pink Pill Button -->
            <router-link
              to="/shop"
              class="px-7 py-3 bg-[#DE1B54] hover:bg-[#C51345] text-white font-bold text-xs sm:text-sm rounded-full shadow-md hover:shadow-lg transition-transform hover:scale-105 active:scale-95 flex items-center space-x-2 cursor-pointer"
            >
              <span>Shop Now</span>
              <span class="text-base">&rarr;</span>
            </router-link>

            <!-- Learn More: Outlined Rounded Pill Button -->
            <button
              @click="scrollToAbout"
              class="px-7 py-3 bg-white/80 hover:bg-white text-[#2A1810] hover:text-[#DE1B54] border border-[#2A1810]/30 hover:border-[#DE1B54] font-bold text-xs sm:text-sm rounded-full shadow-2xs transition-all cursor-pointer"
            >
              Learn More
            </button>
          </div>

          <!-- Fresh Delivery Badge (Exact Match to Reference Mockup) -->
          <div class="pt-2 flex items-center justify-center lg:justify-start space-x-2 text-xs text-[#786B60]">
            <span class="text-sm">🚚</span>
            <span class="font-medium">Freshly baked and delivered to your door.</span>
          </div>
        </div>
      </div>

      <!-- Carousel Navigation Arrows on Far Screen Edges -->
      <button
        @click="prevSlide"
        class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white/90 hover:bg-white text-gray-700 shadow-md flex items-center justify-center hover:text-[#DE1B54] transition z-20 cursor-pointer"
        title="Previous"
      >
        <span class="text-base font-black">&lsaquo;</span>
      </button>

      <button
        @click="nextSlide"
        class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white/90 hover:bg-white text-gray-700 shadow-md flex items-center justify-center hover:text-[#DE1B54] transition z-20 cursor-pointer"
        title="Next"
      >
        <span class="text-base font-black">&rsaquo;</span>
      </button>

      <!-- Carousel Pagination Dots: Bottom Center -->
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center space-x-2 z-20">
        <span class="w-6 h-2 rounded-full bg-[#DE1B54] transition-all"></span>
        <span class="w-2 h-2 rounded-full bg-white/90 border border-[#D8CDC5] transition-all"></span>
        <span class="w-2 h-2 rounded-full bg-white/90 border border-[#D8CDC5] transition-all"></span>
      </div>
    </section>

    <!-- 2. "OUR POPULAR PRODUCTS" SECTION: With Interactive Category Filter Pills -->
    <section id="popular-products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header with Cake Icon & Dashes -->
      <div class="text-center space-y-1.5 mb-6">
        <div class="flex items-center justify-center space-x-3 text-gray-300">
          <span class="h-px w-10 sm:w-16 bg-[#D8CDC5]"></span>
          <span class="text-xl sm:text-2xl text-[#DE1B54]">🎂</span>
          <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810]">
            Our Popular Products
          </h2>
          <span class="h-px w-10 sm:w-16 bg-[#D8CDC5]"></span>
        </div>
        <p class="text-xs sm:text-sm text-[#786B60]">
          Handpicked favorites from our bakery
        </p>
      </div>

      <!-- Category Filter Pills (Exact Match to Uploaded Image) -->
      <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5 mb-8">
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          @click="selectedCategory = cat"
          class="px-5 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 cursor-pointer"
          :class="selectedCategory === cat
            ? 'bg-[#DE1B54] text-white shadow-xs font-bold scale-105'
            : 'bg-white hover:bg-rose-50 border border-[#E5DAD1] text-[#554035] hover:text-[#DE1B54] hover:border-[#DE1B54]/40'"
        >
          {{ cat }}
        </button>
      </div>

      <!-- 5 Product Cards Horizontal Grid: Exact Match to Uploaded Image -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 transition-all duration-300">
        <div
          v-for="product in displayedProducts"
          :key="product.id"
          class="bg-white rounded-2xl border border-[#EFE8E1] hover:border-[#DE1B54]/40 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between overflow-hidden group p-3.5 space-y-3 cursor-pointer"
          @click="goToProduct(product.productId)"
        >
          <!-- Image Container with Heart Button (Clean Rounded) -->
          <div class="relative aspect-4/3 rounded-xl overflow-hidden bg-rose-50/30">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-500"
              loading="lazy"
            />

            <!-- Heart Wishlist Button -->
            <button
              type="button"
              @click.stop="toggleWishlist(product.id)"
              class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-xs shadow-2xs hover:scale-115 active:scale-90 transition-all cursor-pointer z-10"
              :class="wishlist.includes(product.id) ? 'text-[#DE1B54]' : 'text-gray-400 hover:text-[#DE1B54]'"
              title="Add to Wishlist"
            >
              <span>{{ wishlist.includes(product.id) ? '♥' : '♡' }}</span>
            </button>

            <!-- Ye'Tsom badge if fasting friendly -->
            <span
              v-if="product.is_fasting"
              class="absolute bottom-2 left-2 bg-emerald-800/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full shadow-2xs flex items-center space-x-1 pointer-events-none"
            >
              <span>🌿</span>
              <span>Ye'Tsom / ጾም</span>
            </span>
          </div>

          <!-- Product Details -->
          <div class="space-y-1.5 flex-1 flex flex-col justify-between">
            <div>
              <router-link
                :to="`/product/${product.productId}`"
                @click.stop
                class="font-serif font-bold text-sm text-[#2A1810] group-hover:text-[#DE1B54] transition-colors block truncate"
              >
                {{ product.name }}
              </router-link>

              <!-- Star Rating & Review Count -->
              <div class="flex items-center space-x-1.5 text-xs pt-0.5">
                <span class="text-amber-400 tracking-tighter">★★★★★</span>
                <span class="text-gray-700 text-[11px] font-bold">{{ product.rating }}</span>
                <span class="text-gray-400 text-[10px]">({{ product.reviews }} reviews)</span>
              </div>
            </div>

            <!-- Price in Bold Vivid Pink -->
            <div class="pt-1">
              <span class="text-sm font-black text-[#DE1B54]">
                ETB {{ product.price.toLocaleString() }}
              </span>
            </div>

            <!-- Full-width Vivid Pink Add to Cart Button -->
            <button
              type="button"
              @click.stop="handleAddToCart(product)"
              :disabled="product.isAdding"
              class="w-full py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center space-x-1.5 cursor-pointer active:scale-95"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>{{ product.isAdding ? 'Added! ✓' : 'Add to Cart' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Centered "Explore All Products →" Rounded Pill Button (Exact Match to Mockup) -->
      <div class="mt-10 text-center">
        <router-link
          to="/shop"
          class="px-8 py-2.5 rounded-full border-2 border-[#DE1B54] text-[#DE1B54] hover:bg-[#DE1B54] hover:text-white font-bold text-xs sm:text-sm transition-all duration-300 shadow-2xs hover:shadow-md cursor-pointer inline-flex items-center space-x-2 group"
        >
          <span>Explore All Products</span>
          <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
        </router-link>
      </div>
    </section>

    <!-- 3. "ABOUT SWEETBAKES - A BAKERY YOU CAN TRUST" SECTION (Exact Match to Reference Mockup) -->
    <section id="about" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="rounded-3xl bg-gradient-to-br from-[#FAF7F4] via-white to-[#FDF4F0] border border-[#EFE8E1] p-6 sm:p-10 lg:p-12 shadow-sm relative overflow-hidden group">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          <!-- Left Column: Baker Chef Photo with smooth hover zoom & lazy loading -->
          <div class="lg:col-span-6 flex justify-center">
            <div class="relative w-full max-w-md lg:max-w-none">
              <div class="rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg aspect-4/3 sm:aspect-16/10 bg-amber-50/20">
                <img
                  src="/mockup/baker_hires.jpg"
                  alt="SweetBakes Baker Chef"
                  class="w-full h-full object-cover object-center group-hover:scale-104 transition-transform duration-700"
                  loading="lazy"
                />
              </div>
            </div>
          </div>

          <!-- Right Column: About Details, Checklist, Stats & Scalloped Stamp -->
          <div class="lg:col-span-6 space-y-6 relative">
            <!-- Scalloped Stamp / Seal on top right: "SINCE 2015 ♡" (Exact Match to Mockup) -->
            <div class="absolute -top-3 sm:-top-6 right-0 sm:right-2">
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-rose-50/95 border-2 border-dashed border-[#DE1B54] flex flex-col items-center justify-center text-center p-2 shadow-xs text-[#DE1B54] rotate-6 hover:rotate-12 transition-transform duration-300 cursor-pointer select-none">
                <span class="text-[9px] sm:text-[10px] font-bold tracking-wider uppercase font-serif">SINCE</span>
                <span class="text-base sm:text-lg font-black font-serif leading-none">2015</span>
                <span class="text-xs">♡</span>
              </div>
            </div>

            <!-- Header with Cupcake & Cursive Tagline -->
            <div class="flex items-center space-x-2 text-[#DE1B54] font-script text-2xl sm:text-3xl">
              <span>🧁</span>
              <span>About SweetBakes</span>
            </div>

            <!-- Large Bold Serif Headline -->
            <h3 class="text-3xl sm:text-4xl font-serif font-black text-[#2A1810] leading-tight">
              A Bakery You Can Trust
            </h3>

            <!-- Body Paragraph -->
            <p class="text-xs sm:text-sm text-[#6E5D53] leading-relaxed max-w-lg">
              At SweetBakes, we believe in the joy of fresh-baked goodness. Our cakes and bread are made with love, using the finest ingredients and creative designs to make your moments sweeter.
            </p>

            <!-- 4 Bullets with Dark Circular Checkmarks (Exact Match) -->
            <div class="space-y-2 text-xs sm:text-sm font-semibold text-[#2A1810]">
              <div class="flex items-center space-x-3 p-1.5 -ml-1.5 rounded-xl hover:bg-rose-50/50 transition-colors cursor-default">
                <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>Premium Quality</span>
              </div>
              <div class="flex items-center space-x-3 p-1.5 -ml-1.5 rounded-xl hover:bg-rose-50/50 transition-colors cursor-default">
                <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>Fresh Ingredients</span>
              </div>
              <div class="flex items-center space-x-3 p-1.5 -ml-1.5 rounded-xl hover:bg-rose-50/50 transition-colors cursor-default">
                <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>Custom Cake Orders</span>
              </div>
              <div class="flex items-center space-x-3 p-1.5 -ml-1.5 rounded-xl hover:bg-rose-50/50 transition-colors cursor-default">
                <span class="w-5 h-5 rounded-full bg-[#2A1810] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>Multi-Branch Service</span>
              </div>
            </div>

            <!-- Action & Experience Stats Row (Exact Match to Uploaded Image) -->
            <div class="pt-3 flex flex-wrap items-center justify-between gap-4 border-t border-[#F0E6DD]">
              <!-- Discover More Button -->
              <router-link
                to="/branches"
                class="px-8 py-3 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs sm:text-sm font-bold rounded-full shadow-md hover:shadow-lg transition-transform hover:scale-105 active:scale-95 inline-flex items-center space-x-2 cursor-pointer group"
              >
                <span>Discover More</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
              </router-link>

              <!-- Stats: 10+ Years of Experience & 4 Branches -->
              <div class="flex items-center space-x-6 text-xs text-[#554035]">
                <div class="flex items-center space-x-2">
                  <span class="text-xl">👩‍🍳</span>
                  <div>
                    <div class="font-bold text-sm text-[#2A1810] leading-none">10+</div>
                    <div class="text-[10px] text-[#786B60]">Years of Experience</div>
                  </div>
                </div>

                <div class="h-7 w-px bg-rose-200"></div>

                <div class="flex items-center space-x-2">
                  <span class="text-xl">🏪</span>
                  <div>
                    <div class="font-bold text-sm text-[#2A1810] leading-none">4</div>
                    <div class="text-[10px] text-[#786B60]">Branches</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. "WHAT OUR CUSTOMERS SAY" (REVIEWS) SECTION: With Flanking Navigation Arrows -->
    <section id="reviews" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <!-- Decorative Ambient Blush Clouds at the Top Corners (Exact Match to Mockup) -->
      <div class="absolute -top-10 -left-10 w-72 h-72 bg-rose-100/50 rounded-full blur-3xl pointer-events-none -z-10"></div>
      <div class="absolute -top-10 -right-10 w-72 h-72 bg-rose-100/50 rounded-full blur-3xl pointer-events-none -z-10"></div>

      <!-- Section Header with Cupcake & Delicate Pink Divider Lines -->
      <div class="text-center space-y-1.5 mb-10">
        <div class="flex items-center justify-center space-x-3 text-rose-200">
          <span class="h-px w-14 sm:w-24 bg-rose-200"></span>
          <span class="text-xl sm:text-2xl text-[#DE1B54]">🧁</span>
          <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810]">
            What Our Customers Say
          </h2>
          <span class="h-px w-14 sm:w-24 bg-rose-200"></span>
        </div>
        <p class="text-xs sm:text-sm text-[#786B60]">
          Real reviews from our valued customers
        </p>
      </div>

      <!-- Testimonial Cards Carousel with Left & Right Arrows -->
      <div class="relative flex items-center">
        <!-- Previous Slide Arrow (Left) -->
        <button
          @click="prevReviewSlide"
          type="button"
          class="hidden sm:flex -left-4 sm:-left-5 absolute z-20 w-10 h-10 rounded-full bg-white border border-rose-100 shadow-md items-center justify-center text-gray-600 hover:text-[#DE1B54] hover:border-[#DE1B54]/40 hover:scale-105 transition cursor-pointer"
          title="Previous Reviews"
        >
          <span class="text-lg font-black leading-none">&lsaquo;</span>
        </button>

        <!-- 3 Testimonial Cards Grid -->
        <div
          class="w-full grid grid-cols-1 md:grid-cols-3 gap-6 transition-all duration-500"
          @mouseenter="pauseReviewAutoplay"
          @mouseleave="startReviewAutoplay"
        >
          <div
            v-for="(rev, idx) in currentReviewsSlide"
            :key="idx"
            class="bg-white rounded-3xl border border-rose-100/80 p-6 sm:p-7 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_16px_36px_rgba(222,27,84,0.1)] hover:-translate-y-1.5 transition-all duration-300 relative flex flex-col justify-between space-y-4 group cursor-default"
          >
            <!-- Pink Double Quotation Mark Icon in Top-Right -->
            <div class="absolute top-6 right-6 select-none opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all">
              <svg class="w-6 h-6 text-[#F472B6] fill-current" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
              </svg>
            </div>

            <!-- User Info Row: Avatar + Name + 5 Stars -->
            <div class="flex items-center space-x-3.5 pr-8">
              <img
                :src="rev.avatar"
                :alt="rev.name"
                class="w-13 h-13 sm:w-14 sm:h-14 rounded-full object-cover border-2 border-rose-100/80 shadow-2xs group-hover:scale-105 transition-transform"
                loading="lazy"
              />
              <div>
                <h4 class="font-serif font-bold text-base text-[#2A1810] group-hover:text-[#DE1B54] transition-colors">
                  {{ rev.name }}
                </h4>
                <div class="text-amber-400 text-xs sm:text-sm tracking-tighter mt-0.5">★★★★★</div>
              </div>
            </div>

            <!-- Quote Text -->
            <p class="text-xs sm:text-sm text-[#554035] leading-relaxed italic pt-1">
              &ldquo;{{ rev.quote }}&rdquo;
            </p>
          </div>
        </div>

        <!-- Next Slide Arrow (Right) -->
        <button
          @click="nextReviewSlide"
          type="button"
          class="hidden sm:flex -right-4 sm:-right-5 absolute z-20 w-10 h-10 rounded-full bg-white border border-rose-100 shadow-md items-center justify-center text-gray-600 hover:text-[#DE1B54] hover:border-[#DE1B54]/40 hover:scale-105 transition cursor-pointer"
          title="Next Reviews"
        >
          <span class="text-lg font-black leading-none">&rsaquo;</span>
        </button>
      </div>

      <!-- Interactive Carousel Pagination Dots -->
      <div class="flex items-center justify-center space-x-2.5 pt-8 sm:pt-10">
        <button
          v-for="dotIdx in reviewSlides.length"
          :key="dotIdx"
          type="button"
          @click="setReviewSlide(dotIdx - 1)"
          class="transition-all duration-300 cursor-pointer"
          :class="activeReviewSlide === dotIdx - 1
            ? 'w-3 h-3 rounded-full bg-[#DE1B54] shadow-xs scale-110' 
            : 'w-3 h-3 rounded-full bg-[#E5D7D0] hover:bg-rose-300'"
          :title="`View review slide ${dotIdx}`"
        ></button>
      </div>
    </section>

    <!-- 5. "SPECIAL OFFER: MAKE EVERY MOMENT SWEETER!" BANNER (Exact Match to Uploaded Image) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#FFF0F4] via-[#FDE4EE] to-[#FCE4EC] border border-[#F6C5D6] p-6 sm:p-8 lg:p-9 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm group">
        <!-- Left Column: Special Offer Text & Call to Action -->
        <div class="space-y-3 text-center md:text-left z-10 max-w-xl">
          <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#DE1B54] text-white shadow-2xs">
            Special Offer
          </span>
          <h3 class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#2A1810] tracking-tight">
            Make Every Moment <span class="text-[#DE1B54] font-script italic">Sweeter!</span>
          </h3>
          <p class="text-xs sm:text-sm text-[#6E5D53] leading-relaxed">
            Enjoy a special discount on selected bakery products. Handcrafted daily with love.
          </p>
        </div>

        <!-- Right Column: 15% OFF Badge, Action Button, and Featured Celebration Cake -->
        <div class="flex items-center space-x-3 sm:space-x-5 z-10 shrink-0">
          <!-- 15% OFF Oval Badge -->
          <div class="px-5 py-2.5 rounded-full bg-[#DE1B54] text-white font-black text-sm sm:text-base tracking-wide shadow-md whitespace-nowrap animate-pulse">
            15% OFF
          </div>

          <!-- Shop the Offer Button -->
          <router-link
            to="/shop"
            class="px-6 py-2.5 rounded-full bg-white hover:bg-rose-50 text-[#DE1B54] font-bold text-xs sm:text-sm border border-[#DE1B54]/30 shadow-xs hover:shadow-md transition-all inline-flex items-center space-x-1.5 whitespace-nowrap cursor-pointer hover:scale-103"
          >
            <span>Shop the Offer</span>
            <span>&rarr;</span>
          </router-link>

          <!-- Pink Celebration Cake Image -->
          <div class="hidden sm:block w-28 sm:w-36 lg:w-44 shrink-0 -my-4 -mr-2">
            <img
              src="/mockup/offer_cake.jpg"
              alt="SweetBakes Celebration Cake"
              class="w-full h-auto object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../../stores/cart';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';

const router = useRouter();
const cartStore = useCartStore();
const branchStore = useBranchStore();
const toastStore = useToastStore();

const wishlist = ref([1, 4]);

// Category Filter state: 'All', 'Cakes', 'Bread', 'Cupcakes', 'Pastries'
const selectedCategory = ref('All');
const categories = ['All', 'Cakes', 'Bread', 'Cupcakes', 'Pastries'];

// Popular Products with category metadata matching the uploaded mockup
const allPopularProducts = ref([
  {
    id: 1,
    productId: 1,
    category: 'Cakes',
    name: 'Chocolate Cake',
    price: 850,
    rating: '4.8',
    reviews: 124,
    image: '/mockup/product_chocolate_cake.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: true,
  },
  {
    id: 2,
    productId: 3,
    category: 'Cakes',
    name: 'Vanilla Cake',
    price: 750,
    rating: '4.7',
    reviews: 98,
    image: '/mockup/product_vanilla_cake.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: true,
  },
  {
    id: 3,
    productId: 2,
    category: 'Bread',
    name: 'Fresh Bread',
    price: 400,
    rating: '4.9',
    reviews: 200,
    image: '/mockup/product_fresh_bread.jpg',
    is_fasting: true,
    isAdding: false,
    isHeroPopular: true,
  },
  {
    id: 4,
    productId: 14,
    category: 'Cupcakes',
    name: 'Cupcakes',
    price: 250,
    rating: '4.8',
    reviews: 76,
    image: '/mockup/product_cupcakes.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: true,
  },
  {
    id: 5,
    productId: 4,
    category: 'Pastries',
    name: 'Croissant',
    price: 250,
    rating: '4.7',
    reviews: 85,
    image: '/mockup/product_croissant.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: true,
  },
  // Additional items for category specific tabs
  {
    id: 6,
    productId: 1,
    category: 'Cakes',
    name: 'Strawberry Cake',
    price: 850,
    rating: '4.9',
    reviews: 112,
    image: '/mockup/shop_strawberry_cake.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 7,
    productId: 12,
    category: 'Cakes',
    name: "Ye'Tsom Chocolate Cake",
    price: 850,
    rating: '4.9',
    reviews: 64,
    image: '/mockup/shop_chocolate_cake.jpg',
    is_fasting: true,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 8,
    productId: 7,
    category: 'Bread',
    name: 'Rustic Rye Sourdough',
    price: 250,
    rating: '4.8',
    reviews: 95,
    image: '/mockup/shop_fresh_bread.jpg',
    is_fasting: true,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 9,
    productId: 5,
    category: 'Bread',
    name: 'Whole Wheat Bread',
    price: 55,
    rating: '4.7',
    reviews: 50,
    image: '/mockup/product_fresh_bread.jpg',
    is_fasting: true,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 10,
    productId: 14,
    category: 'Cupcakes',
    name: 'Strawberry Cupcakes',
    price: 260,
    rating: '4.8',
    reviews: 45,
    image: '/mockup/shop_cupcakes.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 11,
    productId: 6,
    category: 'Pastries',
    name: 'Blueberry Muffin Box',
    price: 320,
    rating: '4.9',
    reviews: 58,
    image: '/mockup/shop_pastries.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: false,
  },
  {
    id: 12,
    productId: 8,
    category: 'Pastries',
    name: 'Almond Croissant',
    price: 180,
    rating: '4.8',
    reviews: 62,
    image: '/mockup/shop_croissant.jpg',
    is_fasting: false,
    isAdding: false,
    isHeroPopular: false,
  },
]);

// Displayed Products computed from category filter
const displayedProducts = computed(() => {
  if (selectedCategory.value === 'All') {
    return allPopularProducts.value.filter(p => p.isHeroPopular);
  }
  return allPopularProducts.value.filter(p => p.category === selectedCategory.value);
});

// 3 Review Slides for Interactive Carousel
const reviewSlides = [
  [
    { name: 'Sara Ahmed', avatar: '/mockup/avatar_sara.jpg', quote: 'The cake was amazing! Fresh, beautiful and delicious. Highly recommended!' },
    { name: 'Daniel Kebede', avatar: '/mockup/avatar_daniel.jpg', quote: 'Great service and tasty bread. Will order again soon!' },
    { name: 'Mekdes Tadesse', avatar: '/mockup/avatar_mekdes.jpg', quote: 'Best bakery in Addis! The custom cake exceeded my expectations.' },
  ],
  [
    { name: 'Bethlehem Haile', avatar: '/mockup/avatar_sara.jpg', quote: 'Ordered the Ye’Tsom fasting cake for our celebration. Perfectly moist and flavorful!' },
    { name: 'Yonas Alemu', avatar: '/mockup/avatar_daniel.jpg', quote: 'The sourdough bread has that artisan golden crust. My daily morning ritual in Bole.' },
    { name: 'Selamawit Bekele', avatar: '/mockup/avatar_mekdes.jpg', quote: 'Their custom 3-tier centerpiece was the highlight of our wedding reception. Master bakers!' },
  ],
  [
    { name: 'Abel Tesfaye', avatar: '/mockup/avatar_daniel.jpg', quote: 'Delivered warm to CMC in under 35 minutes! SweetBakes is our family’s go-to bakery.' },
    { name: 'Helina Girma', avatar: '/mockup/avatar_sara.jpg', quote: 'SweetBakes cupcakes are the absolute hit at our office parties. Truly top tier in Ethiopia!' },
    { name: 'Dawit Mengistu', avatar: '/mockup/avatar_mekdes.jpg', quote: 'Exceptional customer service, pure ingredients, and incredible artisan chocolate ganache.' },
  ],
];

const activeReviewSlide = ref(0);
let reviewTimer = null;

const currentReviewsSlide = computed(() => reviewSlides[activeReviewSlide.value]);

function setReviewSlide(idx) {
  activeReviewSlide.value = idx;
}

function prevReviewSlide() {
  activeReviewSlide.value = (activeReviewSlide.value - 1 + reviewSlides.length) % reviewSlides.length;
}

function nextReviewSlide() {
  activeReviewSlide.value = (activeReviewSlide.value + 1) % reviewSlides.length;
}

function startReviewAutoplay() {
  if (reviewTimer) clearInterval(reviewTimer);
  reviewTimer = setInterval(() => {
    activeReviewSlide.value = (activeReviewSlide.value + 1) % reviewSlides.length;
  }, 6000);
}

function pauseReviewAutoplay() {
  if (reviewTimer) {
    clearInterval(reviewTimer);
    reviewTimer = null;
  }
}

onMounted(() => {
  startReviewAutoplay();
});

onUnmounted(() => {
  pauseReviewAutoplay();
});

function goToProduct(id) {
  router.push(`/product/${id}`);
}

function toggleWishlist(id) {
  if (wishlist.value.includes(id)) {
    wishlist.value = wishlist.value.filter(i => i !== id);
    toastStore.info('Removed from favorites.');
  } else {
    wishlist.value.push(id);
    toastStore.success('Added to favorites!');
  }
}

function handleAddToCart(p) {
  p.isAdding = true;

  const result = cartStore.addItem(
    {
      product_id: p.productId,
      product_name: p.name,
      price: p.price,
      image_url: p.image,
      available_stock: 40,
      available: 40,
    },
    branchStore.selectedBranchId || 2,
    1
  );

  setTimeout(() => {
    p.isAdding = false;
  }, 400);

  if (result && result.success === false) {
    toastStore.error(result.message || 'Unable to add item.');
  } else {
    toastStore.success(`Added ${p.name} to cart!`);
    cartStore.openDrawer();
  }
}

function scrollToAbout() {
  const el = document.getElementById('about');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
}

function prevSlide() {
  toastStore.info('Viewing previous bakery special');
}

function nextSlide() {
  toastStore.info('Viewing next bakery special');
}
</script>
