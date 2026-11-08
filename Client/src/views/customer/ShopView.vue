<template>
  <div class="bg-[#FAF7F5] dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12 animate-fade-in-up">
    <!-- 1. SHOP HERO BANNER -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#22130C] dark:via-[#1E110A] dark:to-[#170E0A] min-h-[170px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD] dark:border-[#382318]">
      <!-- Background Bakery Imagery on the Right with Shimmer -->
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none" :class="{ 'shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E]': !bannerLoaded }">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Delicious Products Banner"
          class="w-full h-full object-cover object-center lg:object-left transition-all duration-700"
          :class="bannerLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-98'"
          loading="eager"
          @load="bannerLoaded = true"
          @error="onBannerError"
        />
        <!-- Soft gradient mask smoothly merging into blush peach on the left -->
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block dark:from-[#170E0A] dark:via-[#170E0A]/80"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 dark:bg-[#170E0A]/85 lg:hidden"></div>
      </div>

      <!-- Banner Text on Left -->
      <div class="sweetbakes-container w-full py-6 sm:py-8 relative z-10">
        <div class="max-w-xl space-y-1.5 sm:space-y-2 text-center lg:text-left">
          <!-- Script Subtitle with Dash Flourishes -->
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
            <span>{{ localeStore.isAmharic ? 'የእኛ ሱቅ' : 'Shop Our' }}</span>
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
          </div>

          <!-- Large Bold Serif Title -->
          <h1 class="text-2xl xs:text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-tight">
            {{ activePageTitle }}
          </h1>

          <!-- Tagline with Pink Heart -->
          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>{{ localeStore.isAmharic ? 'በፍቅር እና በጥራት የተጋገረ!' : 'Freshly baked with love, just for you!' }}</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. MAIN SHOPPING AREA -->
    <div class="sweetbakes-container py-6 sm:py-10">
      <!-- Mobile Filter Trigger Bar (Sticky on mobile < 1024px) -->
      <div class="lg:hidden w-full mb-5 sticky top-16 z-30">
        <div class="bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md p-2.5 rounded-2xl border border-[#EFE8E1] dark:border-[#382318] shadow-md flex items-center justify-between gap-3">
          <button
            @click="mobileFiltersOpen = true"
            type="button"
            class="flex-1 min-h-[42px] py-2 px-4 bg-[#FAF7F5] dark:bg-[#2A1810] hover:bg-rose-50 dark:hover:bg-[#341822] rounded-xl text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center justify-between cursor-pointer transition active:scale-98 border border-gray-200 dark:border-[#382318]"
          >
            <div class="flex items-center space-x-2">
              <span class="text-base">⚙️</span>
              <span>Filter &amp; Search</span>
              <span
                v-if="activeFilterCount > 0"
                class="w-5 h-5 rounded-full bg-[#DE1B54] text-white text-[10px] flex items-center justify-center font-bold"
              >
                {{ activeFilterCount }}
              </span>
            </div>
            <span class="text-xs text-[#DE1B54] font-bold">Adjust →</span>
          </button>

          <!-- Quick Mobile Sort Selector -->
          <select
            v-model="sortBy"
            @change="onFilterChange"
            class="bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl px-3 py-2 text-xs font-semibold text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none min-h-[42px] cursor-pointer"
          >
            <option value="popular">{{ localeStore.isAmharic ? 'ተወዳጅ' : 'Popular' }}</option>
            <option value="price_low">{{ localeStore.isAmharic ? 'ዋጋ: ከዝቅተኛ' : 'Price: Low' }}</option>
            <option value="price_high">{{ localeStore.isAmharic ? 'ዋጋ: ከከፍተኛ' : 'Price: High' }}</option>
            <option value="rating">{{ localeStore.isAmharic ? 'ከፍተኛ ደረጃ' : 'Top Rated' }}</option>
            <option value="name">{{ localeStore.isAmharic ? 'ስም ሀ–ፐ' : 'Name A–Z' }}</option>
          </select>
        </div>
      </div>

      <!-- Mobile Filter Slide-over Drawer -->
      <Teleport to="body">
        <div
          v-if="mobileFiltersOpen"
          class="fixed inset-0 z-50 flex lg:hidden"
          role="dialog"
          aria-modal="true"
        >
          <!-- Backdrop -->
          <div
            @click="mobileFiltersOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity animate-fade-in"
          ></div>

          <!-- Drawer Panel -->
          <div class="relative ml-auto w-full max-w-xs sm:max-w-sm bg-white dark:bg-[#1E120B] text-[#29231F] dark:text-[#FAF3EE] h-full shadow-2xl flex flex-col z-10 animate-slide-in-right overflow-hidden">
            <!-- Header -->
            <div class="p-4 bg-[#24150D] text-white flex items-center justify-between shrink-0">
              <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider">
                <span>⚙️</span>
                <span>{{ localeStore.isAmharic ? 'ማጣሪያዎች እና ፍለጋ' : 'Filters & Refinements' }}</span>
              </div>
              <button
                type="button"
                @click="mobileFiltersOpen = false"
                class="p-1 rounded-lg text-rose-300 hover:text-white cursor-pointer"
              >
                ✕
              </button>
            </div>

            <!-- Drawer Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-5 space-y-6">
              <!-- Search inside drawer -->
              <div class="relative">
                <input
                  v-model="searchQuery"
                  @input="onSearchInput"
                  type="text"
                  :placeholder="localeStore.t('search_placeholder')"
                  class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#44281B] focus:border-[#DE1B54] rounded-xl py-2 pl-3 pr-8 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
                />
                <span v-if="searchQuery" @click="clearSearch" class="absolute right-2.5 top-2.5 text-xs text-gray-400 hover:text-gray-600 cursor-pointer">✕</span>
              </div>

              <!-- In-Stock Only Toggle -->
              <label class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] cursor-pointer">
                <div class="flex items-center space-x-2 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                  <span>📦</span>
                  <span>{{ localeStore.isAmharic ? 'በክምችት ያሉ ብቻ' : 'In-Stock Items Only' }}</span>
                </div>
                <input
                  type="checkbox"
                  v-model="inStockOnly"
                  @change="onFilterChange"
                  class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-4 h-4 cursor-pointer"
                />
              </label>

              <!-- Categories -->
              <div class="space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.t('categories_label') }}
                </div>
                <div class="space-y-2 text-xs">
                  <label class="flex items-center justify-between cursor-pointer">
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        :checked="selectedCategories.length === 0 && !fastingOnly"
                        @change="clearCategorySelection"
                        class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                      />
                      <span :class="selectedCategories.length === 0 && !fastingOnly ? 'font-bold text-[#DE1B54]' : ''">
                        {{ localeStore.t('all_categories') }}
                      </span>
                    </div>
                    <span class="text-gray-400 text-[11px]">({{ allProducts.length }})</span>
                  </label>

                  <label
                    v-for="cat in liveCategories"
                    :key="cat.category_id || cat.category_name"
                    class="flex items-center justify-between cursor-pointer"
                  >
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        :checked="isCategoryChecked(cat.category_name)"
                        @change="toggleCategoryCheckbox(cat.category_name)"
                        class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                      />
                      <span :class="isCategoryChecked(cat.category_name) ? 'font-bold text-[#DE1B54]' : ''">
                        {{ cat.category_name }}
                      </span>
                    </div>
                    <span class="text-gray-400 text-[11px]">({{ getCatProductCount(cat.category_name) }})</span>
                  </label>

                  <!-- Fasting -->
                  <label class="flex items-center justify-between cursor-pointer pt-2 border-t border-gray-100 dark:border-[#331E14]">
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        v-model="fastingOnly"
                        @change="onFilterChange"
                        class="rounded text-emerald-700 focus:ring-emerald-600 w-3.5 h-3.5"
                      />
                      <span :class="fastingOnly ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-emerald-800 dark:text-emerald-300'">
                        🌿 {{ localeStore.isAmharic ? 'የጾም' : "Ye'Tsom (ጾም)" }}
                      </span>
                    </div>
                    <span class="text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold px-1.5 py-0.2 rounded">Vegan</span>
                  </label>
                </div>
              </div>

              <!-- Price Range Slider -->
              <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-[#331E14]">
                <div class="flex justify-between items-center text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                  <span>{{ localeStore.t('price_range') }}</span>
                  <span class="text-[#DE1B54] font-black">{{ localeStore.isAmharic ? 'እስከ' : 'Up to' }} {{ localeStore.formatPrice(maxPrice) }}</span>
                </div>
                <input
                  type="range"
                  min="50"
                  max="1500"
                  step="50"
                  v-model.number="maxPrice"
                  @change="onFilterChange"
                  class="w-full accent-[#DE1B54] cursor-pointer"
                />
              </div>

              <!-- Rating Filter -->
              <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-[#331E14]">
                <div class="text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.t('rating_filter') }}
                </div>
                <div class="space-y-1.5 text-xs">
                  <label v-for="r in [5, 4, 3]" :key="r" class="flex items-center space-x-2 cursor-pointer">
                    <input
                      type="radio"
                      name="mobileRatingFilter"
                      :value="r"
                      v-model="selectedRating"
                      @change="onFilterChange"
                      class="text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                    />
                    <div class="flex items-center space-x-1 text-amber-400">
                      <span>{{ '★'.repeat(r) }}{{ '☆'.repeat(5 - r) }}</span>
                      <span class="text-gray-500 text-[11px]">{{ localeStore.isAmharic ? 'እና ከዚያ በላይ' : '& Up' }}</span>
                    </div>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer text-gray-500 text-xs">
                    <input
                      type="radio"
                      name="mobileRatingFilter"
                      :value="null"
                      v-model="selectedRating"
                      @change="onFilterChange"
                      class="text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                    />
                    <span>{{ localeStore.isAmharic ? 'ሁሉም ደረጃዎች' : 'All Ratings' }}</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Drawer Bottom Sticky Actions -->
            <div class="p-4 bg-[#FAF7F5] dark:bg-[#150A05] border-t border-gray-200 dark:border-[#382318] flex items-center gap-3 shrink-0">
              <button
                type="button"
                @click="resetFilters"
                class="px-4 py-2.5 min-h-[42px] bg-gray-200 dark:bg-[#2A1810] text-[#2A1810] dark:text-[#FAF3EE] rounded-xl text-xs font-bold transition hover:bg-gray-300 cursor-pointer"
              >
                {{ localeStore.isAmharic ? 'መልስ' : 'Reset' }}
              </button>
              <button
                type="button"
                @click="mobileFiltersOpen = false"
                class="flex-1 py-2.5 min-h-[42px] bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center space-x-1 cursor-pointer"
              >
                <span>{{ localeStore.isAmharic ? `${filteredProducts.length} ምርቶችን አሳይ` : `View ${filteredProducts.length} Products` }}</span>
              </button>
            </div>
          </div>
        </div>
      </Teleport>

      <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
        <!-- ================= LEFT SIDEBAR (Desktop) ================= -->
        <aside class="hidden lg:block w-72 xl:w-80 shrink-0 space-y-4">
          <!-- Filter Card -->
          <div class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] shadow-xs overflow-hidden">
            <!-- Dark Chocolate Header -->
            <div class="bg-[#24150D] dark:bg-[#150A05] text-white px-5 py-3.5 flex items-center justify-between">
              <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>{{ localeStore.t('filter_products') }}</span>
              </div>
              <span class="text-xs text-rose-400 font-semibold cursor-pointer hover:underline" @click="resetFilters">
                {{ localeStore.t('clear_filter') }}
              </span>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-6">
              <!-- Quick Search Input -->
              <div class="relative">
                <input
                  v-model="searchQuery"
                  @input="onSearchInput"
                  type="text"
                  :placeholder="localeStore.t('search_placeholder')"
                  class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#44281B] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl py-2 pl-3 pr-8 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
                />
                <span v-if="searchQuery" @click="clearSearch" class="absolute right-2.5 top-2.5 text-xs text-gray-400 hover:text-gray-600 cursor-pointer">✕</span>
              </div>

              <!-- In-Stock Items Only Toggle -->
              <label class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] cursor-pointer hover:border-rose-200 transition">
                <div class="flex items-center space-x-2 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                  <span>📦</span>
                  <span>{{ localeStore.isAmharic ? 'በክምችት ያሉ ብቻ' : 'In-Stock Only' }}</span>
                </div>
                <input
                  type="checkbox"
                  v-model="inStockOnly"
                  @change="onFilterChange"
                  class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-4 h-4 cursor-pointer"
                />
              </label>

              <!-- Categories Accordion -->
              <div class="space-y-3">
                <div
                  @click="categoriesOpen = !categoriesOpen"
                  class="flex items-center justify-between cursor-pointer text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] select-none"
                >
                  <span>{{ localeStore.t('categories_label') }}</span>
                  <span class="text-gray-400 transition-transform duration-200" :class="categoriesOpen ? 'rotate-180' : ''">▲</span>
                </div>

                <div v-show="categoriesOpen" class="space-y-2 text-xs pt-1">
                  <!-- All Categories -->
                  <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        :checked="selectedCategories.length === 0 && !fastingOnly"
                        @change="clearCategorySelection"
                        class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                      />
                      <span :class="selectedCategories.length === 0 && !fastingOnly ? 'font-bold text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#FAF3EE]'">
                        {{ localeStore.t('all_categories') }}
                      </span>
                    </div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">({{ allProducts.length }})</span>
                  </label>

                  <!-- Dynamic Category Options from Database -->
                  <label
                    v-for="cat in liveCategories"
                    :key="cat.category_id || cat.category_name"
                    class="flex items-center justify-between cursor-pointer group"
                  >
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        :checked="isCategoryChecked(cat.category_name)"
                        @change="toggleCategoryCheckbox(cat.category_name)"
                        class="rounded text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                      />
                      <span :class="isCategoryChecked(cat.category_name) ? 'font-bold text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#FAF3EE]'">
                        {{ cat.category_name }}
                      </span>
                    </div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">({{ getCatProductCount(cat.category_name) }})</span>
                  </label>

                  <!-- Ye'Tsom (Fasting) Option -->
                  <label class="flex items-center justify-between cursor-pointer group pt-1 border-t border-gray-100 dark:border-[#331E14]">
                    <div class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        v-model="fastingOnly"
                        @change="onFilterChange"
                        class="rounded text-emerald-700 focus:ring-emerald-600 w-3.5 h-3.5"
                      />
                      <span :class="fastingOnly ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-emerald-800 dark:text-emerald-300'">
                        🌿 {{ localeStore.isAmharic ? 'የጾም' : "Ye'Tsom (ጾም)" }}
                      </span>
                    </div>
                    <span class="text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-bold px-1.5 py-0.2 rounded">Vegan</span>
                  </label>
                </div>
              </div>

              <!-- Price Range Slider -->
              <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-[#331E14]">
                <div class="flex justify-between items-center text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                  <span>{{ localeStore.t('price_range') }}</span>
                  <span class="text-[#DE1B54] font-black">{{ localeStore.isAmharic ? 'እስከ' : 'Up to' }} {{ localeStore.formatPrice(maxPrice) }}</span>
                </div>

                <div class="space-y-2">
                  <input
                    type="range"
                    min="50"
                    max="1500"
                    step="50"
                    v-model.number="maxPrice"
                    @change="onFilterChange"
                    class="w-full accent-[#DE1B54] cursor-pointer"
                  />
                  <div class="flex justify-between text-[11px] text-gray-400 dark:text-gray-500 font-medium">
                    <span>{{ localeStore.formatPrice(50) }}</span>
                    <span>{{ localeStore.formatPrice(1500) }}</span>
                  </div>
                </div>
              </div>

              <!-- Rating Filter -->
              <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-[#331E14]">
                <div class="text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.t('rating_filter') }}
                </div>
                <div class="space-y-2 text-xs">
                  <label
                    v-for="r in [5, 4, 3]"
                    :key="r"
                    class="flex items-center space-x-2 cursor-pointer group select-none"
                  >
                    <input
                      type="radio"
                      name="ratingFilter"
                      :value="r"
                      v-model="selectedRating"
                      @change="onFilterChange"
                      class="text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                    />
                    <div class="flex items-center space-x-1 text-amber-400 text-xs">
                      <span>{{ '★'.repeat(r) }}{{ '☆'.repeat(5 - r) }}</span>
                      <span class="text-gray-600 dark:text-gray-300 text-[11px] font-medium ml-1">{{ localeStore.isAmharic ? 'እና ከዚያ በላይ' : '& Up' }}</span>
                    </div>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer group select-none text-[11px] text-gray-500 dark:text-gray-400">
                    <input
                      type="radio"
                      name="ratingFilter"
                      :value="null"
                      v-model="selectedRating"
                      @change="onFilterChange"
                      class="text-[#DE1B54] focus:ring-[#DE1B54] w-3.5 h-3.5"
                    />
                    <span>{{ localeStore.isAmharic ? 'ሁሉም ደረጃዎች' : 'All Ratings' }}</span>
                  </label>
                </div>
              </div>

              <!-- Reset Filters Button -->
              <div class="pt-2">
                <button
                  type="button"
                  @click="resetFilters"
                  class="w-full py-2.5 min-h-[42px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center space-x-1.5 cursor-pointer active:scale-95"
                >
                  <span>🔄</span>
                  <span>{{ localeStore.isAmharic ? 'ሁሉንም ማጣሪያዎች መልስ' : 'Reset All Filters' }}</span>
                </button>
              </div>
            </div>
          </div>
        </aside>

        <!-- ================= RIGHT COLUMN: PRODUCTS & TOOLBAR ================= -->
        <main class="flex-1 space-y-6 w-full">
          <!-- Top Toolbar: Product Counts, Sort Dropdown & View Mode -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-100 dark:border-[#382318]">
            <!-- Results Count -->
            <div class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF]">
              {{ localeStore.isAmharic ? 'የሚታዩት' : 'Showing' }} <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ paginatedProducts.length > 0 ? (currentPage - 1) * pageSize + 1 : 0 }}–{{ Math.min(currentPage * pageSize, filteredProducts.length) }}</strong> {{ localeStore.isAmharic ? 'ከ' : 'of' }} <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ filteredProducts.length }}</strong> {{ localeStore.isAmharic ? 'ምርቶች' : 'products' }}
            </div>

            <!-- Controls: Sort & View Toggle -->
            <div class="flex items-center space-x-3 justify-end">
              <!-- Sort Dropdown -->
              <div class="flex items-center space-x-1.5 text-xs text-[#2A1810] dark:text-[#FAF3EE]">
                <select
                  v-model="sortBy"
                  @change="onFilterChange"
                  class="bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54] shadow-2xs cursor-pointer min-h-[38px]"
                >
                  <option value="popular">{{ localeStore.t('sort_popular') }}</option>
                  <option value="price_low">{{ localeStore.t('sort_price_low') }}</option>
                  <option value="price_high">{{ localeStore.t('sort_price_high') }}</option>
                  <option value="rating">{{ localeStore.t('sort_rating') }}</option>
                  <option value="name">{{ localeStore.isAmharic ? 'የምርት ስም (ሀ-ፐ / A-Z)' : 'Product Name (A-Z)' }}</option>
                </select>
              </div>

              <!-- View Mode Toggle (Grid / List) -->
              <div class="flex items-center space-x-1 bg-gray-50 dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl p-0.5">
                <!-- Grid View Button -->
                <button
                  type="button"
                  @click="setViewMode('grid')"
                  class="p-1.5 rounded-lg text-xs transition cursor-pointer min-w-[32px] min-h-[32px] flex items-center justify-center"
                  :class="viewMode === 'grid' ? 'bg-[#DE1B54] text-white shadow-2xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                  title="Grid View"
                >
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                  </svg>
                </button>
                <!-- List View Button -->
                <button
                  type="button"
                  @click="setViewMode('list')"
                  class="p-1.5 rounded-lg text-xs transition cursor-pointer min-w-[32px] min-h-[32px] flex items-center justify-center"
                  :class="viewMode === 'list' ? 'bg-[#DE1B54] text-white shadow-2xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                  title="List View"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Active Filter Tags Row -->
          <div v-if="activeFilterCount > 0" class="flex flex-wrap items-center gap-2 pb-1">
            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ localeStore.isAmharic ? 'የተመረጡት:' : 'Active:' }}</span>

            <span
              v-for="cat in selectedCategories"
              :key="cat"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-rose-50 dark:bg-[#341822] text-[#DE1B54] border border-rose-200 dark:border-[#DE1B54]/40 rounded-full text-xs font-bold shadow-2xs"
            >
              <span>{{ cat }}</span>
              <button type="button" @click="toggleCategoryCheckbox(cat)" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <span
              v-if="fastingOnly"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50 rounded-full text-xs font-bold shadow-2xs"
            >
              <span>🌿 {{ localeStore.isAmharic ? 'የጾም' : "Ye'Tsom (ጾም)" }}</span>
              <button type="button" @click="fastingOnly = false; onFilterChange()" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <span
              v-if="inStockOnly"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-700/50 rounded-full text-xs font-bold shadow-2xs"
            >
              <span>📦 {{ localeStore.isAmharic ? 'በክምችት ያሉ ብቻ' : 'In-Stock Only' }}</span>
              <button type="button" @click="inStockOnly = false; onFilterChange()" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <span
              v-if="searchQuery"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-gray-100 dark:bg-[#2A1810] text-gray-800 dark:text-[#FAF3EE] border border-gray-200 dark:border-[#382318] rounded-full text-xs font-bold shadow-2xs"
            >
              <span>{{ localeStore.isAmharic ? 'ፍለጋ:' : 'Search:' }} "{{ searchQuery }}"</span>
              <button type="button" @click="clearSearch" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <span
              v-if="maxPrice < 1500"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-gray-100 dark:bg-[#2A1810] text-gray-800 dark:text-[#FAF3EE] border border-gray-200 dark:border-[#382318] rounded-full text-xs font-bold shadow-2xs"
            >
              <span>≤ {{ localeStore.formatPrice(maxPrice) }}</span>
              <button type="button" @click="maxPrice = 1500; onFilterChange()" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <span
              v-if="selectedRating"
              class="inline-flex items-center space-x-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50 rounded-full text-xs font-bold shadow-2xs"
            >
              <span>★ {{ selectedRating }}+ {{ localeStore.isAmharic ? 'ኮከቦች' : 'Stars' }}</span>
              <button type="button" @click="selectedRating = null; onFilterChange()" class="hover:text-black dark:hover:text-white font-black text-xs cursor-pointer ml-1">✕</button>
            </span>

            <button
              type="button"
              @click="resetFilters"
              class="text-xs text-gray-500 dark:text-gray-400 hover:text-[#DE1B54] font-semibold underline cursor-pointer ml-1"
            >
              {{ localeStore.isAmharic ? 'ሁሉንም አጽዳ' : 'Clear All' }}
            </button>
          </div>

          <!-- Error State -->
          <ErrorState
            v-if="hasError"
            :title="localeStore.isAmharic ? 'የምርት ካታሎግ መጫን አልተቻለም' : 'Could Not Load Bakery Catalog'"
            :message="localeStore.isAmharic ? 'እባክዎ እንደገና ይሞክሩ ወይም ሌላ ቅርንጫፍ ይምረጡ።' : 'Please check your connection and tap retry to load fresh items.'"
            @retry="loadProducts(selectedBranchId)"
          />

          <!-- Skeleton Loading Grid -->
          <div
            v-else-if="loading"
            class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-5"
          >
            <ProductCardSkeleton v-for="n in 8" :key="`shop-skel-${n}`" />
          </div>

          <!-- Empty State -->
          <EmptyState
            v-else-if="paginatedProducts.length === 0"
            icon="🔍"
            :title="localeStore.isAmharic ? 'ምንም አይነት ምርት አልተገኘም' : 'No bakery items match your criteria'"
            :description="localeStore.isAmharic ? 'እባክዎ የዋጋ ክልልዎን ያስተካክሉ ወይም ሌላ ቃል ይፈልጉ።' : 'Try adjusting your price range, clearing selected filters, or searching for another sweet delight.'"
            :actionLabel="localeStore.isAmharic ? 'ሁሉንም ማጣሪያዎች አጽዳ' : 'Reset All Filters'"
            @action="resetFilters"
          />

          <!-- ================= BALANCED PRODUCT GRID ================= -->
          <div
            v-else-if="viewMode === 'grid'"
            class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-5"
          >
            <div
              v-for="p in paginatedProducts"
              :key="p.product_id"
              class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] hover:border-[#DE1B54]/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group p-3 sm:p-3.5 space-y-3"
            >
              <!-- Image Container with Shimmer Wave -->
              <div
                @click="goToProduct(p.product_id)"
                class="relative aspect-4/3 rounded-xl overflow-hidden bg-[#F0E8DF] dark:bg-[#25150E] cursor-pointer"
                :class="{ 'shimmer-wave': !shopLoadedImages[p.product_id] }"
              >
                <img
                  :src="getProductImage(p)"
                  :alt="p.product_name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-all duration-500"
                  :class="shopLoadedImages[p.product_id] ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                  loading="lazy"
                  @load="shopLoadedImages[p.product_id] = true"
                  @error="shopLoadedImages[p.product_id] = true"
                />

                <!-- Discount Tag (Top Left) -->
                <span
                  v-if="getDiscountBadge(p)"
                  class="absolute top-2 left-2 bg-[#DE1B54] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-2xs pointer-events-none"
                >
                  {{ getDiscountBadge(p) }}
                </span>

                <!-- Heart Wishlist Button (Top Right) -->
                <button
                  type="button"
                  @click.stop="toggleWishlist(p)"
                  class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 dark:bg-[#2A1810]/90 backdrop-blur-xs flex items-center justify-center text-xs shadow-2xs hover:scale-115 active:scale-90 transition cursor-pointer z-10"
                  :class="wishlistStore.isWishlisted(p.product_id) ? 'text-[#DE1B54]' : 'text-gray-400 hover:text-[#DE1B54]'"
                  title="Save to favorites"
                >
                  <span class="transition-transform duration-200" :class="{ 'scale-125': wishlistStore.isWishlisted(p.product_id) }">
                    {{ wishlistStore.isWishlisted(p.product_id) ? '♥' : '♡' }}
                  </span>
                </button>

                <!-- Ethiopian Fasting (Ye'Tsom) Badge -->
                <span
                  v-if="p.is_fasting"
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
                    :to="`/product/${p.product_id}`"
                    class="font-product font-bold text-sm xs:text-base text-[#2A1810] dark:text-[#FAF3EE] group-hover:text-[#DE1B54] transition-colors block truncate"
                  >
                    {{ localeStore.tProduct(p.product_name) }}
                  </router-link>

                  <!-- Star Rating & Review Count -->
                  <div class="flex items-center space-x-1.5 text-xs sm:text-sm pt-0.5">
                    <span class="text-amber-400 tracking-tighter">★★★★★</span>
                    <span class="text-gray-400 dark:text-gray-500 text-[11px]">({{ getReviewCount(p) }} {{ localeStore.t('reviews_count') }})</span>
                  </div>
                </div>

                <!-- Price Row -->
                <div class="flex items-baseline space-x-1.5 pt-1">
                  <span class="text-sm xs:text-base font-black text-[#DE1B54]">
                    {{ localeStore.formatPrice(p.resolved_price || p.base_price) }}
                  </span>
                  <span v-if="getOriginalPrice(p)" class="text-[11px] xs:text-xs text-gray-400 dark:text-gray-500 line-through">
                    {{ localeStore.formatPrice(getOriginalPrice(p)) }}
                  </span>
                </div>

                <!-- Animated Add to Cart Button -->
                <button
                  type="button"
                  @click="handleAddToCart(p)"
                  :disabled="p.isAdding || (p.available ?? 0) <= 0"
                  class="w-full py-2 min-h-[42px] disabled:opacity-50 text-white text-xs xs:text-sm font-bold rounded-xl transition-all duration-300 shadow-xs flex items-center justify-center space-x-1.5 cursor-pointer active:scale-95"
                  :class="p.isAdding 
                    ? 'bg-[#387A54] scale-98' 
                    : ((p.available ?? 0) <= 0 ? 'bg-gray-300 dark:bg-gray-800 text-gray-500 cursor-not-allowed' : 'bg-[#DE1B54] hover:bg-[#C51345]')"
                >
                  <svg v-if="!p.isAdding" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span v-else class="text-xs">✓</span>
                  <span>{{ p.isAdding ? (localeStore.isAmharic ? 'ተጨምሯል!' : 'Added to Bag!') : ((p.available ?? 0) <= 0 ? (localeStore.isAmharic ? 'አልቋል' : 'Sold Out') : localeStore.t('add_to_cart')) }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- ================= LIST VIEW MODE ================= -->
          <div v-else class="space-y-4">
            <div
              v-for="p in paginatedProducts"
              :key="p.product_id"
              class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] hover:border-[#DE1B54]/40 hover:shadow-lg transition p-4 flex flex-col sm:flex-row items-center justify-between gap-5"
            >
              <div
                @click="goToProduct(p.product_id)"
                class="flex items-center space-x-4 w-full sm:w-auto cursor-pointer group"
              >
                <img
                  :src="getProductImage(p)"
                  :alt="p.product_name"
                  class="w-20 h-20 rounded-xl object-cover shrink-0 bg-rose-50/20 group-hover:scale-105 transition"
                />
                <div>
                  <h3 class="font-product font-bold text-base sm:text-lg text-[#2A1810] dark:text-[#FAF3EE] group-hover:text-[#DE1B54] transition block">
                    {{ localeStore.tProduct(p.product_name) }}
                  </h3>
                  <div class="flex items-center space-x-1.5 text-xs sm:text-sm text-amber-400 mt-0.5">
                    <span>★★★★★</span>
                    <span class="text-gray-400 dark:text-gray-500 text-[11px]">({{ getReviewCount(p) }} {{ localeStore.t('reviews_count') }})</span>
                  </div>
                  <p class="text-sm text-[#6E5D53] dark:text-[#C5B7AF] line-clamp-1 mt-1 max-w-md">
                    {{ p.description || (localeStore.isAmharic ? 'በጥራት እና በፍቅር በእጅ የተዘጋጀ።' : 'Deliciously handcrafted with finest ingredients.') }}
                  </p>
                </div>
              </div>

              <div class="flex items-center space-x-4 shrink-0 w-full sm:w-auto justify-between sm:justify-end">
                <div class="text-right">
                  <div class="text-base font-black text-[#DE1B54]">
                    {{ localeStore.formatPrice(p.resolved_price || p.base_price) }}
                  </div>
                  <div v-if="getOriginalPrice(p)" class="text-[10px] text-gray-400 dark:text-gray-500 line-through">
                    {{ localeStore.formatPrice(getOriginalPrice(p)) }}
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    type="button"
                    @click.stop="toggleWishlist(p)"
                    class="p-2 min-h-[40px] min-w-[40px] rounded-xl border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] flex items-center justify-center text-sm transition cursor-pointer"
                    :class="wishlistStore.isWishlisted(p.product_id) ? 'text-[#DE1B54] bg-rose-50 dark:bg-[#341822]' : 'text-gray-400 hover:text-[#DE1B54] bg-white dark:bg-[#1E120B]'"
                    title="Save to favorites"
                  >
                    <span>{{ wishlistStore.isWishlisted(p.product_id) ? '♥' : '♡' }}</span>
                  </button>
                  <button
                    type="button"
                    @click="handleAddToCart(p)"
                    :disabled="p.isAdding || (p.available ?? 0) <= 0"
                    class="min-h-[40px] px-5 py-2.5 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer active:scale-95"
                    :class="p.isAdding ? 'bg-[#387A54]' : ((p.available ?? 0) <= 0 ? 'bg-gray-300 dark:bg-gray-800 text-gray-500 cursor-not-allowed' : 'bg-[#DE1B54] hover:bg-[#C51345]')"
                  >
                    <span>{{ p.isAdding ? (localeStore.isAmharic ? 'ተጨምሯል!' : 'Added!') : ((p.available ?? 0) <= 0 ? (localeStore.isAmharic ? 'አልቋል' : 'Sold Out') : (localeStore.isAmharic ? 'ወደ ቅርጫት ጨምር' : 'Add to Bag')) }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ================= PAGINATION ================= -->
          <div v-if="totalPages > 1" class="flex items-center justify-center space-x-2 pt-6">
            <!-- Prev Button -->
            <button
              v-if="currentPage > 1"
              type="button"
              @click="setPage(currentPage - 1)"
              class="px-3 h-9 min-h-[38px] rounded-xl text-xs font-bold bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-gray-700 dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822] hover:text-[#DE1B54] transition flex items-center space-x-1 cursor-pointer"
            >
              <span>&larr;</span>
              <span>{{ localeStore.isAmharic ? 'ቀዳሚ' : 'Prev' }}</span>
            </button>

            <button
              v-for="page in totalPages"
              :key="page"
              type="button"
              @click="setPage(page)"
              class="w-9 h-9 rounded-xl text-xs font-bold transition flex items-center justify-center cursor-pointer min-h-[38px] min-w-[38px]"
              :class="currentPage === page ? 'bg-[#DE1B54] text-white shadow-xs' : 'bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-gray-700 dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822] hover:text-[#DE1B54]'"
            >
              {{ page }}
            </button>

            <!-- Next Button -->
            <button
              v-if="currentPage < totalPages"
              type="button"
              @click="setPage(currentPage + 1)"
              class="px-4 h-9 min-h-[38px] rounded-xl text-xs font-bold bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-gray-700 dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822] hover:text-[#DE1B54] transition flex items-center space-x-1 cursor-pointer"
            >
              <span>{{ localeStore.isAmharic ? 'ቀጣይ' : 'Next' }}</span>
              <span>&rarr;</span>
            </button>
          </div>
        </main>
      </div>
    </div>

    <!-- 3. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-rose-50 via-rose-100/60 to-rose-50 dark:from-[#25150E] dark:via-[#2F1A12] dark:to-[#25150E] border-t border-rose-200/60 dark:border-[#3D2619] py-4 mt-8">
      <div class="sweetbakes-container flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
        <div class="flex items-center space-x-1.5 font-script text-2xl text-[#DE1B54]">
          <span class="font-bold">SweetBakes</span>
          <span>♡</span>
        </div>

        <div class="font-script text-lg sm:text-xl text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
          <span class="italic">{{ localeStore.isAmharic ? 'ምክንያቱም ህይወት በኬክ የበለጠ ይጣፍጣል!' : 'Because life is sweeter with cake!' }}</span>
          <span class="text-[#DE1B54] not-italic">♡</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductCardSkeleton from '../../components/ProductCardSkeleton.vue';
import EmptyState from '../../components/EmptyState.vue';
import ErrorState from '../../components/ErrorState.vue';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import { useWishlistStore } from '../../stores/wishlist';
import api from '../../api';

const route = useRoute();
const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();
const localeStore = useLocaleStore();
const wishlistStore = useWishlistStore();

const products = ref([]);
const loading = ref(true);
const hasError = ref(false);
const bannerLoaded = ref(false);
const shopLoadedImages = ref({});
const mobileFiltersOpen = ref(false);

// Filter & Search states
const searchQuery = ref('');
const selectedCategories = ref([]);
const fastingOnly = ref(false);
const inStockOnly = ref(false);
const maxPrice = ref(1500);
const selectedRating = ref(null);
const categoriesOpen = ref(true);
const sortBy = ref('popular');
const viewMode = ref(localStorage.getItem('sweetbakes_shop_view_mode') || 'grid');
const currentPage = ref(1);
const pageSize = 12;

let searchDebounceTimer = null;

// Dynamic categories loaded from database
const liveCategories = ref([]);

// Active Filter Count for badges
const activeFilterCount = computed(() => {
  let count = 0;
  if (selectedCategories.value.length > 0) count += selectedCategories.value.length;
  if (fastingOnly.value) count++;
  if (inStockOnly.value) count++;
  if (searchQuery.value.trim()) count++;
  if (maxPrice.value < 1500) count++;
  if (selectedRating.value) count++;
  if (sortBy.value !== 'popular') count++;
  return count;
});

// Dynamic page title matching the selected category / fasting mode
const activePageTitle = computed(() => {
  if (fastingOnly.value) {
    return 'Ye’Tsom Fasting Delights';
  }
  if (
    selectedCategories.value.includes('Cakes') ||
    selectedCategories.value.includes('Birthday Cakes') ||
    selectedCategories.value.includes('Wedding Cakes')
  ) {
    return 'Our Delicious Cakes';
  }
  if (selectedCategories.value.includes('Bread')) {
    return 'Fresh Artisan Bread';
  }
  if (
    selectedCategories.value.includes('Pastries') ||
    selectedCategories.value.includes('Cupcakes')
  ) {
    return 'Cupcakes & Croissants';
  }
  if (selectedCategories.value.length === 1) {
    return `Our Delicious ${selectedCategories.value[0]}`;
  }
  return 'Our Delicious Products';
});

// Category helper checks
function isCategoryChecked(catName) {
  if (catName === 'Birthday Cakes' || catName === 'Wedding Cakes') {
    return selectedCategories.value.includes(catName) || selectedCategories.value.includes('Cakes');
  }
  if (catName === 'Pastries') {
    return selectedCategories.value.includes('Pastries');
  }
  if (catName === 'Cupcakes') {
    return selectedCategories.value.includes('Cupcakes') || selectedCategories.value.includes('Pastries');
  }
  return selectedCategories.value.includes(catName);
}

function toggleCategoryCheckbox(catName) {
  fastingOnly.value = false;
  if (isCategoryChecked(catName)) {
    selectedCategories.value = selectedCategories.value.filter(
      (c) =>
        c !== catName &&
        !(catName.includes('Cake') && c === 'Cakes') &&
        !(catName === 'Cupcakes' && c === 'Pastries') &&
        !(catName === 'Pastries' && c === 'Pastries')
    );
  } else {
    selectedCategories.value.push(catName);
  }
  currentPage.value = 1;
  syncStateToUrl();
}

function clearCategorySelection() {
  selectedCategories.value = [];
  fastingOnly.value = false;
  currentPage.value = 1;
  syncStateToUrl();
}

function clearSearch() {
  searchQuery.value = '';
  currentPage.value = 1;
  syncStateToUrl();
}

function onSearchInput() {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1;
    syncStateToUrl();
  }, 300);
}

function onFilterChange() {
  currentPage.value = 1;
  syncStateToUrl();
}

function setViewMode(mode) {
  viewMode.value = mode;
  localStorage.setItem('sweetbakes_shop_view_mode', mode);
  syncStateToUrl();
}

function setPage(page) {
  currentPage.value = page;
  syncStateToUrl();
  window.scrollTo({ top: 220, behavior: 'smooth' });
}

function resetFilters() {
  selectedCategories.value = [];
  fastingOnly.value = false;
  inStockOnly.value = false;
  searchQuery.value = '';
  maxPrice.value = 1500;
  selectedRating.value = null;
  sortBy.value = 'popular';
  currentPage.value = 1;
  syncStateToUrl();
  toastStore.info('All filters have been reset.');
}

// Bidirectional URL Query Synchronization (Zero State Loss on Refresh)
function syncStateToUrl() {
  const query = {};
  if (searchQuery.value.trim()) query.search = searchQuery.value.trim();
  if (selectedCategories.value.length === 1) query.category = selectedCategories.value[0];
  else if (selectedCategories.value.length > 1) query.category = selectedCategories.value;
  if (fastingOnly.value) query.yetsom = '1';
  if (inStockOnly.value) query.in_stock = '1';
  if (maxPrice.value < 1500) query.max_price = String(maxPrice.value);
  if (selectedRating.value) query.rating = String(selectedRating.value);
  if (sortBy.value !== 'popular') query.sort = sortBy.value;
  if (currentPage.value > 1) query.page = String(currentPage.value);
  if (viewMode.value !== 'grid') query.view = viewMode.value;

  router.replace({ path: '/shop', query });
}

function syncUrlToState(query) {
  if (query.category) {
    selectedCategories.value = Array.isArray(query.category) ? query.category : [query.category];
  } else {
    selectedCategories.value = [];
  }

  fastingOnly.value = query.yetsom === '1' || query.fasting === '1';
  inStockOnly.value = query.in_stock === '1';

  if (query.search !== undefined) {
    searchQuery.value = query.search || '';
  } else {
    searchQuery.value = '';
  }

  if (query.max_price) {
    maxPrice.value = Number(query.max_price) || 1500;
  } else {
    maxPrice.value = 1500;
  }

  if (query.rating) {
    selectedRating.value = Number(query.rating) || null;
  } else {
    selectedRating.value = null;
  }

  if (query.sort) {
    sortBy.value = query.sort;
  } else {
    sortBy.value = 'popular';
  }

  if (query.page) {
    currentPage.value = Math.max(1, Number(query.page) || 1);
  } else {
    currentPage.value = 1;
  }

  if (query.view) {
    viewMode.value = query.view;
  }
}

// Watch route.query so browser back/forward buttons & initial loads work seamlessly
watch(
  () => route.query,
  (newQuery) => {
    syncUrlToState(newQuery);
  },
  { deep: true, immediate: true }
);

function getCatProductCount(catName) {
  const cLower = catName.toLowerCase();
  const count = allProducts.value.filter((p) => {
    const pCat = (p.category_name || '').toLowerCase();
    const pName = (p.product_name || '').toLowerCase();
    if (cLower.includes('cake') && !cLower.includes('cupcake')) {
      if (pName.includes('cupcake')) return false;
      return pCat.includes('cake') || pName.includes('cake');
    }
    if (cLower.includes('bread')) {
      return (
        pCat.includes('bread') ||
        pCat.includes('sourdough') ||
        pName.includes('bread') ||
        pName.includes('sourdough')
      );
    }
    if (cLower.includes('cupcake')) {
      return pCat.includes('cupcake') || pName.includes('cupcake');
    }
    if (cLower.includes('pastr')) {
      return (
        pCat.includes('pastr') ||
        pCat.includes('croissant') ||
        pName.includes('croissant') ||
        pName.includes('muffin') ||
        pName.includes('pastr')
      );
    }
    if (cLower.includes('cookie')) {
      return pCat.includes('cookie') || pName.includes('cookie');
    }
    return pCat.includes(cLower) || pName.includes(cLower);
  }).length;
  
  if (count > 0) return count;
  const found = liveCategories.value.find(c => (c.category_name || '').toLowerCase() === cLower);
  return found?.products_count || 0;
}

function getProductImage(p) {
  return p.image_url || p.images?.[0]?.image_url || '/mockup/shop_chocolate_cake.jpg';
}

function getProductRating(p) {
  return Number(p.rating || 0);
}

function goToProduct(id) {
  router.push(`/product/${id}`);
}

function getDiscountBadge(p) {
  return p.discount_badge || null;
}

function getOriginalPrice(p) {
  return p.original_price || null;
}

function getReviewCount(p) {
  return p.reviews_count ?? p.reviews ?? 0;
}

async function loadCategories() {
  try {
    const res = await api.get('/categories');
    if (Array.isArray(res.data) && res.data.length > 0) {
      liveCategories.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load categories', err);
  }
}

async function loadProducts(targetBranchId) {
  loading.value = true;
  hasError.value = false;
  try {
    const res = await api.get(`/catalog/products?branch_id=${targetBranchId}`);
    if (res.data) {
      products.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load catalog products', err);
    hasError.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  const branchId = branchStore.selectedBranchId || 2;
  await Promise.all([
    loadProducts(branchId),
    loadCategories(),
    wishlistStore.init(),
  ]);
});

watch(
  () => branchStore.selectedBranchId,
  (newBranchId) => {
    if (newBranchId) {
      loadProducts(newBranchId);
    }
  }
);

const allProducts = computed(() => products.value);

const filteredProducts = computed(() => {
  return allProducts.value.filter((p) => {
    const price = Number(p.resolved_price ?? p.base_price ?? 0);
    if (price > maxPrice.value) return false;

    if (fastingOnly.value && !p.is_fasting) return false;

    if (inStockOnly.value) {
      const avail = Number(p.available ?? p.available_stock ?? 0);
      if (avail <= 0 || p.stock_status === 'Sold out' || p.isSoldOut === true) return false;
    }

    if (selectedRating.value !== null && selectedRating.value !== undefined) {
      const rating = getProductRating(p);
      if (rating < selectedRating.value) return false;
    }

    if (searchQuery.value.trim()) {
      const q = searchQuery.value.trim().toLowerCase();
      const matchName = p.product_name.toLowerCase().includes(q);
      const matchDesc = (p.description || '').toLowerCase().includes(q);
      if (!matchName && !matchDesc) return false;
    }

    if (selectedCategories.value.length > 0) {
      const pCat = (p.category_name || '').toLowerCase();
      const pName = p.product_name.toLowerCase();

      const matched = selectedCategories.value.some((c) => {
        const catLower = c.toLowerCase();

        if (catLower.includes('cake') && !catLower.includes('cupcake')) {
          if (pName.includes('cupcake')) return false;
          return pCat.includes('cake') || pName.includes('cake');
        }

        if (catLower.includes('bread')) {
          return (
            pCat.includes('bread') ||
            pCat.includes('sourdough') ||
            pName.includes('bread') ||
            pName.includes('sourdough')
          );
        }

        if (catLower.includes('pastr') || catLower.includes('cupcake')) {
          return (
            pCat.includes('pastr') ||
            pCat.includes('croissant') ||
            pCat.includes('danish') ||
            pName.includes('croissant') ||
            pName.includes('cupcake') ||
            pName.includes('muffin') ||
            pName.includes('pastr')
          );
        }

        if (catLower.includes('cookie')) {
          return pCat.includes('cookie') || pName.includes('cookie');
        }

        return pCat.includes(catLower) || pName.includes(catLower);
      });

      if (!matched) return false;
    }

    return true;
  });
});

const sortedProducts = computed(() => {
  const list = [...filteredProducts.value];
  if (sortBy.value === 'price_low') {
    return list.sort((a, b) => (a.resolved_price || a.base_price) - (b.resolved_price || b.base_price));
  }
  if (sortBy.value === 'price_high') {
    return list.sort((a, b) => (b.resolved_price || b.base_price) - (a.resolved_price || a.base_price));
  }
  if (sortBy.value === 'name') {
    return list.sort((a, b) => a.product_name.localeCompare(b.product_name));
  }
  if (sortBy.value === 'rating') {
    return list.sort((a, b) => getReviewCount(b) - getReviewCount(a));
  }
  return list;
});

const totalPages = computed(() => Math.max(1, Math.ceil(sortedProducts.value.length / pageSize)));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return sortedProducts.value.slice(start, start + pageSize);
});

async function toggleWishlist(productOrId) {
  const res = await wishlistStore.toggleWishlist(productOrId);
  const name = typeof productOrId === 'object' && productOrId !== null ? productOrId.product_name : 'Item';
  if (res.wishlisted) {
    toastStore.success(`Saved "${name}" to your favorites!`);
  } else {
    toastStore.info(`Removed "${name}" from favorites.`);
  }
}

function handleAddToCart(p) {
  const avail = Number(p.available ?? 0);
  const isSoldOut = avail <= 0 || p.stock_status === 'Sold out';
  if (isSoldOut) {
    toastStore.warning(`${p.product_name} is currently sold out at this branch.`);
    return;
  }

  if (!authStore.isAuthenticated) {
    toastStore.info(localeStore.isAmharic 
      ? 'ምርቶችን ወደ ቅርጫት ለመጨመር እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to add items to your cart.');

    const pendingItem = {
      product_id: p.product_id,
      product_name: p.product_name,
      price: Number(p.resolved_price || p.base_price),
      image_url: getProductImage(p),
      available_stock: avail,
      available: avail,
      isSoldOut: false,
      branch_id: branchStore.selectedBranchId,
      quantity: 1,
    };
    try {
      sessionStorage.setItem('pending_cart_item', JSON.stringify(pendingItem));
    } catch (e) {}

    router.push({
      path: '/login',
      query: {
        redirect: route.fullPath || '/shop',
        pending_add_id: p.product_id,
      },
    });
    return;
  }

  p.isAdding = true;

  const result = cartStore.addItem(
    {
      product_id: p.product_id,
      product_name: p.product_name,
      price: Number(p.resolved_price || p.base_price),
      image_url: getProductImage(p),
      available_stock: avail,
      available: avail,
      isSoldOut: false,
    },
    branchStore.selectedBranchId,
    1
  );

  setTimeout(() => {
    p.isAdding = false;
  }, 600);

  if (result && result.reason === 'capped') {
    toastStore.warning(result.message);
  } else if (result && result.success === false) {
    toastStore.error(result.message || 'Unable to add item.');
  } else {
    toastStore.success(`Added "${p.product_name}" to your bag.`);
    cartStore.openDrawer();
  }
}

function onBannerError(e) {
  e.target.src = '/mockup/hero_right_hires.jpg';
}
</script>
