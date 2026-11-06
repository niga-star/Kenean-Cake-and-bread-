<template>
  <div class="bg-white min-h-screen text-[#29231F] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-8">
      
      <!-- 1. BREADCRUMBS: Exact match to mockup (🏠 > Shop > Cakes > Chocolate Cake) -->
      <nav class="flex items-center space-x-2 text-xs text-gray-500 font-medium">
        <router-link to="/" class="hover:text-[#DE1B54] flex items-center text-gray-600 hover:text-[#DE1B54] transition">
          <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
          </svg>
        </router-link>
        <span class="text-gray-300">&gt;</span>
        <router-link to="/shop" class="hover:text-[#DE1B54] transition">Shop</router-link>
        <span class="text-gray-300">&gt;</span>
        <router-link :to="`/shop?category=${encodeURIComponent(categoryName)}`" class="hover:text-[#DE1B54] transition">
          {{ categoryName }}
        </router-link>
        <span class="text-gray-300">&gt;</span>
        <span class="text-[#2A1810] font-semibold truncate max-w-xs">{{ productName }}</span>
      </nav>

      <!-- 2. UPPER PRODUCT SHOWCASE: 2 Columns (Left Gallery + Right Product Details) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- ================= LEFT GALLERY (5-6 cols) ================= -->
        <div class="lg:col-span-6 flex flex-row gap-3 sm:gap-4 items-start">
          
          <!-- Vertical Thumbnail Strip with Up/Down Arrows -->
          <div class="flex flex-col items-center space-y-2 shrink-0">
            <!-- Up Arrow -->
            <button
              @click="prevImage"
              class="w-6 h-6 rounded-md bg-gray-50 hover:bg-gray-100 text-gray-500 hover:text-[#DE1B54] flex items-center justify-center text-xs transition cursor-pointer"
              title="Previous photo"
            >
              &#9650;
            </button>

            <!-- 4 Vertical Thumbnails -->
            <div class="flex flex-col space-y-2">
              <button
                v-for="(img, idx) in activeGallery"
                :key="idx"
                @click="activeImageIdx = idx"
                class="w-14 sm:w-16 h-12 sm:h-14 rounded-xl overflow-hidden border-2 transition duration-200 cursor-pointer shadow-2xs shrink-0"
                :class="activeImageIdx === idx 
                  ? 'border-[#DE1B54] ring-2 ring-[#DE1B54]/20 scale-102' 
                  : 'border-gray-200 opacity-70 hover:opacity-100 hover:border-gray-300'"
              >
                <img :src="img" :alt="`${productName} thumbnail ${idx + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>

            <!-- Down Arrow -->
            <button
              @click="nextImage"
              class="w-6 h-6 rounded-md bg-gray-50 hover:bg-gray-100 text-gray-500 hover:text-[#DE1B54] flex items-center justify-center text-xs transition cursor-pointer"
              title="Next photo"
            >
              &#9660;
            </button>
          </div>

          <!-- Large Main Image Display Card -->
          <div class="relative flex-1 aspect-4/3 sm:aspect-5/4 rounded-2xl overflow-hidden bg-[#FAF7F5] border border-gray-200/80 shadow-xs group">
            <!-- Top-Left: "Best Seller" Badge -->
            <span class="absolute top-3 left-3 z-10 bg-[#DE1B54] text-white text-[11px] font-bold px-3 py-1 rounded-md shadow-2xs tracking-wide">
              Best Seller
            </span>

            <!-- Top-Right: Wishlist Heart Button -->
            <button
              type="button"
              @click="toggleWishlist"
              class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-white/95 shadow-xs flex items-center justify-center text-base hover:scale-110 transition cursor-pointer"
              :class="isWishlisted ? 'text-[#DE1B54]' : 'text-gray-400 hover:text-[#DE1B54]'"
              title="Add to Wishlist"
            >
              <span>{{ isWishlisted ? '♥' : '♡' }}</span>
            </button>

            <!-- Ye'Tsom Fasting Badge if applicable -->
            <span
              v-if="product?.is_fasting"
              class="absolute bottom-3 left-3 z-10 bg-emerald-800/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-2xs flex items-center space-x-1"
            >
              <span>🌿</span>
              <span>Ye'Tsom (ጾም)</span>
            </span>

            <!-- Main Cake Image -->
            <img
              :src="activeGallery[activeImageIdx] || '/mockup/detail_main_cake.jpg'"
              :alt="productName"
              class="w-full h-full object-cover group-hover:scale-102 transition duration-500 cursor-zoom-in"
              @click="showZoom = true"
            />

            <!-- Bottom-Right: "View Larger" Pill Button -->
            <button
              @click="showZoom = true"
              class="absolute bottom-3 right-3 z-10 bg-[#DE1B54] hover:bg-[#C51345] text-white text-[11px] font-bold px-3.5 py-1.5 rounded-full shadow-md flex items-center space-x-1.5 transition active:scale-95 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
              </svg>
              <span>View Larger</span>
            </button>
          </div>
        </div>

        <!-- ================= RIGHT DETAILS & ORDER PANEL (6-7 cols) ================= -->
        <div class="lg:col-span-6 space-y-5">
          <!-- Title -->
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#2A1810] tracking-tight leading-tight">
            {{ productName }}
          </h1>

          <!-- Reviews Score & Stock Status Pill -->
          <div class="flex flex-wrap items-center gap-3 text-xs">
            <!-- Stars & Reviews -->
            <div class="flex items-center space-x-1.5 text-amber-400">
              <span class="text-sm tracking-tighter">★★★★★</span>
              <span class="font-bold text-[#2A1810] text-xs">4.8</span>
              <span class="text-gray-400 font-normal">({{ reviewCount }} Reviews)</span>
            </div>

            <span class="text-gray-300">|</span>

            <!-- Stock Status Pill -->
            <span
              v-if="(product?.available ?? 15) > 0"
              class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EAF7EE] text-[#2E7D32] border border-[#C6EBD0]"
            >
              <span>In Stock</span>
              <span>✔</span>
            </span>
            <span
              v-else
              class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-[#DE1B54] border border-rose-200"
            >
              <span>Sold Out</span>
            </span>
          </div>

          <!-- Price Row with Crossed Out & Discount Tag -->
          <div class="flex items-center space-x-3 pt-1">
            <span class="text-2xl sm:text-3xl font-black text-[#DE1B54]">
              ETB {{ currentPrice.toLocaleString() }}
            </span>
            <span v-if="originalPrice" class="text-sm sm:text-base text-gray-400 line-through">
              ETB {{ originalPrice.toLocaleString() }}
            </span>
            <span v-if="discountBadge" class="bg-[#DE1B54] text-white text-xs font-bold px-2 py-0.5 rounded-md">
              {{ discountBadge }}
            </span>
          </div>

          <!-- Product Short Description -->
          <p class="text-xs sm:text-sm text-[#6E5D53] leading-relaxed">
            {{ productDescription }}
          </p>

          <!-- Ethiopian Fasting Callout if applicable -->
          <div v-if="product?.is_fasting" class="bg-emerald-50 rounded-xl border border-emerald-200 p-3 flex items-start space-x-2.5 text-emerald-950 text-xs">
            <span class="text-base shrink-0 mt-0.5">🌿</span>
            <div class="space-y-0.5">
              <div class="font-bold text-emerald-900">100% Ethiopian Fasting Friendly (የጾም)</div>
              <p class="text-emerald-800 text-[11px]">Handcrafted strictly vegan and dairy-free without butter, eggs, or milk.</p>
            </div>
          </div>

          <!-- Size Selector (6 Inch, 8 Inch, 10 Inch, 12 Inch) -->
          <div class="space-y-2 pt-2">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-[#2A1810]">Size:</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
              <button
                v-for="s in sizeOptions"
                :key="s.label"
                type="button"
                @click="selectedSize = s.label"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer"
                :class="selectedSize === s.label
                  ? 'border-2 border-[#DE1B54] text-[#DE1B54] bg-rose-50/20'
                  : 'border border-gray-200 text-gray-700 bg-white hover:bg-gray-50'"
              >
                {{ s.label }}
              </button>
            </div>
          </div>

          <!-- Quantity Stepper -->
          <div class="flex items-center space-x-3 pt-1">
            <span class="text-xs font-bold text-[#2A1810]">Quantity:</span>
            <div class="flex items-center border border-gray-200 rounded-xl bg-white shadow-2xs">
              <button
                type="button"
                @click="decreaseQty"
                class="w-8 h-8 flex items-center justify-center text-xs font-bold text-gray-600 hover:text-[#DE1B54] rounded-l-xl cursor-pointer disabled:opacity-40"
                :disabled="quantity <= 1"
              >
                &minus;
              </button>
              <span class="w-9 text-center text-xs font-bold text-[#2A1810]">
                {{ quantity }}
              </span>
              <button
                type="button"
                @click="increaseQty"
                class="w-8 h-8 flex items-center justify-center text-xs font-bold text-gray-600 hover:text-[#DE1B54] rounded-r-xl cursor-pointer disabled:opacity-40"
                :disabled="quantity >= (product?.available ?? 20)"
              >
                +
              </button>
            </div>
          </div>

          <!-- Piped Writing on Cake (Collapsible / Optional for custom occasion) -->
          <div v-if="isCakeCategory" class="pt-1">
            <button
              type="button"
              @click="showCustomMessage = !showCustomMessage"
              class="text-xs text-[#DE1B54] font-bold hover:underline flex items-center space-x-1 cursor-pointer"
            >
              <span>✍️ Add complimentary custom piped writing on cake</span>
              <span>{{ showCustomMessage ? '▲' : '▼' }}</span>
            </button>
            <div v-show="showCustomMessage" class="mt-2 space-y-2 bg-rose-50/40 p-3 rounded-xl border border-rose-100">
              <input
                v-model="cakeMessage"
                type="text"
                maxlength="40"
                placeholder="e.g. Happy 30th Birthday Bethlehem! 🎉"
                class="w-full bg-white border border-gray-200 focus:border-[#DE1B54] rounded-lg px-3 py-1.5 text-xs text-[#2A1810] focus:outline-none"
              />
            </div>
          </div>

          <!-- Action Buttons: [ 🛒 Add to Cart ] & [ ♥ Add to Wishlist ] -->
          <div class="flex flex-col sm:flex-row items-center gap-3 pt-3">
            <!-- Add to Cart Button -->
            <button
              type="button"
              @click="handleAddToCart"
              :disabled="(product?.available ?? 15) <= 0"
              class="w-full sm:flex-1 py-3 px-6 bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition transform active:scale-98 flex items-center justify-center space-x-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Add to Cart</span>
            </button>

            <!-- Add to Wishlist Button -->
            <button
              type="button"
              @click="toggleWishlist"
              class="w-full sm:w-auto py-3 px-6 border border-[#DE1B54] text-[#DE1B54] hover:bg-rose-50 font-bold text-xs sm:text-sm rounded-xl transition shadow-2xs flex items-center justify-center space-x-2 cursor-pointer"
            >
              <span class="text-base">{{ isWishlisted ? '♥' : '♡' }}</span>
              <span>Add to Wishlist</span>
            </button>
          </div>

          <!-- Trust Badges Bar (Free Delivery, Secure Payment, 24/7 Support) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t border-gray-100">
            <!-- Free Delivery -->
            <div class="flex items-center space-x-2.5 p-3 rounded-xl bg-[#FAF7F5] border border-gray-100">
              <svg class="w-5 h-5 text-[#2A1810] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4l3 3v5a1 1 0 01-1 1h-1" />
              </svg>
              <div class="leading-tight">
                <div class="text-xs font-bold text-[#2A1810]">Free Delivery</div>
                <div class="text-[10px] text-gray-500">On Orders Over ETB 1,000</div>
              </div>
            </div>

            <!-- Secure Payment -->
            <div class="flex items-center space-x-2.5 p-3 rounded-xl bg-[#FAF7F5] border border-gray-100">
              <svg class="w-5 h-5 text-[#2A1810] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <div class="leading-tight">
                <div class="text-xs font-bold text-[#2A1810]">Secure Payment</div>
                <div class="text-[10px] text-gray-500">Cash or Online</div>
              </div>
            </div>

            <!-- 24/7 Support -->
            <div class="flex items-center space-x-2.5 p-3 rounded-xl bg-[#FAF7F5] border border-gray-100">
              <svg class="w-5 h-5 text-[#2A1810] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <div class="leading-tight">
                <div class="text-xs font-bold text-[#2A1810]">24/7 Support</div>
                <div class="text-[10px] text-gray-500">We're here to help</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. BOTTOM SECTION: 3 Columns / Cards (Description & Bullet Points, Customer Reviews Breakdown, Promo Card) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-4 items-stretch">
        
        <!-- CARD 1 (5 cols): Tabbed Description & Key Bullets -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
          <div>
            <!-- Tabs Header: Description, Ingredients, Reviews -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-3 text-xs font-bold">
              <div class="flex items-center space-x-5">
                <button
                  type="button"
                  @click="activeTab = 'desc'"
                  class="pb-1 transition cursor-pointer"
                  :class="activeTab === 'desc' ? 'text-[#DE1B54] border-b-2 border-[#DE1B54]' : 'text-gray-500 hover:text-[#2A1810]'"
                >
                  Description
                </button>
                <button
                  type="button"
                  @click="activeTab = 'ingr'"
                  class="pb-1 transition cursor-pointer"
                  :class="activeTab === 'ingr' ? 'text-[#DE1B54] border-b-2 border-[#DE1B54]' : 'text-gray-500 hover:text-[#2A1810]'"
                >
                  Ingredients
                </button>
                <button
                  type="button"
                  @click="activeTab = 'reviews'"
                  class="pb-1 transition cursor-pointer"
                  :class="activeTab === 'reviews' ? 'text-[#DE1B54] border-b-2 border-[#DE1B54]' : 'text-gray-500 hover:text-[#2A1810]'"
                >
                  Reviews ({{ reviewCount }})
                </button>
              </div>
              <span class="text-gray-300 text-xs">✕</span>
            </div>

            <!-- Tab Body: Description -->
            <div v-if="activeTab === 'desc'" class="pt-4 space-y-3">
              <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810]">About This Cake</h3>
              <p class="text-xs text-[#6E5D53] leading-relaxed">
                {{ aboutCakeText }}
              </p>

              <!-- 3 Green Checkmark Bullets: Exact Match to Mockup -->
              <div class="space-y-2 pt-2">
                <div class="flex items-center space-x-2 text-xs text-[#2A1810] font-medium">
                  <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✔</span>
                  <span>Premium quality ingredients</span>
                </div>
                <div class="flex items-center space-x-2 text-xs text-[#2A1810] font-medium">
                  <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✔</span>
                  <span>Moist and delicious taste</span>
                </div>
                <div class="flex items-center space-x-2 text-xs text-[#2A1810] font-medium">
                  <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✔</span>
                  <span>Perfect for all occasions</span>
                </div>
              </div>
            </div>

            <!-- Tab Body: Ingredients -->
            <div v-else-if="activeTab === 'ingr'" class="pt-4 space-y-3">
              <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810]">Finest Natural Ingredients</h3>
              <p class="text-xs text-[#6E5D53] leading-relaxed">
                Dutch-processed Belgian cocoa powder, unbleached pastry flour, Madagascar Bourbon vanilla, cultured dairy butter, and Belgian chocolate ganache.
              </p>
              <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                <strong>Allergen Notice:</strong> Contains wheat and dairy. Fasting items are prepared strictly dairy/egg-free.
              </div>
            </div>

            <!-- Tab Body: Reviews List -->
            <div v-else class="pt-4 space-y-3">
              <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810]">Recent Customer Feedback</h3>
              <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1 text-xs">
                <div v-for="(rev, idx) in customerReviews" :key="idx" class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-[#2A1810]">{{ rev.name }}</span>
                    <span class="text-amber-400 text-[11px]">★★★★★</span>
                  </div>
                  <p class="text-[11px] text-[#6E5D53]">{{ rev.comment }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 2 (3-4 cols): Customer Reviews Score & Breakdown -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
          <div class="space-y-4">
            <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810]">Customer Reviews</h3>

            <!-- Big Score & Gold Stars -->
            <div>
              <div class="text-3xl sm:text-4xl font-serif font-black text-[#2A1810]">4.8</div>
              <div class="flex items-center space-x-1 text-amber-400 text-sm mt-0.5">
                <span>★★★★★</span>
              </div>
              <div class="text-[11px] text-gray-400 mt-0.5">Based on {{ reviewCount }} reviews</div>
            </div>

            <!-- 5-Star Breakdown Bars -->
            <div class="space-y-1.5 text-[11px]">
              <div
                v-for="bar in ratingBreakdown"
                :key="bar.stars"
                class="flex items-center space-x-2"
              >
                <span class="w-3 text-gray-600 font-semibold text-right">{{ bar.stars }}</span>
                <span class="text-amber-400 text-[10px]">★</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                  <div
                    class="bg-[#E5A83B] h-full rounded-full transition-all duration-500"
                    :style="{ width: bar.pct + '%' }"
                  ></div>
                </div>
                <span class="w-8 text-gray-400 text-right text-[10px]">{{ bar.pct }}%</span>
              </div>
            </div>
          </div>

          <!-- Write a Review Button: Exact Match to Mockup -->
          <div class="pt-4">
            <button
              type="button"
              @click="showReviewModal = true"
              class="w-full py-2.5 border border-[#DE1B54] text-[#DE1B54] hover:bg-rose-50 text-xs font-bold rounded-xl transition shadow-2xs flex items-center justify-center cursor-pointer active:scale-95"
            >
              Write a Review
            </button>
          </div>
        </div>

        <!-- CARD 3 (4 cols): Promo Upsell Card "Make Every Moment Sweeter! ♡" -->
        <div class="lg:col-span-4 rounded-2xl bg-gradient-to-br from-[#FFF0EC] via-[#FCECE6] to-[#FCE1D8] border border-rose-200/60 p-5 sm:p-6 shadow-xs relative overflow-hidden flex flex-col justify-between min-h-[220px]">
          <!-- Top Text with Script Font -->
          <div class="space-y-2 z-10 max-w-[210px] sm:max-w-[240px]">
            <div class="font-script text-2xl sm:text-3xl text-[#2A1810] leading-none">
              <span>Make Every Moment</span>
              <br />
              <span class="text-[#DE1B54]">Sweeter! ♡</span>
            </div>
            <p class="text-[11px] sm:text-xs text-[#6E5D53] leading-snug">
              Order your favorite cake today and spread the happiness!
            </p>
          </div>

          <!-- Bottom: Shop More Button -->
          <div class="z-10 pt-4">
            <router-link
              to="/shop"
              class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#27160E] hover:bg-[#3B2317] text-white text-xs font-bold rounded-full transition shadow-xs cursor-pointer active:scale-95"
            >
              <span>Shop More</span>
              <span>&rarr;</span>
            </router-link>
          </div>

          <!-- Cropped Floral Cake Photo Placed Naturally at Bottom Right -->
          <div class="absolute -bottom-2 -right-2 w-36 sm:w-44 h-auto pointer-events-none select-none">
            <img
              src="/mockup/detail_promo_card.jpg"
              alt="SweetBakes Celebration Cake"
              class="w-full h-auto object-contain rounded-tl-2xl"
              loading="lazy"
            />
          </div>
        </div>

      </div>

    </div>

    <!-- 4. ZOOM MODAL (LIGHTBOX) -->
    <div
      v-if="showZoom"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
      @click="showZoom = false"
    >
      <div class="relative max-w-3xl w-full max-h-[85vh] flex items-center justify-center" @click.stop>
        <img
          :src="activeGallery[activeImageIdx]"
          :alt="productName"
          class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl"
        />
        <button
          type="button"
          @click="showZoom = false"
          class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-black font-bold flex items-center justify-center shadow-lg transition cursor-pointer"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- 5. WRITE A REVIEW MODAL -->
    <div
      v-if="showReviewModal"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-center justify-center p-4"
      @click="showReviewModal = false"
    >
      <div
        class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl relative border border-gray-100"
        @click.stop
      >
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
          <h3 class="font-serif font-bold text-base text-[#2A1810]">Write a Review</h3>
          <button
            type="button"
            @click="showReviewModal = false"
            class="text-gray-400 hover:text-gray-600 font-bold text-base cursor-pointer"
          >
            ✕
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="block font-bold text-[#2A1810] mb-1">Your Rating:</label>
            <div class="flex items-center space-x-1.5 text-xl cursor-pointer">
              <span
                v-for="s in 5"
                :key="s"
                @click="newReview.rating = s"
                :class="s <= newReview.rating ? 'text-amber-400' : 'text-gray-300'"
              >
                ★
              </span>
            </div>
          </div>

          <div>
            <label class="block font-bold text-[#2A1810] mb-1">Your Name:</label>
            <input
              v-model="newReview.name"
              type="text"
              placeholder="e.g. Sara Ahmed"
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] focus:outline-none"
            />
          </div>

          <div>
            <label class="block font-bold text-[#2A1810] mb-1">Review Comments:</label>
            <textarea
              v-model="newReview.comment"
              rows="3"
              placeholder="Tell others what you loved about this cake..."
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] focus:outline-none"
            ></textarea>
          </div>
        </div>

        <div class="pt-2 flex items-center justify-end space-x-2">
          <button
            type="button"
            @click="showReviewModal = false"
            class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submitReview"
            class="px-5 py-2 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
          >
            Submit Review
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const route = useRoute();
const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const toastStore = useToastStore();

const product = ref(null);
const activeTab = ref('desc');
const activeImageIdx = ref(0);
const quantity = ref(1);
const selectedSize = ref('8 Inch');
const cakeMessage = ref('');
const showCustomMessage = ref(false);
const showZoom = ref(false);
const showReviewModal = ref(false);
const isWishlisted = ref(false);

const newReview = ref({
  rating: 5,
  name: '',
  comment: '',
});

const sizeOptions = [
  { label: '6 Inch', multiplier: 0.8 },
  { label: '8 Inch', multiplier: 1.0 },
  { label: '10 Inch', multiplier: 1.35 },
  { label: '12 Inch', multiplier: 1.7 },
];

const ratingBreakdown = [
  { stars: 5, pct: 87 },
  { stars: 4, pct: 8 },
  { stars: 3, pct: 3 },
  { stars: 2, pct: 1 },
  { stars: 1, pct: 1 },
];

const customerReviews = ref([
  { name: 'Sara Ahmed', comment: 'The chocolate cake was incredible! So moist and rich.' },
  { name: 'Daniel Kebede', comment: 'Ordered for my sister’s birthday in Bole, fresh and on time.' },
  { name: 'Mekdes Tadesse', comment: 'SweetBakes never disappoints. Best bakery in Addis Ababa!' },
]);

// Fallback high-res mockup thumbnails matching media_1793991448017_73abd7f1.jpg
const defaultCakeGallery = [
  '/mockup/detail_main_cake.jpg', // Main Chocolate cake
  '/mockup/detail_thumb_2.jpg',    // Slice view
  '/mockup/detail_thumb_3.jpg',    // Berry topping
  '/mockup/detail_thumb_4.jpg',    // Light cake variant
];

const activeGallery = ref([...defaultCakeGallery]);

const productName = computed(() => product.value?.product_name || 'Chocolate Cake');
const categoryName = computed(() => product.value?.category_name || product.value?.category?.category_name || 'Cakes');
const isCakeCategory = computed(() => categoryName.value.toLowerCase().includes('cake') || productName.value.toLowerCase().includes('cake'));

const productDescription = computed(() => {
  return product.value?.description || 'Rich chocolate cake made with premium ingredients, perfect for birthdays, celebrations and special moments.';
});

const aboutCakeText = computed(() => {
  if (productName.value.toLowerCase().includes('vanilla')) {
    return 'Our Vanilla Cake is infused with authentic Madagascar vanilla beans, whipped sweet cream and delicate sponge layers.';
  }
  if (productName.value.toLowerCase().includes('bread')) {
    return 'Our Fresh Bread is baked daily using slow-fermentation sourdough techniques for golden crust and soft interior.';
  }
  return 'Our Chocolate Cake is a perfect blend of rich cocoa, soft sponge and creamy chocolate ganache. Made with the finest ingredients, it\'s sure to make any occasion sweeter.';
});

const reviewCount = computed(() => {
  if (product.value?.product_id === 1) return 124;
  if (product.value?.product_id === 3) return 98;
  if (product.value?.product_id === 2) return 90;
  return 85;
});

const originalPrice = computed(() => {
  const base = Number(product.value?.resolved_price || product.value?.base_price || 850);
  const sizeObj = sizeOptions.find((s) => s.label === selectedSize.value);
  const mult = sizeObj?.multiplier || 1.0;
  if (product.value?.product_id === 1 || !product.value) {
    return Math.round(1000 * mult);
  }
  if (product.value?.product_id === 3) {
    return Math.round(830 * mult);
  }
  return null;
});

const discountBadge = computed(() => {
  if (product.value?.product_id === 1 || !product.value) return '15% OFF';
  if (product.value?.product_id === 3) return '10% OFF';
  return null;
});

const currentPrice = computed(() => {
  const base = Number(product.value?.resolved_price || product.value?.base_price || 850);
  const sizeObj = sizeOptions.find((s) => s.label === selectedSize.value);
  return Math.round(base * (sizeObj?.multiplier || 1.0));
});

function prevImage() {
  if (activeImageIdx.value > 0) {
    activeImageIdx.value--;
  } else {
    activeImageIdx.value = activeGallery.value.length - 1;
  }
}

function nextImage() {
  if (activeImageIdx.value < activeGallery.value.length - 1) {
    activeImageIdx.value++;
  } else {
    activeImageIdx.value = 0;
  }
}

function increaseQty() {
  const maxAvail = product.value?.available || 25;
  if (quantity.value < maxAvail) {
    quantity.value++;
  }
}

function decreaseQty() {
  if (quantity.value > 1) {
    quantity.value--;
  }
}

function toggleWishlist() {
  isWishlisted.value = !isWishlisted.value;
  if (isWishlisted.value) {
    toastStore.success(`Added ${productName.value} to your wishlist!`);
  } else {
    toastStore.info(`Removed ${productName.value} from wishlist.`);
  }
}

function submitReview() {
  if (!newReview.value.name.trim() || !newReview.value.comment.trim()) {
    toastStore.error('Please enter your name and a brief review.');
    return;
  }
  customerReviews.value.unshift({
    name: newReview.value.name.trim(),
    comment: newReview.value.comment.trim(),
  });
  toastStore.success('Thank you for your review! It has been posted.');
  newReview.value.name = '';
  newReview.value.comment = '';
  showReviewModal.value = false;
  activeTab.value = 'reviews';
}

function handleAddToCart() {
  if (!product.value && !productName.value) return;

  const avail = Number(product.value?.available ?? product.value?.available_stock ?? 30);
  if (avail <= 0) {
    toastStore.error(`${productName.value} is sold out.`);
    return;
  }

  let itemTitle = `${productName.value} (${selectedSize.value})`;
  if (cakeMessage.value.trim()) {
    itemTitle += ` — "${cakeMessage.value.trim()}"`;
  }

  const result = cartStore.addItem(
    {
      product_id: product.value?.product_id || 1,
      product_name: itemTitle,
      price: currentPrice.value,
      image_url: activeGallery.value[activeImageIdx.value] || '/mockup/detail_main_cake.jpg',
      available: avail,
      available_stock: avail,
    },
    branchStore.selectedBranchId || 2,
    quantity.value
  );

  if (result && result.success === false) {
    toastStore.error(result.message || 'Unable to add item to cart.');
  } else {
    toastStore.success(`Added ${quantity.value}x "${itemTitle}" to cart!`);
    cartStore.openDrawer();
  }
}

async function loadProduct(branchId) {
  const id = route.params.id;
  try {
    const res = await api.get(`/catalog/products/${id}?branch_id=${branchId}`);
    if (res.data) {
      product.value = res.data;
      // Setup gallery
      if (res.data.images && res.data.images.length >= 2) {
        activeGallery.value = res.data.images.map((img) => img.image_url);
      } else if (res.data.image_url) {
        activeGallery.value = [
          res.data.image_url,
          '/mockup/detail_thumb_2.jpg',
          '/mockup/detail_thumb_3.jpg',
          '/mockup/detail_thumb_4.jpg',
        ];
      } else if (Number(id) === 1) {
        activeGallery.value = [...defaultCakeGallery];
      }
    }
  } catch (err) {
    console.warn('Fallback product data loaded', err);
    product.value = {
      product_id: parseInt(id) || 1,
      product_name: 'Chocolate Cake',
      category_name: 'Cakes',
      base_price: 850,
      resolved_price: 850,
      available: 24,
      description: 'Rich chocolate cake made with premium ingredients, perfect for birthdays, celebrations and special moments.',
    };
    activeGallery.value = [...defaultCakeGallery];
  }
}

onMounted(() => {
  const branchId = branchStore.selectedBranchId || 2;
  loadProduct(branchId);
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      const branchId = branchStore.selectedBranchId || 2;
      loadProduct(branchId);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }
);

watch(
  () => branchStore.selectedBranchId,
  (newBranchId) => {
    if (newBranchId) {
      loadProduct(newBranchId);
    }
  }
);
</script>
