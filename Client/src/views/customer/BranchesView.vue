<template>
  <div class="bg-[#FAF7F5] dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12 animate-fade-in-up">
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
        <div class="relative flex-1">
          <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="localeStore.t('search_branch_placeholder')"
            class="w-full pl-10 pr-8 py-2 min-h-[40px] bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-2.5 top-2.5 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Filter Dropdowns & Active Branch Indicator -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
          <select
            v-model="cityFilter"
            class="bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-semibold focus:outline-none transition cursor-pointer min-h-[40px]"
          >
            <option value="all">{{ localeStore.t('all_subcities') }}</option>
            <option value="Bole">Bole</option>
            <option value="Kirkos">Kirkos / Mexico</option>
            <option value="Yeka">Yeka / Megenagna</option>
            <option value="Arada">Arada / Piassa</option>
          </select>

          <select
            v-model="fulfillmentFilter"
            class="bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-semibold focus:outline-none transition cursor-pointer min-h-[40px]"
          >
            <option value="all">{{ localeStore.t('all_fulfillment') }}</option>
            <option value="delivery">{{ localeStore.t('delivery_fleet_only') }}</option>
            <option value="pickup">{{ localeStore.t('walkin_counter_only') }}</option>
          </select>

          <!-- Current Active Branch Badge -->
          <div class="px-3 py-1.5 min-h-[40px] rounded-xl bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] text-xs font-bold text-[#DE1B54] flex items-center space-x-1.5">
            <span class="w-2 h-2 rounded-full bg-[#DE1B54] animate-ping"></span>
            <span>{{ localeStore.t('active_branch_tag') }} {{ localeStore.tBranch(activeBranchName) }}</span>
          </div>
        </div>

      </div>
    </div>

    <!-- 3. MAIN SECTION: Branch Cards List (7 cols) & Interactive Map (5 cols) -->
    <div class="sweetbakes-container pb-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- ================= LEFT: BRANCH LIST (7 cols) ================= -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Empty State -->
          <div v-if="filteredBranches.length === 0" class="py-12 text-center bg-gray-50 dark:bg-[#1E120B] rounded-2xl border border-gray-200 dark:border-[#382318] p-6 space-y-2">
            <div class="text-3xl">🔍</div>
            <h3 class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">No branches found matching your search</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Try clearing your search query or selecting "All Sub-Cities".</p>
            <button
              @click="searchQuery = ''; cityFilter = 'all';"
              class="px-4 py-2 min-h-[38px] bg-[#DE1B54] text-white text-xs font-bold rounded-xl hover:bg-[#C51345] transition shadow-xs cursor-pointer"
            >
              Reset Filters
            </button>
          </div>

          <!-- Branch Cards -->
          <div
            v-for="branch in filteredBranches"
            :key="branch.branch_id"
            class="bg-white dark:bg-[#1E120B] rounded-2xl border transition-all duration-300 p-5 sm:p-6 relative space-y-4 shadow-xs"
            :class="branchStore.selectedBranchId === branch.branch_id 
              ? 'border-[#DE1B54] ring-2 ring-[#DE1B54]/20 bg-rose-50/10 dark:bg-[#341822]/20' 
              : 'border-[#EFE8E1] dark:border-[#382318] hover:border-[#DE1B54]/40 hover:shadow-md'"
          >
            <!-- Top Row: Name, Badge & Status -->
            <div class="flex items-start justify-between gap-3">
              <div class="space-y-1">
                <div class="flex items-center space-x-2.5">
                  <h3 class="text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ localeStore.tBranch(branch.branch_name) }}
                  </h3>
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                    :class="branch.branch_type === 'Main' || branch.is_main 
                      ? 'bg-[#24150D] text-white' 
                      : 'bg-rose-50 dark:bg-[#341822] text-[#DE1B54] border border-rose-200 dark:border-[#58263A]'"
                  >
                    {{ branch.branch_type === 'Main' || branch.is_main ? (localeStore.isAmharic ? 'ዋና መጋገሪያ ማዕከል' : 'Central Kitchen HQ') : (localeStore.isAmharic ? `ቅርንጫፍ ${String(branch.branch_id).padStart(2, '0')}` : `Branch ${String(branch.branch_id).padStart(2, '0')}`) }}
                  </span>
                </div>

                <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] flex items-center space-x-1.5">
                  <span class="text-gray-400">📍</span>
                  <span>{{ branch.address || branch.address_line1 || branch.city }}, {{ localeStore.isAmharic ? 'አዲስ አበባ' : 'Addis Ababa' }}</span>
                </p>

                <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] flex items-center space-x-2 pt-0.5">
                  <span class="flex items-center space-x-1">
                    <span>📞</span>
                    <a :href="`tel:${branch.phone || '+251911123456'}`" class="hover:text-[#DE1B54] font-medium">{{ branch.phone || '+251 911 123 456' }}</a>
                  </span>
                  <span>&middot;</span>
                  <span class="flex items-center space-x-1">
                    <span>⏰</span>
                    <span>{{ branch.opening_hours || (localeStore.isAmharic ? 'በየቀኑ ከጠዋቱ 12:30 – ማታ 3:30' : '06:30 – 21:30 Daily') }}</span>
                  </span>
                </p>
              </div>

              <!-- Open Status Badge -->
              <span class="inline-flex items-center space-x-1.5 text-xs font-bold text-[#2E7D32] bg-[#EAF7EE] border border-[#C6EBD0] px-3 py-1 rounded-full shrink-0">
                <span class="w-2 h-2 rounded-full bg-[#2E7D32] animate-pulse"></span>
                <span>{{ localeStore.isAmharic ? 'በመጋገር ላይ' : 'Baking Now' }}</span>
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
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-[#382318]">
              <div class="flex items-center space-x-2.5">
                <!-- If currently selected -->
                <button
                  v-if="branchStore.selectedBranchId === branch.branch_id"
                  class="px-5 py-2.5 bg-[#DE1B54] text-white text-xs font-bold rounded-xl shadow-xs cursor-default flex items-center space-x-1.5"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>{{ localeStore.isAmharic ? 'ዋና ቅርንጫፍ (የተመረጠ)' : 'Active Branch (Shopping Here)' }}</span>
                </button>

                <!-- If not active: click to switch -->
                <button
                  v-else
                  @click="selectBranch(branch)"
                  class="px-5 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer active:scale-95 flex items-center space-x-1.5"
                >
                  <span>{{ localeStore.isAmharic ? 'ይህንን ቅርንጫፍ ምረጥ' : 'Select & Shop This Branch' }}</span>
                  <span>&rarr;</span>
                </button>

                <!-- Delivery Areas Button -->
                <button
                  @click="showZonesFor(branch)"
                  class="px-4 py-2.5 bg-white dark:bg-[#1E120B] border border-[#DE1B54] hover:bg-rose-50 dark:hover:bg-[#341822] text-[#DE1B54] text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer"
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

        <!-- ================= RIGHT: INTERACTIVE MAP & GUIDE (5 cols) ================= -->
        <div class="lg:col-span-5 space-y-5">
          <!-- Interactive Illustrative Map -->
          <div class="bg-white rounded-2xl border border-[#EFE8E1] p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
              <div class="flex items-center space-x-2">
                <span class="text-rose-500">📍</span>
                <h3 class="font-serif font-bold text-sm text-[#2A1810]">Addis Ababa Branch Map</h3>
              </div>
              <span class="text-[11px] text-[#DE1B54] font-bold">5 Active Locations</span>
            </div>

            <!-- Stylized Map Container -->
            <div class="relative w-full h-80 rounded-xl bg-gradient-to-br from-[#FAF7F5] via-[#FFF5F2] to-[#FAF7F5] overflow-hidden border border-gray-200">
              <!-- Subtle street grid pattern -->
              <svg class="absolute inset-0 w-full h-full opacity-30" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <pattern id="streetGrid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#DE1B54" stroke-width="0.5" />
                  </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#streetGrid)" />
              </svg>

              <!-- Illustrated Arterial Roads (Ring Road, Bole Rd, Churchill) -->
              <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <path d="M 20 240 Q 160 140 380 100" fill="none" stroke="#F0D9D1" stroke-width="7" />
                <path d="M 110 30 Q 180 160 230 270" fill="none" stroke="#F0D9D1" stroke-width="6" />
                <path d="M 250 40 Q 220 180 340 250" fill="none" stroke="#F0D9D1" stroke-width="5" />
              </svg>

              <!-- Mexico HQ Pin (ID: 1) -->
              <div
                @click="selectBranchById(1)"
                class="absolute top-[48%] left-[26%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group z-10"
                title="Mexico Central Kitchen HQ"
              >
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center shadow-md font-bold text-xs ring-4 transition group-hover:scale-110"
                  :class="branchStore.selectedBranchId === 1 ? 'bg-[#DE1B54] text-white ring-[#DE1B54]/30 scale-110' : 'bg-[#24150D] text-white ring-[#24150D]/20'"
                >
                  HQ
                </div>
                <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-[#2A1810] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow whitespace-nowrap">
                  Mexico HQ
                </span>
              </div>

              <!-- Bole Flagship Pin (ID: 2) -->
              <div
                @click="selectBranchById(2)"
                class="absolute top-[38%] left-[64%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group z-10"
                title="Bole Flagship & Espresso"
              >
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center shadow-md font-bold text-xs ring-4 transition group-hover:scale-110"
                  :class="branchStore.selectedBranchId === 2 ? 'bg-[#DE1B54] text-white ring-[#DE1B54]/30 scale-110' : 'bg-[#DE1B54] text-white ring-[#DE1B54]/20'"
                >
                  1
                </div>
                <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-[#2A1810] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow whitespace-nowrap">
                  Bole
                </span>
              </div>

              <!-- Megenagna Pin (ID: 3) -->
              <div
                @click="selectBranchById(3)"
                class="absolute top-[68%] left-[76%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group z-10"
                title="Megenagna Bakehouse"
              >
                <div
                  class="w-7 h-7 rounded-full flex items-center justify-center shadow-md font-bold text-xs ring-4 transition group-hover:scale-110"
                  :class="branchStore.selectedBranchId === 3 ? 'bg-[#DE1B54] text-white ring-[#DE1B54]/30 scale-110' : 'bg-gray-700 text-white ring-gray-700/20'"
                >
                  2
                </div>
                <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-[#2A1810] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow whitespace-nowrap">
                  Megenagna
                </span>
              </div>

              <!-- Piassa Pin (ID: 4) -->
              <div
                @click="selectBranchById(4)"
                class="absolute top-[28%] left-[40%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group z-10"
                title="Piassa Heritage Bakehouse"
              >
                <div
                  class="w-7 h-7 rounded-full flex items-center justify-center shadow-md font-bold text-xs ring-4 transition group-hover:scale-110"
                  :class="branchStore.selectedBranchId === 4 ? 'bg-[#DE1B54] text-white ring-[#DE1B54]/30 scale-110' : 'bg-gray-700 text-white ring-gray-700/20'"
                >
                  3
                </div>
                <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-[#2A1810] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow whitespace-nowrap">
                  Piassa
                </span>
              </div>

              <!-- CMC Pin (ID: 5) -->
              <div
                @click="selectBranchById(5)"
                class="absolute top-[75%] left-[45%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group z-10"
                title="CMC Royal Pastry Hub"
              >
                <div
                  class="w-7 h-7 rounded-full flex items-center justify-center shadow-md font-bold text-xs ring-4 transition group-hover:scale-110"
                  :class="branchStore.selectedBranchId === 5 ? 'bg-[#DE1B54] text-white ring-[#DE1B54]/30 scale-110' : 'bg-gray-700 text-white ring-gray-700/20'"
                >
                  4
                </div>
                <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-[#2A1810] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow whitespace-nowrap">
                  CMC Hub
                </span>
              </div>
            </div>

            <p class="text-[11px] text-[#6E5D53] leading-relaxed italic text-center pt-1">
              Click any pin on the map to switch that kitchen to your active bakery.
            </p>
          </div>

          <!-- Why Choose a Branch Callout Box -->
          <div class="bg-gradient-to-br from-[#FFF5F2] to-[#FAF7F5] rounded-2xl border border-rose-100 p-5 space-y-2 text-xs">
            <div class="font-bold text-[#2A1810] flex items-center space-x-1.5 uppercase tracking-wider text-[11px]">
              <span>💡</span>
              <span>Why choose a branch first?</span>
            </div>
            <p class="text-[#6E5D53] leading-relaxed">
              Every croissant, sourdough loaf, and chocolate cake is freshly pulled from the ovens on-site at each individual branch. Because inventory is localized, choosing the closest branch ensures the freshest bakery items and shortest delivery transit times across Addis Ababa.
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
        class="bg-white rounded-2xl border border-gray-100 max-w-md w-full p-6 space-y-5 shadow-2xl relative cursor-default"
        @click.stop
      >
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1810]">
              {{ activeZoneModalBranch.branch_name }} Delivery Zones
            </h3>
            <p class="text-xs text-[#6E5D53]">Standard transit fees &amp; coverage areas</p>
          </div>
          <button
            @click="activeZoneModalBranch = null"
            class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none cursor-pointer"
          >
            &times;
          </button>
        </div>

        <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
          <div
            v-for="zone in modalZones"
            :key="zone.zone_id"
            class="flex items-center justify-between p-3.5 rounded-xl bg-[#FAF7F5] border border-gray-100"
          >
            <div>
              <div class="text-xs font-bold text-[#2A1810]">{{ zone.zone_name }}</div>
              <div class="text-[11px] text-gray-400">Estimated delivery: 30–45 mins</div>
            </div>
            <div class="text-xs font-black text-[#DE1B54]">
              ETB {{ zone.delivery_fee }}
            </div>
          </div>

          <div v-if="modalZones.length === 0" class="text-center py-6 text-xs text-gray-500">
            No specific zones registered for this branch. Standard Addis Ababa delivery rate applies.
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <button
            @click="activeZoneModalBranch = null"
            class="px-5 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer transition active:scale-95"
          >
            Done
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
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const router = useRouter();
const branchStore = useBranchStore();
const toast = useToastStore();
const localeStore = useLocaleStore();

const searchQuery = ref('');
const cityFilter = ref('all');
const fulfillmentFilter = ref('all');

const activeZoneModalBranch = ref(null);
const modalZones = ref([]);

onMounted(async () => {
  if (branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }
});

const branchesList = computed(() => {
  return branchStore.branches || [];
});

const activeBranchName = computed(() => {
  const current = branchesList.value.find(b => b.branch_id === branchStore.selectedBranchId);
  return current?.branch_name || (branchesList.value[0]?.branch_name || 'Select Branch');
});

const filteredBranches = computed(() => {
  return branchesList.value.filter((b) => {
    const query = searchQuery.value.toLowerCase().trim();
    const matchSearch =
      !query ||
      b.branch_name.toLowerCase().includes(query) ||
      (b.city && b.city.toLowerCase().includes(query)) ||
      (b.address && b.address.toLowerCase().includes(query));

    const matchCity = cityFilter.value === 'all' || (b.city && b.city.toLowerCase().includes(cityFilter.value.toLowerCase()));
    return matchSearch && matchCity;
  });
});

function selectBranch(branch) {
  branchStore.setSelectedBranch(branch.branch_id);
  toast.success(`Active branch set to ${branch.branch_name}!`);
  router.push('/shop');
}

function selectBranchById(id) {
  const branch = branchesList.value.find((b) => b.branch_id === id);
  if (branch) {
    selectBranch(branch);
  }
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
