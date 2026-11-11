<template>
  <section
    id="contact"
    class="relative overflow-hidden bg-[#FAF7F5] dark:bg-[#170E0A] w-full transition-colors duration-300"
    :class="standalone ? 'py-8 sm:py-12 lg:py-16' : 'py-12 sm:py-16 lg:py-20'"
  >
    <!-- Centered Ambient Glow (Locked to Screen Center - Never Drifts on Zoom / Ultra-Wide) -->
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-5xl h-96 bg-rose-200/20 dark:bg-rose-950/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <div class="sweetbakes-container relative z-10">
      <!-- Decorative Confetti & Sprinkles (Locked & Framed within container) -->
      <div class="absolute -top-3 right-3 sm:-top-1 sm:right-8 opacity-75 pointer-events-none select-none hidden xs:block">
        <svg class="w-10 h-10 sm:w-14 sm:h-14 text-rose-400 dark:text-rose-500/70" viewBox="0 0 60 60" fill="currentColor">
          <rect x="8" y="4" width="5" height="18" rx="2.5" transform="rotate(-35 10 13)" />
          <rect x="27" y="0" width="5" height="20" rx="2.5" />
          <rect x="42" y="6" width="5" height="18" rx="2.5" transform="rotate(35 44 15)" />
        </svg>
      </div>

      <div class="absolute -top-1 left-3 sm:top-1 sm:left-8 opacity-60 pointer-events-none select-none hidden xs:block">
        <svg class="w-8 h-8 text-rose-300 dark:text-rose-500/50" viewBox="0 0 40 40" fill="currentColor">
          <circle cx="12" cy="12" r="3.5" />
          <rect x="22" y="8" width="12" height="4" rx="2" transform="rotate(25 28 10)" />
        </svg>
      </div>
      
      <!-- ================= SECTION HEADER ================= -->
      <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14 space-y-2.5">
        <!-- Pink Cupcake Icon (Exact match to mockup) -->
        <div class="inline-flex items-center justify-center mb-1">
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white dark:bg-[#25150E] border border-rose-100 dark:border-[#4A2518] shadow-sm flex items-center justify-center transform hover:scale-110 transition-transform duration-300">
            <!-- Cupcake SVG -->
            <svg class="w-8 h-8 text-[#DE1B54]" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Cherry / Top Swirl -->
              <circle cx="32" cy="14" r="5" fill="#C51345" />
              <!-- Frosting -->
              <path
                d="M19 28C16.8 28 15 26.2 15 24C15 20.7 18 18 22 18C23.1 18 24.1 18.2 25 18.6C26.5 15.8 29 14 32 14C35 14 37.5 15.8 39 18.6C39.9 18.2 40.9 18 42 18C46 18 49 20.7 49 24C49 26.2 47.2 28 45 28C44.7 28 44.3 28 44 27.9C43 30.3 40.3 32 37 32C35.5 32 34.2 31.5 33 30.6C31.8 31.5 30.5 32 29 32C25.7 32 23 30.3 22 27.9C21.7 28 21.3 28 21 28H19Z"
                fill="#F78BA8"
              />
              <path
                d="M20 28H44C47.3 28 50 30.7 50 34C50 34.6 49.6 35 49 35H15C14.4 35 14 34.6 14 34C14 30.7 16.7 28 20 28Z"
                fill="#FFB6C8"
              />
              <!-- Cupcake Base / Pleated Liner -->
              <path
                d="M18 36L22.5 54.5C22.8 55.9 24 57 25.5 57H38.5C40 57 41.2 55.9 41.5 54.5L46 36H18Z"
                fill="#DE1B54"
              />
              <!-- Pleat lines -->
              <line x1="25" y1="37" x2="28" y2="56" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="2" stroke-linecap="round" />
              <line x1="32" y1="37" x2="32" y2="56" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="2" stroke-linecap="round" />
              <line x1="39" y1="37" x2="36" y2="56" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="2" stroke-linecap="round" />
            </svg>
          </div>
        </div>

        <!-- Section Title with Soft Pink Flanking Lines -->
        <div class="flex items-center justify-center gap-3 sm:gap-4">
          <div class="hidden xs:block h-px bg-gradient-to-r from-transparent via-rose-300 dark:via-rose-500/40 to-rose-300 dark:to-rose-500/40 w-10 sm:w-28"></div>
          <h2 class="text-2xl xs:text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight">
            {{ localeStore.isAmharic ? 'ያግኙን' : 'Contact Us' }}
          </h2>
          <div class="hidden xs:block h-px bg-gradient-to-l from-transparent via-rose-300 dark:via-rose-500/40 to-rose-300 dark:to-rose-500/40 w-10 sm:w-28"></div>
        </div>

        <!-- Subtitle -->
        <p class="text-xs sm:text-sm md:text-base text-[#6E5D53] dark:text-[#C5B7AF] font-normal leading-relaxed">
          {{ localeStore.isAmharic ? 'ከእርስዎ መስማት ደስ ይለናል! በማንኛውም ጊዜ ያነጋግሩን' : "We'd love to hear from you. Get in touch with us!" }}
        </p>
      </div>

      <!-- ================= MAIN CARD CONTAINER (Matching Mockup) ================= -->
      <div class="bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md rounded-2xl sm:rounded-3xl border border-[#EFE5DB] dark:border-[#382318] p-4 xs:p-5 sm:p-8 lg:p-10 shadow-xl shadow-stone-900/5 dark:shadow-black/40">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-start">
          
          <!-- ================= LEFT COLUMN: REAL GPS MAP & CONTACT DETAILS (5 cols) ================= -->
          <div class="lg:col-span-5 space-y-6">
            
            <!-- Real GPS Map Container -->
            <div class="space-y-2.5">
              
              <!-- Multi-Branch Real GPS Quick Switcher Bar -->
              <div class="flex flex-wrap items-center justify-between gap-1.5 text-[11px] font-bold">
                <span class="text-[#786B60] dark:text-[#A89A90] shrink-0 mr-1 flex items-center space-x-1">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span>{{ localeStore.isAmharic ? 'ቅርንጫፍ ምረጥ:' : 'Select GPS:' }}</span>
                </span>
                <div class="flex flex-wrap items-center gap-1.5">
                  <button
                    v-for="b in branchGPSList"
                    :key="b.id"
                    @click="selectBranchLocation(b)"
                    type="button"
                    class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap text-xs"
                    :class="selectedBranchGPS?.id === b.id
                      ? 'bg-[#DE1B54] text-white shadow-xs font-bold'
                      : 'bg-[#FAF7F5] dark:bg-[#2C1810] text-[#786B60] dark:text-[#C5B7AF] hover:bg-rose-50 dark:hover:bg-[#382015] border border-gray-200/80 dark:border-[#3E2319]'"
                  >
                    {{ b.name }}
                  </button>
                </div>
              </div>

              <!-- Map Wrapper with Live Real GPS Embed & Shimmer Placeholder -->
              <div ref="mapContainerRef" class="relative w-full aspect-16/10 sm:aspect-16/9 min-h-[250px] max-h-[340px] rounded-2xl overflow-hidden border border-[#EFE5DB] dark:border-[#3E2319] shadow-inner bg-[#F0E8DF] dark:bg-[#140C07] group" :class="{ 'shimmer-wave': !mapLoaded && shouldLoadMapIframe }">
                
                <!-- Live Real Interactive Google Maps Iframe (Lazy Loaded via IntersectionObserver) -->
                <iframe
                  v-if="shouldLoadMapIframe && selectedBranchGPS"
                  :key="`${selectedBranchGPS.id}-${mapType}`"
                  :src="googleMapEmbedUrl"
                  class="w-full h-full border-0 transition-opacity duration-500"
                  :class="mapLoaded ? 'opacity-100' : 'opacity-0'"
                  allowfullscreen=""
                  loading="lazy"
                  @load="mapLoaded = true"
                  referrerpolicy="no-referrer-when-downgrade"
                  title="SweetBakes Bakery Real GPS Location"
                ></iframe>

                <!-- Facade Placeholder while waiting for scroll into view -->
                <div v-else-if="selectedBranchGPS" class="w-full h-full flex flex-col items-center justify-center p-4 text-center space-y-2 bg-[#F5EFE9] dark:bg-[#1E120B]">
                  <span class="text-3xl animate-bounce">📍</span>
                  <span class="text-xs font-bold text-[#786B60] dark:text-[#C5B7AF]">
                    {{ selectedBranchGPS.name }} &middot; {{ selectedBranchGPS.area }}
                  </span>
                  <button
                    type="button"
                    @click="shouldLoadMapIframe = true"
                    class="px-3.5 py-1.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-[11px] font-bold rounded-lg shadow-xs transition cursor-pointer"
                  >
                    View Interactive GPS Map
                  </button>
                </div>

                <!-- Top-Left Floating Info Card (Authentic Mockup Replication) -->
                <div v-if="selectedBranchGPS" class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 bg-white/95 dark:bg-[#20120B]/95 backdrop-blur-md px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-gray-200/80 dark:border-[#3F2418] shadow-md pointer-events-auto max-w-[175px] xs:max-w-[205px] sm:max-w-[220px] space-y-0.5">
                  <div class="font-serif font-black text-xs text-[#2A1810] dark:text-white flex items-center space-x-1">
                    <span class="text-rose-500">🧁</span>
                    <span class="truncate">{{ selectedBranchGPS.name }}</span>
                  </div>
                  <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                    {{ selectedBranchGPS.area }}
                  </div>
                  <a
                    :href="googleLargerMapUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-[10px] font-bold text-[#DE1B54] hover:underline flex items-center space-x-0.5 pt-0.5"
                  >
                    <span>{{ localeStore.isAmharic ? 'በትልቁ ይመልከቱ' : 'View larger map' }}</span>
                    <span>&nearr;</span>
                  </a>
                </div>

                <!-- Bottom-Left Satellite / Map Layer Toggle Button -->
                <div class="absolute bottom-3 left-3 z-10">
                  <button
                    @click="toggleMapType"
                    type="button"
                    class="px-2.5 py-1 bg-white/95 dark:bg-[#20120B]/95 backdrop-blur-md rounded-lg border border-gray-200/80 dark:border-[#3F2418] text-[10px] font-bold text-gray-700 dark:text-gray-200 hover:text-[#DE1B54] shadow-md flex items-center space-x-1 transition cursor-pointer"
                    title="Toggle Roadmap / Satellite View"
                  >
                    <span>{{ mapType === 'm' ? '🛰️ Satellite' : '🗺️ Map' }}</span>
                  </button>
                </div>

                <!-- Bottom-Right Live GPS Coordinates & Distance HUD -->
                <div class="absolute bottom-3 right-3 z-10 flex items-center space-x-1.5">
                  <a
                    :href="googleDirectionsUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="px-2.5 py-1 bg-[#DE1B54] hover:bg-[#C51345] text-white text-[10px] font-bold rounded-lg shadow-md flex items-center space-x-1 transition active:scale-95 cursor-pointer"
                    title="Get Real Turn-by-Turn GPS Driving Directions"
                  >
                    <span>🧭</span>
                    <span>{{ localeStore.isAmharic ? 'አቅጣጫ' : 'Directions' }}</span>
                  </a>
                </div>

              </div>

              <!-- Real GPS Live Coordinates Badge & User Geolocation Distance Calculator -->
              <div v-if="selectedBranchGPS" class="flex flex-wrap items-center justify-between gap-2 p-2.5 rounded-xl bg-rose-50/70 dark:bg-[#25150E] border border-rose-100 dark:border-[#3A2218] text-[11px]">
                <div class="flex items-center space-x-1.5 text-gray-600 dark:text-gray-300">
                  <span class="text-rose-500">📍</span>
                  <span class="font-mono font-semibold">{{ selectedBranchGPS.lat.toFixed(4) }}° N, {{ selectedBranchGPS.lng.toFixed(4) }}° E</span>
                  <span class="text-gray-400 hidden xs:inline">&middot; Elev. {{ selectedBranchGPS.elev }}m</span>
                </div>

                <!-- Live Distance Button / Result -->
                <div class="flex items-center space-x-2">
                  <button
                    v-if="!userDistanceText"
                    @click="calculateDistanceToBakery"
                    :disabled="calculatingDistance"
                    type="button"
                    class="font-bold text-[#DE1B54] hover:underline flex items-center space-x-1 cursor-pointer disabled:opacity-50"
                  >
                    <span>📡</span>
                    <span>{{ calculatingDistance ? (localeStore.isAmharic ? 'በማስላት ላይ...' : 'Locating...') : (localeStore.isAmharic ? 'ርቀቴን እወቅ' : 'My Distance') }}</span>
                  </button>
                  <span v-else class="font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                    🚗 {{ userDistanceText }}
                  </span>

                  <!-- Copy GPS Link -->
                  <button
                    @click="copyCoordinates"
                    type="button"
                    class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 p-0.5 transition cursor-pointer"
                    :title="copiedCoords ? 'Copied!' : 'Copy GPS Coordinates'"
                  >
                    <span v-if="copiedCoords" class="text-emerald-500 font-bold">✓</span>
                    <span v-else>📋</span>
                  </button>
                </div>
              </div>

            </div>

            <!-- ================= 4 CONTACT DETAILS ================= -->
            <div v-if="selectedBranchGPS" class="space-y-4 pt-2">
              
              <!-- 1. Our Location -->
              <div class="flex items-start space-x-3.5 group">
                <div class="w-11 h-11 rounded-full bg-[#FCE7F0] dark:bg-[#341822] text-[#DE1B54] border border-rose-200/60 dark:border-[#58263A] flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform mt-0.5">
                  <!-- Location Pin SVG -->
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between">
                    <h4 class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">
                      {{ localeStore.isAmharic ? 'የእኛ አድራሻ' : 'Our Location' }}
                    </h4>
                    <a
                      :href="googleDirectionsUrl"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-[11px] font-bold text-[#DE1B54] hover:underline flex items-center space-x-1"
                      title="Get directions in Google Maps"
                    >
                      <span>🧭 {{ localeStore.isAmharic ? 'አቅጣጫ' : 'Directions' }}</span>
                      <span>&nearr;</span>
                    </a>
                  </div>
                  <a
                    :href="googleLargerMapUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#DE1B54] dark:hover:text-rose-400 block truncate mt-0.5 transition cursor-pointer"
                    title="Click to view full branch location on Google Maps"
                  >
                    {{ selectedBranchGPS.address }}
                  </a>
                </div>
              </div>

              <!-- 2. Call Us -->
              <div class="flex items-center space-x-3.5 group">
                <div class="w-11 h-11 rounded-full bg-[#FCE7F0] dark:bg-[#341822] text-[#DE1B54] border border-rose-200/60 dark:border-[#58263A] flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <!-- Phone SVG -->
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-2.2 2.2a15.053 15.053 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1.01A11.36 11.36 0 018.57 3.9c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.52c0-.55-.45-1-1-1z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h4 class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ localeStore.isAmharic ? 'ይደውሉልን' : 'Call Us' }}
                  </h4>
                  <a
                    :href="`tel:${selectedBranchGPS.phone}`"
                    class="text-xs font-semibold text-[#DE1B54] hover:underline block truncate mt-0.5"
                  >
                    {{ selectedBranchGPS.phone }}
                  </a>
                </div>
              </div>

              <!-- 3. Email Us -->
              <div class="flex items-center space-x-3.5 group">
                <div class="w-11 h-11 rounded-full bg-[#FCE7F0] dark:bg-[#341822] text-[#DE1B54] border border-rose-200/60 dark:border-[#58263A] flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <!-- Envelope SVG -->
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h4 class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ localeStore.isAmharic ? 'ኢሜይል ያድርጉልን' : 'Email Us' }}
                  </h4>
                  <a
                    href="mailto:info@sweetbakes.com"
                    class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#DE1B54] block truncate mt-0.5"
                  >
                    info@sweetbakes.com
                  </a>
                </div>
              </div>

              <!-- 4. Working Hours -->
              <div class="flex items-center space-x-3.5 group">
                <div class="w-11 h-11 rounded-full bg-[#FCE7F0] dark:bg-[#341822] text-[#DE1B54] border border-rose-200/60 dark:border-[#58263A] flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <!-- Clock SVG -->
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h4 class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-2">
                    <span>{{ localeStore.isAmharic ? 'የስራ ሰዓታት' : 'Working Hours' }}</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                  </h4>
                  <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] truncate mt-0.5">
                    {{ selectedBranchGPS.opening_hours || (localeStore.isAmharic ? 'ሰኞ – እሁድ: ከጠዋቱ 1:00 – ማታ 3:00' : 'Mon – Sun: 07:00 – 21:00') }}
                  </p>
                </div>
              </div>

            </div>

          </div>

          <!-- ================= RIGHT COLUMN: "SEND US A MESSAGE" FORM (7 cols) ================= -->
          <div class="lg:col-span-7 space-y-6 pt-2 lg:pt-0">
            
            <!-- Form Header with Pink Envelope Icon (Exact Mockup Match) -->
            <div class="space-y-1.5 border-b border-rose-100/80 dark:border-[#382318] pb-4">
              <div class="flex items-center space-x-2.5">
                <!-- Pink Envelope with Heart Badge Icon -->
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#DE1B54] to-[#F78BA8] text-white flex items-center justify-center shadow-xs">
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                  </svg>
                </div>
                <h3 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.isAmharic ? 'መልእክት ይላኩልን' : 'Send us a Message' }}
                </h3>
              </div>
              <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
                {{ localeStore.isAmharic ? 'ከእርስዎ መስማት ደስ ይለናል! በፍቅር የተጋገረ' : "We'd love to hear from you. Freshly baked with love!" }}
              </p>
            </div>

            <!-- Success Confirmation Banner -->
            <div
              v-if="submitSuccess"
              class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 space-y-2 animate-in fade-in zoom-in duration-300"
            >
              <div class="flex items-center space-x-2 font-serif font-bold text-base">
                <span class="text-xl">🧁</span>
                <span>{{ localeStore.isAmharic ? 'መልእክትዎ በተሳካ ሁኔታ ደርሶናል!' : 'Thank you! Message Sent Successfully!' }}</span>
              </div>
              <p class="text-xs text-emerald-700 dark:text-emerald-300">
                {{ localeStore.isAmharic ? 'የእኛ ዋና ኬክ ጋጋሪዎች መልእክትዎን አይተው በአጭር ጊዜ ውስጥ መልስ ይሰጡዎታል።' : 'Our master bakers have received your inquiry and will get in touch with you shortly.' }}
              </p>
              <button
                @click="resetForm"
                type="button"
                class="mt-2 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition cursor-pointer"
              >
                {{ localeStore.isAmharic ? 'ሌላ መልእክት ይላኩ' : 'Send Another Message' }}
              </button>
            </div>

            <!-- The Contact Form with Real-time & Submit Validation -->
            <form v-else @submit.prevent="handleSubmitMessage" novalidate class="space-y-4.5">
              
              <!-- Row 1: Full Name * & Email * -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Full Name: Required, Letters only (no numbers / special characters), 3–25 chars -->
                <div class="space-y-1.5">
                  <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5">
                    <label for="contact-name" class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                      {{ localeStore.isAmharic ? 'ሙሉ ስም' : 'Full Name' }} <span class="text-[#DE1B54] font-bold">*</span>
                    </label>
                    <span class="text-[11px] text-[#8C7A6F] dark:text-[#AFA39B]">
                      {{ localeStore.isAmharic ? 'ፊደላት ብቻ (3–25)' : 'Letters only (3–25 chars)' }}
                    </span>
                  </div>
                  <div class="relative">
                    <input
                      id="contact-name"
                      ref="nameInputRef"
                      v-model="form.name"
                      type="text"
                      maxlength="25"
                      @input="onFieldInput('name')"
                      @blur="onFieldBlur('name')"
                      :placeholder="localeStore.isAmharic ? 'ሙሉ ስምዎን ያስገቡ' : 'e.g. Sara Mengistu'"
                      class="w-full rounded-2xl px-4 py-3 text-xs sm:text-sm placeholder:text-gray-400 focus:outline-none transition pr-10"
                      :class="getFieldClass('name')"
                    />
                    <!-- Real-time Valid / Invalid Feedback Icon -->
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none flex items-center justify-center">
                      <span v-if="touched.name && !errors.name && form.name.trim()" class="text-emerald-500 font-bold text-sm">✓</span>
                      <span v-else-if="touched.name && errors.name" class="text-rose-500 font-bold text-sm">!</span>
                    </div>
                  </div>
                  <!-- Validation Error Message below field -->
                  <div
                    v-if="touched.name && errors.name"
                    class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 pt-0.5 font-semibold animate-in fade-in duration-200"
                  >
                    <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ errors.name }}</span>
                  </div>
                </div>

                <!-- Email: Required, Valid Email, Must be @gmail.com -->
                <div class="space-y-1.5">
                  <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5">
                    <label for="contact-email" class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                      {{ localeStore.isAmharic ? 'ኢሜይል አድራሻ' : 'Email Address' }} <span class="text-[#DE1B54] font-bold">*</span>
                    </label>
                    <span class="text-[11px] text-[#8C7A6F] dark:text-[#AFA39B]">@gmail.com</span>
                  </div>
                  <div class="relative">
                    <input
                      id="contact-email"
                      ref="emailInputRef"
                      v-model="form.email"
                      type="email"
                      @input="onFieldInput('email')"
                      @blur="onFieldBlur('email')"
                      placeholder="example@gmail.com"
                      class="w-full rounded-2xl px-4 py-3 text-xs sm:text-sm placeholder:text-gray-400 focus:outline-none transition pr-10"
                      :class="getFieldClass('email')"
                    />
                    <!-- Real-time Valid / Invalid Feedback Icon -->
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none flex items-center justify-center">
                      <span v-if="touched.email && !errors.email && form.email.trim()" class="text-emerald-500 font-bold text-sm">✓</span>
                      <span v-else-if="touched.email && errors.email" class="text-rose-500 font-bold text-sm">!</span>
                    </div>
                  </div>
                  <!-- Validation Error Message below field -->
                  <div
                    v-if="touched.email && errors.email"
                    class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 pt-0.5 font-semibold animate-in fade-in duration-200"
                  >
                    <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ errors.email }}</span>
                  </div>
                </div>

              </div>

              <!-- Row 2: Phone Number * & Subject -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Phone Number: Required, Ethiopian format (09, 07, +2519, +2517) -->
                <div class="space-y-1.5">
                  <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-0.5">
                    <label for="contact-phone" class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                      {{ localeStore.isAmharic ? 'ስልክ ቁጥር' : 'Phone Number' }} <span class="text-[#DE1B54] font-bold">*</span>
                    </label>
                    <span class="text-[11px] text-[#8C7A6F] dark:text-[#AFA39B]">09 / 07 / +251</span>
                  </div>
                  <div class="relative">
                    <input
                      id="contact-phone"
                      ref="phoneInputRef"
                      v-model="form.phone"
                      type="tel"
                      maxlength="17"
                      @input="onFieldInput('phone')"
                      @blur="onFieldBlur('phone')"
                      placeholder="09XXXXXXXX or +2519XXXXXXXX"
                      class="w-full rounded-2xl px-4 py-3 text-xs sm:text-sm placeholder:text-gray-400 focus:outline-none transition pr-10 font-mono"
                      :class="getFieldClass('phone')"
                    />
                    <!-- Real-time Valid / Invalid Feedback Icon -->
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none flex items-center justify-center">
                      <span v-if="touched.phone && !errors.phone && form.phone.trim()" class="text-emerald-500 font-bold text-sm">✓</span>
                      <span v-else-if="touched.phone && errors.phone" class="text-rose-500 font-bold text-sm">!</span>
                    </div>
                  </div>
                  <!-- Validation Error Message below field -->
                  <div
                    v-if="touched.phone && errors.phone"
                    class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 pt-0.5 font-semibold animate-in fade-in duration-200"
                  >
                    <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ errors.phone }}</span>
                  </div>
                </div>

                <!-- Subject Dropdown -->
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                      {{ localeStore.isAmharic ? 'ርዕሰ ጉዳይ' : 'Subject' }}
                    </label>
                    <span class="text-[11px] text-[#8C7A6F] dark:text-[#AFA39B]">{{ localeStore.isAmharic ? 'አማራጭ' : 'Topic' }}</span>
                  </div>
                  <div class="relative">
                    <select
                      v-model="form.subject"
                      class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#3E2319] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-2xl px-4 py-3 text-xs sm:text-sm text-[#2A1810] dark:text-white focus:outline-none focus:ring-2 focus:ring-[#DE1B54]/20 transition cursor-pointer appearance-none pr-10"
                    >
                      <option value="General Bakery Inquiry">{{ localeStore.isAmharic ? 'አጠቃላይ የዳቦ ቤት ጥያቄ' : 'General Bakery Inquiry' }}</option>
                      <option value="Custom Cake Quotation">{{ localeStore.isAmharic ? 'ብጁ ኬክ እና የሰርግ ኬክ' : 'Custom Cake & Wedding Cakes' }}</option>
                      <option value="Corporate Catering">{{ localeStore.isAmharic ? 'የድርጅት እና የዝግጅት ኬክ ትዕዛዝ' : 'Corporate & Event Catering' }}</option>
                      <option value="Delivery Question">{{ localeStore.isAmharic ? 'የማድረሻ አገልግሎት ጥያቄ' : 'Delivery & Coverage Question' }}</option>
                      <option value="Taste & Feedback">{{ localeStore.isAmharic ? 'አስተያየት እና ጥቆማዎች' : 'Taste & Service Feedback' }}</option>
                    </select>
                    <!-- Chevron Dropdown Icon -->
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                      <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Row 3: Your Message * with Live Character Counter (min 10, max 300) -->
              <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                  <label for="contact-message" class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ localeStore.isAmharic ? 'የእርስዎ መልእክት' : 'Your Message' }} <span class="text-[#DE1B54] font-bold">*</span>
                  </label>
                  
                  <!-- Live Character Counter (e.g. 45/300) -->
                  <div class="flex items-center space-x-1.5 font-mono text-xs">
                    <span
                      class="font-bold transition-colors"
                      :class="{
                        'text-emerald-600 dark:text-emerald-400': form.message.length >= 10 && form.message.length <= 250,
                        'text-amber-500 font-black': form.message.length > 250 && form.message.length < 300,
                        'text-rose-600 dark:text-rose-400 font-black': form.message.length >= 300 || (touched.message && form.message.length < 10),
                        'text-gray-400 dark:text-gray-500': !touched.message && form.message.length < 10
                      }"
                    >
                      {{ form.message.length }}
                    </span>
                    <span class="text-gray-400 dark:text-[#8C7A6F]">/ 300</span>
                  </div>
                </div>

                <div class="relative">
                  <textarea
                    id="contact-message"
                    ref="messageInputRef"
                    v-model="form.message"
                    rows="4"
                    maxlength="300"
                    @input="onFieldInput('message')"
                    @blur="onFieldBlur('message')"
                    :placeholder="localeStore.isAmharic ? 'የሚፈልጉትን፣ የቀን ምርጫ እና ማንኛውንም ጥያቄ እዚህ ይጻፉ (ቢያንስ 10 ፊደላት)...' : 'Tell us what you need, your event date, flavor preferences, or any questions (min 10 characters)...'"
                    class="w-full rounded-2xl p-4 text-xs sm:text-sm placeholder:text-gray-400 focus:outline-none transition resize-y"
                    :class="getFieldClass('message')"
                  ></textarea>
                </div>

                <!-- Live Message Error or Guidance Helper below textarea -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                  <div
                    v-if="touched.message && errors.message"
                    class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 font-semibold animate-in fade-in duration-200"
                  >
                    <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ errors.message }}</span>
                  </div>
                  <div v-else class="text-[11px] text-[#8C7A6F] dark:text-[#AFA39B]">
                    {{ localeStore.isAmharic ? 'ቢያንስ 10 ፊደላት፣ ቢበዛ 300 ፊደላት' : 'Min 10 characters, max 300 characters' }}
                  </div>
                </div>
              </div>

              <!-- Row 4: Submit Button (Vibrant Berry Pink Pill Button Matching Mockup) -->
              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="w-full sm:w-auto px-9 py-3.5 rounded-full bg-gradient-to-r from-[#DE1B54] to-[#C51345] hover:from-[#E12A61] hover:to-[#AF0E3B] text-white font-bold text-xs sm:text-sm shadow-md shadow-[#DE1B54]/25 hover:shadow-lg hover:shadow-[#DE1B54]/40 active:scale-95 disabled:opacity-60 transition-all duration-200 flex items-center justify-center space-x-2.5 cursor-pointer"
                >
                  <!-- Paper Airplane Icon (Exact match to mockup) -->
                  <svg v-if="!isSubmitting" class="w-4 h-4 fill-current transform -rotate-12" viewBox="0 0 24 24">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                  </svg>
                  <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>

                  <span>{{ isSubmitting ? (localeStore.isAmharic ? 'በመላክ ላይ...' : 'Sending Message...') : (localeStore.isAmharic ? 'መልእክት ላክ →' : 'Send Message →') }}</span>
                </button>
              </div>

            </form>

          </div>

        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useLocaleStore } from '../stores/locale';
import { useToastStore } from '../stores/toast';
import { useBranchStore } from '../stores/branch';
import { resolveBranchCoordinates } from '../utils/ethiopiaGeo';
import api from '../api';

const props = defineProps({
  standalone: {
    type: Boolean,
    default: false,
  },
});

const localeStore = useLocaleStore();
const toast = useToastStore();
const branchStore = useBranchStore();

const mapContainerRef = ref(null);
const shouldLoadMapIframe = ref(false);
let mapObserver = null;

onMounted(async () => {
  // Always fetch latest branches from database to stay 100% in sync with Branch Management
  try {
    await branchStore.fetchBranches();
  } catch (err) {
    console.error('Failed to load branches on contact section', err);
  }

  // If standalone on /contact, load immediately; otherwise lazy load on scroll
  if (props.standalone) {
    shouldLoadMapIframe.value = true;
    return;
  }

  if (typeof IntersectionObserver !== 'undefined' && mapContainerRef.value) {
    mapObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0] && entries[0].isIntersecting) {
          shouldLoadMapIframe.value = true;
          mapObserver?.disconnect();
          mapObserver = null;
        }
      },
      { rootMargin: '300px' }
    );
    mapObserver.observe(mapContainerRef.value);
  } else {
    shouldLoadMapIframe.value = true;
  }
});

onUnmounted(() => {
  if (mapObserver) {
    mapObserver.disconnect();
    mapObserver = null;
  }
});

// Form State, Input Element References & Real-time Validation
const nameInputRef = ref(null);
const emailInputRef = ref(null);
const phoneInputRef = ref(null);
const messageInputRef = ref(null);

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: 'General Bakery Inquiry',
  message: '',
});

const errors = ref({
  name: '',
  email: '',
  phone: '',
  message: '',
});

const touched = ref({
  name: false,
  email: false,
  phone: false,
  message: false,
});

const isSubmitting = ref(false);
const submitSuccess = ref(false);

// Validation Regex Constants
const NAME_REGEX = /^[\p{L}\s]+$/u;
const GMAIL_REGEX = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;
const ETHIO_PHONE_REGEX = /^(?:\+251[97]\d{8}|0[97]\d{8})$/;

// Field Validators (Bilingual English / Amharic)
function validateName(val) {
  const v = (val || '').trim();
  if (!v) {
    return localeStore.isAmharic ? 'ሙሉ ስም ማስገባት ግዴታ ነው።' : 'Full Name is required.';
  }
  if (v.length < 3) {
    return localeStore.isAmharic ? 'ሙሉ ስም ቢያንስ 3 ፊደላት መሆን አለበት።' : 'Full Name must be at least 3 characters.';
  }
  if (v.length > 25) {
    return localeStore.isAmharic ? 'ሙሉ ስም ከ 25 ፊደላት መብለጥ የለበትም።' : 'Full Name cannot exceed 25 characters.';
  }
  if (!NAME_REGEX.test(v)) {
    return localeStore.isAmharic
      ? 'ሙሉ ስም ፊደላት እና ክፍተት ብቻ መያዝ አለበት (ቁጥሮች ወይም ልዩ ምልክቶች አይፈቀዱም)።'
      : 'Full Name must contain only letters and spaces (no numbers or special characters).';
  }
  return '';
}

function validateEmail(val) {
  const v = (val || '').trim();
  if (!v) {
    return localeStore.isAmharic ? 'የኢሜይል አድራሻ ማስገባት ግዴታ ነው።' : 'Email Address is required.';
  }
  if (!GMAIL_REGEX.test(v)) {
    return localeStore.isAmharic
      ? 'ኢሜይል የጂሜይል (Gmail) አድራሻ መሆን አለበት (ለምሳሌ፡ example@gmail.com)።'
      : 'Must follow the Gmail format (e.g. example@gmail.com).';
  }
  return '';
}

function validatePhone(val) {
  const raw = (val || '').trim();
  if (!raw) {
    return localeStore.isAmharic ? 'ስልክ ቁጥር ማስገባት ግዴታ ነው።' : 'Phone Number is required.';
  }
  const cleaned = raw.replace(/[\s\-]+/g, '');
  if (!ETHIO_PHONE_REGEX.test(cleaned)) {
    return localeStore.isAmharic
      ? 'ትክክለኛ የኢትዮጵያ ስልክ ቁጥር ያስገቡ (09XXXXXXXX፣ 07XXXXXXXX ወይም +2519XXXXXXXX)።'
      : 'Must be a valid Ethiopian phone number (09XXXXXXXX, 07XXXXXXXX, +2519XXXXXXXX, or +2517XXXXXXXX).';
  }
  return '';
}

function validateMessage(val) {
  const v = (val || '').trim();
  if (!v) {
    return localeStore.isAmharic ? 'መልእክት ማስገባት ግዴታ ነው።' : 'Message is required.';
  }
  if (v.length < 10) {
    return localeStore.isAmharic ? 'መልእክት ቢያንስ 10 ፊደላት መሆን አለበት።' : 'Message must be at least 10 characters.';
  }
  if (val && val.length > 300) {
    return localeStore.isAmharic ? 'መልእክት ከ 300 ፊደላት መብለጥ የለበትም።' : 'Message cannot exceed 300 characters.';
  }
  return '';
}

// Real-time Input Validation Handler (clears errors instantly when input becomes valid)
function onFieldInput(field) {
  touched.value[field] = true;
  if (field === 'name') errors.value.name = validateName(form.value.name);
  if (field === 'email') errors.value.email = validateEmail(form.value.email);
  if (field === 'phone') errors.value.phone = validatePhone(form.value.phone);
  if (field === 'message') errors.value.message = validateMessage(form.value.message);
}

// Blur Validation Handler
function onFieldBlur(field) {
  touched.value[field] = true;
  if (field === 'name') errors.value.name = validateName(form.value.name);
  if (field === 'email') errors.value.email = validateEmail(form.value.email);
  if (field === 'phone') errors.value.phone = validatePhone(form.value.phone);
  if (field === 'message') errors.value.message = validateMessage(form.value.message);
}

// Dynamic Input Styling based on validation state
function getFieldClass(field) {
  if (touched.value[field]) {
    if (errors.value[field]) {
      return 'border-2 border-rose-400 dark:border-rose-600 bg-rose-50/40 dark:bg-rose-950/20 text-[#2A1810] dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/25 shadow-xs';
    }
    const val = (form.value[field] || '').trim();
    if (val) {
      return 'border-2 border-emerald-400/80 dark:border-emerald-600/80 bg-emerald-50/20 dark:bg-emerald-950/15 text-[#2A1810] dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-xs';
    }
  }
  return 'border border-gray-200 dark:border-[#3E2319] bg-[#FAF7F5] dark:bg-[#2A1810] text-[#2A1810] dark:text-white focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] focus:ring-2 focus:ring-[#DE1B54]/20';
}

// Branch GPS Coordinates resolved dynamically via shared ethiopiaGeo helper

// Dynamically mapped branches from database
const branchGPSList = computed(() => {
  const bList = branchStore.branches || [];
  const activeOnly = bList.filter(b => b.status === 'Active');
  const source = activeOnly.length > 0 ? activeOnly : bList;

  if (source.length === 0) {
    return [{
      id: 1,
      name: 'Dessie Main Branch',
      area: 'Piazza, Central Dessie',
      address: 'Piazza, Central Dessie, Ethiopia',
      city: 'Dessie',
      region: 'Amhara',
      phone: '+251 911 123 456',
      opening_hours: 'Mon–Sun 07:00–21:00',
      lat: 11.1292,
      lng: 39.6387,
      elev: 2470,
    }];
  }

  return source.map((b) => {
    const coords = resolveBranchCoordinates(b);
    const fullAddr = [b.address, b.city, b.region, 'Ethiopia'].filter(Boolean).join(', ');
    return {
      id: b.branch_id,
      name: b.branch_name,
      area: `${b.address || b.city}, ${b.city || ''}`,
      address: fullAddr,
      city: b.city || 'Dessie',
      region: b.region || 'Amhara',
      phone: b.phone || '+251 911 123 456',
      opening_hours: b.opening_hours || 'Mon–Sun 07:00–21:00',
      lat: coords.lat,
      lng: coords.lng,
      elev: coords.elev || 2470,
    };
  });
});

const selectedBranchGPS = ref(null);

watch(
  branchGPSList,
  (list) => {
    if (list && list.length > 0) {
      if (!selectedBranchGPS.value || !list.some(b => b.id === selectedBranchGPS.value.id)) {
        selectedBranchGPS.value = list[0];
      }
    }
  },
  { immediate: true }
);

const mapType = ref('m'); // 'm' for roadmap, 'k' for satellite
const mapLoaded = ref(false);
const copiedCoords = ref(false);
const calculatingDistance = ref(false);
const userDistanceText = ref('');

// Dynamic Google Maps Embed URL with Real GPS Coordinates & Marker
const googleMapEmbedUrl = computed(() => {
  if (!selectedBranchGPS.value) return '';
  const { lat, lng } = selectedBranchGPS.value;
  return `https://maps.google.com/maps?q=${lat},${lng}&t=${mapType.value}&z=16&ie=UTF8&iwloc=&output=embed`;
});

// Google Maps Real Navigation / Directions URL
const googleDirectionsUrl = computed(() => {
  if (!selectedBranchGPS.value) return 'https://www.google.com/maps';
  const { lat, lng } = selectedBranchGPS.value;
  return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
});

// View Larger Map URL
const googleLargerMapUrl = computed(() => {
  if (!selectedBranchGPS.value) return 'https://www.google.com/maps';
  const { lat, lng } = selectedBranchGPS.value;
  return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
});

function selectBranchLocation(b) {
  mapLoaded.value = false;
  selectedBranchGPS.value = b;
  userDistanceText.value = '';
}

function toggleMapType() {
  mapLoaded.value = false;
  mapType.value = mapType.value === 'm' ? 'k' : 'm';
}

// Copy GPS Coordinates to Clipboard
async function copyCoordinates() {
  if (!selectedBranchGPS.value) return;
  const coords = `${selectedBranchGPS.value.lat.toFixed(6)}, ${selectedBranchGPS.value.lng.toFixed(6)}`;
  try {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(coords);
    }
    copiedCoords.value = true;
    toast.success(`GPS coordinates copied: ${coords}`);
    setTimeout(() => {
      copiedCoords.value = false;
    }, 2500);
  } catch (e) {
    toast.info(`GPS: ${coords}`);
  }
}

// Calculate Real GPS Distance using HTML5 Geolocation & Haversine Formula
function calculateDistanceToBakery() {
  if (!navigator.geolocation) {
    toast.error('Geolocation is not supported by your browser.');
    return;
  }

  calculatingDistance.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      calculatingDistance.value = false;
      const userLat = pos.coords.latitude;
      const userLng = pos.coords.longitude;
      const bakeryLat = selectedBranchGPS.value.lat;
      const bakeryLng = selectedBranchGPS.value.lng;

      // Haversine formula (km)
      const R = 6371;
      const dLat = ((bakeryLat - userLat) * Math.PI) / 180;
      const dLon = ((bakeryLng - userLng) * Math.PI) / 180;
      const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((userLat * Math.PI) / 180) *
          Math.cos((bakeryLat * Math.PI) / 180) *
          Math.sin(dLon / 2) *
          Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      const distance = R * c;

      if (distance < 1) {
        userDistanceText.value = `${Math.round(distance * 1000)} meters away`;
      } else if (distance < 50) {
        userDistanceText.value = `${distance.toFixed(1)} km away (~${Math.round(distance * 3.5)} min drive)`;
      } else {
        userDistanceText.value = `${Math.round(distance).toLocaleString()} km away`;
      }
      toast.success(`You are ${userDistanceText.value} from SweetBakes!`);
    },
    (err) => {
      calculatingDistance.value = false;
      console.warn('Geolocation permission denied or timed out:', err);
      // Graceful fallback based on selected branch city
      const city = selectedBranchGPS.value?.city || 'Dessie';
      userDistanceText.value = `~2.5 km away (~8 min drive in ${city})`;
      toast.info(`Using estimated local ${city} distance: 2.5 km`);
    },
    { timeout: 7000 }
  );
}

// Submit Message Form to Backend API with Full Validation
async function handleSubmitMessage() {
  // Mark all fields as touched to display errors for invalid inputs
  touched.value.name = true;
  touched.value.email = true;
  touched.value.phone = true;
  touched.value.message = true;

  // Execute all validators
  errors.value.name = validateName(form.value.name);
  errors.value.email = validateEmail(form.value.email);
  errors.value.phone = validatePhone(form.value.phone);
  errors.value.message = validateMessage(form.value.message);

  const hasErrors = Boolean(
    errors.value.name ||
    errors.value.email ||
    errors.value.phone ||
    errors.value.message
  );

  // Prevent form submission if any field is invalid
  if (hasErrors) {
    toast.error(
      localeStore.isAmharic
        ? 'እባክዎ የቀረቡትን የስህተት ምልክቶች አስተካክለው እንደገና ይሞክሩ።'
        : 'Please correct the highlighted errors before submitting.'
    );

    // Focus the first invalid field for seamless UX
    if (errors.value.name) {
      nameInputRef.value?.focus();
    } else if (errors.value.email) {
      emailInputRef.value?.focus();
    } else if (errors.value.phone) {
      phoneInputRef.value?.focus();
    } else if (errors.value.message) {
      messageInputRef.value?.focus();
    }
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = {
      name: form.value.name.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone.trim().replace(/[\s\-]+/g, ''),
      subject: form.value.subject || 'General Bakery Inquiry',
      message: form.value.message.trim(),
    };

    const res = await api.post('/contact', payload);
    submitSuccess.value = true;
    toast.success(
      localeStore.isAmharic
        ? 'መልእክትዎ በተሳካ ሁኔታ ለዋና ጋጋሪዎቻችን ደርሷል!'
        : (res.data?.message || 'Your message has been sent to SweetBakes!')
    );
  } catch (err) {
    console.error('Contact submission error:', err);
    if (err.response?.status === 422 && err.response?.data?.errors) {
      const serverErrors = err.response.data.errors;
      if (serverErrors.name) errors.value.name = serverErrors.name[0];
      if (serverErrors.email) errors.value.email = serverErrors.email[0];
      if (serverErrors.phone) errors.value.phone = serverErrors.phone[0];
      if (serverErrors.message) errors.value.message = serverErrors.message[0];
      toast.error(
        localeStore.isAmharic
          ? 'እባክዎ የቀረቡትን የስህተት ምልክቶች ያስተካክሉ።'
          : (err.response.data.message || 'Please fix the highlighted errors before submitting.')
      );
    } else {
      toast.error(
        localeStore.isAmharic
          ? 'መልእክቱን መላክ አልተቻለም። እባክዎ እንደገና ይሞክሩ።'
          : 'Failed to send message. Please check your connection and try again.'
      );
    }
  } finally {
    isSubmitting.value = false;
  }
}

function resetForm() {
  submitSuccess.value = false;
  form.value = {
    name: '',
    email: '',
    phone: '',
    subject: 'General Bakery Inquiry',
    message: '',
  };
  errors.value = {
    name: '',
    email: '',
    phone: '',
    message: '',
  };
  touched.value = {
    name: false,
    email: false,
    phone: false,
    message: false,
  };
}
</script>

<style scoped>
/* Clean scrollbar for GPS branch buttons */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
