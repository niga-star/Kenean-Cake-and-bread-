<template>
  <div class="space-y-12 sm:space-y-16 md:space-y-20 selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-8 sm:pb-12 w-full">
    <!-- 1. HERO SECTION: Full-Bleed 100% Cover Bakery Background (Desktop) + Tailored Mobile/Tablet Editorial Showcase -->
    <section class="relative overflow-hidden lg:min-h-[620px] xl:min-h-[680px] flex items-center w-full">
      <!-- Full-Bleed Background Image Layer -->
      <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none" :class="{ 'shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E]': !heroImageLoaded }">
        <img
          :src="currentBanner?.image_url || businessInfo?.hero_image_url || '/mockup/hero_banner_hq.jpg'"
          :alt="heroTitle1"
          class="w-full h-full object-cover object-[70%_35%] lg:object-[center_30%] transition-all duration-700"
          :class="heroImageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-102'"
          loading="eager"
          fetchpriority="high"
          decoding="async"
          @load="heroImageLoaded = true"
        />

        <!-- Mobile & Tablet High-Readability Frosted Veil (Prevents cake artwork from colliding with centered/left copy) -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#FAF7F5]/92 via-[#FAF7F5]/86 to-[#FAF7F5]/95 dark:from-[#170E0A]/92 dark:via-[#170E0A]/86 dark:to-[#170E0A]/95 lg:hidden pointer-events-none"></div>

        <!-- Desktop Left-Half Gradient Scrim (Leaves right cake 100% unobstructed on 1024px+) -->
        <div class="hidden lg:block absolute inset-0 bg-gradient-to-r from-[#FAF7F5]/95 via-[#FAF7F5]/75 via-45% to-transparent dark:from-[#170E0A]/95 dark:via-[#170E0A]/75 dark:via-45% dark:to-transparent pointer-events-none"></div>

        <!-- Floating Luxury Artisan Stamp Badge on Image (Desktop) -->
        <div class="animate-hero-stamp hidden xl:flex absolute bottom-12 right-10 bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/60 dark:border-[#3E2319] shadow-xl items-center space-x-3 pointer-events-auto z-10">
          <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-[#331A24] text-[#DE1B54] flex items-center justify-center text-xl shrink-0">
            🍰
          </div>
          <div class="space-y-0.5">
            <div class="text-xs font-serif font-black text-[#2A1810] dark:text-white leading-tight">Masterpiece Cakes</div>
            <div class="text-[10px] text-amber-500 font-bold tracking-tight">★ 4.9 &middot; Handcrafted Daily</div>
          </div>
        </div>
      </div>

      <!-- Hero Foreground Grid (Single-Column Mobile -> 2-Col Tablet -> Left Editorial Column on Desktop) -->
      <div class="sweetbakes-container pt-6 pb-12 sm:pt-10 sm:pb-16 lg:py-24 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-12 lg:block gap-6 sm:gap-8 items-center">
          <div class="md:col-span-7 lg:max-w-xl xl:max-w-2xl space-y-3.5 sm:space-y-4 lg:space-y-5 text-center md:text-left mx-auto lg:mx-0">
            <!-- Compact Artisan Pill Badge with Amber Star and Red Heart -->
            <div class="animate-hero-badge inline-flex items-center space-x-1.5 sm:space-x-2 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md border border-rose-300/80 dark:border-[#58263A] text-[#DE1B54] text-[11px] xs:text-xs sm:text-sm font-bold tracking-wide shadow-xs max-w-full">
              <span class="text-amber-500 font-black shrink-0">★</span>
              <span class="font-bold tracking-wide truncate">{{ heroSubtitle }}</span>
              <span class="text-[#DE1B54] font-black shrink-0">♥</span>
            </div>

            <!-- Large Bold Serif Title with Hand-script "Love ♡" in Signature SweetBakes Red -->
            <h1 class="animate-hero-title text-fluid-hero font-serif font-black text-[#1A0C06] dark:text-white tracking-tight leading-[1.14] drop-shadow-[0_2px_4px_rgba(255,255,255,0.95)] dark:drop-shadow-[0_2px_12px_rgba(0,0,0,0.9)]">
              <template v-if="localeStore.isAmharic">
                <span>{{ heroTitle1 }}</span>
                <span class="inline-block font-logo text-[#DE1B54] dark:text-rose-400 font-normal italic ml-2 drop-shadow-sm">♡</span>
                <span class="block text-fluid-hero-sub font-serif font-black text-[#DE1B54] dark:text-rose-400 mt-1 min-h-[2.6em] min-[480px]:min-h-[1.35em] lg:min-h-[1.25em] drop-shadow-[0_2px_4px_rgba(255,255,255,0.95)] dark:drop-shadow-[0_2px_12px_rgba(0,0,0,0.9)]">
                  <span>{{ typedText || heroTitle2 }}</span>
                  <span class="typewriter-cursor text-[#DE1B54] dark:text-rose-400 ml-1 font-mono font-light select-none">|</span>
                </span>
              </template>
              <template v-else>
                <span>{{ heroTitle1Parts.prefix }}</span>
                <span v-if="heroTitle1Parts.highlight" class="inline-block font-logo text-[#DE1B54] dark:text-rose-400 font-normal italic ml-2 text-[1.12em] drop-shadow-sm">{{ heroTitle1Parts.highlight }}</span>
                <span class="block text-fluid-hero-sub font-serif font-black text-[#DE1B54] dark:text-rose-400 mt-1 min-h-[2.5em] min-[480px]:min-h-[1.35em] lg:min-h-[1.25em] drop-shadow-[0_2px_4px_rgba(255,255,255,0.95)] dark:drop-shadow-[0_2px_12px_rgba(0,0,0,0.9)]">
                  <span>{{ typedText || heroTitle2 }}</span>
                  <span class="typewriter-cursor text-[#DE1B54] dark:text-rose-400 ml-1 font-mono font-light select-none">|</span>
                </span>
              </template>
            </h1>

            <!-- Body Description: Crisp & Readable on Mobile and Desktop -->
            <p class="animate-hero-desc text-sm xs:text-[15px] sm:text-lg md:text-xl text-[#1C0F0A] dark:text-[#F3EDE6] font-medium leading-relaxed max-w-md sm:max-w-lg lg:max-w-xl mx-auto md:mx-0 drop-shadow-[0_1px_3px_rgba(255,255,255,0.9)] dark:drop-shadow-[0_1px_8px_rgba(0,0,0,0.9)]">
              {{ heroDesc }}
            </p>

            <!-- CTA Buttons: Clean Stacked Pills on Mobile (320-499px) & Side-by-Side on Larger Screens -->
            <div class="animate-hero-cta flex flex-col min-[500px]:flex-row items-stretch min-[500px]:items-center justify-center md:justify-start gap-2.5 sm:gap-4 pt-1 sm:pt-2 w-full max-w-[340px] min-[500px]:max-w-none mx-auto md:mx-0">
              <!-- Order Online: Signature SweetBakes Red Pill Button -->
              <router-link
                :to="currentBanner?.link_url || businessInfo?.hero_button_link || '/shop'"
                class="w-full min-[500px]:w-auto px-6 sm:px-9 py-3.5 sm:py-4 min-h-[48px] bg-[#DE1B54] hover:bg-[#C51345] text-white font-black text-sm sm:text-base tracking-wide rounded-full shadow-lg hover:shadow-xl transition-all hover:scale-105 active:scale-95 flex items-center justify-center space-x-2.5 cursor-pointer whitespace-nowrap"
              >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>{{ heroButtonText }}</span>
                <span class="text-base sm:text-lg font-black">&rarr;</span>
              </router-link>

              <!-- Custom Studio Button -->
              <router-link
                to="/custom-cake"
                class="w-full min-[500px]:w-auto px-6 sm:px-8 py-3.5 sm:py-4 min-h-[48px] bg-white hover:bg-rose-50 dark:bg-[#1E120B] dark:hover:bg-[#2A1710] text-[#1C0F0A] dark:text-[#FAF3EE] hover:text-[#DE1B54] border-2 border-[#1C0F0A]/15 hover:border-[#DE1B54] font-black text-sm sm:text-base rounded-full shadow-md transition-all flex items-center justify-center space-x-2 cursor-pointer whitespace-nowrap"
              >
                <span class="text-base sm:text-lg">🎂</span>
                <span>{{ localeStore.isAmharic ? 'ልዩ ኬክ ስቱዲዮ' : 'Custom Studio' }}</span>
              </router-link>
            </div>

            <!-- Micro Trust Chips -->
            <div class="animate-hero-chips pt-1 sm:pt-2 flex flex-wrap items-center justify-center md:justify-start gap-2 sm:gap-3 text-[11px] xs:text-xs sm:text-sm text-[#1C0F0A] dark:text-[#FAF3EE]">
              <span class="inline-flex items-center space-x-1.5 px-3 sm:px-3.5 py-1.5 rounded-full bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md border border-stone-200/90 dark:border-[#382318] shadow-xs whitespace-nowrap">
                <span>🚚</span>
                <span class="font-bold text-[#1C0F0A] dark:text-[#F0E6DE]">{{ localeStore.isAmharic ? 'ፈጣን ማድረሻ' : 'Fast Delivery' }}</span>
              </span>
              <span class="inline-flex items-center space-x-1.5 px-3 sm:px-3.5 py-1.5 rounded-full bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md border border-stone-200/90 dark:border-[#382318] shadow-xs whitespace-nowrap">
                <span class="text-amber-500 font-black">★</span>
                <span class="font-black text-[#1C0F0A] dark:text-white">4.9</span>
                <span class="text-[#3A2218] dark:text-[#C5B7AF] font-bold">({{ localeStore.isAmharic ? '12k+ አስተያየቶች' : '12k+ reviews' }})</span>
              </span>
              <span class="inline-flex items-center space-x-1.5 px-3 sm:px-3.5 py-1.5 rounded-full bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md border border-stone-200/90 dark:border-[#382318] shadow-xs whitespace-nowrap">
                <span>🌿</span>
                <span class="font-bold text-[#1C0F0A] dark:text-[#F0E6DE]">{{ localeStore.isAmharic ? '100% ትኩስ ጥራት' : '100% Fresh' }}</span>
              </span>
            </div>
          </div>

          <!-- Dedicated Mobile & Tablet Framed Bakery Showcase Card (Hidden on 1024px+ Desktop) -->
          <div class="md:col-span-5 lg:hidden w-full max-w-md md:max-w-none mx-auto">
            <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl border-2 border-white/90 dark:border-[#3E2319] aspect-[16/10] sm:aspect-[4/3] bg-[#F0E8DF] dark:bg-[#25150E]">
              <img
                :src="currentBanner?.image_url || businessInfo?.hero_image_url || '/mockup/hero_banner_hq.jpg'"
                :alt="heroTitle1"
                class="w-full h-full object-cover object-[72%_42%]"
                loading="eager"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent pointer-events-none"></div>
              <div class="absolute bottom-2.5 left-2.5 sm:bottom-3 sm:left-3 bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/70 dark:border-[#3E2319] shadow-md flex items-center space-x-2">
                <span class="text-base leading-none">🍰</span>
                <div class="leading-tight">
                  <div class="text-[11px] font-serif font-black text-[#2A1810] dark:text-white">Masterpiece Cakes</div>
                  <div class="text-[10px] text-amber-500 font-bold">★ 4.9 &middot; Handcrafted Daily</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Organic Wave Divider (Matching Reference Image with Signature Red Accent Curve) -->
      <div class="absolute inset-x-0 bottom-0 pointer-events-none overflow-hidden leading-none z-10 w-full">
        <svg class="relative block w-full h-7 sm:h-12 md:h-16 lg:h-20" viewBox="0 0 1440 120" fill="none" preserveAspectRatio="none">
          <!-- Red Accent Wave Layer -->
          <path d="M0,64L60,58.7C120,53,240,43,360,48C480,53,600,75,720,80C840,85,960,75,1080,64C1200,53,1320,43,1380,37.3L1440,32L1440,120L1380,120C1320,120,1200,120,1080,120C960,120,840,120,720,120C600,120,480,120,360,120C240,120,120,120,60,120L0,120Z" class="fill-[#DE1B54]/20 dark:fill-[#DE1B54]/30"></path>
          <!-- Background Canvas Wave -->
          <path d="M0,76L60,71.3C120,66,240,55,360,60.7C480,66,600,87,720,87.3C840,87,960,66,1080,60.7C1200,55,1320,66,1380,71.3L1440,76L1440,120L1380,120C1320,120,1200,120,1080,120C960,120,840,120,720,120C600,120,480,120,360,120C240,120,120,120,60,120L0,120Z" class="fill-[#FAF7F5] dark:fill-[#170E0A]"></path>
        </svg>
      </div>

      <!-- Carousel Navigation Arrows (Desktop) -->
      <button
        v-if="banners.length > 1"
        @click="prevSlide"
        class="hidden lg:flex absolute left-3 xl:left-6 top-1/2 -translate-y-1/2 w-10 h-10 xl:w-12 xl:h-12 rounded-full bg-white/90 hover:bg-white dark:bg-[#1E120B]/90 text-gray-700 dark:text-gray-200 shadow-md items-center justify-center hover:text-[#DE1B54] transition z-20 cursor-pointer"
        title="Previous"
      >
        <span class="text-base font-black">&lsaquo;</span>
      </button>

      <button
        v-if="banners.length > 1"
        @click="nextSlide"
        class="hidden lg:flex absolute right-3 xl:right-6 top-1/2 -translate-y-1/2 w-10 h-10 xl:w-12 xl:h-12 rounded-full bg-white/90 hover:bg-white dark:bg-[#1E120B]/90 text-gray-700 dark:text-gray-200 shadow-md items-center justify-center hover:text-[#DE1B54] transition z-20 cursor-pointer"
        title="Next"
      >
        <span class="text-base font-black">&rsaquo;</span>
      </button>

      <!-- Carousel Pagination Dots: Positioned above Organic Wave Divider -->
      <div v-if="banners.length > 1" class="absolute bottom-7 sm:bottom-10 lg:bottom-12 left-1/2 -translate-x-1/2 flex items-center space-x-2 z-20">
        <button
          v-for="(b, idx) in banners"
          :key="idx"
          @click="currentBannerIdx = idx; heroImageLoaded = false"
          class="transition-all cursor-pointer p-1"
          :class="currentBannerIdx === idx ? 'w-7 h-2 rounded-full bg-[#DE1B54]' : 'w-2 h-2 rounded-full bg-white/90 border border-[#D8CDC5]'"
        ></button>
      </div>
    </section>

    <!-- 2. "ABOUT SWEETBAKES - A BAKERY YOU CAN TRUST" SECTION -->
    <section id="about" class="sweetbakes-container" v-reveal>
      <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#FAF7F4] via-white to-[#FDF4F0] dark:from-[#20130C] dark:via-[#1A0E08] dark:to-[#22150E] border border-[#EFE8E1] dark:border-[#382318] p-4 xs:p-5 sm:p-8 md:p-10 lg:p-12 shadow-sm relative overflow-hidden group">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-stretch">
          <!-- Left Column: Baker Chef Photo (Expanded Frame with Upward Shifted Crop) -->
          <div class="md:col-span-6 flex justify-center order-2 md:order-1">
            <div class="relative w-full h-full flex flex-col">
              <div
                class="relative w-full h-full aspect-[4/3] sm:aspect-auto min-h-[220px] xs:min-h-[260px] sm:min-h-[380px] md:min-h-[460px] lg:min-h-[500px] xl:min-h-[520px] rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl border border-white/90 dark:border-[#382318] bg-[#F0E8DF] dark:bg-[#25150E]"
                :class="{ 'shimmer-wave': !aboutImageLoaded }"
              >
                <img
                  :src="businessInfo?.about_image_url || '/mockup/baker_hires.jpg'"
                  alt="SweetBakes Baker Chef"
                  class="w-full h-full object-cover object-[center_18%] group-hover:scale-104 transition-all duration-700"
                  :class="aboutImageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-98'"
                  loading="lazy"
                  @load="aboutImageLoaded = true"
                />
              </div>
            </div>
          </div>

          <!-- Right Column: About Details, Checklist, Stats & Scalloped Stamp -->
          <div class="md:col-span-6 space-y-4 sm:space-y-5 relative order-1 md:order-2 flex flex-col justify-between min-w-0">
            <!-- Header Row with Stamp in Top Right -->
            <div class="flex items-start justify-between gap-2.5 xs:gap-3 sm:gap-4">
              <div class="min-w-0 flex-1">
                <div v-reveal:text="80" class="flex items-center space-x-2 text-[#DE1B54] font-script text-lg xs:text-xl sm:text-2xl lg:text-3xl mb-1">
                  <span class="shrink-0">🧁</span>
                  <span class="leading-snug">{{ (localeStore.isAmharic ? businessInfo?.about_tag_am : businessInfo?.about_tag_en) || localeStore.t('about_tag') }}</span>
                </div>

                <!-- Headline -->
                <h3 v-reveal:text="150" class="text-fluid-h2 font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
                  {{ (localeStore.isAmharic ? businessInfo?.about_title_am : businessInfo?.about_title_en) || localeStore.t('about_title') }}
                </h3>
              </div>

              <!-- Scalloped Stamp / Seal: "SINCE 2015 ♡" -->
              <div v-reveal:scale="200" class="shrink-0">
                <div class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 rounded-full bg-rose-50/95 dark:bg-[#341822]/95 border-2 border-dashed border-[#DE1B54] flex flex-col items-center justify-center text-center p-1 sm:p-1.5 shadow-xs text-[#DE1B54] rotate-6 hover:rotate-12 transition-transform duration-300 cursor-pointer select-none">
                  <span class="text-[8px] sm:text-[9px] font-bold tracking-wider uppercase font-serif">{{ localeStore.isAmharic ? 'ከ' : 'SINCE' }}</span>
                  <span class="text-sm sm:text-base lg:text-lg font-black font-serif leading-none">{{ businessInfo?.since_year || '2015' }}</span>
                  <span class="text-[8px] sm:text-[9px] font-bold tracking-wider uppercase font-serif" v-if="localeStore.isAmharic">ጀምሮ</span>
                  <span class="text-[10px] sm:text-xs" v-else>♡</span>
                </div>
              </div>
            </div>

            <!-- Body Paragraph -->
            <p v-reveal:text="220" class="text-sm sm:text-base lg:text-[17px] font-bold text-[#2A1810] dark:text-[#EAE1DA] leading-relaxed max-w-lg">
              {{ (localeStore.isAmharic ? businessInfo?.about_story_am : businessInfo?.about_story_en) || localeStore.t('about_desc') }}
            </p>

            <!-- 4 Bullets with Dark Circular Checkmarks -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5 text-xs xs:text-sm sm:text-base font-bold text-[#1C0F0A] dark:text-[#FAF3EE]">
              <div v-reveal:text="260" class="flex items-start sm:items-center space-x-2.5 p-1 rounded-xl min-w-0">
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[11px] font-black shrink-0 shadow-2xs mt-0.5 sm:mt-0">✓</span>
                <span class="leading-snug">{{ (localeStore.isAmharic ? businessInfo?.about_bullet_1_am : businessInfo?.about_bullet_1_en) || localeStore.t('premium_quality') }}</span>
              </div>
              <div v-reveal:text="300" class="flex items-start sm:items-center space-x-2.5 p-1 rounded-xl min-w-0">
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[11px] font-black shrink-0 shadow-2xs mt-0.5 sm:mt-0">✓</span>
                <span class="leading-snug">{{ (localeStore.isAmharic ? businessInfo?.about_bullet_2_am : businessInfo?.about_bullet_2_en) || localeStore.t('fresh_ingredients') }}</span>
              </div>
              <div v-reveal:text="340" class="flex items-start sm:items-center space-x-2.5 p-1 rounded-xl min-w-0">
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[11px] font-black shrink-0 shadow-2xs mt-0.5 sm:mt-0">✓</span>
                <span class="leading-snug">{{ (localeStore.isAmharic ? businessInfo?.about_bullet_3_am : businessInfo?.about_bullet_3_en) || localeStore.t('custom_orders') }}</span>
              </div>
              <div v-reveal:text="380" class="flex items-start sm:items-center space-x-2.5 p-1 rounded-xl min-w-0">
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[11px] font-black shrink-0 shadow-2xs mt-0.5 sm:mt-0">✓</span>
                <span class="leading-snug">{{ (localeStore.isAmharic ? businessInfo?.about_bullet_4_am : businessInfo?.about_bullet_4_en) || localeStore.t('multibranch_service') }}</span>
              </div>
            </div>

            <!-- Action & Experience Stats Row -->
            <div v-reveal:text="420" class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 border-t border-[#F0E6DD] dark:border-[#382318]">
              <!-- Discover More Button -->
              <router-link
                to="/branches"
                class="w-full sm:w-auto justify-center px-7 sm:px-9 py-3.5 sm:py-4 min-h-[48px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-sm sm:text-base font-bold rounded-full shadow-md hover:shadow-lg transition-transform hover:scale-105 active:scale-95 inline-flex items-center space-x-2 cursor-pointer group whitespace-nowrap"
              >
                <span>{{ localeStore.t('discover_more') }}</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
              </router-link>

              <!-- Stats: Dynamic Experience & Branches count from Database -->
              <div class="flex flex-wrap xs:flex-nowrap items-center justify-center sm:justify-start gap-3 xs:gap-4 sm:gap-6 text-sm text-[#554035] dark:text-[#C5B7AF] pt-1 sm:pt-0">
                <div class="flex items-center space-x-2">
                  <span class="text-xl sm:text-2xl">👩‍🍳</span>
                  <div>
                    <div class="font-black text-base sm:text-lg text-[#1A0C06] dark:text-white leading-none">
                      {{ businessInfo?.stats?.find(s => s.id === 'experience')?.value || '12+' }}
                    </div>
                    <div class="text-[11px] xs:text-xs sm:text-[13px] font-bold text-[#6E5D53] dark:text-[#A8988E]">{{ localeStore.t('years_exp') }}</div>
                  </div>
                </div>

                <div class="h-8 w-px bg-rose-200 dark:bg-[#58263A]"></div>

                <div class="flex items-center space-x-2">
                  <span class="text-xl sm:text-2xl">🏪</span>
                  <div>
                    <div class="font-black text-base sm:text-lg text-[#1A0C06] dark:text-white leading-none">{{ branchStore.branches?.length || 4 }}</div>
                    <div class="text-[11px] xs:text-xs sm:text-[13px] font-bold text-[#6E5D53] dark:text-[#A8988E]">{{ localeStore.t('branches_stat') }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. "OUR POPULAR PRODUCTS" SECTION: With Interactive Category Filter Pills -->
    <section id="popular-products" class="sweetbakes-container" v-reveal>
      <!-- Section Header with Cake Icon & Dashes -->
      <div class="text-center space-y-1.5 mb-6 sm:mb-8">
        <div v-reveal:text="80" class="flex items-center justify-center space-x-2.5 sm:space-x-3 text-gray-300">
          <span class="hidden xs:block h-px w-8 sm:w-16 bg-[#D8CDC5] dark:bg-[#3D261B]"></span>
          <span class="text-xl sm:text-2xl text-[#DE1B54]">🎂</span>
          <h2 class="text-fluid-h2 font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
            {{ localeStore.t('popular_title') }}
          </h2>
          <span class="hidden xs:block h-px w-8 sm:w-16 bg-[#D8CDC5] dark:bg-[#3D261B]"></span>
        </div>
        <p v-reveal:text="160" class="text-sm sm:text-base font-bold text-[#553C2E] dark:text-[#D4C5BB]">
          {{ localeStore.t('popular_sub') }}
        </p>
      </div>

      <!-- Category Filter Pills -->
      <div v-reveal:text="240" class="flex flex-wrap items-center justify-center gap-1.5 xs:gap-2 sm:gap-2.5 mb-8">
        <button
          v-for="cat in categoryItems"
          :key="cat.key"
          type="button"
          @click="selectedCategory = cat.key"
          class="px-3.5 xs:px-4 sm:px-6 py-2 sm:py-2.5 min-h-[40px] sm:min-h-[44px] rounded-full text-xs xs:text-sm sm:text-base font-bold transition-all duration-200 cursor-pointer whitespace-nowrap"
          :class="selectedCategory === cat.key
            ? 'bg-[#DE1B54] text-white shadow-sm font-black scale-105'
            : 'bg-white dark:bg-[#1E120B] hover:bg-rose-50 dark:hover:bg-[#341822] border border-[#E5DAD1] dark:border-[#382318] text-[#332014] dark:text-[#E8DED8] hover:text-[#DE1B54] hover:border-[#DE1B54]/40'"
        >
          {{ localeStore.t(cat.transKey) }}
        </button>
      </div>

      <!-- Responsive Product Cards Grid (1 col on <=360px, 2 on 361px-767px, 3 on tablets, 4 on laptop, 5 on desktop) -->
      <!-- Skeleton Loading State -->
      <div v-if="loadingProducts" class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5 sm:gap-4 lg:gap-5">
        <ProductCardSkeleton v-for="n in 5" :key="`home-skel-${n}`" />
      </div>

      <!-- Empty state when no products in category -->
      <EmptyState
        v-else-if="displayedProducts.length === 0"
        icon="🧁"
        :title="localeStore.isAmharic ? 'በዚህ ምድብ ምንም ምርቶች አልተገኙም' : 'No Products Available'"
        :description="localeStore.isAmharic ? 'እባክዎ ሌላ ምድብ ይምረጡ ወይም ሁሉንም ምርቶች ይመልከቱ።' : 'No items match your selection right now. Browse our full shop!'"
        :actionLabel="localeStore.isAmharic ? 'ሁሉንም አሳይ' : 'Show All Bakes'"
        @action="selectedCategory = 'All'"
      />

      <!-- Real Loaded Products Grid -->
      <div v-else class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5 sm:gap-4 lg:gap-5 transition-all duration-300">
        <div
          v-for="product in displayedProducts"
          :key="product.id"
          class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] hover:border-[#DE1B54]/40 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between overflow-hidden group p-3 sm:p-3.5 space-y-3 cursor-pointer h-full"
          @click="goToProduct(product.productId)"
        >
          <!-- Image Container with Heart Button (Clean Rounded & Shimmer) -->
          <div class="relative aspect-4/3 rounded-xl overflow-hidden bg-[#F0E8DF] dark:bg-[#25150E]" :class="{ 'shimmer-wave': !loadedImages[product.id] }">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-106 transition-all duration-500"
              :class="loadedImages[product.id] ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
              loading="lazy"
              @load="loadedImages[product.id] = true"
              @error="loadedImages[product.id] = true"
            />

            <!-- Heart Wishlist Button -->
            <button
              type="button"
              @click.stop="toggleWishlist(product)"
              class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/95 dark:bg-[#1E120B]/95 backdrop-blur-xs flex items-center justify-center text-xs shadow-2xs hover:scale-115 active:scale-90 transition-all cursor-pointer z-10"
              :class="wishlistStore.isWishlisted(product.id || product.product_id) ? 'text-[#DE1B54]' : 'text-gray-400 hover:text-[#DE1B54]'"
              title="Add to Wishlist"
            >
              <span>{{ wishlistStore.isWishlisted(product.id || product.product_id) ? '♥' : '♡' }}</span>
            </button>

            <!-- Ye'Tsom badge if fasting friendly -->
            <span
              v-if="product.is_fasting"
              class="absolute bottom-2 left-2 bg-emerald-800/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full shadow-2xs flex items-center space-x-1 pointer-events-none"
            >
              <span>🌿</span>
              <span>{{ localeStore.t('yetsom_badge') }}</span>
            </span>

            <!-- Sold Out overlay badge if 0 stock at current branch -->
            <span
              v-if="product.isSoldOut"
              class="absolute top-2 left-2 bg-rose-900/90 text-white text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full shadow-2xs pointer-events-none"
            >
              {{ localeStore.t('sold_out') || 'Sold Out' }}
            </span>
          </div>

          <!-- Product Details -->
          <div class="space-y-1.5 flex-1 flex flex-col justify-between">
            <div>
              <router-link
                :to="`/product/${product.productId}`"
                @click.stop
                class="font-serif font-bold text-sm xs:text-base text-[#1A0C06] dark:text-[#FAF3EE] group-hover:text-[#DE1B54] transition-colors block truncate"
              >
                {{ localeStore.tProduct(product.name) }}
              </router-link>

              <!-- Star Rating & Review Count -->
              <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[11px] xs:text-xs pt-0.5">
                <span class="text-amber-400 tracking-tighter">★★★★★</span>
                <span class="text-gray-800 dark:text-gray-200 font-bold">{{ product.rating }}</span>
                <span class="text-gray-500 dark:text-gray-400">({{ product.reviews }} {{ localeStore.t('reviews_count') }})</span>
              </div>
            </div>

            <!-- Price in Bold Vivid Pink -->
            <div class="pt-1">
              <span class="text-sm xs:text-base font-black text-[#DE1B54]">
                {{ localeStore.t('currency') }} {{ product.price.toLocaleString() }}
              </span>
            </div>

            <!-- Full-width Vivid Pink Add to Cart Button -->
            <button
              type="button"
              @click.stop="handleAddToCart(product)"
              :disabled="product.isAdding || product.isSoldOut"
              class="w-full px-2 sm:px-3 py-2.5 min-h-[44px] text-[11px] xs:text-xs sm:text-sm font-bold rounded-xl transition shadow-xs flex items-center justify-center space-x-1.5"
              :class="product.isSoldOut
                ? 'bg-gray-200 dark:bg-[#382318] text-gray-400 dark:text-gray-500 cursor-not-allowed'
                : 'bg-[#DE1B54] hover:bg-[#C51345] text-white cursor-pointer active:scale-95'"
            >
              <svg v-if="!product.isSoldOut" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="truncate">{{ product.isAdding ? localeStore.t('added_cart') : (product.isSoldOut ? localeStore.t('sold_out') : localeStore.t('add_to_cart')) }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Centered "Explore All Products →" Rounded Pill Button -->
      <div v-reveal:text="100" class="mt-8 sm:mt-10 text-center">
        <router-link
          to="/shop"
          class="px-8 py-3 min-h-[44px] rounded-full border-2 border-[#DE1B54] text-[#DE1B54] hover:bg-[#DE1B54] hover:text-white font-bold text-xs sm:text-sm transition-all duration-300 shadow-2xs hover:shadow-md cursor-pointer inline-flex items-center space-x-2 group"
        >
          <span>{{ localeStore.t('explore_all') }}</span>
          <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
        </router-link>
      </div>
    </section>

    <!-- 4. BESPOKE 3D CAKE STUDIO: Interactive Experience Banner (Exact Studio Showcase Design) -->
    <section class="sweetbakes-container" v-reveal>
      <div class="relative overflow-hidden rounded-[1.5rem] sm:rounded-[2.25rem] min-h-[340px] sm:min-h-[440px] lg:min-h-[480px] lg:aspect-[1024/562] flex items-center border border-[#F5D2DC]/80 dark:border-[#3E231A] shadow-[0_14px_45px_rgba(222,27,84,0.1)] dark:shadow-[0_14px_45px_rgba(0,0,0,0.55)] hover:shadow-[0_20px_55px_rgba(222,27,84,0.16)] transition-all duration-500 group">
        <!-- Full-Bleed Ultra-HD Studio Background Image -->
        <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none">
          <img
            src="/mockup/cake_studio_hero_bg.jpg"
            alt="SweetBakes 3D Cake Studio Masterpiece"
            class="w-full h-full object-cover object-[76%_center] sm:object-right lg:object-center group-hover:scale-102 transition-transform duration-700"
            loading="lazy"
          />
          <!-- Mobile Readability Veil (< 640px) + Tablet Left Gradient + Desktop Exact Glow -->
          <div class="absolute inset-0 bg-gradient-to-b from-[#FFF9FA]/96 via-[#FFF9FA]/90 to-[#FFF9FA]/96 sm:bg-gradient-to-r sm:from-[#FFF9FA]/92 sm:via-[#FFF9FA]/65 sm:via-50% sm:to-transparent lg:from-[#FFF9FA]/35 lg:via-transparent lg:to-transparent dark:from-[#170E0A]/96 dark:via-[#170E0A]/88 dark:to-[#170E0A]/94 sm:dark:via-[#170E0A]/80 sm:dark:to-[#170E0A]/25 pointer-events-none"></div>
        </div>

        <!-- Content Container Positioned Over Left Studio Glow -->
        <div class="relative z-10 w-full p-5 xs:p-6 sm:px-10 sm:py-12 lg:px-14 lg:py-14 xl:px-16">
          <div class="max-w-md lg:max-w-lg xl:max-w-xl space-y-3.5 sm:space-y-5 text-center sm:text-left mx-auto sm:mx-0">
            <!-- Pill Badge: 🍰 Cake Studio -->
            <div class="inline-flex items-center space-x-2 px-3.5 sm:px-4 py-1.5 rounded-full bg-white/95 dark:bg-[#2A151B]/95 backdrop-blur-md text-[#26150F] dark:text-[#FAF3EE] border border-[#F5CAD8] dark:border-rose-900/60 font-bold text-xs sm:text-sm shadow-2xs">
              <span class="text-base leading-none">🍰</span>
              <span>{{ localeStore.isAmharic ? 'ኬክ ስቱዲዮ' : 'Cake Studio' }}</span>
            </div>

            <!-- Big Bold Title with Radiating Pink Sparkle Lines on "Dream Masterpiece" -->
            <h2 class="text-[22px] min-[360px]:text-[25px] xs:text-[27px] sm:text-4xl md:text-[42px] lg:text-[48px] xl:text-[56px] font-sans font-black text-[#22120D] dark:text-[#FAF3EE] tracking-tight leading-[1.12]">
              <template v-if="localeStore.isAmharic">
                <span>የህልምዎን ኬክ</span><br />
                <span class="text-[#E61960] dark:text-rose-400 relative inline-block">
                  በ3D ይንደፉ
                  <svg class="absolute -top-2.5 -right-5 sm:-top-3.5 sm:-right-8 w-5 h-5 sm:w-7 sm:h-7 text-[#E61960]" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round">
                    <path d="M5 17L9 13" />
                    <path d="M13 10L16 4" />
                    <path d="M21 14L27 11" />
                  </svg>
                </span><br />
                <span>በቀላሉ ያዘጋጁ</span>
              </template>
              <template v-else>
                <span>Create Your</span><br />
                <span class="text-[#E61960] dark:text-rose-400 relative inline-block sm:whitespace-nowrap">
                  Dream Masterpiece
                  <svg class="absolute -top-2.5 -right-5 sm:-top-3.5 sm:-right-8 w-5 h-5 sm:w-7 sm:h-7 text-[#E61960]" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round">
                    <path d="M5 17L9 13" />
                    <path d="M13 10L16 4" />
                    <path d="M21 14L27 11" />
                  </svg>
                </span><br />
                <span>in 3D</span>
              </template>
            </h2>

            <!-- Clean 2-Line Subtitle Paragraph -->
            <p class="text-sm sm:text-base lg:text-[17px] text-[#3A2720] dark:text-[#E5D7CE] font-medium leading-snug max-w-sm sm:max-w-md mx-auto sm:mx-0 whitespace-pre-line">
              {{ localeStore.isAmharic
                ? 'አስደናቂ ኬኮችን በቀላሉ ይንደፉ።\nሃሳብዎን ወደ እውነት ይለውጡ!'
                : 'Design stunning cakes with ease.\nMake your ideas come to life!'
              }}
            </p>

            <!-- Rocket Launch CTA Button -->
            <div class="pt-1 sm:pt-2">
              <router-link
                to="/custom-cake"
                class="w-full sm:w-auto justify-center inline-flex items-center space-x-2 px-6 sm:px-8 py-3.5 sm:py-4 min-h-[48px] rounded-full bg-gradient-to-r from-[#EE1D62] to-[#DE1B54] hover:from-[#D81656] hover:to-[#C51345] text-white font-bold text-sm sm:text-base shadow-[0_10px_25px_rgba(222,27,84,0.35)] hover:shadow-[0_14px_30px_rgba(222,27,84,0.48)] hover:scale-105 active:scale-95 transition-all cursor-pointer group/btn whitespace-nowrap"
              >
                <span class="text-base sm:text-lg leading-none">🚀</span>
                <span>{{ localeStore.isAmharic ? 'የ3D ኬክ ስቱዲዮውን ይክፈቱ' : 'Launch 3D Cake Studio' }}</span>
                <span class="text-lg leading-none group-hover/btn:translate-x-1 transition-transform">&rarr;</span>
              </router-link>
            </div>

            <!-- Mobile Framed Studio Cake Preview (< 640px so text and artwork never compete) -->
            <div class="sm:hidden pt-2">
              <div class="rounded-2xl overflow-hidden shadow-md border border-[#F5CAD8]/80 dark:border-[#4A2632] aspect-[16/9]">
                <img
                  src="/mockup/cake_studio_hero_bg.jpg"
                  alt="SweetBakes 3D Cake Preview"
                  class="w-full h-full object-cover object-[80%_center]"
                  loading="lazy"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. "WHAT OUR CUSTOMERS SAY" (REVIEWS) SECTION: With Flanking Navigation Arrows -->
    <section id="reviews" class="sweetbakes-container relative" v-reveal>
      <!-- Symmetrical Centered Ambient Blush Cloud (Locked to Center, Never Distorts on Zoom) -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl h-72 bg-rose-100/40 dark:bg-rose-950/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

      <!-- Section Header with Cupcake & Delicate Pink Divider Lines -->
      <div class="text-center space-y-1.5 mb-8 sm:mb-10">
        <div v-reveal:text="80" class="flex items-center justify-center space-x-2.5 sm:space-x-3 text-rose-200 dark:text-[#58263A]">
          <span class="hidden xs:block h-px w-8 sm:w-24 bg-rose-200 dark:bg-[#58263A]"></span>
          <span class="text-xl sm:text-2xl text-[#DE1B54]">🧁</span>
          <h2 class="text-fluid-h2 font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
            {{ localeStore.t('reviews_title') }}
          </h2>
          <span class="hidden xs:block h-px w-8 sm:w-24 bg-rose-200 dark:bg-[#58263A]"></span>
        </div>
        <p v-reveal:text="150" class="text-xs sm:text-sm text-[#786B60] dark:text-[#C5B7AF]">
          {{ localeStore.t('reviews_sub') }}
        </p>
      </div>

      <!-- Empty State when no customer reviews -->
      <div v-if="reviewSlides.length === 0" class="text-center py-16 px-4 rounded-3xl bg-rose-50/40 dark:bg-[#1E120B] border border-rose-100 dark:border-[#382318] max-w-lg mx-auto space-y-3">
        <span class="text-4xl block">💬</span>
        <h4 class="font-serif font-bold text-lg text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'እስካሁን ምንም አስተያየት አልተሰጠም' : 'No Customer Reviews Yet' }}</h4>
        <p class="text-xs text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የመጀመሪያው አስተያየት ሰጪ ይሁኑ! ኬክ ወይም ዳቦ በማዘዝ አስተያየትዎን ያካፍሉ።' : 'Be the first to order and share your SweetBakes celebration experience!' }}</p>
        <router-link to="/shop" class="inline-block px-5 py-2 min-h-[38px] text-xs font-bold bg-[#DE1B54] text-white rounded-full hover:bg-[#C51345] transition cursor-pointer">
          {{ localeStore.isAmharic ? 'አሁን ይዘዙ' : 'Order Now' }}
        </router-link>
      </div>

      <!-- Testimonial Cards Carousel with Left & Right Arrows -->
      <div v-else class="relative flex items-center">
        <!-- Previous Slide Arrow (Left) -->
        <button
          v-if="reviewSlides.length > 1"
          @click="prevReviewSlide"
          type="button"
          class="hidden md:flex -left-4 sm:-left-5 absolute z-20 w-10 h-10 rounded-full bg-white dark:bg-[#1E120B] border border-rose-100 dark:border-[#382318] shadow-md items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] hover:border-[#DE1B54]/40 hover:scale-105 transition cursor-pointer"
          title="Previous Reviews"
        >
          <span class="text-lg font-black leading-none">&lsaquo;</span>
        </button>

        <!-- Testimonial Cards Grid -->
        <div
          class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 transition-all duration-500"
          @mouseenter="pauseReviewAutoplay"
          @mouseleave="startReviewAutoplay"
        >
          <div
            v-for="(rev, idx) in currentReviewsSlide"
            :key="idx"
            class="bg-white dark:bg-[#1E120B] rounded-3xl border border-rose-100/80 dark:border-[#382318] p-5 sm:p-7 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_16px_36px_rgba(222,27,84,0.1)] hover:-translate-y-1.5 transition-all duration-300 relative flex flex-col justify-between space-y-4 group cursor-default h-full"
          >
            <!-- Pink Double Quotation Mark Icon in Top-Right -->
            <div class="absolute top-5 right-5 sm:top-6 sm:right-6 select-none opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#F472B6] fill-current" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
              </svg>
            </div>

            <!-- User Info Row: Avatar + Name + Stars -->
            <div class="flex items-center space-x-3.5 pr-8">
              <img
                v-if="rev.avatar"
                :src="rev.avatar"
                :alt="rev.name"
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover border-2 border-rose-100/80 dark:border-[#58263A] shadow-2xs group-hover:scale-105 transition-transform"
                loading="lazy"
              />
              <div
                v-else
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-rose-100 to-rose-200 dark:from-[#451829] dark:to-[#31111c] text-[#DE1B54] dark:text-rose-300 font-bold font-serif flex items-center justify-center text-base shrink-0 shadow-2xs border border-rose-200/60"
              >
                {{ (rev.name || 'C').charAt(0).toUpperCase() }}
              </div>
              <div>
                <h4 class="font-serif font-bold text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE] group-hover:text-[#DE1B54] transition-colors flex items-center gap-1.5">
                  <span>{{ localeStore.tName(rev.name) }}</span>
                  <span class="text-[10px] font-sans font-bold px-1.5 py-0.2 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">✓ {{ localeStore.isAmharic ? 'የተረጋገጠ' : 'Verified' }}</span>
                </h4>
                <div class="flex items-center space-x-2 mt-0.5">
                  <div class="text-amber-400 text-xs sm:text-sm tracking-tighter">
                    <span v-for="s in 5" :key="s" :class="s <= (rev.rating || 5) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'">★</span>
                  </div>
                  <span v-if="rev.product_name" class="text-[11px] text-[#8A7B71] dark:text-[#A8988E] truncate max-w-[130px]">· {{ rev.product_name }}</span>
                </div>
              </div>
            </div>

            <!-- Quote Text -->
            <p class="text-xs sm:text-sm font-medium text-[#4A3B32] dark:text-[#E8DDD3] leading-relaxed italic pt-1 flex-1">
              &ldquo;{{ localeStore.tQuote(rev.quote) }}&rdquo;
            </p>
          </div>
        </div>

        <!-- Next Slide Arrow (Right) -->
        <button
          v-if="reviewSlides.length > 1"
          @click="nextReviewSlide"
          type="button"
          class="hidden md:flex -right-4 sm:-right-5 absolute z-20 w-10 h-10 rounded-full bg-white dark:bg-[#1E120B] border border-rose-100 dark:border-[#382318] shadow-md items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] hover:border-[#DE1B54]/40 hover:scale-105 transition cursor-pointer"
          title="Next Reviews"
        >
          <span class="text-lg font-black leading-none">&rsaquo;</span>
        </button>
      </div>

      <!-- Interactive Carousel Controls + Pagination Dots -->
      <div v-if="reviewSlides.length > 1" class="flex items-center justify-center space-x-2 sm:space-x-2.5 pt-6 sm:pt-10">
        <button
          type="button"
          @click="prevReviewSlide"
          class="md:hidden w-9 h-9 rounded-full bg-white dark:bg-[#1E120B] border border-rose-100 dark:border-[#382318] shadow-xs flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] cursor-pointer active:scale-95"
          aria-label="Previous Reviews"
        >
          <span class="text-base font-black leading-none">&lsaquo;</span>
        </button>

        <button
          v-for="dotIdx in reviewSlides.length"
          :key="dotIdx"
          type="button"
          @click="setReviewSlide(dotIdx - 1)"
          class="min-h-[32px] min-w-[32px] flex items-center justify-center transition-all duration-300 cursor-pointer"
          :title="`View review slide ${dotIdx}`"
        >
          <span
            class="transition-all duration-300 block"
            :class="activeReviewSlide === dotIdx - 1
              ? 'w-5 h-2.5 rounded-full bg-[#DE1B54] shadow-xs' 
              : 'w-2.5 h-2.5 rounded-full bg-[#E5D7D0] dark:bg-[#3E271B] hover:bg-rose-300'"
          ></span>
        </button>

        <button
          type="button"
          @click="nextReviewSlide"
          class="md:hidden w-9 h-9 rounded-full bg-white dark:bg-[#1E120B] border border-rose-100 dark:border-[#382318] shadow-xs flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[#DE1B54] cursor-pointer active:scale-95"
          aria-label="Next Reviews"
        >
          <span class="text-base font-black leading-none">&rsaquo;</span>
        </button>
      </div>
    </section>

    <!-- 6. "SPECIAL OFFER: MAKE EVERY MOMENT SWEETER!" BANNER -->
    <section class="sweetbakes-container" v-reveal>
      <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-[#FFF0F4] via-[#FDE4EE] to-[#FCE4EC] dark:from-[#341822] dark:via-[#29121B] dark:to-[#381A25] border border-[#F6C5D6] dark:border-[#552438] p-4 xs:p-5 sm:p-8 lg:p-10 flex flex-col md:flex-row items-center justify-between gap-5 sm:gap-6 shadow-sm group">
        <!-- Left Column: Special Offer Text & Call to Action -->
        <div class="space-y-3 sm:space-y-4 text-center md:text-left z-10 max-w-xl w-full">
          <span v-reveal:text="80" class="inline-block px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-[#DE1B54] text-white shadow-2xs">
            {{ localeStore.t('special_offer') }}
          </span>
          <h3 v-reveal:text="150" class="text-fluid-h2 font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight">
            {{ localeStore.t('offer_title_1') }} <span class="text-[#DE1B54] font-script italic">{{ localeStore.t('offer_title_2') }}</span>
          </h3>
          <p v-reveal:text="220" class="text-sm sm:text-base lg:text-[17px] font-bold text-[#2A1810] dark:text-[#FAF3EE] leading-relaxed">
            {{ localeStore.t('offer_desc') }}
          </p>
        </div>

        <!-- Right Column: Dynamic Discount Badge, Action Button, and Featured Celebration Cake -->
        <div v-reveal:text="280" class="flex flex-wrap sm:flex-nowrap items-center justify-center md:justify-end gap-2.5 xs:gap-3 sm:gap-4 z-10 w-full md:w-auto">
          <!-- Discount Badge (from Database) -->
          <div class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-full bg-[#DE1B54] text-white font-black text-xs xs:text-sm sm:text-base tracking-wide shadow-md whitespace-nowrap animate-pulse">
            {{ activePromotion ? `${activePromotion.discount_value}% OFF` : localeStore.t('discount_label') }}
          </div>

          <!-- Shop the Offer Button -->
          <router-link
            to="/shop"
            class="px-5 sm:px-6 py-2.5 sm:py-3 min-h-[44px] sm:min-h-[46px] rounded-full bg-white dark:bg-[#221610] hover:bg-rose-50 dark:hover:bg-[#341822] text-[#DE1B54] font-black text-xs xs:text-sm sm:text-base border border-[#DE1B54]/30 shadow-xs hover:shadow-md transition-all inline-flex items-center space-x-2 whitespace-nowrap cursor-pointer hover:scale-103"
          >
            <span>{{ localeStore.t('shop_offer') }}</span>
            <span>&rarr;</span>
          </router-link>

          <!-- Pink Celebration Cake Image -->
          <div class="block w-20 xs:w-24 sm:w-32 lg:w-40 shrink-0 sm:-my-3 sm:-mr-2">
            <img
              src="/mockup/offer_cake.jpg"
              alt="SweetBakes Celebration Cake"
              class="w-full h-auto object-contain rounded-2xl sm:rounded-none drop-shadow-md group-hover:scale-105 transition-all duration-500"
              :class="offerImageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
              loading="lazy"
              @load="offerImageLoaded = true"
            />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import ProductCardSkeleton from '../../components/ProductCardSkeleton.vue';
import EmptyState from '../../components/EmptyState.vue';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import { useWishlistStore } from '../../stores/wishlist';
import api from '../../api';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();
const branchStore = useBranchStore();
const toastStore = useToastStore();
const localeStore = useLocaleStore();
const wishlistStore = useWishlistStore();
const heroImageLoaded = ref(false);
const aboutImageLoaded = ref(false);
const offerImageLoaded = ref(false);
const loadedImages = ref({});

// Dynamic Homepage Banners from Database
const banners = ref([]);
const currentBannerIdx = ref(0);

const currentBanner = computed(() => {
  return banners.value[currentBannerIdx.value] || null;
});

const heroSubtitle = computed(() => {
  if (currentBanner.value) {
    const text = localeStore.isAmharic ? currentBanner.value.subtitle_am : currentBanner.value.subtitle_en;
    if (text) return text;
  }
  if (businessInfo.value) {
    const text = localeStore.isAmharic ? businessInfo.value.hero_subtitle_am : businessInfo.value.hero_subtitle_en;
    if (text) return text;
  }
  return localeStore.t('hero_sub');
});

const heroTitle1 = computed(() => {
  if (currentBanner.value) {
    const text = localeStore.isAmharic ? currentBanner.value.title_am : currentBanner.value.title_en;
    if (text) return text;
  }
  if (businessInfo.value) {
    const text = localeStore.isAmharic ? businessInfo.value.hero_title_am : businessInfo.value.hero_title_en;
    if (text) return text;
  }
  return localeStore.t('hero_title_1');
});

const heroTitle1Parts = computed(() => {
  const t = heroTitle1.value || '';
  const match = t.match(/^(.*?)(love.*)$/i);
  if (match) {
    return {
      prefix: match[1],
      highlight: match[2].includes('♡') ? match[2] : `${match[2]} ♡`
    };
  }
  return { prefix: t, highlight: '' };
});

const heroTitle2 = computed(() => {
  if (currentBanner.value) {
    const text = localeStore.isAmharic ? (currentBanner.value.badge_am || currentBanner.value.subtitle_am) : (currentBanner.value.badge_en || currentBanner.value.subtitle_en);
    if (text) return text;
  }
  if (businessInfo.value) {
    const text = localeStore.isAmharic ? businessInfo.value.hero_title2_am : businessInfo.value.hero_title2_en;
    if (text) return text;
  }
  return localeStore.t('hero_title_2');
});

// Dynamic Luxury Typewriter Effect for Hero Headline
const currentPhraseIndex = ref(0);
const typedText = ref('');
const isDeleting = ref(false);
let typewriterTimeout = null;

const englishPhrases = computed(() => [
  heroTitle2.value || "Addis Ababa's Favorite 🍰",
  "Dream Wedding Cakes 🎂",
  "Artisanal Sourdough Loaves 🍞",
  "Birthday Masterpieces 🎉",
  "Handcrafted Daily with Love ✨",
]);

const amharicPhrases = computed(() => [
  heroTitle2.value || "የአዲስ አበባ ተመራጭ 🍰",
  "የህልም ሰርግ ኬኮች 🎂",
  "ትኩስ የተፈጥሮ ዳቦ 🍞",
  "የልደት ዋና ድንቅ ስራዎች 🎉",
  "በፍቅር የተዘጋጀ ጣዕም ✨",
]);

const currentPhrases = computed(() => {
  return localeStore.isAmharic ? amharicPhrases.value : englishPhrases.value;
});

function tickTypewriter() {
  if (typeof window === 'undefined') return;
  const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) {
    typedText.value = currentPhrases.value[0] || heroTitle2.value || '';
    return;
  }

  const phrases = currentPhrases.value;
  if (!phrases || phrases.length === 0) return;

  const fullText = phrases[currentPhraseIndex.value % phrases.length];

  if (!isDeleting.value) {
    typedText.value = fullText.slice(0, typedText.value.length + 1);

    if (typedText.value === fullText) {
      isDeleting.value = true;
      typewriterTimeout = setTimeout(tickTypewriter, 2200);
      return;
    }
    typewriterTimeout = setTimeout(tickTypewriter, 75);
  } else {
    typedText.value = fullText.slice(0, typedText.value.length - 1);

    if (typedText.value === '') {
      isDeleting.value = false;
      currentPhraseIndex.value = (currentPhraseIndex.value + 1) % phrases.length;
      typewriterTimeout = setTimeout(tickTypewriter, 450);
      return;
    }
    typewriterTimeout = setTimeout(tickTypewriter, 40);
  }
}

function startTypewriter() {
  if (typewriterTimeout) clearTimeout(typewriterTimeout);
  typedText.value = '';
  isDeleting.value = false;
  tickTypewriter();
}

watch(() => localeStore.isAmharic, () => {
  startTypewriter();
});

const heroDesc = computed(() => {
  if (currentBanner.value) {
    const text = localeStore.isAmharic ? currentBanner.value.description_am : currentBanner.value.description_en;
    if (text) return text;
  }
  if (businessInfo.value) {
    const text = localeStore.isAmharic ? businessInfo.value.hero_desc_am : businessInfo.value.hero_desc_en;
    if (text) return text;
  }
  return localeStore.t('hero_desc');
});

const heroButtonText = computed(() => {
  if (currentBanner.value) {
    const text = localeStore.isAmharic ? currentBanner.value.button_text_am : currentBanner.value.button_text_en;
    if (text) return text;
  }
  if (businessInfo.value) {
    const text = localeStore.isAmharic ? businessInfo.value.hero_button_text_am : businessInfo.value.hero_button_text_en;
    if (text) return text;
  }
  return localeStore.t('shop_now');
});

const heroBadge = computed(() => {
  if (currentBanner.value) {
    return (localeStore.isAmharic ? currentBanner.value.badge_am : currentBanner.value.badge_en) || localeStore.t('delivery_badge');
  }
  return localeStore.t('delivery_badge');
});

// Real Business Info from Database
const businessInfo = ref(null);

async function fetchBusinessInfo() {
  try {
    const res = await api.get('/business-info');
    if (res.data) {
      businessInfo.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load business info', err);
  }
}

async function fetchBanners() {
  try {
    const res = await api.get('/banners');
    if (Array.isArray(res.data) && res.data.length > 0) {
      banners.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load banners', err);
  }
}

// Category Filter state & bilingual items
const selectedCategory = ref('All');
const categoryItems = [
  { key: 'All', transKey: 'cat_all' },
  { key: 'Cakes', transKey: 'cat_cakes' },
  { key: 'Bread', transKey: 'cat_bread' },
  { key: 'Cupcakes', transKey: 'cat_cupcakes' },
  { key: 'Pastries', transKey: 'cat_pastries' },
];

// Live products loaded from MySQL database
const liveProducts = ref([]);
const activePromotion = ref(null);
const loadingProducts = ref(true);

async function fetchLiveProducts() {
  loadingProducts.value = true;
  try {
    if (!branchStore.branches || branchStore.branches.length === 0) {
      await branchStore.fetchBranches();
    }
    const branchId = branchStore.selectedBranchId || branchStore.branches[0]?.branch_id || '';
    const query = branchId ? `?branch_id=${branchId}` : '';
    const res = await api.get(`/catalog/products${query}`);
    if (Array.isArray(res.data)) {
      liveProducts.value = res.data.map(p => {
        const avail = Number(p.available ?? p.available_stock ?? 20);
        const isSoldOut = avail <= 0 || p.stock_status === 'Sold out';
        return {
          id: p.product_id,
          productId: p.product_id,
          category: p.category_name || 'Cakes',
          name: p.product_name,
          price: Number(p.resolved_price || p.base_price),
          rating: p.rating ? String(p.rating) : '0',
          reviews: p.reviews_count || 0,
          image: p.image_url || '/mockup/shop_chocolate_cake.jpg',
          is_fasting: Boolean(p.is_fasting),
          available: avail,
          available_stock: avail,
          stock_status: p.stock_status,
          isSoldOut: isSoldOut,
          isAdding: false,
        };
      });
    }
  } catch (err) {
    console.error('Failed to load products from database', err);
  } finally {
    loadingProducts.value = false;
  }
}

// Displayed Products computed dynamically from live database products
const displayedProducts = computed(() => {
  if (liveProducts.value.length === 0) return [];

  if (selectedCategory.value === 'All') {
    return liveProducts.value.slice(0, 10);
  }

  return liveProducts.value.filter(p => {
    const cat = (p.category || '').toLowerCase();
    const target = selectedCategory.value.toLowerCase();
    if (target.includes('cake') && !target.includes('cupcake')) {
      return cat.includes('cake') && !cat.includes('cupcake');
    }
    return cat.includes(target);
  });
});

// Dynamic Reviews Slides loaded from MySQL database with instant authentic fallback
const defaultFeaturedReviews = [
  [
    {
      review_id: 6,
      name: 'Hanan Mohammed',
      avatar: null,
      rating: 5,
      quote: 'Their custom cake team brought our anniversary sketch to life in 3D! Plus the chocolate chip cookies are chewy and loaded with chocolate.',
      product_id: 6,
      product_name: 'Double Chocolate Chip Cookie',
      created_at: '4 days ago',
    },
    {
      review_id: 5,
      name: 'Yonas Bekele',
      avatar: null,
      rating: 5,
      quote: 'Crispy crust and soft airy crumb. You can immediately tell they use traditional sourdough fermentation. Our go-to bakery every single weekend!',
      product_id: 3,
      product_name: 'Artisan French Baguette',
      created_at: '6 days ago',
    },
    {
      review_id: 4,
      name: 'Almaz Hailu',
      avatar: null,
      rating: 5,
      quote: 'The red velvet cupcakes with cream cheese frosting are pure heaven. Fast delivery right to our office in Piazza, beautifully packaged.',
      product_id: 5,
      product_name: 'Red Velvet Cupcake',
      created_at: '1 week ago',
    },
  ],
  [
    {
      review_id: 3,
      name: 'Mekdes Tadesse',
      avatar: null,
      rating: 5,
      quote: "Ordered the Strawberry Shortcake for my daughter's birthday. Fresh berries, light sponge, and elegant piping. Exceeded our highest expectations!",
      product_id: 1,
      product_name: 'Strawberry Shortcake',
      created_at: '1 week ago',
    },
    {
      review_id: 2,
      name: 'Daniel Kebede',
      avatar: null,
      rating: 5,
      quote: 'Best French croissants in Dessie! Golden, flaky, and packed with real butter flavor. Pairs wonderfully with their espresso every morning.',
      product_id: 4,
      product_name: 'Golden Butter Croissant',
      created_at: '1 week ago',
    },
    {
      review_id: 1,
      name: 'Sara Ahmed',
      avatar: null,
      rating: 5,
      quote: 'The Chocolate Truffle Cake was absolutely sublime! So rich, moist, and arrived perfectly chilled at our Dessie family celebration. Everyone was asking where we ordered it!',
      product_id: 2,
      product_name: 'Chocolate Truffle Cake',
      created_at: '2 weeks ago',
    },
  ],
];

const reviewSlides = ref(defaultFeaturedReviews);

async function fetchFeaturedReviews() {
  try {
    const res = await api.get('/reviews/featured');
    if (res.data?.slides && res.data.slides.length > 0) {
      reviewSlides.value = res.data.slides;
    } else {
      reviewSlides.value = defaultFeaturedReviews;
    }
  } catch (err) {
    reviewSlides.value = defaultFeaturedReviews;
  }
}

async function fetchActivePromotion() {
  try {
    const res = await api.get('/promotions/active');
    if (Array.isArray(res.data) && res.data.length > 0) {
      activePromotion.value = res.data[0];
    }
  } catch (err) {
    console.error('Failed to load active promotion from API', err);
  }
}

const activeReviewSlide = ref(0);
let reviewTimer = null;

const currentReviewsSlide = computed(() => {
  return reviewSlides.value[activeReviewSlide.value] || reviewSlides.value[0] || [];
});

function setReviewSlide(idx) {
  activeReviewSlide.value = idx;
}

function prevReviewSlide() {
  if (reviewSlides.value.length > 0) {
    activeReviewSlide.value = (activeReviewSlide.value - 1 + reviewSlides.value.length) % reviewSlides.value.length;
  }
}

function nextReviewSlide() {
  if (reviewSlides.value.length > 0) {
    activeReviewSlide.value = (activeReviewSlide.value + 1) % reviewSlides.value.length;
  }
}

function startReviewAutoplay() {
  if (reviewTimer) clearInterval(reviewTimer);
  reviewTimer = setInterval(() => {
    if (reviewSlides.value.length > 0) {
      activeReviewSlide.value = (activeReviewSlide.value + 1) % reviewSlides.value.length;
    }
  }, 6000);
}

function pauseReviewAutoplay() {
  if (reviewTimer) {
    clearInterval(reviewTimer);
    reviewTimer = null;
  }
}

// Watch selected branch changes to reload dynamic branch inventory prices
watch(() => branchStore.selectedBranchId, () => {
  fetchLiveProducts();
});

onMounted(() => {
  fetchBanners();
  fetchBusinessInfo();
  fetchLiveProducts();
  fetchFeaturedReviews();
  fetchActivePromotion();
  wishlistStore.init();
  startReviewAutoplay();
  startTypewriter();
});

onUnmounted(() => {
  pauseReviewAutoplay();
  if (typewriterTimeout) clearTimeout(typewriterTimeout);
});

function goToProduct(id) {
  router.push(`/product/${id}`);
}

async function toggleWishlist(product) {
  const res = await wishlistStore.toggleWishlist(product);
  const name = product.name || product.product_name || 'Item';
  if (res.wishlisted) {
    toastStore.success(`Added "${name}" to favorites!`);
  } else {
    toastStore.info(`Removed "${name}" from favorites.`);
  }
}

function handleAddToCart(p) {
  if (!branchStore.isCurrentBranchShoppingEnabled) {
    toastStore.warning(localeStore.isAmharic 
      ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለግዢ ዝግ ነው።' 
      : 'Sorry, this branch is currently unavailable for shopping.');
    return;
  }

  if (p.isSoldOut || (p.available ?? 0) <= 0) {
    toastStore.warning(`${p.name} is currently sold out at ${branchStore.currentBranchName} branch.`);
    return;
  }

  if (!authStore.isAuthenticated) {
    toastStore.info(localeStore.isAmharic 
      ? 'ምርቶችን ወደ ቅርጫት ለመጨመር እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to add items to your cart.');

    const pendingItem = {
      product_id: p.productId,
      product_name: p.name,
      price: p.price,
      image_url: p.image,
      available_stock: p.available ?? 0,
      available: p.available ?? 0,
      isSoldOut: p.isSoldOut,
      branch_id: branchStore.selectedBranchId,
      quantity: 1,
    };
    try {
      sessionStorage.setItem('pending_cart_item', JSON.stringify(pendingItem));
    } catch (e) {}

    router.push({
      path: '/login',
      query: {
        redirect: '/',
        pending_add_id: p.productId,
      },
    });
    return;
  }

  p.isAdding = true;

  const result = cartStore.addItem(
    {
      product_id: p.productId,
      product_name: p.name,
      price: p.price,
      image_url: p.image,
      available_stock: p.available ?? 0,
      available: p.available ?? 0,
      isSoldOut: p.isSoldOut,
    },
    branchStore.selectedBranchId,
    1
  );

  setTimeout(() => {
    p.isAdding = false;
  }, 400);

  if (result && result.reason === 'capped') {
    toastStore.warning(result.message);
  } else if (result && result.success === false) {
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
  if (banners.value.length > 0) {
    currentBannerIdx.value = (currentBannerIdx.value - 1 + banners.value.length) % banners.value.length;
    heroImageLoaded.value = false;
  }
}

function nextSlide() {
  if (banners.value.length > 0) {
    currentBannerIdx.value = (currentBannerIdx.value + 1) % banners.value.length;
    heroImageLoaded.value = false;
  }
}
</script>
