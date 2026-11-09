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
      <!-- Interactive Cake Visualizer Studio (3D WebGL / 2D Graceful Fallback) -->
      <div class="bg-gradient-to-b from-[#FAF7F5] via-[#FFFBF8] to-white dark:from-[#24150E] dark:via-[#1E110A] dark:to-[#170E0A] rounded-3xl border border-[#EFE8E1] dark:border-[#382318] p-5 sm:p-7 shadow-xs relative overflow-hidden">
        
        <!-- Studio Header with Mode Toggle & Action Toolbar -->
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4 border-b border-[#F0E6DD] dark:border-[#382318] pb-4 mb-5">
          <!-- Studio Title & Tech Pill -->
          <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#DE1B54] to-rose-400 text-white flex items-center justify-center text-sm shadow-xs font-bold">
              🎂
            </div>
            <div>
              <div class="flex items-center space-x-2">
                <h2 class="text-base sm:text-lg font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.isAmharic ? 'የቀጥታ 3D ኬክ ስቱዲዮ' : '3D Interactive Cake Studio' }}
                </h2>
                <span
                  class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider border"
                  :class="visualizerMode === '3d' && webglSupported 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300' 
                    : 'bg-stone-100 text-stone-700 border-stone-300 dark:bg-stone-900 dark:text-stone-300'"
                >
                  {{ visualizerMode === '3d' && webglSupported ? 'WebGL 3D Active' : '2D Mode' }}
                </span>
              </div>
              <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF]">
                {{ localeStore.isAmharic ? 'ኬክዎን በ 3D አሽከርክረው፣ አቅርበው ይመልከቱ እና ቅጽበታዊ ፎቶ ያንሱ!' : 'Rotate, pan, zoom, customize frosting and capture your 3D centerpiece!' }}
              </p>
            </div>
          </div>

          <!-- Studio Toolbar: Mode Switcher & Camera Controls -->
          <div class="flex flex-wrap items-center justify-center gap-2">
            <!-- Mode Switcher -->
            <div class="bg-[#F2ECE4] dark:bg-[#2A1810] p-1 rounded-full flex items-center space-x-1 border border-stone-200 dark:border-[#382318]">
              <button
                type="button"
                @click="switchVisualizerMode('3d')"
                :disabled="!webglSupported"
                class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer flex items-center space-x-1"
                :class="visualizerMode === '3d' 
                  ? 'bg-white dark:bg-[#1E120B] text-[#DE1B54] shadow-2xs' 
                  : 'text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#2A1810]'"
              >
                <span>✨</span>
                <span>{{ localeStore.isAmharic ? '3D ስቱዲዮ' : '3D Studio' }}</span>
              </button>
              <button
                type="button"
                @click="switchVisualizerMode('2d')"
                class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer flex items-center space-x-1"
                :class="visualizerMode === '2d' 
                  ? 'bg-white dark:bg-[#1E120B] text-[#DE1B54] shadow-2xs' 
                  : 'text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#2A1810]'"
              >
                <span>🖼️</span>
                <span>{{ localeStore.isAmharic ? 'ክላሲክ 2D' : 'Classic 2D' }}</span>
              </button>
            </div>

            <!-- 3D Actions (only active when visualizerMode === '3d') -->
            <template v-if="visualizerMode === '3d' && webglSupported">
              <button
                type="button"
                @click="autoRotate = !autoRotate"
                class="px-3 py-1.5 rounded-full text-xs font-semibold border transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
                :class="autoRotate 
                  ? 'bg-rose-50 text-[#DE1B54] border-rose-300 dark:bg-rose-950/40 dark:border-rose-900' 
                  : 'bg-white text-stone-700 border-stone-200 dark:bg-[#2A1810] dark:text-[#C5B7AF] dark:border-[#382318] hover:bg-stone-50'"
                :title="autoRotate ? 'Pause auto-spin' : 'Enable auto-spin'"
              >
                <span>{{ autoRotate ? '⏸️' : '🔄' }}</span>
                <span>{{ autoRotate ? (localeStore.isAmharic ? 'እሽክርክሪት አቁም' : 'Spinning') : (localeStore.isAmharic ? 'አሽከርክር' : 'Auto-Spin') }}</span>
              </button>

              <button
                type="button"
                @click="resetCameraView"
                class="px-3 py-1.5 bg-white dark:bg-[#2A1810] hover:bg-stone-50 dark:hover:bg-[#341822] text-stone-700 dark:text-[#FAF3EE] border border-stone-200 dark:border-[#382318] rounded-full text-xs font-semibold transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
                title="Reset camera view"
              >
                <span>🎯</span>
                <span>{{ localeStore.isAmharic ? 'ካሜራ መልስ' : 'Reset View' }}</span>
              </button>

              <button
                type="button"
                @click="take3DSnapshot"
                class="px-3.5 py-1.5 bg-gradient-to-r from-[#DE1B54] to-[#B31340] hover:from-[#C51345] hover:to-[#960E34] text-white rounded-full text-xs font-bold transition shadow-xs cursor-pointer flex items-center space-x-1.5 active:scale-95"
                title="Capture high-resolution 3D preview snapshot"
              >
                <span>📸</span>
                <span>{{ localeStore.isAmharic ? '3D ፎቶ አንሳ' : 'Capture 3D Snapshot' }}</span>
              </button>
            </template>
          </div>
        </div>

        <!-- Viewport Main Grid: Specs, Viewport, Palette -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
          
          <!-- Left Column: Specs Summary (3 cols) -->
          <div class="lg:col-span-3 space-y-3 text-center lg:text-left order-2 lg:order-1">
            <div class="bg-white/80 dark:bg-[#1E120B]/80 backdrop-blur-xs p-4 rounded-2xl border border-stone-200 dark:border-[#382318] space-y-2 shadow-2xs">
              <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64] dark:text-[#A8988C]">Configuration Summary</div>
              <div class="text-base sm:text-lg font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
                {{ cakeSize }}
              </div>
              <div class="space-y-1 text-xs text-[#6E5D53] dark:text-[#C5B7AF] pt-1 border-t border-stone-100 dark:border-[#382318]">
                <div><span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ጣዕም:' : 'Flavor:' }}</span> {{ flavor }}</div>
                <div><span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ክሬም:' : 'Frosting:' }}</span> {{ filling }}</div>
                <div><span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ዝግጅት:' : 'Occasion:' }}</span> {{ occasion }}</div>
                <div v-if="message.trim()" class="truncate text-[#DE1B54] font-semibold italic">
                  &ldquo;{{ message }}&rdquo;
                </div>
                <div v-if="isFasting" class="text-emerald-700 dark:text-emerald-400 font-bold flex items-center justify-center lg:justify-start space-x-1 pt-1">
                  <span>🌿</span>
                  <span>100% Ye'Tsom Fasting Certified</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Center Column: Viewport Canvas (6 cols) -->
          <div class="lg:col-span-6 flex flex-col items-center justify-center order-1 lg:order-2 relative">
            
            <!-- Snapshot Flash Animation Overlay -->
            <div
              v-if="snapshotFlash"
              class="absolute inset-0 bg-white/90 z-30 rounded-2xl pointer-events-none transition-opacity duration-300"
            ></div>

            <!-- MODE 1: Three.js 3D WebGL Canvas -->
            <div
              v-show="visualizerMode === '3d' && webglSupported"
              class="w-full relative flex flex-col items-center"
            >
              <div class="w-full h-[280px] sm:h-[340px] lg:h-[380px] rounded-2xl bg-radial from-white via-rose-50/30 to-[#FAF4EE] dark:from-[#26160F] dark:via-[#1E110A] dark:to-[#170E0A] border border-stone-200 dark:border-[#382318] shadow-inner relative overflow-hidden flex items-center justify-center">
                <canvas
                  ref="threeCanvasRef"
                  class="w-full h-full block cursor-grab active:cursor-grabbing touch-none select-none"
                ></canvas>

                <!-- Floating Interaction Hint Pill -->
                <div class="absolute bottom-2.5 inset-x-0 mx-auto w-max px-3 py-1 bg-black/60 backdrop-blur-xs text-white/90 text-[10px] rounded-full pointer-events-none flex items-center space-x-1.5 shadow-md">
                  <span>🖱️</span>
                  <span>{{ localeStore.isAmharic ? 'ለመዞር ይጎትቱ • ለማቅረብ ያሸብርቁ' : 'Drag to rotate • Scroll to zoom • Touch friendly' }}</span>
                </div>
              </div>
            </div>

            <!-- MODE 2: Classic 2D Layered Fallback -->
            <div
              v-if="visualizerMode === '2d' || !webglSupported"
              class="w-full flex flex-col items-center justify-end min-h-[240px] sm:min-h-[280px] py-4 relative"
            >
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
          </div>

          <!-- Right Column: Palette & Snapshot Thumbnail (3 cols) -->
          <div class="lg:col-span-3 space-y-3 text-center lg:text-right order-3">
            <!-- Palette Card -->
            <div class="bg-white/80 dark:bg-[#1E120B]/80 backdrop-blur-xs p-4 rounded-2xl border border-stone-200 dark:border-[#382318] text-xs space-y-2 shadow-2xs">
              <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የቀለም ቅንብር:' : 'Palette Preview:' }}</div>
              <div class="flex items-center justify-center lg:justify-end space-x-2">
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

            <!-- Captured 3D Snapshot Pill / Thumbnail -->
            <div
              v-if="capturedSnapshotUrl"
              class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 p-3 rounded-2xl space-y-2 shadow-2xs"
            >
              <div class="flex items-center justify-between text-[11px] font-bold text-emerald-800 dark:text-emerald-300">
                <span>3D Snapshot Saved ✓</span>
                <span class="text-[9px] bg-emerald-200/60 dark:bg-emerald-900 text-emerald-900 dark:text-emerald-200 px-1.5 py-0.5 rounded">Attached</span>
              </div>
              <div class="w-full aspect-video rounded-xl overflow-hidden border border-emerald-200/60 dark:border-emerald-800/60">
                <img :src="capturedSnapshotUrl" alt="3D Cake Snapshot" class="w-full h-full object-cover" />
              </div>
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
import * as THREE from 'three';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
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

// 3D Visualizer State
const visualizerMode = ref('3d'); // '3d' or '2d'
const webglSupported = ref(true);
const threeCanvasRef = ref(null);
const autoRotate = ref(true);
const snapshotFlash = ref(false);
const capturedSnapshotUrl = ref(null);

// Three.js internal references
let scene = null;
let camera = null;
let renderer = null;
let animationFrameId = null;
let cakeGroup = null;
let resizeObserver = null;
let isDraggingOrbit = false;
let currentRotation = { x: 0.28, y: -0.45 };
let targetZoom = 8.5;
let currentZoom = 8.5;

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

// =========================================================================
// THREE.JS 3D CAKE STUDIO ENGINE
// =========================================================================

function switchVisualizerMode(mode) {
  visualizerMode.value = mode;
  if (mode === '3d') {
    nextTick(() => {
      initThree();
    });
  }
}

function initThree() {
  if (!threeCanvasRef.value) return;

  try {
    const canvas = threeCanvasRef.value;
    const width = canvas.parentElement?.clientWidth || canvas.clientWidth || 460;
    const height = canvas.parentElement?.clientHeight || canvas.clientHeight || 360;

    // Dispose old renderer if exists
    if (renderer) {
      destroyThree();
    }

    // 1. Scene
    scene = new THREE.Scene();

    // 2. Camera
    camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
    camera.position.set(0, 3.0, targetZoom);

    // 3. Renderer with preserveDrawingBuffer for snapshot capture
    renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      antialias: true,
      alpha: true,
      preserveDrawingBuffer: true,
    });
    renderer.setSize(width, height, false);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;

    // 4. Studio Lighting
    const ambientLight = new THREE.AmbientLight(0xfff5ea, 1.4);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xffffff, 1.8);
    keyLight.position.set(6, 9, 6);
    scene.add(keyLight);

    const rimLight = new THREE.DirectionalLight(0xffe2d8, 0.9);
    rimLight.position.set(-6, 4, -5);
    scene.add(rimLight);

    const accentPoint = new THREE.PointLight(0xffb3c6, 1.2, 12);
    accentPoint.position.set(0, 6, 4);
    scene.add(accentPoint);

    // 5. Cake Root Group
    cakeGroup = new THREE.Group();
    cakeGroup.position.set(0, -1.0, 0);
    cakeGroup.rotation.x = currentRotation.x;
    cakeGroup.rotation.y = currentRotation.y;
    scene.add(cakeGroup);

    // 6. Build initial 3D Cake Meshes
    build3DCake();

    // 7. Setup Orbit Interaction Listeners
    setupCanvasControls(canvas);

    // 8. Start Animation Loop
    startAnimationLoop();

    // 9. Resize Handling
    if (window.ResizeObserver && canvas.parentElement) {
      resizeObserver = new ResizeObserver(() => {
        if (!renderer || !camera || !canvas.parentElement) return;
        const newW = canvas.parentElement.clientWidth;
        const newH = canvas.parentElement.clientHeight;
        if (newW > 0 && newH > 0) {
          camera.aspect = newW / newH;
          camera.updateProjectionMatrix();
          renderer.setSize(newW, newH, false);
        }
      });
      resizeObserver.observe(canvas.parentElement);
    }

    webglSupported.value = true;
  } catch (err) {
    console.warn('WebGL initialization failed, falling back to 2D view', err);
    webglSupported.value = false;
    visualizerMode.value = '2d';
  }
}

function build3DCake() {
  if (!cakeGroup) return;

  // Clear existing meshes
  while (cakeGroup.children.length > 0) {
    const obj = cakeGroup.children[0];
    if (obj.geometry) obj.geometry.dispose();
    if (obj.material) {
      if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
      else obj.material.dispose();
    }
    cakeGroup.remove(obj);
  }

  const primaryColor = new THREE.Color(cakeColorStyle.value.primary);
  const fillingColor = new THREE.Color(cakeColorStyle.value.fillingColor);

  // Frosting Material (Silky Buttercream Sheen)
  const frostingMat = new THREE.MeshStandardMaterial({
    color: primaryColor,
    roughness: 0.35,
    metalness: 0.04,
  });

  const fillingMat = new THREE.MeshStandardMaterial({
    color: fillingColor,
    roughness: 0.25,
    metalness: 0.08,
  });

  // Silver Cake Stand / Board
  const standGeo = new THREE.CylinderGeometry(2.9, 3.1, 0.2, 48);
  const standMat = new THREE.MeshStandardMaterial({
    color: 0xdedede,
    metalness: 0.85,
    roughness: 0.25,
  });
  const standMesh = new THREE.Mesh(standGeo, standMat);
  standMesh.position.y = 0.1;
  cakeGroup.add(standMesh);

  // Helper: pearl border ring
  function addPearlRing(radius, y, count) {
    const pearlGeo = new THREE.SphereGeometry(0.075, 12, 12);
    const pearlMat = new THREE.MeshStandardMaterial({
      color: 0xfffaea,
      metalness: 0.3,
      roughness: 0.15,
    });
    for (let i = 0; i < count; i++) {
      const angle = (i / count) * Math.PI * 2;
      const pearl = new THREE.Mesh(pearlGeo, pearlMat);
      pearl.position.set(Math.cos(angle) * radius, y, Math.sin(angle) * radius);
      cakeGroup.add(pearl);
    }
  }

  const tiers = tierCount.value;

  // BASE TIER (Tier 1) - Always present
  const baseH = tiers === 1 ? 1.8 : 1.35;
  const baseR = 2.2;
  const baseY = 0.2 + baseH / 2;
  const baseGeo = new THREE.CylinderGeometry(baseR, baseR, baseH, 64);
  const baseMesh = new THREE.Mesh(baseGeo, frostingMat);
  baseMesh.position.y = baseY;
  cakeGroup.add(baseMesh);

  // Drip collar on Base tier
  const drip1Geo = new THREE.CylinderGeometry(baseR + 0.03, baseR + 0.03, 0.15, 64);
  const drip1 = new THREE.Mesh(drip1Geo, fillingMat);
  drip1.position.y = baseY + baseH / 2 - 0.07;
  cakeGroup.add(drip1);

  addPearlRing(baseR + 0.02, 0.25, 28);

  let currentTopY = 0.2 + baseH;

  // MIDDLE TIER (Tier 2)
  if (tiers >= 2) {
    const midH = 1.25;
    const midR = 1.55;
    const midY = currentTopY + midH / 2;
    const midGeo = new THREE.CylinderGeometry(midR, midR, midH, 64);
    const midMesh = new THREE.Mesh(midGeo, frostingMat);
    midMesh.position.y = midY;
    cakeGroup.add(midMesh);

    const drip2Geo = new THREE.CylinderGeometry(midR + 0.03, midR + 0.03, 0.12, 64);
    const drip2 = new THREE.Mesh(drip2Geo, fillingMat);
    drip2.position.y = midY + midH / 2 - 0.06;
    cakeGroup.add(drip2);

    addPearlRing(midR + 0.02, currentTopY + 0.05, 20);
    currentTopY += midH;
  }

  // TOP TIER (Tier 3)
  if (tiers >= 3) {
    const topH = 1.15;
    const topR = 1.0;
    const topY = currentTopY + topH / 2;
    const topGeo = new THREE.CylinderGeometry(topR, topR, topH, 64);
    const topMesh = new THREE.Mesh(topGeo, frostingMat);
    topMesh.position.y = topY;
    cakeGroup.add(topMesh);

    const drip3Geo = new THREE.CylinderGeometry(topR + 0.03, topR + 0.03, 0.1, 64);
    const drip3 = new THREE.Mesh(drip3Geo, fillingMat);
    drip3.position.y = topY + topH / 2 - 0.05;
    cakeGroup.add(drip3);

    addPearlRing(topR + 0.02, currentTopY + 0.05, 14);
    currentTopY += topH;
  }

  // OCCASION TOPPER
  buildOccasionTopper(currentTopY);

  // PIPED INSCRIPTION RIBBON / PLAQUE
  buildDedicationRibbon(baseR, baseY);
}

function buildOccasionTopper(topY) {
  const occ = occasion.value;
  const goldMat = new THREE.MeshStandardMaterial({
    color: 0xf5b83d,
    metalness: 0.9,
    roughness: 0.2,
  });

  if (occ === 'Birthday') {
    // 3D Birthday Candle with flame
    const candleGeo = new THREE.CylinderGeometry(0.08, 0.08, 0.9, 16);
    const candleMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.3 });
    const candle = new THREE.Mesh(candleGeo, candleMat);
    candle.position.set(0, topY + 0.45, 0);
    cakeGroup.add(candle);

    // Wick
    const wickGeo = new THREE.CylinderGeometry(0.015, 0.015, 0.15, 8);
    const wickMat = new THREE.MeshBasicMaterial({ color: 0x222222 });
    const wick = new THREE.Mesh(wickGeo, wickMat);
    wick.position.set(0, topY + 0.95, 0);
    cakeGroup.add(wick);

    // Flame
    const flameGeo = new THREE.ConeGeometry(0.12, 0.35, 16);
    const flameMat = new THREE.MeshBasicMaterial({ color: 0xff9900 });
    const flame = new THREE.Mesh(flameGeo, flameMat);
    flame.position.set(0, topY + 1.15, 0);
    cakeGroup.add(flame);
  } else if (occ === 'Wedding') {
    // Interlocking Golden Rings
    const ringGeo = new THREE.TorusGeometry(0.35, 0.06, 16, 32);
    const ring1 = new THREE.Mesh(ringGeo, goldMat);
    ring1.position.set(-0.2, topY + 0.45, 0);
    ring1.rotation.y = 0.4;
    const ring2 = new THREE.Mesh(ringGeo, goldMat);
    ring2.position.set(0.2, topY + 0.45, 0);
    ring2.rotation.y = -0.4;
    cakeGroup.add(ring1);
    cakeGroup.add(ring2);
  } else {
    // Golden Celebration Star / Disc Emblem
    const discGeo = new THREE.CylinderGeometry(0.5, 0.5, 0.08, 32);
    const disc = new THREE.Mesh(discGeo, goldMat);
    disc.position.set(0, topY + 0.4, 0);
    disc.rotation.x = Math.PI / 2;
    cakeGroup.add(disc);

    // Golden sphere crown
    const starGeo = new THREE.SphereGeometry(0.2, 16, 16);
    const star = new THREE.Mesh(starGeo, goldMat);
    star.position.set(0, topY + 0.8, 0);
    cakeGroup.add(star);
  }
}

function buildDedicationRibbon(baseR, baseY) {
  if (!message.value || !message.value.trim()) return;

  const canvas = document.createElement('canvas');
  canvas.width = 512;
  canvas.height = 128;
  const ctx = canvas.getContext('2d');

  // Background ribbon plaque
  ctx.fillStyle = '#FFF8F2';
  ctx.beginPath();
  if (ctx.roundRect) {
    ctx.roundRect(8, 8, 496, 112, 24);
  } else {
    ctx.rect(8, 8, 496, 112);
  }
  ctx.fill();

  // Border
  ctx.lineWidth = 5;
  ctx.strokeStyle = '#DE1B54';
  ctx.stroke();

  // Text
  ctx.fillStyle = '#DE1B54';
  ctx.font = 'bold 26px "Playfair Display", Georgia, serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';

  const textToDraw = message.value.length > 28 ? message.value.substring(0, 26) + '...' : message.value;
  ctx.fillText(textToDraw, 256, 64);

  const texture = new THREE.CanvasTexture(canvas);
  texture.needsUpdate = true;

  const plaqueGeo = new THREE.PlaneGeometry(1.6, 0.42);
  const plaqueMat = new THREE.MeshBasicMaterial({
    map: texture,
    transparent: true,
  });

  const plaqueMesh = new THREE.Mesh(plaqueGeo, plaqueMat);
  // Position slightly in front of base tier facing the default camera angle
  plaqueMesh.position.set(0, baseY, baseR + 0.05);
  cakeGroup.add(plaqueMesh);
}

function setupCanvasControls(canvas) {
  let isDown = false;
  let startX = 0;
  let startY = 0;
  let lastTouchDist = 0;

  function onPointerDown(clientX, clientY) {
    isDown = true;
    isDraggingOrbit = true;
    startX = clientX;
    startY = clientY;
  }

  function onPointerMove(clientX, clientY) {
    if (!isDown || !cakeGroup) return;
    const deltaX = clientX - startX;
    const deltaY = clientY - startY;
    startX = clientX;
    startY = clientY;

    currentRotation.y += deltaX * 0.012;
    currentRotation.x = Math.max(-0.15, Math.min(0.65, currentRotation.x + deltaY * 0.008));
    cakeGroup.rotation.y = currentRotation.y;
    cakeGroup.rotation.x = currentRotation.x;
  }

  function onPointerUp() {
    isDown = false;
    isDraggingOrbit = false;
  }

  canvas.addEventListener('mousedown', (e) => onPointerDown(e.clientX, e.clientY));
  window.addEventListener('mousemove', (e) => onPointerMove(e.clientX, e.clientY));
  window.addEventListener('mouseup', onPointerUp);

  // Touch
  canvas.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1) {
      onPointerDown(e.touches[0].clientX, e.touches[0].clientY);
    } else if (e.touches.length === 2) {
      lastTouchDist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
    }
  }, { passive: true });

  canvas.addEventListener('touchmove', (e) => {
    if (e.touches.length === 1) {
      onPointerMove(e.touches[0].clientX, e.touches[0].clientY);
    } else if (e.touches.length === 2) {
      const dist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      const diff = lastTouchDist - dist;
      lastTouchDist = dist;
      targetZoom = Math.max(5.5, Math.min(13.0, targetZoom + diff * 0.02));
    }
  }, { passive: true });

  canvas.addEventListener('touchend', onPointerUp, { passive: true });

  // Scroll to zoom
  canvas.addEventListener('wheel', (e) => {
    e.preventDefault();
    targetZoom = Math.max(5.5, Math.min(13.0, targetZoom + e.deltaY * 0.005));
  }, { passive: false });
}

function startAnimationLoop() {
  function animate() {
    animationFrameId = requestAnimationFrame(animate);

    if (autoRotate.value && cakeGroup && !isDraggingOrbit) {
      currentRotation.y += 0.004;
      cakeGroup.rotation.y = currentRotation.y;
    }

    // Smooth camera zoom damping
    if (camera) {
      currentZoom += (targetZoom - currentZoom) * 0.1;
      camera.position.z = currentZoom;
      camera.lookAt(0, 1.2, 0);
    }

    if (renderer && scene && camera) {
      renderer.render(scene, camera);
    }
  }
  animate();
}

function resetCameraView() {
  currentRotation = { x: 0.28, y: -0.45 };
  targetZoom = 8.5;
  if (cakeGroup) {
    cakeGroup.rotation.x = currentRotation.x;
    cakeGroup.rotation.y = currentRotation.y;
  }
}

function take3DSnapshot() {
  if (!renderer || !scene || !camera) return;

  // Render high quality frame
  renderer.render(scene, camera);
  const dataUrl = renderer.domElement.toDataURL('image/jpeg', 0.92);
  capturedSnapshotUrl.value = dataUrl;

  // Add to inspiration gallery if not already present
  if (!referenceSamples.value.includes(dataUrl)) {
    referenceSamples.value.unshift(dataUrl);
  }

  snapshotFlash.value = true;
  setTimeout(() => {
    snapshotFlash.value = false;
  }, 300);

  toast.success('3D Snapshot captured and attached to order brief! 📸');
}

function destroyThree() {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
    animationFrameId = null;
  }
  if (resizeObserver) {
    resizeObserver.disconnect();
    resizeObserver = null;
  }
  if (renderer) {
    renderer.dispose();
    renderer = null;
  }
  scene = null;
  camera = null;
  cakeGroup = null;
}

// Watchers to update 3D model whenever user changes flavors, size, message, occasion
watch(
  [cakeSize, flavor, filling, isFasting, occasion, message],
  () => {
    if (visualizerMode.value === '3d' && cakeGroup) {
      build3DCake();
    }
  },
  { deep: true }
);

onUnmounted(() => {
  destroyThree();
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
      if (authStore.user?.full_name) {
        customerName.value = authStore.user.full_name;
      } else if (draft.customerName) {
        customerName.value = draft.customerName;
      }
      if (authStore.user?.phone) {
        customerPhone.value = authStore.user.phone;
      } else if (draft.customerPhone) {
        customerPhone.value = draft.customerPhone;
      }
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
    if (authStore.user.full_name) customerName.value = authStore.user.full_name;
    if (authStore.user.phone) customerPhone.value = authStore.user.phone;
  }

  if (!restored && branchStore.selectedBranchId) {
    preparingBranchId.value = branchStore.selectedBranchId;
  }

  // Initialize Three.js interactive 3D studio
  if (visualizerMode.value === '3d') {
    nextTick(() => {
      initThree();
    });
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
    // Automatic snapshot capture if in 3D mode and none captured yet
    if (visualizerMode.value === '3d' && webglSupported.value && renderer && scene && camera) {
      if (!capturedSnapshotUrl.value) {
        renderer.render(scene, camera);
        capturedSnapshotUrl.value = renderer.domElement.toDataURL('image/jpeg', 0.92);
        if (!referenceSamples.value.includes(capturedSnapshotUrl.value)) {
          referenceSamples.value.unshift(capturedSnapshotUrl.value);
        }
      }
    }

    const payload = {
      branch_id: preparingBranchId.value,
      customer_name: customerName.value ? customerName.value.trim() : '',
      customer_phone: customerPhone.value ? customerPhone.value.trim() : '',
      cake_size: cakeSize.value,
      flavor: `${flavor.value} with ${filling.value}${isFasting.value ? ' (100% Ye\'Tsom Fasting Certified)' : ''}`,
      needed_by: neededBy.value,
      design: `${occasion.value} cake. ${description.value}`,
      design_config: {
        mode: visualizerMode.value,
        tierCount: tierCount.value,
        cakeSize: cakeSize.value,
        flavor: flavor.value,
        filling: filling.value,
        occasion: occasion.value,
        isFasting: isFasting.value,
        message: message.value,
        primaryColor: cakeColorStyle.value.primary,
        fillingColor: cakeColorStyle.value.fillingColor,
        description: description.value,
      },
      snapshot_url: capturedSnapshotUrl.value || null,
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
