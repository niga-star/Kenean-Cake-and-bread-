<template>
  <div class="bg-[#FAF7F5] dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO BANNER: Responsive with fluid typography and dark mode overlay -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#22130C] dark:via-[#1E110A] dark:to-[#170E0A] min-h-[170px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD] dark:border-[#382318]">
      <!-- Background Bakery Imagery on Right -->
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Branches Banner"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block dark:from-[#170E0A] dark:via-[#170E0A]/80"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 dark:bg-[#170E0A]/85 lg:hidden"></div>
      </div>

      <!-- Banner Text on Left -->
      <div class="sweetbakes-container w-full py-6 sm:py-8 relative z-10">
        <div class="max-w-xl space-y-1.5 sm:space-y-2 text-center lg:text-left">
          <!-- Script Subtitle with Dash Flourishes -->
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
            <span>{{ localeStore.t('our_bakehouses_sub') }}</span>
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
          </div>

          <!-- Large Bold Serif Title -->
          <h1 class="text-2xl xs:text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-tight">
            {{ localeStore.t('find_switch_branches') }}
          </h1>

          <!-- Tagline with Pink Heart -->
          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>{{ localeStore.t('freshly_baked_local') }}</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. SEARCH, FILTER & ACTIVE BRANCH TOOLBAR -->
    <div class="sweetbakes-container py-4 sm:py-6">
      <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4 bg-white dark:bg-[#1E120B] p-4 sm:p-5 rounded-2xl border border-[#EFE8E1] dark:border-[#382318] shadow-xs">
        
        <!-- Search Input -->
        <div class="relative flex-1 min-w-0">
          <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="localeStore.t('search_branch_placeholder')"
            class="w-full pl-10 pr-8 py-2 min-h-[42px] bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs sm:text-sm text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="absolute right-2.5 top-2.5 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-0.5"
            aria-label="Clear search"
          >
            ✕
          </button>
        </div>

        <!-- Dynamic Filter Dropdowns & Active Branch Indicator -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
          <select
            v-model="areaFilter"
            data-testid="branch-area-filter"
            class="bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-semibold focus:outline-none transition cursor-pointer min-h-[42px]"
          >
            <option value="all">{{ localeStore.isAmharic ? 'ሁሉም አካባቢዎች (ደሴ)' : 'All Areas' }}</option>
            <option v-for="area in areaFilterOptions" :key="area" :value="area">
              {{ area }}
            </option>
          </select>

          <select
            v-model="fulfillmentFilter"
            class="bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-semibold focus:outline-none transition cursor-pointer min-h-[42px]"
          >
            <option value="all">{{ localeStore.t('all_fulfillment') }}</option>
            <option value="delivery">{{ localeStore.t('delivery_fleet_only') }}</option>
            <option value="pickup">{{ localeStore.t('walkin_counter_only') }}</option>
          </select>

          <!-- Current Active Branch Badge -->
          <div class="px-3 py-1.5 min-h-[42px] rounded-xl bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] text-xs font-bold text-[#DE1B54] flex items-center space-x-1.5">
            <span class="w-2 h-2 rounded-full bg-[#DE1B54] animate-ping shrink-0"></span>
            <span class="truncate max-w-[160px]">{{ localeStore.t('active_branch_tag') }} {{ localeStore.tBranch(activeBranchName) }}</span>
          </div>
        </div>

      </div>
    </div>

    <!-- 3. MAIN SECTION: Branch Cards List (7 cols) & Real Interactive Dessie Map (5 cols) -->
    <div class="sweetbakes-container pb-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- ================= LEFT: REAL DATABASE BRANCH LIST (7 cols) ================= -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Loading State -->
          <div v-if="branchStore.loading && branchesList.length === 0" class="py-12 text-center bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-6 space-y-2">
            <div class="w-8 h-8 border-3 border-[#DE1B54] border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] font-medium">
              {{ localeStore.isAmharic ? 'የቅርንጫፎች ዝርዝር በመጫን ላይ...' : 'Loading bakery branch locations...' }}
            </p>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredBranches.length === 0" class="py-12 text-center bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-6 space-y-3">
            <div class="text-3xl">🔍</div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.isAmharic ? 'ከፍለጋዎ ጋር የሚዛመድ ቅርንጫፍ አልተገኘም' : 'No branches found matching your search' }}
            </h3>
            <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF]">
              {{ localeStore.isAmharic ? 'ፍለጋዎን ያጽዱ ወይም ሁሉንም አካባቢዎች ይምረጡ።' : 'Try clearing your search query or selecting "All Areas".' }}
            </p>
            <button
              type="button"
              @click="searchQuery = ''; areaFilter = 'all'; fulfillmentFilter = 'all';"
              class="px-5 py-2.5 min-h-[40px] bg-[#DE1B54] text-white text-xs font-bold rounded-xl hover:bg-[#C51345] transition shadow-xs cursor-pointer"
            >
              {{ localeStore.isAmharic ? 'ማጣሪያዎችን ዳግም አስጀምር' : 'Reset Filters' }}
            </button>
          </div>

          <!-- Branch Cards -->
          <div
            v-for="branch in filteredBranches"
            :key="branch.branch_id"
            class="bg-white dark:bg-[#1E120B] rounded-2xl border transition-all duration-300 p-5 sm:p-6 relative space-y-4 shadow-xs"
            :class="[
              branchStore.selectedBranchId === branch.branch_id
                ? 'border-[#DE1B54] ring-2 ring-[#DE1B54]/20 bg-rose-50/10 dark:bg-[#341822]/20'
                : focusedBranchId === branch.branch_id
                  ? 'border-[#DE1B54]/60 shadow-md'
                  : 'border-[#EFE8E1] dark:border-[#382318] hover:border-[#DE1B54]/40 hover:shadow-md'
            ]"
          >
            <!-- Top Row: Name, Badge & Live Status -->
            <div class="flex flex-wrap sm:flex-nowrap items-start justify-between gap-3">
              <div class="space-y-1.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-base sm:text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ localeStore.tBranch(branch.branch_name) }}
                  </h3>
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0"
                    :class="branch.branch_type === 'Main' || branch.is_main 
                      ? 'bg-[#24150D] text-white' 
                      : 'bg-rose-50 dark:bg-[#341822] text-[#DE1B54] border border-rose-200 dark:border-[#58263A]'"
                  >
                    {{ branch.branch_type === 'Main' || branch.is_main
                      ? (localeStore.isAmharic ? 'ዋና መጋገሪያ ማዕከል' : 'Central Kitchen HQ')
                      : (branch.branch_code || (localeStore.isAmharic ? `ቅርንጫፍ ${String(branch.branch_id).padStart(2, '0')}` : `Branch ${String(branch.branch_id).padStart(2, '0')}`)) }}
                  </span>
                </div>

                <p class="text-xs sm:text-[13px] text-[#6E5D53] dark:text-[#C5B7AF] flex items-start space-x-1.5">
                  <span class="text-[#DE1B54] shrink-0">📍</span>
                  <span>{{ formatBranchLocation(branch, localeStore.isAmharic) }}</span>
                </p>

                <div class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] flex flex-wrap items-center gap-x-2.5 gap-y-1 pt-0.5">
                  <span class="flex items-center space-x-1">
                    <span>📞</span>
                    <a :href="`tel:${branch.phone || '+251911123456'}`" class="hover:text-[#DE1B54] font-semibold">{{ branch.phone || '+251 911 123 456' }}</a>
                  </span>
                  <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                  <span class="flex items-center space-x-1">
                    <span>⏰</span>
                    <span>{{ branch.opening_hours || (localeStore.isAmharic ? 'በየቀኑ 07:00 – 21:00' : 'Mon–Sun 07:00 – 21:00') }}</span>
                  </span>
                </div>
              </div>

              <!-- Open / Paused Status Badge -->
              <span
                class="inline-flex items-center space-x-1.5 text-xs font-bold px-3 py-1 rounded-full shrink-0"
                :class="branch.is_shopping_enabled === false
                  ? 'text-amber-800 bg-amber-50 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900'
                  : 'text-[#2E7D32] bg-[#EAF7EE] border border-[#C6EBD0] dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900'"
              >
                <span
                  class="w-2 h-2 rounded-full"
                  :class="branch.is_shopping_enabled === false ? 'bg-amber-500' : 'bg-[#2E7D32] animate-pulse'"
                ></span>
                <span>{{ branch.is_shopping_enabled === false ? (localeStore.isAmharic ? 'ለጊዜው ዝግ ነው' : 'Temporarily Paused') : (localeStore.isAmharic ? 'በመጋገር ላይ' : 'Baking Now') }}</span>
              </span>
            </div>

            <!-- Capability Tags -->
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[11px] font-medium text-[#2A1810] dark:text-[#FAF3EE]">
                🛵 {{ localeStore.isAmharic ? 'ፈጣን ማድረስ' : 'Fast Delivery' }}
              </span>
              <span class="px-2.5 py-0.5 rounded-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[11px] font-medium text-[#2A1810] dark:text-[#FAF3EE]">
                🛍️ {{ localeStore.isAmharic ? 'ከቅርንጫፍ መውሰድ' : 'In-Store Pickup' }}
              </span>
              <span class="px-2.5 py-0.5 rounded-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[11px] font-medium text-[#2A1810] dark:text-[#FAF3EE]">
                🎂 {{ localeStore.isAmharic ? 'ልዩ የኬክ ስቱዲዮ' : 'Custom Cake Studio' }}
              </span>
              <span class="px-2.5 py-0.5 rounded-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[11px] font-medium text-[#2A1810] dark:text-[#FAF3EE]">
                ☕ {{ localeStore.isAmharic ? 'ካፌ' : 'Espresso Cafe' }}
              </span>
            </div>

            <!-- Bottom Action Row -->
            <div class="flex flex-wrap items-center justify-between gap-2.5 pt-3 border-t border-gray-100 dark:border-[#382318]">
              <div class="flex flex-wrap items-center gap-2">
                <!-- If currently active shopping branch -->
                <button
                  v-if="branchStore.selectedBranchId === branch.branch_id"
                  type="button"
                  class="px-4 sm:px-5 py-2.5 min-h-[40px] bg-[#DE1B54] text-white text-xs font-bold rounded-xl shadow-xs cursor-default flex items-center space-x-1.5"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>{{ localeStore.isAmharic ? 'ዋና ቅርንጫፍ (የተመረጠ)' : 'Active Branch (Shopping Here)' }}</span>
                </button>

                <!-- If not active: click to switch -->
                <button
                  v-else
                  type="button"
                  @click="selectBranch(branch)"
                  class="px-4 sm:px-5 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer active:scale-95 flex items-center space-x-1.5"
                >
                  <span>{{ localeStore.isAmharic ? 'ይህንን ቅርንጫፍ ምረጥ' : 'Select & Shop This Branch' }}</span>
                  <span>&rarr;</span>
                </button>

                <!-- Focus Pin on Dessie Map -->
                <button
                  type="button"
                  @click="focusBranchOnMap(branch, true)"
                  class="px-3.5 py-2.5 min-h-[40px] bg-[#FAF7F5] dark:bg-[#2A1810] border border-[#E5DAD1] dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1"
                >
                  <span>📍</span>
                  <span>{{ localeStore.isAmharic ? 'በካርታ ላይ እይ' : 'Show on Map' }}</span>
                </button>

                <!-- Delivery Areas Button -->
                <button
                  type="button"
                  @click="showZonesFor(branch)"
                  class="px-3.5 py-2.5 min-h-[40px] bg-white dark:bg-[#1E120B] border border-[#DE1B54]/60 hover:border-[#DE1B54] hover:bg-rose-50 dark:hover:bg-[#341822] text-[#DE1B54] text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer"
                >
                  🚚 {{ localeStore.isAmharic ? 'የማድረሻ ዞኖች' : 'Delivery Areas' }}
                </button>
              </div>

              <a
                :href="`tel:${branch.phone || '+251911123456'}`"
                class="text-xs font-bold text-[#DE1B54] hover:underline flex items-center space-x-1"
              >
                <span>{{ localeStore.isAmharic ? 'ለቅርንጫፉ ደውል' : 'Call Counter' }}</span>
                <span>&rarr;</span>
              </a>
            </div>
          </div>
        </div>

        <!-- ================= RIGHT: REAL INTERACTIVE DESSIE TOWN MAP (5 cols) ================= -->
        <div ref="mapCardSectionRef" class="lg:col-span-5 lg:sticky lg:top-24 space-y-5">
          <!-- Interactive Map Card -->
          <div class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-4 sm:p-5 shadow-xs space-y-3.5">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-[#382318] pb-3">
              <div class="flex items-center space-x-2 min-w-0">
                <span class="text-[#DE1B54] text-base shrink-0">📍</span>
                <div class="min-w-0">
                  <h2 class="font-serif font-bold text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE] truncate">
                    {{ localeStore.isAmharic ? 'የደሴ ቅርንጫፎች ካርታ' : 'Dessie Town Branch Map' }}
                  </h2>
                  <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF] truncate">
                    {{ localeStore.isAmharic ? 'ደሴ፣ አማራ ክልል፣ ኢትዮጵያ · ቀጥታ GPS' : 'Dessie, Amhara Region, Ethiopia · Live GPS' }}
                  </p>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-[#341822] border border-rose-200/70 dark:border-[#58263A] text-[11px] text-[#DE1B54] font-bold shrink-0">
                {{ mapBranches.length }} {{ localeStore.isAmharic ? 'ንቁ ቅርንጫፎች' : (mapBranches.length === 1 ? 'Active Location' : 'Active Locations') }}
              </span>
            </div>

            <!-- Interactive Map Controls Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
              <div class="inline-flex items-center p-0.5 rounded-lg bg-[#FAF7F5] dark:bg-[#2A1810] border border-[#E5DAD1] dark:border-[#382318]">
                <button
                  type="button"
                  @click="setMapLayer('street')"
                  class="px-2.5 py-1 rounded-md font-bold transition cursor-pointer"
                  :class="mapLayerMode === 'street' ? 'bg-white dark:bg-[#1E120B] text-[#DE1B54] shadow-2xs' : 'text-[#6E5D53] dark:text-[#C5B7AF]'"
                >
                  🗺️ {{ localeStore.isAmharic ? 'መንገድ' : 'Street Map' }}
                </button>
                <button
                  type="button"
                  @click="setMapLayer('satellite')"
                  class="px-2.5 py-1 rounded-md font-bold transition cursor-pointer"
                  :class="mapLayerMode === 'satellite' ? 'bg-white dark:bg-[#1E120B] text-[#DE1B54] shadow-2xs' : 'text-[#6E5D53] dark:text-[#C5B7AF]'"
                >
                  🛰️ {{ localeStore.isAmharic ? 'ሳተላይት' : 'Satellite' }}
                </button>
              </div>

              <div class="flex items-center space-x-1.5">
                <button
                  type="button"
                  @click="fitMapToAllBranches"
                  class="px-2.5 py-1 rounded-lg bg-[#FAF7F5] dark:bg-[#2A1810] border border-[#E5DAD1] dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] font-semibold transition cursor-pointer"
                  title="Zoom to fit all branches in Dessie"
                >
                  🎯 {{ localeStore.isAmharic ? 'ሁሉንም አሳይ' : 'Show All' }}
                </button>
                <button
                  type="button"
                  @click="refreshLiveBranches"
                  :disabled="branchStore.loading"
                  class="px-2.5 py-1 rounded-lg bg-[#FAF7F5] dark:bg-[#2A1810] border border-[#E5DAD1] dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] font-semibold transition cursor-pointer disabled:opacity-50"
                  title="Refresh branches from server"
                >
                  {{ branchStore.loading ? '⏳' : '↻' }}
                </button>
              </div>
            </div>

            <!-- Real Leaflet Map Canvas -->
            <div class="relative w-full h-80 sm:h-[360px] rounded-xl overflow-hidden border border-[#E5DAD1] dark:border-[#382318] bg-[#FFF5F2] dark:bg-[#25150E]">
              <div
                ref="leafletMapContainerRef"
                data-testid="dessie-leaflet-map"
                class="w-full h-full z-10"
              ></div>
            </div>

            <!-- Selected / Focused Branch Spotlight Card -->
            <div
              v-if="focusedBranch"
              data-testid="map-selected-branch-card"
              class="p-4 rounded-xl bg-gradient-to-br from-[#FFF5F8] to-[#FAF7F5] dark:from-[#2B151D] dark:to-[#22140D] border border-[#F5D2DC] dark:border-[#4A2632] space-y-2.5"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#DE1B54] shrink-0"></span>
                    <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE] truncate">
                      {{ localeStore.tBranch(focusedBranch.branch_name) }}
                    </h3>
                  </div>
                  <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] mt-0.5">
                    📍 {{ formatBranchLocation(focusedBranch, localeStore.isAmharic) }}
                  </p>
                </div>
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-white/90 dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] text-[#DE1B54] shrink-0">
                  {{ focusedBranch.lat.toFixed(4) }}°N, {{ focusedBranch.lng.toFixed(4) }}°E
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#2A1810] dark:text-[#FAF3EE]">
                <a :href="`tel:${focusedBranch.phone || '+251911123456'}`" class="font-semibold hover:text-[#DE1B54] flex items-center space-x-1">
                  <span>📞</span>
                  <span>{{ focusedBranch.phone || '+251 911 123 456' }}</span>
                </a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-[#6E5D53] dark:text-[#C5B7AF]">
                  ⏰ {{ focusedBranch.opening_hours || '07:00 – 21:00' }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-2 pt-1">
                <button
                  type="button"
                  @click="selectBranch(focusedBranch)"
                  class="flex-1 min-w-[140px] px-3.5 py-2 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-lg shadow-2xs transition cursor-pointer active:scale-98 flex items-center justify-center space-x-1.5"
                >
                  <span>{{ branchStore.selectedBranchId === focusedBranch.branch_id
                    ? (localeStore.isAmharic ? '✓ አሁን የተመረጠ · ይዘዙ →' : '✓ Active Branch · Shop →')
                    : (localeStore.isAmharic ? 'ይህን ቅርንጫፍ ምረጥ →' : 'Select & Shop Here →') }}</span>
                </button>
                <a
                  :href="`https://www.google.com/maps/search/?api=1&query=${focusedBranch.lat},${focusedBranch.lng}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-3 py-2 min-h-[38px] bg-white dark:bg-[#1E120B] border border-[#E5DAD1] dark:border-[#382318] hover:border-[#DE1B54] text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] rounded-lg transition inline-flex items-center space-x-1"
                >
                  <span>🧭</span>
                  <span>{{ localeStore.isAmharic ? 'አቅጣጫ' : 'Directions' }}</span>
                </a>
              </div>
            </div>

            <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed italic text-center">
              {{ localeStore.isAmharic ? 'ዝርዝር መረጃ ለማየት በካርታው ላይ ማንኛውንም የቅርንጫፍ ምልክት ይጫኑ።' : 'Tap or click any pin on the Dessie map to view branch contact details and shop there.' }}
            </p>
          </div>

          <!-- Why Choose a Branch Callout Box -->
          <div class="bg-gradient-to-br from-[#FFF5F2] to-[#FAF7F5] dark:from-[#24150E] dark:to-[#1C100A] rounded-2xl border border-rose-100 dark:border-[#382318] p-5 space-y-2 text-xs">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5 uppercase tracking-wider text-[11px]">
              <span>💡</span>
              <span>{{ localeStore.isAmharic ? 'ለምን ቅርንጫፍ መጀመሪያ ይምረጡ?' : 'Why choose a branch first?' }}</span>
            </div>
            <p class="text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
              {{ localeStore.isAmharic
                ? 'እያንዳንዱ ክሮሳን፣ ትኩስ ዳቦ እና የቸኮሌት ኬክ በየቅርንጫፉ ምድጃ በቀጥታ ተጋግሮ ይወጣል። በደሴ ከተማ ለእርስዎ ቅርብ የሆነውን ቅርንጫፍ መምረጥ ምርጥ ትኩስነት እና ፈጣን ማድረስ ያረጋግጥልዎታል።'
                : 'Every croissant, sourdough loaf, and chocolate cake is freshly pulled from the ovens on-site at each individual branch. Choosing your closest Dessie branch ensures the freshest bakery items and fastest delivery.' }}
            </p>
          </div>
        </div>

      </div>
    </div>

    <!-- 4. DELIVERY ZONES MODAL -->
    <div
      v-if="activeZoneModalBranch"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-center justify-center p-4 cursor-pointer"
      @click="activeZoneModalBranch = null"
    >
      <div
        class="bg-white dark:bg-[#1E120B] rounded-2xl border border-gray-100 dark:border-[#382318] max-w-md w-full p-6 space-y-5 shadow-2xl relative cursor-default"
        @click.stop
      >
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.tBranch(activeZoneModalBranch.branch_name) }} {{ localeStore.isAmharic ? 'የማድረሻ ዞኖች' : 'Delivery Zones' }}
            </h3>
            <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF]">
              {{ localeStore.isAmharic ? 'መደበኛ የማድረሻ ክፍያ እና የአገልግሎት ክልሎች' : 'Standard transit fees & coverage areas' }}
            </p>
          </div>
          <button
            type="button"
            @click="activeZoneModalBranch = null"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold leading-none cursor-pointer"
          >
            &times;
          </button>
        </div>

        <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
          <div
            v-for="zone in modalZones"
            :key="zone.zone_id"
            class="flex items-center justify-between p-3.5 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-100 dark:border-[#382318]"
          >
            <div>
              <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ zone.zone_name }}</div>
              <div class="text-[11px] text-gray-500 dark:text-gray-400">
                {{ localeStore.isAmharic ? 'ግምታዊ የመድረሻ ጊዜ፡ 30–45 ደቂቃ' : 'Estimated delivery: 30–45 mins' }}
              </div>
            </div>
            <div class="text-xs font-black text-[#DE1B54]">
              {{ localeStore.formatPrice(zone.delivery_fee) }}
            </div>
          </div>

          <div v-if="modalZones.length === 0" class="text-center py-6 text-xs text-gray-500 dark:text-gray-400">
            {{ localeStore.isAmharic
              ? 'ለዚህ ቅርንጫፍ የተለየ ዞን አልተመዘገበም። መደበኛ የደሴ የማድረሻ ክፍያ ተግባራዊ ይሆናል።'
              : 'No specific zones registered for this branch. Standard local Dessie delivery rate applies.' }}
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <button
            type="button"
            @click="activeZoneModalBranch = null"
            class="px-5 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer transition active:scale-95"
          >
            {{ localeStore.isAmharic ? 'ዝጋ' : 'Done' }}
          </button>
        </div>
      </div>
    </div>

    <!-- 5. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-rose-50 via-rose-100/60 to-rose-50 dark:from-[#25150E] dark:via-[#20110A] dark:to-[#170E0A] border-t border-rose-200/60 dark:border-[#382318] py-4 mt-8">
      <div class="sweetbakes-container flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
        <div class="flex items-center space-x-1.5 font-script text-2xl text-[#DE1B54]">
          <span class="font-bold">SweetBakes</span>
          <span>♡</span>
        </div>
        <div class="font-script text-lg sm:text-xl text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
          <span class="italic">Because life is sweeter with cake!</span>
          <span class="text-[#DE1B54] not-italic">♡</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import { DESSIE_CENTER, resolveBranchesForMap, formatBranchLocation } from '../../utils/ethiopiaGeo';
import api from '../../api';

const router = useRouter();
const branchStore = useBranchStore();
const toast = useToastStore();
const localeStore = useLocaleStore();

const searchQuery = ref('');
const areaFilter = ref('all');
const fulfillmentFilter = ref('all');
const focusedBranchId = ref(null);

const activeZoneModalBranch = ref(null);
const modalZones = ref([]);

const leafletMapContainerRef = ref(null);
const mapCardSectionRef = ref(null);
const mapLayerMode = ref('street');

let leafletMap = null;
let markerLayerGroup = null;
let streetTileLayer = null;
let satelliteTileLayer = null;
const markerLookup = new Map();

function escapeHtml(raw) {
  return String(raw || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

const branchesList = computed(() => {
  const all = branchStore.branches || [];
  const active = all.filter((b) => !b.status || b.status === 'Active');
  return active;
});

// Dynamically derive unique real location/area filter options from DB branches
const areaFilterOptions = computed(() => {
  const seen = new Map();
  branchesList.value.forEach((b) => {
    const city = (b.city || '').trim();
    if (city && !seen.has(city.toLowerCase())) {
      seen.set(city.toLowerCase(), city);
    }
    const addrFirst = (b.address || '').split(',')[0]?.trim();
    if (addrFirst && addrFirst.length <= 36 && !seen.has(addrFirst.toLowerCase())) {
      seen.set(addrFirst.toLowerCase(), addrFirst);
    }
  });
  return Array.from(seen.values());
});

const activeBranchName = computed(() => {
  const current = branchesList.value.find((b) => b.branch_id === branchStore.selectedBranchId);
  return current?.branch_name || branchesList.value[0]?.branch_name || 'Select Branch';
});

const filteredBranches = computed(() => {
  return branchesList.value.filter((b) => {
    const query = searchQuery.value.toLowerCase().trim();
    const matchSearch =
      !query ||
      (b.branch_name || '').toLowerCase().includes(query) ||
      (b.branch_code || '').toLowerCase().includes(query) ||
      (b.city || '').toLowerCase().includes(query) ||
      (b.region || '').toLowerCase().includes(query) ||
      (b.address || '').toLowerCase().includes(query) ||
      (b.phone || '').toLowerCase().includes(query);

    const areaNeedle = areaFilter.value.toLowerCase();
    const matchArea =
      areaFilter.value === 'all' ||
      (b.city || '').toLowerCase().includes(areaNeedle) ||
      (b.address || '').toLowerCase().includes(areaNeedle) ||
      (b.region || '').toLowerCase().includes(areaNeedle) ||
      (b.branch_name || '').toLowerCase().includes(areaNeedle);

    const hasDelivery = !Array.isArray(b.delivery_zones) || b.delivery_zones.length > 0;
    const isOpenForPickup = b.is_shopping_enabled !== false;
    const matchFulfillment =
      fulfillmentFilter.value === 'all' ||
      (fulfillmentFilter.value === 'delivery' && hasDelivery && isOpenForPickup) ||
      (fulfillmentFilter.value === 'pickup' && isOpenForPickup);

    return matchSearch && matchArea && matchFulfillment;
  });
});

// Branches plotted on the interactive Dessie map (follows active filters dynamically, falls back cleanly when search returns 0)
const mapBranches = computed(() => {
  const source = filteredBranches.value.length > 0 ? filteredBranches.value : branchesList.value;
  return resolveBranchesForMap(source);
});

const focusedBranch = computed(() => {
  const list = mapBranches.value;
  if (list.length === 0) return null;
  if (focusedBranchId.value) {
    const found = list.find((b) => b.branch_id === focusedBranchId.value);
    if (found) return found;
  }
  const active = list.find((b) => b.branch_id === branchStore.selectedBranchId);
  return active || list[0];
});

function buildPinIcon(branch, idx, isFocused) {
  const isMain = branch.branch_type === 'Main' || branch.is_main;
  const badge = isMain ? 'HQ' : String(idx + 1);
  const shortName = escapeHtml((branch.branch_name || 'Branch').replace(/^Main Branch\s*-\s*/i, ''));

  return L.divIcon({
    className: 'sweet-map-marker-wrapper',
    html: `
      <div class="sweet-map-pin ${isFocused ? 'sweet-map-pin--active' : ''} ${isMain ? 'sweet-map-pin--hq' : ''}">
        <span class="sweet-map-pin-halo"></span>
        <span class="sweet-map-pin-circle">${badge}</span>
        <span class="sweet-map-pin-label">${shortName}</span>
      </div>
    `,
    iconSize: [110, 56],
    iconAnchor: [55, 22],
    popupAnchor: [0, -24],
  });
}

function buildPopupContent(branch) {
  const name = escapeHtml(localeStore.tBranch(branch.branch_name));
  const loc = escapeHtml(formatBranchLocation(branch, localeStore.isAmharic));
  const phone = escapeHtml(branch.phone || '+251 911 123 456');
  const hours = escapeHtml(branch.opening_hours || '07:00 – 21:00');
  return `
    <div style="min-width:190px;font-family:Poppins,system-ui,sans-serif;line-height:1.45;">
      <div style="font-weight:800;font-size:13px;color:#2A1810;margin-bottom:3px;">${name}</div>
      <div style="font-size:11px;color:#6E5D53;margin-bottom:4px;">📍 ${loc}</div>
      <div style="font-size:11px;color:#DE1B54;font-weight:700;">📞 ${phone}</div>
      <div style="font-size:10px;color:#786B60;margin-top:2px;">⏰ ${hours}</div>
    </div>
  `;
}

function initLeafletMap() {
  if (!leafletMapContainerRef.value || leafletMap) return;

  leafletMap = L.map(leafletMapContainerRef.value, {
    center: [DESSIE_CENTER.lat, DESSIE_CENTER.lng],
    zoom: 13,
    zoomControl: true,
    scrollWheelZoom: true,
  });

  streetTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  });

  satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{x}/{y}', {
    attribution: '&copy; Esri Imagery',
    maxZoom: 18,
  });

  streetTileLayer.addTo(leafletMap);
  markerLayerGroup = L.layerGroup().addTo(leafletMap);

  syncMapMarkers(true);
}

function setMapLayer(mode) {
  mapLayerMode.value = mode;
  if (!leafletMap) return;
  if (mode === 'satellite') {
    if (streetTileLayer && leafletMap.hasLayer(streetTileLayer)) leafletMap.removeLayer(streetTileLayer);
    if (satelliteTileLayer && !leafletMap.hasLayer(satelliteTileLayer)) satelliteTileLayer.addTo(leafletMap);
  } else {
    if (satelliteTileLayer && leafletMap.hasLayer(satelliteTileLayer)) leafletMap.removeLayer(satelliteTileLayer);
    if (streetTileLayer && !leafletMap.hasLayer(streetTileLayer)) streetTileLayer.addTo(leafletMap);
  }
}

function syncMapMarkers(shouldFitBounds = false) {
  if (!leafletMap || !markerLayerGroup) return;

  markerLayerGroup.clearLayers();
  markerLookup.clear();

  const branches = mapBranches.value;
  if (branches.length === 0) {
    leafletMap.setView([DESSIE_CENTER.lat, DESSIE_CENTER.lng], 13);
    return;
  }

  const currentFocusId = focusedBranch.value?.branch_id;
  const latLngs = [];

  branches.forEach((branch, idx) => {
    const isFocused = branch.branch_id === currentFocusId;
    const marker = L.marker([branch.lat, branch.lng], {
      icon: buildPinIcon(branch, idx, isFocused),
      title: branch.branch_name,
      zIndexOffset: isFocused ? 500 : 100 - idx,
    });

    marker.bindPopup(buildPopupContent(branch));
    marker.on('click', () => {
      focusedBranchId.value = branch.branch_id;
    });

    marker.addTo(markerLayerGroup);
    markerLookup.set(branch.branch_id, { marker, branch });
    latLngs.push([branch.lat, branch.lng]);
  });

  if (shouldFitBounds && latLngs.length > 0) {
    fitBoundsFromCoords(latLngs);
  }
}

function fitBoundsFromCoords(latLngs) {
  if (!leafletMap || latLngs.length === 0) return;
  if (latLngs.length === 1) {
    leafletMap.setView(latLngs[0], 14, { animate: true });
    return;
  }
  const bounds = L.latLngBounds(latLngs);
  leafletMap.fitBounds(bounds, { padding: [44, 44], maxZoom: 15, animate: true });
}

function fitMapToAllBranches() {
  const latLngs = mapBranches.value.map((b) => [b.lat, b.lng]);
  fitBoundsFromCoords(latLngs);
}

function focusBranchOnMap(branch, scrollIntoViewOnMobile = false) {
  if (!branch) return;
  focusedBranchId.value = branch.branch_id;

  const entry = markerLookup.get(branch.branch_id);
  if (leafletMap && entry) {
    const targetZoom = Math.max(leafletMap.getZoom(), 14);
    leafletMap.flyTo([entry.branch.lat, entry.branch.lng], targetZoom, { duration: 0.55 });
    nextTick(() => {
      entry.marker.openPopup();
    });
  }

  if (scrollIntoViewOnMobile && window.innerWidth < 1024 && mapCardSectionRef.value) {
    mapCardSectionRef.value.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

async function refreshLiveBranches() {
  await branchStore.fetchBranches();
}

function handleWindowFocusRefresh() {
  if (document.visibilityState === 'visible') {
    branchStore.fetchBranches();
  }
}

watch(
  mapBranches,
  (nextList, prevList) => {
    const listChangedSizeOrIds =
      !prevList ||
      nextList.length !== prevList.length ||
      nextList.some((b, i) => b.branch_id !== prevList[i]?.branch_id || b.lat !== prevList[i]?.lat || b.lng !== prevList[i]?.lng);
    syncMapMarkers(listChangedSizeOrIds);
  },
  { deep: true }
);

watch(focusedBranchId, () => {
  syncMapMarkers(false);
  const entry = focusedBranchId.value ? markerLookup.get(focusedBranchId.value) : null;
  if (entry) {
    entry.marker.openPopup();
  }
});

watch(() => localeStore.currentLocale, () => {
  syncMapMarkers(false);
});

onMounted(async () => {
  await branchStore.fetchBranches();
  await nextTick();
  initLeafletMap();

  if (typeof window !== 'undefined') {
    window.addEventListener('focus', handleWindowFocusRefresh);
    document.addEventListener('visibilitychange', handleWindowFocusRefresh);
  }
});

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('focus', handleWindowFocusRefresh);
    document.removeEventListener('visibilitychange', handleWindowFocusRefresh);
  }
  if (leafletMap) {
    leafletMap.remove();
    leafletMap = null;
  }
  markerLayerGroup = null;
  markerLookup.clear();
});

function selectBranch(branch) {
  branchStore.setSelectedBranch(branch.branch_id);
  toast.success(`Active branch set to ${branch.branch_name}!`);
  router.push('/shop');
}

async function showZonesFor(branch) {
  activeZoneModalBranch.value = branch;
  try {
    const res = await api.get(`/branches/${branch.branch_id}/delivery-zones`);
    modalZones.value = Array.isArray(res.data) ? res.data : [];
  } catch {
    modalZones.value = [];
  }
}
</script>

<style>
.leaflet-container img,
.leaflet-container .leaflet-tile {
  max-width: none !important;
  max-height: none !important;
}

.sweet-map-marker-wrapper {
  background: transparent;
  border: none;
}

.sweet-map-pin {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}

.sweet-map-pin-halo {
  position: absolute;
  top: -2px;
  width: 36px;
  height: 36px;
  border-radius: 9999px;
  background-color: rgba(36, 21, 13, 0.16);
  transition: transform 0.25s ease, background-color 0.25s ease;
}

.sweet-map-pin--active .sweet-map-pin-halo {
  background-color: rgba(222, 27, 84, 0.28);
  transform: scale(1.22);
}

.sweet-map-pin-circle {
  position: relative;
  z-index: 2;
  width: 32px;
  height: 32px;
  border-radius: 9999px;
  background-color: #24150D;
  color: #FFFFFF;
  font-family: Poppins, system-ui, sans-serif;
  font-size: 11px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
  border: 2px solid #FFFFFF;
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.sweet-map-pin--active .sweet-map-pin-circle {
  background-color: #DE1B54;
  transform: scale(1.12);
}

.sweet-map-pin:hover .sweet-map-pin-circle {
  transform: scale(1.14);
}

.sweet-map-pin-label {
  position: relative;
  z-index: 2;
  margin-top: 3px;
  padding: 2px 7px;
  border-radius: 6px;
  background-color: #2A1810;
  color: #FFFFFF;
  font-family: Poppins, system-ui, sans-serif;
  font-size: 10px;
  font-weight: 700;
  white-space: nowrap;
  max-width: 115px;
  overflow: hidden;
  text-overflow: ellipsis;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.22);
}

.sweet-map-pin--active .sweet-map-pin-label {
  background-color: #DE1B54;
}
</style>
