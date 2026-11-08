<template>
  <div class="bg-white dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12 animate-fade-in-up">
    <!-- 1. HERO BANNER -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#22130C] dark:via-[#1E110A] dark:to-[#170E0A] min-h-[180px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD] dark:border-[#382318]">
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Custom Cake Studio"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block dark:from-[#170E0A] dark:via-[#170E0A]/80"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 dark:bg-[#170E0A]/85 lg:hidden"></div>
      </div>

      <div class="sweetbakes-container w-full py-8 relative z-10">
        <div class="max-w-xl space-y-2 text-center lg:text-left">
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-2xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
            <span>{{ localeStore.t('custom_cake') }}</span>
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-tight">
            {{ localeStore.isAmharic ? 'የህልምዎን ኬክ ይንደፉ' : 'Design Your Dream Cake' }}
          </h1>

          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>{{ localeStore.isAmharic ? 'ለየት ያሉ የክብረ በዓል ኬኮች በፍቅር በእጅ የተዘጋጁ!' : 'Bespoke celebration centerpieces handcrafted with love!' }}</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- Restored Draft Banner -->
    <div v-if="hasRestoredDraft" class="sweetbakes-container pt-6">
      <div class="bg-rose-50/70 dark:bg-[#2A1810] border border-rose-200 dark:border-[#4A2518] px-4 py-2.5 rounded-2xl flex items-center justify-between text-xs text-[#2A1810] dark:text-[#FAF3EE] shadow-2xs">
        <div class="flex items-center space-x-2">
          <span>💾</span>
          <span>{{ localeStore.isAmharic ? 'የቀደመውን የኬክ ንድፍዎን ረቂቅ መልሰነዋል::' : 'We restored your cake design draft from your last session.' }}</span>
        </div>
        <button
          type="button"
          @click="clearSavedDraft"
          class="text-xs font-bold text-[#DE1B54] hover:underline cursor-pointer"
        >
          {{ localeStore.isAmharic ? 'ቅጹን አጽዳ' : 'Reset form' }}
        </button>
      </div>
    </div>

    <!-- 2. QUICK INSPIRATION PRESET CHIPS -->
    <div class="sweetbakes-container pt-6 sm:pt-8">
      <div class="bg-gradient-to-r from-rose-50/50 via-white to-rose-50/50 dark:from-[#25150E] dark:via-[#1E120B] dark:to-[#25150E] border border-rose-100 dark:border-[#382318] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center space-x-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-bold">
          <span class="text-rose-500 text-base">✨</span>
          <span>{{ localeStore.isAmharic ? 'ፈጣን የሃሳብ መነሻዎች:' : 'Quick Inspiration Presets:' }}</span>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-2">
          <button
            v-for="preset in inspirationPresets"
            :key="preset.name"
            type="button"
            @click="applyPreset(preset)"
            class="px-3.5 py-1.5 bg-white dark:bg-[#1E120B] hover:bg-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] hover:text-white border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] rounded-full text-xs font-semibold transition-all shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer flex items-center space-x-1.5"
          >
            <span>{{ preset.icon }}</span>
            <span>{{ localeStore.isAmharic ? (preset.nameAm || preset.name) : preset.name }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 3. INTERACTIVE 3D/LAYERED CAKE CANVAS & TWO-COLUMN BUILDER -->
    <div class="sweetbakes-container py-8 space-y-8">
      <!-- Interactive Cake Visualizer Canvas -->
      <div class="bg-gradient-to-b from-[#FAF7F5] to-white dark:from-[#24150E] dark:to-[#170E0A] rounded-3xl border border-[#EFE8E1] dark:border-[#382318] p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <!-- Left: Cake Specs & Palette Summary -->
          <div class="space-y-3 max-w-sm text-center md:text-left">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-rose-50 dark:bg-[#341822] text-[#DE1B54] rounded-full text-xs font-bold border border-rose-200 dark:border-rose-900/50">
              <span>🎂</span>
              <span>{{ localeStore.isAmharic ? 'የቀጥታ ንድፍ ስቱዲዮ' : 'Live Visualizer Studio' }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
              {{ cakeSize }}
            </h2>
            <div class="space-y-1 text-xs text-[#6E5D53] dark:text-[#C5B7AF]">
              <div><strong>{{ localeStore.isAmharic ? 'ጣዕም:' : 'Flavor:' }}</strong> {{ flavor }}</div>
              <div><strong>{{ localeStore.isAmharic ? 'ክሬም:' : 'Frosting:' }}</strong> {{ filling }}</div>
              <div><strong>{{ localeStore.isAmharic ? 'ዝግጅት:' : 'Occasion:' }}</strong> {{ occasion }}</div>
              <div v-if="isFasting" class="text-emerald-700 dark:text-emerald-400 font-bold flex items-center justify-center md:justify-start space-x-1">
                <span>🌿</span>
                <span>{{ localeStore.isAmharic ? '100% የጾም የተረጋገጠ (የጾም)' : "100% Ye'Tsom Certified (የጾም)" }}</span>
              </div>
            </div>
          </div>

          <!-- Center: Dynamic Interactive Layered Cake Preview -->
          <div class="flex-1 flex flex-col items-center justify-end min-h-[220px] sm:min-h-[250px] relative">
            <!-- Occasion Topper -->
            <div class="text-2xl sm:text-3xl animate-bounce mb-1">
              {{ occasionTopper }}
            </div>

            <!-- Top Tier (T3) - Only for 3-Tier -->
            <div
              v-if="tierCount >= 3"
              class="w-24 sm:w-28 h-12 rounded-t-xl border-2 border-white/60 dark:border-black/30 shadow-md relative transition-all duration-500 flex items-center justify-center"
              :style="{ backgroundColor: cakeColorStyle.primary }"
            >
              <div class="absolute inset-x-0 bottom-0 h-2 bg-black/10"></div>
              <!-- Frosting pearls -->
              <div class="absolute -bottom-1 inset-x-1 flex justify-around">
                <span v-for="p in 5" :key="p" class="w-1.5 h-1.5 rounded-full bg-amber-100 shadow-2xs"></span>
              </div>
            </div>

            <!-- Middle Tier (T2) - For 2-Tier & 3-Tier -->
            <div
              v-if="tierCount >= 2"
              class="h-14 sm:h-16 rounded-t-xl border-2 border-white/60 dark:border-black/30 shadow-md relative transition-all duration-500 flex items-center justify-center"
              :class="tierCount === 2 ? 'w-36 sm:w-44' : 'w-36 sm:w-44'"
              :style="{ backgroundColor: cakeColorStyle.primary }"
            >
              <div class="absolute inset-x-0 bottom-0 h-2 bg-black/10"></div>
              <!-- Frosting pearls -->
              <div class="absolute -bottom-1 inset-x-1 flex justify-around">
                <span v-for="p in 7" :key="p" class="w-1.5 h-1.5 rounded-full bg-amber-100 shadow-2xs"></span>
              </div>
            </div>

            <!-- Base Tier (T1) - Always visible -->
            <div
              class="h-16 sm:h-20 rounded-t-2xl border-2 border-white/60 dark:border-black/30 shadow-lg relative transition-all duration-500 flex items-center justify-center"
              :class="tierCount === 1 ? (cakeSize.includes('6') ? 'w-36 sm:w-44' : 'w-48 sm:w-56') : (tierCount === 2 ? 'w-48 sm:w-56' : 'w-52 sm:w-64')"
              :style="{ backgroundColor: cakeColorStyle.primary }"
            >
              <!-- Drip Overlay -->
              <div class="absolute inset-x-0 top-0 h-3 bg-black/15 rounded-t-2xl"></div>

              <!-- Live Piped Inscription Ribbon on Base Tier -->
              <div
                v-if="message.trim()"
                class="px-3 py-1 bg-white/95 dark:bg-[#1E120B]/95 text-[#DE1B54] font-script text-xs sm:text-sm rounded-full shadow-md border border-[#DE1B54]/30 max-w-[85%] truncate transition-all animate-fade-in z-10"
              >
                &ldquo;{{ message }}&rdquo;
              </div>

              <!-- Frosting border pearls -->
              <div class="absolute -bottom-1.5 inset-x-2 flex justify-around">
                <span v-for="p in 9" :key="p" class="w-2 h-2 rounded-full bg-amber-100 shadow-xs"></span>
              </div>
            </div>

            <!-- Silver Pedestal / Cake Board -->
            <div class="w-64 sm:w-80 h-3.5 bg-gradient-to-r from-gray-300 via-gray-100 to-gray-400 dark:from-gray-700 dark:via-gray-500 dark:to-gray-800 rounded-full shadow-lg mt-0.5 border border-gray-400/30"></div>
          </div>

          <!-- Right: Quick Palette Swatches -->
          <div class="bg-white/80 dark:bg-[#1E120B]/80 backdrop-blur-xs p-4 rounded-2xl border border-gray-200 dark:border-[#382318] text-xs space-y-2 text-center md:text-right shrink-0">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">Palette Preview:</div>
            <div class="flex items-center justify-center md:justify-end space-x-2">
              <span
                class="w-6 h-6 rounded-full border-2 border-white shadow-xs inline-block"
                :style="{ backgroundColor: cakeColorStyle.primary }"
                :title="flavor"
              ></span>
              <span
                class="w-6 h-6 rounded-full border-2 border-white shadow-xs inline-block"
                :style="{ backgroundColor: cakeColorStyle.fillingColor }"
                :title="filling"
              ></span>
              <span
                class="w-6 h-6 rounded-full border-2 border-white shadow-xs inline-block bg-[#DE1B54]"
                title="Signature SweetBakes Ribbon"
              ></span>
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400">
              {{ cakeColorStyle.name }}
            </div>
          </div>
        </div>
      </div>

      <!-- Builder Form Grid -->
      <form @submit.prevent="handleSubmitCustomCake" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- ================= LEFT COLUMN: CAKE ARCHITECTURE & FLAVORS (7 cols) ================= -->
        <div class="lg:col-span-7 bg-white dark:bg-[#1E120B] rounded-3xl border border-[#EFE8E1] dark:border-[#382318] p-6 sm:p-8 space-y-6 shadow-2xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <div class="flex items-center space-x-2">
              <span class="w-6 h-6 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-xs font-bold">1</span>
              <h2 class="text-base sm:text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'የኬክ መዋቅር እና ጣዕም' : 'Cake Architecture & Flavors' }}
              </h2>
            </div>
            <span class="text-xs text-[#DE1B54] font-bold">{{ localeStore.isAmharic ? 'ደረጃ 1 ከ 2' : 'Step 1 of 2' }}</span>
          </div>

          <!-- Preparing Branch Selector -->
          <div class="space-y-1.5 pb-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.isAmharic ? 'አዘጋጅ ቅርንጫፍ (የአዲስ አበባ ማዕድ ቤት)' : 'Preparing Branch (Addis Ababa Kitchen)' }}
            </label>
            <select
              v-model="preparingBranchId"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition font-semibold cursor-pointer min-h-[42px]"
            >
              <option
                v-for="b in branchStore.branches"
                :key="b.branch_id"
                :value="b.branch_id"
                :disabled="b.is_shopping_enabled === false || b.status !== 'Active'"
              >
                {{ localeStore.tBranch(b.branch_name) }} — {{ b.address || b.city }} {{ (b.is_shopping_enabled === false || b.status !== 'Active') ? (localeStore.isAmharic ? '(አገልግሎት አይሰጥም)' : '(Unavailable for Shopping)') : '' }}
              </option>
            </select>
            <div
              v-if="!isPreparingBranchAvailable"
              class="p-3 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 rounded-xl text-xs text-[#DE1B54] flex items-center space-x-2 mt-2"
            >
              <span class="font-bold">⚠️</span>
              <span class="font-medium">{{ localeStore.isAmharic ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለትዕዛዝ ዝግ ነው።' : 'Sorry, this branch is currently unavailable for shopping.' }}</span>
            </div>
            <p v-else class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF]">
              {{ localeStore.isAmharic ? 'በዚህ ቅርንጫፍ የሚገኘው ዋና ኬክ ዲኮሬተር ኬክዎን በእጅ ያዘጋጃል እና ያስጌጣል።' : 'The master pastry decorator at this branch will personally bake and decorate your centerpiece.' }}
            </p>
          </div>

          <!-- Size & Tier Selection Cards -->
          <div class="space-y-2.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.isAmharic ? 'የመጠን እና የፎቅ አቀማመጥ' : 'Size & Tier Configuration' }}
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="s in sizeOptions"
                :key="s.name"
                @click="cakeSize = s.name"
                class="p-4 rounded-2xl border transition-all duration-200 cursor-pointer text-left flex items-start space-x-3 shadow-2xs hover:shadow-xs group"
                :class="cakeSize === s.name 
                  ? 'border-2 border-[#DE1B54] bg-rose-50/40 dark:bg-[#341822]/40 ring-1 ring-[#DE1B54]' 
                  : 'border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] hover:border-[#DE1B54]/40'"
              >
                <span class="text-2xl shrink-0 mt-0.5 group-hover:scale-110 transition-transform">{{ s.icon }}</span>
                <div>
                  <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] group-hover:text-[#DE1B54] transition-colors">
                    {{ localeStore.isAmharic ? (s.nameAm || s.name) : s.name }}
                  </div>
                  <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ localeStore.isAmharic ? (s.servesAm || s.serves) : s.serves }}</div>
                  <div class="text-[11px] text-[#DE1B54] font-black mt-0.5">{{ localeStore.isAmharic ? 'ግምት:' : 'Est:' }} {{ localeStore.isAmharic ? (s.baseEstimateAm || s.baseEstimate) : s.baseEstimate }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ye'Tsom (Ethiopian Fasting) Toggle -->
          <div
            class="p-3.5 rounded-2xl border flex items-center justify-between cursor-pointer transition"
            :class="isFasting ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-950/40' : 'border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810]'"
            @click="isFasting = !isFasting"
          >
            <div class="flex items-center space-x-2.5">
              <input
                type="checkbox"
                v-model="isFasting"
                class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer"
              />
              <div>
                <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                  <span>🌿 100% Ye'Tsom Certified (የጾም)</span>
                  <span class="text-[9px] bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 font-semibold px-2 py-0.2 rounded-full">Vegan &amp; Dairy-Free</span>
                </div>
                <div class="text-[10px] text-emerald-700 dark:text-emerald-300">Strictly baked without dairy, butter, honey, or eggs.</div>
              </div>
            </div>
            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">{{ isFasting ? 'Active ✓' : 'Add' }}</span>
          </div>

          <!-- Sponge Flavor & Filling Selection -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'የስፖንጅ ጣዕም' : 'Sponge Flavor' }}
              </label>
              <select
                v-model="flavor"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition cursor-pointer min-h-[42px]"
              >
                <option value="Belgian Dark Chocolate">{{ localeStore.isAmharic ? 'የቤልጂየም ዳርክ ቸኮሌት 🍫' : 'Belgian Dark Chocolate 🍫' }}</option>
                <option value="Madagascar Bourbon Vanilla">{{ localeStore.isAmharic ? 'የማዳጋስካር ቦርቦን ቫኒላ 🍦' : 'Madagascar Bourbon Vanilla 🍦' }}</option>
                <option value="Classic Red Velvet">{{ localeStore.isAmharic ? 'ክላሲክ ሬድ ቬልቬት 🍰' : 'Classic Red Velvet 🍰' }}</option>
                <option value="Ethiopian Mocha Espresso">{{ localeStore.isAmharic ? 'የኢትዮጵያ ሞካ ኤስፕሬሶ ☕' : 'Ethiopian Mocha Espresso ☕' }}</option>
                <option value="Fresh Strawberry Chiffon">{{ localeStore.isAmharic ? 'ትኩስ ስትሮውቤሪ ሺፎን 🍓' : 'Fresh Strawberry Chiffon 🍓' }}</option>
                <option value="Spiced Carrot & Walnut">{{ localeStore.isAmharic ? 'ካሮት እና ዋልነት ኬክ 🥕' : 'Spiced Carrot & Walnut 🥕' }}</option>
                <option v-if="isFasting" value="Ye'Tsom Vegan Chocolate">{{ localeStore.isAmharic ? 'የጾም ቪጋን ቸኮሌት 🌿' : 'Ye\'Tsom Vegan Chocolate 🌿' }}</option>
                <option v-if="isFasting" value="Ye'Tsom Vegan Vanilla Citrus">{{ localeStore.isAmharic ? 'የጾም ቫኒላ ሲትረስ 🌿' : 'Ye\'Tsom Vegan Vanilla Citrus 🌿' }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'ክሬም እና መሙያ' : 'Filling & Frosting' }}
              </label>
              <select
                v-model="filling"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition cursor-pointer min-h-[42px]"
              >
                <option value="Silky Chocolate Ganache">{{ localeStore.isAmharic ? 'የቸኮሌት ጋናሽ' : 'Silky Chocolate Ganache' }}</option>
                <option value="Whipped Vanilla Buttercream">{{ localeStore.isAmharic ? 'የቫኒላ ቅቤ ክሬም' : 'Whipped Vanilla Buttercream' }}</option>
                <option value="Wild Berry Compote & Mascarpone">{{ localeStore.isAmharic ? 'የዱር ቤሪ እና ማስካርፖን' : 'Wild Berry Compote & Mascarpone' }}</option>
                <option value="Salted Caramel Praline">{{ localeStore.isAmharic ? 'ሶልትድ ካራሜል ፕራሊን' : 'Salted Caramel Praline' }}</option>
                <option value="Cream Cheese Frosting">{{ localeStore.isAmharic ? 'ክሬም ቺዝ ፍሮስቲንግ' : 'Cream Cheese Frosting' }}</option>
                <option v-if="isFasting" value="Vegan Dark Choco Whip">{{ localeStore.isAmharic ? 'የጾም ዳርክ ቸኮሌት ክሬም 🌿' : 'Vegan Dark Choco Whip 🌿' }}</option>
                <option v-if="isFasting" value="Whipped Coconut Cream Frosting">{{ localeStore.isAmharic ? 'የኮኮናት ክሬም ፍሮስቲንግ 🌿' : 'Whipped Coconut Cream Frosting 🌿' }}</option>
              </select>
            </div>
          </div>

          <!-- Occasion & Date Needed -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'የበዓሉ አይነት' : 'Celebration Occasion' }}
              </label>
              <select
                v-model="occasion"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition cursor-pointer min-h-[42px]"
              >
                <option value="Birthday">{{ localeStore.isAmharic ? 'የልደት በዓል 🎂' : 'Birthday Celebration 🎂' }}</option>
                <option value="Wedding">{{ localeStore.isAmharic ? 'የሰርግ ኬክ 💍' : 'Wedding Centerpiece 💍' }}</option>
                <option value="Anniversary">{{ localeStore.isAmharic ? 'የጋብቻ/የፍቅር ዓመት 🥂' : 'Anniversary Celebration 🥂' }}</option>
                <option value="Corporate Event">{{ localeStore.isAmharic ? 'የድርጅት ዝግጅት 🏢' : 'Corporate / Office Gala 🏢' }}</option>
                <option value="Graduation">{{ localeStore.isAmharic ? 'የምረቃ በዓል 🎓' : 'Graduation Ceremony 🎓' }}</option>
                <option value="Baby Shower">{{ localeStore.isAmharic ? 'የህፃን መወለድ (ቤቢ ሻወር) 🍼' : 'Baby Shower / Gender Reveal 🍼' }}</option>
                <option value="Other">{{ localeStore.isAmharic ? 'ልዩ ክብረ በዓል 🎉' : 'Special Celebration 🎉' }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'የሚያስፈልግበት ቀን' : 'Event Date Needed' }}
              </label>
              <input
                v-model="neededBy"
                type="date"
                :min="minDate"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-3.5 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition font-medium min-h-[42px]"
                required
              />
              <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ localeStore.isAmharic ? 'ለእጅ ጥበብ ፎቆች ቢያንስ የ48 ሰዓት ቅድመ ማስታወቂያ ያስፈልጋል።' : 'Requires min. 48 hours notice for handcrafted tiers.' }}</span>
            </div>
          </div>

          <!-- Piped Inscription Message & Live Ribbon Preview -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'በኬኩ ላይ የሚጻፍ መልእክት' : 'Inscription on Cake (Piped Writing)' }}
              </label>
              <span class="text-[10px] text-[#DE1B54] font-semibold">{{ localeStore.isAmharic ? 'ነፃ' : 'Complimentary' }}</span>
            </div>

            <input
              v-model="message"
              type="text"
              maxlength="45"
              :placeholder="localeStore.isAmharic ? 'ለምሳሌ: መልካም ልደት ሳራ! 🎉' : 'e.g. Happy 30th Birthday Sara! 🎉'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[42px]"
            />
          </div>

          <!-- Detailed Theme Instructions -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.isAmharic ? 'የዲዛይን እና የማስዋቢያ ዝርዝር' : 'Theme & Decoration Details' }}
            </label>
            <textarea
              v-model="description"
              rows="3"
              :placeholder="localeStore.isAmharic ? 'የቀለም ምርጫዎን፣ የተፈጥሮ ወይም የስኳር አበባ፣ የቸኮሌት ጠብታ ወይም ሌላ የፈለጉትን እዚህ ይግለጹ...' : 'Describe color palette (e.g. blush pink & gold leaf), edible floral style, custom fondant toppers, chocolate drip...'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl p-3 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
            ></textarea>
          </div>
        </div>

        <!-- ================= RIGHT COLUMN: DESIGN INSPIRATION & QUOTE (5 cols) ================= -->
        <div class="lg:col-span-5 bg-white dark:bg-[#1E120B] rounded-3xl border border-[#EFE8E1] dark:border-[#382318] p-6 sm:p-8 space-y-6 shadow-2xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <div class="flex items-center space-x-2">
              <span class="w-6 h-6 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-xs font-bold">2</span>
              <h2 class="text-base sm:text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.isAmharic ? 'የምሳሌ ፎቶዎች እና ግምታዊ ዋጋ' : 'Inspiration Photos & Quote' }}
              </h2>
            </div>
            <span class="text-xs text-[#DE1B54] font-bold">{{ localeStore.isAmharic ? 'ደረጃ 2 ከ 2' : 'Step 2 of 2' }}</span>
          </div>

          <!-- Drag-and-drop Image Upload Dropzone -->
          <div
            @click="triggerUpload"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onFileDrop"
            class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2 group"
            :class="isDragging 
              ? 'border-[#DE1B54] bg-rose-50/50 dark:bg-[#341822]/60 scale-99' 
              : 'border-[#DE1B54]/40 hover:border-[#DE1B54] bg-[#FAF7F5] dark:bg-[#2A1810] hover:bg-rose-50/20 dark:hover:bg-[#341822]/40'"
          >
            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
            <div v-if="isUploadingImage" class="space-y-2 py-2">
              <div class="w-8 h-8 border-3 border-[#DE1B54] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <div class="text-xs font-bold text-[#DE1B54]">{{ localeStore.isAmharic ? 'ፎቶዎችን በመጫን እና በማረጋገጥ ላይ...' : 'Uploading & validating photo(s)...' }}</div>
            </div>
            <template v-else>
              <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-[#341822] text-[#DE1B54] flex items-center justify-center mx-auto text-xl shadow-2xs group-hover:scale-110 transition-transform">
                📸
              </div>
              <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የምሳሌ ፎቶዎችን ይስቀሉ ወይም ጎትተው ያስቀምጡ' : 'Upload Reference Photos or Drag & Drop' }}</div>
              <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ localeStore.isAmharic ? 'PNG, JPG, WebP እስከ 5MB (ከPinterest፣ Instagram ወይም ንድፍ)' : 'PNG, JPG, WebP up to 5MB (Pinterest, Instagram, or sketches)' }}</div>
            </template>
          </div>

          <!-- Attached Photo Gallery -->
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              <span>{{ localeStore.isAmharic ? 'የተያያዙ ምሳሌዎች' : 'Inspiration References' }} ({{ referenceSamples.length }}):</span>
              <span v-if="referenceSamples.length > 0" class="text-[10px] text-gray-400 dark:text-gray-500">{{ localeStore.isAmharic ? 'በትልቁ ለማየት ይጫኑ' : 'Click photo to zoom' }}</span>
            </div>
            <div v-if="referenceSamples.length > 0" class="grid grid-cols-3 gap-2.5">
              <div
                v-for="(img, idx) in referenceSamples"
                :key="idx"
                class="aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-[#382318] shadow-2xs relative group cursor-pointer"
                @click="previewImage(img)"
              >
                <img :src="img" alt="Reference Cake" class="w-full h-full object-cover group-hover:scale-110 transition duration-300" loading="lazy" />
                <button
                  type="button"
                  @click.stop="removeReference(idx)"
                  class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/70 hover:bg-[#DE1B54] text-white text-[10px] font-bold flex items-center justify-center transition opacity-0 group-hover:opacity-100"
                  title="Remove reference"
                >
                  ✕
                </button>
              </div>
            </div>
            <div v-else class="p-3 bg-[#FAF7F5] dark:bg-[#2A1810] rounded-xl border border-dashed border-gray-200 dark:border-[#382318] text-[11px] text-[#786B60] dark:text-[#C5B7AF] text-center">
              {{ localeStore.isAmharic ? 'አስገዳጅ አይደለም: የምሳሌ ፎቶዎችን ይስቀሉ ወይም በዝርዝር መግለጫው ውስጥ ይጻፉ።' : 'Optional: Upload inspiration images, or describe your theme in the details field.' }}
            </div>
          </div>

          <!-- Real-Time Itemized Price Breakdown & 50% Deposit Estimator -->
          <div class="p-5 bg-gradient-to-br from-rose-50/80 to-[#FAF7F5] dark:from-[#2A1810] dark:to-[#1E120B] rounded-2xl border border-rose-100 dark:border-[#382318] space-y-3 shadow-2xs">
            <div class="flex items-center justify-between border-b border-rose-200/40 dark:border-[#382318] pb-2 text-xs">
              <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ግምታዊ የዋጋ ዝርዝር:' : 'Estimated Price Breakdown:' }}</span>
              <span class="font-black text-[#DE1B54] text-base">{{ estimatedPriceRange }}</span>
            </div>

            <!-- Itemized breakdown lines -->
            <div class="space-y-1.5 text-xs text-[#6E5D53] dark:text-[#C5B7AF]">
              <div class="flex justify-between items-center">
                <span>{{ localeStore.isAmharic ? 'የፎቆች አሰራር' : 'Tier Construction' }} ({{ cakeSize }})</span>
                <span class="font-medium text-[#2A1810] dark:text-[#FAF3EE]">{{ baseTierPriceText }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span>{{ localeStore.isAmharic ? 'የጣዕም እና ክሬም ዝግጅት' : 'Flavor & Filling Handcrafting' }}</span>
                <span class="font-medium text-[#2A1810] dark:text-[#FAF3EE]">{{ flavorPriceText }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span>{{ localeStore.isAmharic ? 'የኬክ ላይ ጽሁፍ' : 'Custom Piped Inscription' }}</span>
                <span class="font-bold text-[#387A54] dark:text-emerald-400">{{ localeStore.isAmharic ? 'በነፃ (0 ብር)' : 'Complimentary (ETB 0)' }}</span>
              </div>
              <div class="pt-2 border-t border-rose-200/40 dark:border-[#382318] flex justify-between items-center text-xs">
                <span>{{ localeStore.isAmharic ? 'የ 50% ቅድመ ክፍያ (ግምታዊ):' : '50% Kitchen Booking Deposit (Est.):' }}</span>
                <span class="font-black text-[#DE1B54]">{{ estimatedDepositText }}</span>
              </div>
            </div>

            <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF] leading-snug pt-1">
              {{ localeStore.isAmharic ? `ትክክለኛው ዋጋ በ ${chosenBranchName} ዋና ኬክ ዲኮሬተር በ 2–4 ሰዓታት ውስጥ ይሰላል። የ 50% ቅድመ ክፍያ ቀጠሮዎን ያረጋግጣል።` : `Final quotation is tailored by the head pastry decorator at ${chosenBranchName} within 2–4 hours. 50% deposit locks in your kitchen oven date.` }}
            </p>
          </div>

          <!-- Contact Information (Autofilled for Authenticated Users) -->
          <div class="p-4 rounded-2xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-100 dark:border-[#382318] space-y-3">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] text-xs flex items-center space-x-1.5">
              <span>👤</span>
              <span>{{ localeStore.isAmharic ? 'የመገኛ አድራሻ (ለዋጋ መረጃ)' : 'Contact Information (For Quotation Updates)' }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ሙሉ ስምዎ *' : 'Your Full Name *' }}</label>
                <input
                  v-model="customerName"
                  type="text"
                  required
                  :placeholder="localeStore.isAmharic ? 'ሙሉ ስምዎን ያስገቡ' : 'Enter your full name'"
                  class="w-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none min-h-[40px]"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ስልክ ቁጥር *' : 'Phone Number *' }}</label>
                <input
                  v-model="customerPhone"
                  type="tel"
                  required
                  placeholder="+251 9XX XXX XXX"
                  class="w-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none min-h-[40px]"
                />
              </div>
            </div>
          </div>

          <!-- Submit Request Button -->
          <button
            type="submit"
            :disabled="isSubmitting || !isPreparingBranchAvailable"
            class="w-full py-4 min-h-[48px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md transition active:scale-98 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <span>{{ isSubmitting ? (localeStore.isAmharic ? 'ጥያቄውን በመላክ ላይ...' : 'Transmitting Design Brief...') : (localeStore.isAmharic ? 'የኬክ ጥያቄውን ላክ →' : 'Submit Design Request →') }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- 4. SUCCESS SUBMISSION MODAL -->
    <div
      v-if="submittedModal"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-center justify-center p-4 cursor-pointer"
      @click="submittedModal = false"
    >
      <div
        class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-100 dark:border-[#382318] max-w-md w-full p-6 sm:p-8 text-center space-y-4 shadow-2xl relative cursor-default"
        @click.stop
      >
        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 flex items-center justify-center mx-auto text-2xl font-bold shadow-xs">
          ✓
        </div>
        <div class="space-y-1">
          <h3 class="font-serif font-black text-2xl text-[#2A1810] dark:text-[#FAF3EE]">
            {{ localeStore.isAmharic ? 'ጥያቄዎ ደርሶናል!' : 'Brief Received!' }}
          </h3>
          <p class="text-xs text-[#2A1810] dark:text-[#FAF3EE] leading-relaxed">
            {{ localeStore.isAmharic ? `የኬክ ንድፍ ጥያቄዎ ` : 'Your custom cake brief ' }}<strong class="text-[#DE1B54]">#{{ requestNumber }}</strong>{{ localeStore.isAmharic ? ` ለ ${chosenBranchName} ዋና ኬክ አዘጋጅ ተላልፏል።` : ` has been forwarded to the head pastry decorator at ${chosenBranchName}.` }}
          </p>
        </div>
        <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
          {{ localeStore.isAmharic ? 'የዲዛይን ሃሳብዎን ተመልክተን በ 2–4 ሰዓታት ውስጥ ይፋዊ የዋጋ ዝርዝር በመለያዎ ውስጥ እንልካለን።' : 'We will review your design brief and post an official quotation to your account within 2–4 hours.' }}
        </p>
        <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-2.5">
          <router-link
            to="/account?tab=custom-cakes"
            class="w-full sm:w-auto px-6 py-3 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
          >
            {{ localeStore.isAmharic ? 'በመለያዬ ውስጥ ተከታተል →' : 'Track in My Account →' }}
          </router-link>
          <router-link
            to="/shop"
            class="w-full sm:w-auto px-6 py-3 bg-gray-50 dark:bg-[#2A1810] hover:bg-gray-100 dark:hover:bg-[#341822] border border-gray-200 dark:border-[#382318] text-gray-700 dark:text-[#FAF3EE] text-xs font-bold rounded-xl transition cursor-pointer"
          >
            {{ localeStore.isAmharic ? 'የዛሬ ኬኮች' : "Shop Today's Bakes" }}
          </router-link>
        </div>
      </div>
    </div>

    <!-- 5. ZOOM PREVIEW MODAL -->
    <div
      v-if="previewZoomImg"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
      @click="previewZoomImg = null"
    >
      <div class="relative max-w-xl w-full flex items-center justify-center" @click.stop>
        <img :src="previewZoomImg" alt="Inspiration Preview" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl" />
        <button
          type="button"
          @click="previewZoomImg = null"
          class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-black font-bold flex items-center justify-center shadow-lg cursor-pointer"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- 6. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const router = useRouter();
const branchStore = useBranchStore();
const authStore = useAuthStore();
const toast = useToastStore();
const localeStore = useLocaleStore();

const fileInput = ref(null);
const isSubmitting = ref(false);
const isUploadingImage = ref(false);
const isDragging = ref(false);
const submittedModal = ref(false);
const previewZoomImg = ref(null);
const isFasting = ref(false);
const requestNumber = ref('CC-0840');
const hasRestoredDraft = ref(false);

const customerName = ref(authStore.user?.full_name || '');
const customerPhone = ref(authStore.user?.phone || '');

const preparingBranchId = ref(2); // Bole Flagship default
const cakeSize = ref('Single Tier 8"');
const flavor = ref('Belgian Dark Chocolate');
const filling = ref('Silky Chocolate Ganache');
const occasion = ref('Birthday');
const neededBy = ref(new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0]);
const message = ref('Happy Birthday! 🎉');
const description = ref('Warm ivory buttercream with blush rose accents, 24k edible gold leaf, and fresh berries.');
const referenceSamples = ref([]);

const DRAFT_KEY = 'sweetbakes_custom_cake_draft';

const minDate = computed(() => {
  const d = new Date(Date.now() + 86400000 * 2);
  return d.toISOString().split('T')[0];
});

// Interactive Tier Count
const tierCount = computed(() => {
  if (cakeSize.value.includes('Three-Tier')) return 3;
  if (cakeSize.value.includes('Two-Tier')) return 2;
  return 1;
});

// Occasion Topper Icon
const occasionTopper = computed(() => {
  switch (occasion.value) {
    case 'Birthday': return '🕯️';
    case 'Wedding': return '💍';
    case 'Anniversary': return '🥂';
    case 'Graduation': return '🎓';
    case 'Baby Shower': return '🍼';
    case 'Corporate Event': return '🏢';
    default: return '✨';
  }
});

// Cake Visualizer Colors based on Flavor
const cakeColorStyle = computed(() => {
  switch (flavor.value) {
    case 'Belgian Dark Chocolate':
      return { primary: '#3B2219', fillingColor: '#281710', name: 'Dark Cocoa & Ganache' };
    case 'Madagascar Bourbon Vanilla':
      return { primary: '#FFF8E7', fillingColor: '#E8D5B5', name: 'Warm Cream & Vanilla' };
    case 'Classic Red Velvet':
      return { primary: '#7C1C28', fillingColor: '#FFFDF9', name: 'Crimson Velvet & Cream' };
    case 'Ethiopian Mocha Espresso':
      return { primary: '#593D2A', fillingColor: '#3E291B', name: 'Espresso Latte' };
    case 'Fresh Strawberry Chiffon':
      return { primary: '#E8869B', fillingColor: '#F7C6D0', name: 'Strawberry Rose' };
    case 'Spiced Carrot & Walnut':
      return { primary: '#B86B33', fillingColor: '#FFF9ED', name: 'Carrot Amber' };
    case 'Ye\'Tsom Vegan Chocolate':
      return { primary: '#2D1B13', fillingColor: '#1F120D', name: 'Ye\'Tsom Pure Cacao' };
    case 'Ye\'Tsom Vegan Vanilla Citrus':
      return { primary: '#FAF2D3', fillingColor: '#E6D79E', name: 'Ye\'Tsom Citrus Velvet' };
    default:
      return { primary: '#FFF8E7', fillingColor: '#DE1B54', name: 'Signature SweetBakes' };
  }
});

const sizeOptions = [
  { name: 'Single Tier 6"', nameAm: 'ባለ 1 ፎቅ 6 ኢንች', serves: 'Serves 6–8 guests', servesAm: 'ከ 6–8 እንግዶች', icon: '🎂', baseEstimate: 'ETB 1,200 – 1,800', baseEstimateAm: '1,200 – 1,800 ብር', basePrice: 1200 },
  { name: 'Single Tier 8"', nameAm: 'ባለ 1 ፎቅ 8 ኢንች', serves: 'Serves 12–16 guests', servesAm: 'ከ 12–16 እንግዶች', icon: '🎂', baseEstimate: 'ETB 1,800 – 2,600', baseEstimateAm: '1,800 – 2,600 ብር', basePrice: 1800 },
  { name: 'Two-Tier Celebration (8" + 6")', nameAm: 'ባለ 2 ፎቅ ክብረ በዓል (8" + 6")', serves: 'Serves 24–30 guests', servesAm: 'ከ 24–30 እንግዶች', icon: '🍰', baseEstimate: 'ETB 3,200 – 4,800', baseEstimateAm: '3,200 – 4,800 ብር', basePrice: 3200 },
  { name: 'Grand Three-Tier Gala (10" + 8" + 6")', nameAm: 'ባለ 3 ፎቅ ጋላ (10" + 8" + 6")', serves: 'Serves 45–60 guests', servesAm: 'ከ 45–60 እንግዶች', icon: '👑', baseEstimate: 'ETB 6,000 – 9,500', baseEstimateAm: '6,000 – 9,500 ብር', basePrice: 6000 },
];

const inspirationPresets = [
  {
    name: 'Blush Floral Birthday',
    nameAm: 'የአበባ ንድፍ ልደት',
    icon: '🌸',
    occasion: 'Birthday',
    flavor: 'Madagascar Bourbon Vanilla',
    filling: 'Wild Berry Compote & Mascarpone',
    description: 'Blush pink and ivory palette with fresh spray roses, edible pearls, and gold drip.',
  },
  {
    name: 'Luxury Wedding Tier',
    nameAm: 'የቅንጦት ሰርግ ፎቅ',
    icon: '💍',
    occasion: 'Wedding',
    size: 'Two-Tier Celebration (8" + 6")',
    flavor: 'Belgian Dark Chocolate',
    filling: 'Silky Chocolate Ganache',
    description: 'Minimalist smooth ivory fondant, hand-painted gold leaf veins, and white sugar peonies.',
  },
  {
    name: 'Graduation Centerpiece',
    nameAm: 'የምረቃ ድምቀት ኬክ',
    icon: '🎓',
    occasion: 'Graduation',
    flavor: 'Classic Red Velvet',
    filling: 'Cream Cheese Frosting',
    description: 'Black and gold celebration theme with custom acrylic graduate silhouette topper.',
  },
  {
    name: 'Choco Decadence Drip',
    nameAm: 'የቸኮሌት ድሪፕ ኬክ',
    icon: '🍫',
    occasion: 'Birthday',
    flavor: 'Belgian Dark Chocolate',
    filling: 'Salted Caramel Praline',
    description: 'Triple Belgian chocolate ganache drip with macarons, chocolate curls, and fresh raspberries.',
  },
];

const estimatedPriceRange = computed(() => {
  const opt = sizeOptions.find((o) => o.name === cakeSize.value);
  if (!opt) return localeStore.isAmharic ? '1,800 – 2,600 ብር' : 'ETB 1,800 – 2,600';
  return localeStore.isAmharic ? opt.baseEstimateAm : opt.baseEstimate;
});

const baseTierPriceText = computed(() => {
  const opt = sizeOptions.find((o) => o.name === cakeSize.value);
  const p = opt ? opt.basePrice : 1800;
  return localeStore.formatPrice(p);
});

const flavorPriceText = computed(() => {
  return localeStore.isAmharic ? 'በግምቱ ውስጥ የተካተተ' : 'Included in estimate';
});

const estimatedDepositText = computed(() => {
  const opt = sizeOptions.find((o) => o.name === cakeSize.value);
  if (!opt) return localeStore.isAmharic ? '900 – 1,300 ብር' : 'ETB 900 – 1,300';
  const minDeposit = Math.round(opt.basePrice * 0.5);
  const maxDeposit = Math.round((parseInt(opt.baseEstimate.split('–')[1].replace(/[^0-9]/g, '')) || opt.basePrice * 1.5) * 0.5);
  return `${localeStore.formatPrice(minDeposit)} – ${localeStore.formatPrice(maxDeposit)}`;
});

const chosenBranchName = computed(() => {
  const b = branchStore.branches.find((br) => br.branch_id === preparingBranchId.value);
  const raw = b ? b.branch_name : 'Bole Flagship & Espresso';
  return localeStore.tBranch(raw);
});

const isPreparingBranchAvailable = computed(() => {
  return branchStore.isBranchShoppingEnabled(preparingBranchId.value);
});

// Draft Persistence (Zero Data Loss on Refresh)
function saveDraftToStorage() {
  try {
    const draft = {
      preparingBranchId: preparingBranchId.value,
      cakeSize: cakeSize.value,
      flavor: flavor.value,
      filling: filling.value,
      isFasting: isFasting.value,
      occasion: occasion.value,
      neededBy: neededBy.value,
      message: message.value,
      description: description.value,
      customerName: customerName.value,
      customerPhone: customerPhone.value,
      referenceSamples: referenceSamples.value,
    };
    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
  } catch (e) {
    console.warn('Could not save custom cake draft', e);
  }
}

function restoreDraftFromStorage() {
  try {
    const stored = localStorage.getItem(DRAFT_KEY);
    if (!stored) return false;
    const draft = JSON.parse(stored);
    if (draft && typeof draft === 'object') {
      if (draft.preparingBranchId) preparingBranchId.value = draft.preparingBranchId;
      if (draft.cakeSize) cakeSize.value = draft.cakeSize;
      if (draft.flavor) flavor.value = draft.flavor;
      if (draft.filling) filling.value = draft.filling;
      if (draft.isFasting !== undefined) isFasting.value = draft.isFasting;
      if (draft.occasion) occasion.value = draft.occasion;
      if (draft.neededBy) neededBy.value = draft.neededBy;
      if (draft.message !== undefined) message.value = draft.message;
      if (draft.description !== undefined) description.value = draft.description;
      if (draft.customerName) customerName.value = draft.customerName;
      if (draft.customerPhone) customerPhone.value = draft.customerPhone;
      if (Array.isArray(draft.referenceSamples) && draft.referenceSamples.length > 0) {
        referenceSamples.value = draft.referenceSamples;
      }
      return true;
    }
  } catch (e) {
    console.warn('Could not restore custom cake draft', e);
  }
  return false;
}

function clearSavedDraft() {
  localStorage.removeItem(DRAFT_KEY);
  hasRestoredDraft.value = false;
  cakeSize.value = 'Single Tier 8"';
  flavor.value = 'Belgian Dark Chocolate';
  filling.value = 'Silky Chocolate Ganache';
  occasion.value = 'Birthday';
  isFasting.value = false;
  message.value = 'Happy Birthday! 🎉';
  description.value = 'Warm ivory buttercream with blush rose accents, 24k edible gold leaf, and fresh berries.';
  referenceSamples.value = [];
  if (authStore.user) {
    customerName.value = authStore.user.full_name || '';
    customerPhone.value = authStore.user.phone || '';
  }
  toast.info('Custom cake draft cleared.');
}

watch(
  [preparingBranchId, cakeSize, flavor, filling, isFasting, occasion, neededBy, message, description, customerName, customerPhone, referenceSamples],
  () => {
    saveDraftToStorage();
  },
  { deep: true }
);

watch(() => branchStore.selectedBranchId, (newId) => {
  if (newId && !hasRestoredDraft.value) {
    preparingBranchId.value = newId;
  }
});

onMounted(async () => {
  if (branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }

  const restored = restoreDraftFromStorage();
  hasRestoredDraft.value = restored;

  if (authStore.user) {
    if (!customerName.value) customerName.value = authStore.user.full_name || '';
    if (!customerPhone.value) customerPhone.value = authStore.user.phone || '';
  }

  if (!restored && branchStore.selectedBranchId) {
    preparingBranchId.value = branchStore.selectedBranchId;
  }
});

function applyPreset(preset) {
  occasion.value = preset.occasion;
  if (preset.size) cakeSize.value = preset.size;
  flavor.value = preset.flavor;
  filling.value = preset.filling;
  description.value = preset.description;
  toast.success(`Applied "${preset.name}" inspiration!`);
}

function triggerUpload() {
  fileInput.value?.click();
}

async function onFileDrop(e) {
  isDragging.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    await processFiles(files);
  }
}

async function onFileChange(e) {
  const files = e.target.files;
  if (files && files.length > 0) {
    await processFiles(files);
  }
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

async function processFiles(files) {
  isUploadingImage.value = true;
  let successCount = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (!file.type.startsWith('image/')) {
      toast.error(`"${file.name}" is not an image file.`);
      continue;
    }

    const formData = new FormData();
    formData.append('image', file);

    try {
      const res = await api.post('/custom-cakes/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (res.data?.image_url) {
        referenceSamples.value.unshift(res.data.image_url);
        successCount++;
      }
    } catch (err) {
      console.error('Failed to upload image', err);
      toast.error(err.response?.data?.message || `Failed to upload "${file.name}". Ensure it is a valid image under 5MB.`);
    }
  }

  isUploadingImage.value = false;
  if (successCount > 0) {
    toast.success(`Successfully uploaded ${successCount} design photo(s) to bakery!`);
  }
}

function removeReference(idx) {
  referenceSamples.value.splice(idx, 1);
  toast.info('Removed photo.');
}

function previewImage(img) {
  previewZoomImg.value = img;
}

async function handleSubmitCustomCake() {
  if (!isPreparingBranchAvailable.value) {
    toast.error(localeStore.isAmharic 
      ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለትዕዛዝ ዝግ ነው።' 
      : 'Sorry, this branch is currently unavailable for shopping.');
    return;
  }

  if (!authStore.isAuthenticated) {
    toast.info(localeStore.isAmharic 
      ? 'የብጁ ኬክ ጥያቄዎን ለማስገባት እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to submit your custom cake order.');
    router.push({ path: '/login', query: { redirect: '/custom-cake' } });
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = {
      branch_id: preparingBranchId.value,
      customer_name: customerName.value ? customerName.value.trim() : '',
      customer_phone: customerPhone.value ? customerPhone.value.trim() : '',
      cake_size: cakeSize.value,
      flavor: `${flavor.value} with ${filling.value}${isFasting.value ? ' (100% Ye\'Tsom Fasting Certified)' : ''}`,
      needed_by: neededBy.value,
      design: `${occasion.value} cake. ${description.value}`,
      message: message.value,
      image_urls: referenceSamples.value,
    };

    const res = await api.post('/custom-cakes', payload);
    requestNumber.value = res.data?.request_number || res.data?.cake_request?.request_number || 'CC-0840';

    // Clear Draft from storage upon successful submission
    localStorage.removeItem(DRAFT_KEY);

    submittedModal.value = true;
    toast.success('Custom cake brief forwarded to head baker!');
  } catch (err) {
    console.error('Failed submitting custom cake', err);
    toast.error(err.response?.data?.message || 'Failed to submit custom cake brief. Please check all required fields.');
  } finally {
    isSubmitting.value = false;
  }
}
</script>
