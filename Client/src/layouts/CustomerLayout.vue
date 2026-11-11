<template>
  <div class="min-h-screen bg-[#FAF7F5] dark:bg-[#170E0A] text-[#29231F] dark:text-[#F7EFE9] flex flex-col font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] overflow-x-clip w-full relative">
    <!-- Top Bar: Dark Charcoal #1A1412 -->
    <div class="bg-[#1A1412] text-[#E8DED8] text-xs py-1.5 sm:py-2 border-b border-[#281E19] print:hidden">
      <div class="sweetbakes-container flex flex-col sm:flex-row items-center justify-between gap-1.5 sm:gap-2">
        <!-- Left: Location, Phone, Email -->
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-2 sm:gap-x-3 gap-y-1 text-[11px] sm:text-xs text-[#D8CDC5] w-full sm:w-auto">
          <!-- Branch Location Trigger -->
          <div>
            <button
              @click="branchStore.openPicker()"
              class="flex items-center space-x-1 sm:space-x-1.5 hover:text-white transition font-medium cursor-pointer"
              title="Choose Your Branch"
            >
              <span class="text-rose-400">📍</span>
              <span class="truncate max-w-[140px] xs:max-w-[200px] sm:max-w-none">{{ activeBranchLabel }}</span>
              <span class="text-[10px] opacity-70">▾</span>
            </button>
          </div>

          <span class="text-[#4A3D36] hidden xs:inline">|</span>

          <a :href="'tel:' + (businessInfo?.phone || '+251911123456')" class="hidden xs:flex items-center space-x-1 hover:text-white transition">
            <span>📞</span>
            <span>{{ businessInfo?.phone || '+251 911 123 456' }}</span>
          </a>

          <span class="text-[#4A3D36] hidden sm:inline">|</span>

          <a :href="'mailto:' + (businessInfo?.email || 'info@sweetbakes.com')" class="hidden sm:flex items-center space-x-1 hover:text-white transition">
            <span>✉️</span>
            <span>{{ businessInfo?.email || 'info@sweetbakes.com' }}</span>
          </a>
        </div>

        <!-- Right: Follow Us Social Icons -->
        <div class="hidden md:flex items-center space-x-3 text-xs text-[#D8CDC5]">
          <span class="text-[#B3A49C] text-[11px]">{{ localeStore.t('follow_us') }}</span>
          <!-- Facebook -->
          <a href="#" class="w-5 h-5 rounded-full flex items-center justify-center hover:text-white transition" title="Facebook">
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
              <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
            </svg>
          </a>
          <!-- Instagram -->
          <a href="#" class="w-5 h-5 rounded-full flex items-center justify-center hover:text-white transition" title="Instagram">
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
            </svg>
          </a>
          <!-- YouTube -->
          <a href="#" class="w-5 h-5 rounded-full flex items-center justify-center hover:text-white transition" title="YouTube">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
            </svg>
          </a>
          <!-- TikTok -->
          <a href="#" class="w-5 h-5 rounded-full flex items-center justify-center hover:text-white transition" title="TikTok">
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
              <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Main Navigation Bar: Pure White -->
    <header ref="headerRef" class="sticky top-0 z-40 bg-white dark:bg-[#1C110C] border-b border-[#F0EAE3] dark:border-[#352117] shadow-xs print:hidden">
      <div class="sweetbakes-container h-16 sm:h-20 flex items-center justify-between gap-1.5 xs:gap-2 sm:gap-4">
        <!-- SweetBakes Logo -->
        <router-link to="/" class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1 lg:flex-initial lg:max-w-[200px] xl:max-w-[280px] 2xl:max-w-none group">
          <!-- Cupcake Icon -->
          <div class="w-8 h-8 xs:w-9 xs:h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-[#331A24] border border-rose-100 dark:border-[#58263A] flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform shrink-0">
            <span class="text-lg xs:text-xl sm:text-2xl">🧁</span>
          </div>
          <div class="min-w-0 flex-1 lg:flex-initial">
            <div class="text-lg xs:text-xl sm:text-3xl lg:text-[28px] font-logo font-bold text-[#2A1810] dark:text-[#FBF4ED] leading-none truncate">
              <template v-if="businessInfo?.site_name">{{ businessInfo.site_name }}</template>
              <template v-else>Sweet<span class="text-[#DE1B54]">Bakes</span></template>
            </div>
            <div class="hidden sm:block text-[10px] sm:text-xs tracking-wider text-[#8C7E77] dark:text-[#A8988E] font-medium uppercase mt-0.5 truncate">
              {{ businessInfo?.site_tagline || 'Cakes · Bread · Happiness' }}
            </div>
          </div>
        </router-link>

        <!-- Center Nav Links (Balanced spacing from 1024px to 2560px) -->
        <nav
          ref="desktopNavRef"
          class="relative hidden lg:flex items-center space-x-4 xl:space-x-8 2xl:space-x-10 text-sm xl:text-base font-bold text-[#2A1810] dark:text-[#F7EFE9] shrink-0 whitespace-nowrap"
        >
          <!-- Home -->
          <router-link
            :ref="(el) => setNavRef('home', el)"
            to="/"
            @click="triggerNavPress('home')"
            class="sweet-nav-link py-2"
            :class="[
              activeNavKey === 'home' ? 'sweet-nav-link--active text-[#DE1B54] font-bold' : '',
              pressedNavKey === 'home' ? 'sweet-nav-link--pressed' : ''
            ]"
          >
            <span class="sweet-nav-label">{{ localeStore.t('home') }}</span>
            <span class="sweet-nav-underline" aria-hidden="true"></span>
          </router-link>

          <!-- Shop Dropdown -->
          <div
            ref="shopWrapperRef"
            class="relative flex items-center"
            @mouseenter="openShopDropdown"
            @mouseleave="scheduleCloseShopDropdown"
          >
            <router-link
              :ref="(el) => setNavRef('shop', el)"
              to="/shop"
              class="sweet-nav-link py-2"
              :class="[
                activeNavKey === 'shop' ? 'sweet-nav-link--active text-[#DE1B54] font-bold' : '',
                pressedNavKey === 'shop' ? 'sweet-nav-link--pressed' : '',
                shopDropdownOpen ? 'sweet-nav-link--open' : ''
              ]"
              :aria-expanded="shopDropdownOpen"
              aria-haspopup="true"
              @click="triggerNavPress('shop'); closeShopDropdownImmediate()"
              @focus="openShopDropdown"
            >
              <span class="sweet-nav-label">
                <span>{{ localeStore.t('shop') }}</span>
                <span
                  class="text-xs opacity-75 inline-block transition-transform duration-250 ease-out"
                  :class="{ 'rotate-180 opacity-100': shopDropdownOpen }"
                >▾</span>
              </span>
              <span class="sweet-nav-underline" aria-hidden="true"></span>
            </router-link>

            <!-- Dropdown Menu (Smooth Fade & Slide-Down + Outside Click Close) -->
            <Transition name="sweet-dropdown">
              <div
                v-if="shopDropdownOpen"
                class="absolute left-0 top-full pt-2 z-50"
              >
                <div class="w-64 bg-white dark:bg-[#221610] rounded-2xl shadow-2xl border border-gray-100/90 dark:border-[#3D261B] p-2">
                  <router-link
                    to="/shop"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.path === '/shop' && !$route.query.category && !$route.query.yetsom && !$route.query.fasting ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54] font-black' : 'text-[#2A1810] dark:text-gray-200 hover:bg-[#FFF5F8] dark:hover:bg-[#2C1720] hover:text-[#DE1B54]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">✨</span>
                    <span>{{ localeStore.t('all_products') }}</span>
                  </router-link>

                  <router-link
                    to="/shop?category=Cakes"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.query.category === 'Cakes' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54] font-black' : 'text-[#2A1810] dark:text-gray-200 hover:bg-[#FFF5F8] dark:hover:bg-[#2C1720] hover:text-[#DE1B54]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">🎂</span>
                    <span>{{ localeStore.t('cakes') }}</span>
                  </router-link>

                  <router-link
                    to="/shop?category=Bread"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.query.category === 'Bread' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54] font-black' : 'text-[#2A1810] dark:text-gray-200 hover:bg-[#FFF5F8] dark:hover:bg-[#2C1720] hover:text-[#DE1B54]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">🥖</span>
                    <span>{{ localeStore.t('bread') }}</span>
                  </router-link>

                  <router-link
                    to="/shop?category=Pastries"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.query.category === 'Pastries' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54] font-black' : 'text-[#2A1810] dark:text-gray-200 hover:bg-[#FFF5F8] dark:hover:bg-[#2C1720] hover:text-[#DE1B54]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">🥐</span>
                    <span>{{ localeStore.t('cupcakes_croissants') }}</span>
                  </router-link>

                  <router-link
                    to="/shop?yetsom=1"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.query.yetsom === '1' || $route.query.fasting === '1' ? 'bg-emerald-100/70 dark:bg-emerald-950/60 text-[#075e43] dark:text-emerald-300 font-black' : 'text-[#0b6b4e] dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-[#06503b]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">🌿</span>
                    <span>{{ localeStore.t('yetsom') }}</span>
                  </router-link>

                  <router-link
                    to="/custom-cake"
                    @click="closeShopDropdownImmediate()"
                    class="sweet-dropdown-item group/item flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-bold"
                    :class="$route.path === '/custom-cake' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54] font-black' : 'text-[#DE1B54] hover:bg-[#FFF5F8] dark:hover:bg-[#2C1720] hover:text-[#C51345]'"
                  >
                    <span class="text-base transition-transform duration-200 group-hover/item:scale-110">✍️</span>
                    <span>{{ localeStore.t('custom_cake') }}</span>
                  </router-link>
                </div>
              </div>
            </Transition>
          </div>

          <!-- About Anchor / Page -->
          <a
            :ref="(el) => setNavRef('about', el)"
            href="/#about"
            @click.prevent="triggerNavPress('about'); goToSection('about')"
            class="sweet-nav-link py-2"
            :class="[
              activeNavKey === 'about' ? 'sweet-nav-link--active text-[#DE1B54] font-bold' : '',
              pressedNavKey === 'about' ? 'sweet-nav-link--pressed' : ''
            ]"
          >
            <span class="sweet-nav-label">{{ localeStore.t('about') }}</span>
            <span class="sweet-nav-underline" aria-hidden="true"></span>
          </a>

          <!-- Reviews Anchor -->
          <a
            :ref="(el) => setNavRef('reviews', el)"
            href="/#reviews"
            @click.prevent="triggerNavPress('reviews'); goToSection('reviews')"
            class="sweet-nav-link py-2"
            :class="[
              activeNavKey === 'reviews' ? 'sweet-nav-link--active text-[#DE1B54] font-bold' : '',
              pressedNavKey === 'reviews' ? 'sweet-nav-link--pressed' : ''
            ]"
          >
            <span class="sweet-nav-label">{{ localeStore.t('reviews') }}</span>
            <span class="sweet-nav-underline" aria-hidden="true"></span>
          </a>

          <!-- Contact -->
          <a
            :ref="(el) => setNavRef('contact', el)"
            href="/#contact"
            @click.prevent="triggerNavPress('contact'); goToSection('contact')"
            class="sweet-nav-link py-2 cursor-pointer"
            :class="[
              activeNavKey === 'contact' ? 'sweet-nav-link--active text-[#DE1B54] font-bold' : '',
              pressedNavKey === 'contact' ? 'sweet-nav-link--pressed' : ''
            ]"
          >
            <span class="sweet-nav-label">{{ localeStore.t('contact') }}</span>
            <span class="sweet-nav-underline" aria-hidden="true"></span>
          </a>

          <!-- Sliding Pink Active Navigation Indicator -->
          <span
            class="sweet-nav-active-bar"
            aria-hidden="true"
            :style="activeBarInlineStyle"
          ></span>
        </nav>

        <!-- Right Side: Branch, Language, Theme, Account, Cart & Mobile Menu -->
        <div class="flex items-center gap-1 xs:gap-1.5 sm:gap-2.5 lg:gap-3 shrink-0">
          <!-- Choose Your Branch Sticky Control (Desktop / Tablet) -->
          <button
            @click="branchStore.openPicker()"
            class="hidden md:flex items-center space-x-1.5 px-3 py-1.5 rounded-full border border-gray-200 dark:border-[#3D261B] hover:border-[#DE1B54] bg-[#FAF7F5] dark:bg-[#1E120B] text-xs font-semibold text-[#2A1810] dark:text-[#FAF3EE] transition shadow-2xs cursor-pointer group shrink-0"
            :title="localeStore.t('select_branch') || 'Choose Your Branch'"
          >
            <span class="text-[#DE1B54] group-hover:scale-110 transition-transform">📍</span>
            <span class="font-bold">{{ activeBranchLabelShort }}</span>
            <span class="text-[10px] text-gray-400 group-hover:text-[#DE1B54]">▾</span>
          </button>

          <!-- Bilingual Language Toggle Button: EN | አማ (Always Accessible) -->
          <button
            @click="localeStore.toggleLocale()"
            data-testid="header-lang-toggle"
            class="flex items-center px-1.5 xs:px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full border border-gray-200 dark:border-[#3D261B] hover:border-[#DE1B54] text-[11px] sm:text-xs font-bold transition shadow-2xs cursor-pointer select-none bg-white dark:bg-[#1E120B] min-h-[34px] sm:min-h-[36px] whitespace-nowrap shrink-0"
            :title="localeStore.isAmharic ? 'Switch to English' : 'ወደ አማርኛ ቀይር'"
            aria-label="Language Switcher"
          >
            <span :class="!localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">EN</span>
            <span class="mx-0.5 sm:mx-1 text-gray-300 dark:text-gray-600">|</span>
            <span :class="localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">አማ</span>
          </button>

          <!-- Theme Toggle Button: Sun & Moon (Always Accessible) -->
          <button
            @click="themeStore.toggleTheme()"
            data-testid="header-theme-toggle"
            class="w-8 h-8 xs:w-9 xs:h-9 rounded-full border border-gray-200 dark:border-[#3D261B] hover:border-[#DE1B54] flex items-center justify-center text-xs sm:text-sm transition shadow-2xs cursor-pointer select-none bg-white dark:bg-[#1E120B] shrink-0"
            :title="themeStore.isDark ? (localeStore.isAmharic ? 'ወደ ብሩህ ገጽታ ቀይር (Light Mode)' : 'Switch to Light Mode') : (localeStore.isAmharic ? 'ወደ ጨለማ ገጽታ ቀይር (Dark Mode)' : 'Switch to Dark Mode')"
            aria-label="Theme Toggle"
          >
            <span v-if="themeStore.isDark" class="text-amber-300 text-sm sm:text-base">☀️</span>
            <span v-else class="text-gray-600 dark:text-gray-300 text-sm sm:text-base">🌙</span>
          </button>

          <!-- Single Dedicated "Sign In" Button for Guests / New Customers -->
          <router-link
            v-if="!authStore.user"
            to="/login"
            data-testid="header-account-btn"
            class="flex items-center space-x-1 sm:space-x-1.5 px-2 xs:px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-[#DE1B54] hover:bg-[#C51345] text-white text-[11px] xs:text-xs sm:text-sm font-bold shadow-xs hover:shadow-md transition-all cursor-pointer whitespace-nowrap active:scale-95 min-h-[34px] sm:min-h-[36px] shrink-0"
            :title="localeStore.t('sign_in')"
            :aria-label="localeStore.t('sign_in')"
          >
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span class="hidden min-[360px]:inline">{{ localeStore.t('sign_in') }}</span>
          </router-link>

          <!-- Authenticated Customer Menu (Visible when logged in) -->
          <div v-else class="relative shrink-0">
            <!-- Click-away backdrop to easily close dropdown -->
            <div
              v-if="accountMenuOpen"
              class="fixed inset-0 z-40 bg-transparent"
              @click="accountMenuOpen = false"
            ></div>

            <button
              @click="accountMenuOpen = !accountMenuOpen"
              data-testid="header-account-btn"
              class="relative z-50 flex items-center space-x-1 sm:space-x-1.5 px-1.5 xs:px-2 sm:px-3 py-1 sm:py-1.5 rounded-full border border-rose-200 dark:border-[#3D261B] bg-rose-50/70 dark:bg-[#1E120B] hover:border-[#DE1B54] text-xs sm:text-sm font-bold text-[#2A1810] dark:text-[#E8DED8] transition shadow-2xs cursor-pointer min-h-[34px] sm:min-h-[36px]"
              :title="localeStore.t('account')"
              :aria-expanded="accountMenuOpen"
              aria-label="User Profile Menu"
            >
              <div class="w-6 h-6 rounded-full overflow-hidden bg-[#DE1B54] text-white flex items-center justify-center text-[11px] sm:text-xs font-black shrink-0">
                <img
                  v-if="authStore.user.avatar_url"
                  :src="authStore.user.avatar_url"
                  alt="Avatar"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{ authStore.user.full_name ? authStore.user.full_name.charAt(0).toUpperCase() : '👤' }}</span>
              </div>
              <span class="hidden md:inline max-w-[100px] truncate">{{ authStore.user.full_name.split(' ')[0] }}</span>
              <span class="hidden xs:inline text-[10px] opacity-60">▾</span>
            </button>

            <!-- Dropdown Menu -->
            <Transition name="sweet-dropdown">
              <div
                v-if="accountMenuOpen"
                class="absolute right-0 mt-2 w-52 max-w-[calc(100vw-1rem)] bg-white dark:bg-[#221610] rounded-2xl shadow-xl border border-gray-100 dark:border-[#3D261B] py-2 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-100 dark:border-[#3D261B] flex items-center space-x-2.5">
                  <div class="w-8 h-8 rounded-full overflow-hidden bg-[#DE1B54] text-white flex items-center justify-center text-xs font-bold shrink-0">
                    <img
                      v-if="authStore.user.avatar_url"
                      :src="authStore.user.avatar_url"
                      alt="Avatar"
                      class="w-full h-full object-cover"
                    />
                    <span v-else>{{ authStore.user.full_name ? authStore.user.full_name.charAt(0).toUpperCase() : '👤' }}</span>
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-xs text-gray-800 dark:text-gray-100 truncate">{{ authStore.user.full_name }}</div>
                    <div class="text-[10px] text-[#DE1B54] font-semibold">{{ authStore.user.role }}</div>
                  </div>
                </div>

                <div class="py-1 text-xs font-medium">
                  <!-- Customer specific links -->
                  <template v-if="!authStore.isStaff">
                    <router-link
                      to="/account"
                      @click="accountMenuOpen = false"
                      class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-rose-50 dark:hover:bg-[#331A24] hover:text-[#DE1B54]"
                    >
                      <span>👤 {{ localeStore.t('my_orders') }}</span>
                    </router-link>

                    <router-link
                      to="/account?tab=wishlist"
                      @click="accountMenuOpen = false"
                      class="flex items-center justify-between px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-rose-50 dark:hover:bg-[#331A24] hover:text-[#DE1B54]"
                    >
                      <div class="flex items-center space-x-2">
                        <span>❤️</span>
                        <span>{{ localeStore.isAmharic ? 'የምኞት ዝርዝር' : 'Wishlist & Saved' }}</span>
                      </div>
                      <span v-if="wishlistStore.count > 0" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/60 text-[#DE1B54]">
                        {{ wishlistStore.count }}
                      </span>
                    </router-link>
                  </template>

                  <!-- Staff specific links (Section 8: Employee account resides in Admin Dashboard) -->
                  <template v-else>
                    <router-link
                      to="/admin/my-account"
                      @click="accountMenuOpen = false"
                      class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 font-semibold hover:bg-rose-50 dark:hover:bg-[#331A24] hover:text-[#DE1B54]"
                    >
                      <span>💼 {{ localeStore.isAmharic ? 'የእኔ ሰራተኛ ፕሮፋይል' : 'My Staff Account' }}</span>
                    </router-link>
                    <router-link
                      to="/admin/overview"
                      @click="accountMenuOpen = false"
                      class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 font-semibold hover:bg-rose-50 dark:hover:bg-[#331A24]"
                    >
                      <span>⚙️ {{ localeStore.t('staff_portal') }}</span>
                    </router-link>
                  </template>

                  <button
                    @click="handleLogout"
                    class="w-full text-left flex items-center px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 border-t border-gray-100 dark:border-[#3D261B] mt-1 cursor-pointer"
                  >
                    <span>🚪 {{ localeStore.t('logout') }}</span>
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Quick Wishlist Heart Button (Tablet & Desktop header; also inside mobile drawer on phones) -->
          <router-link
            :to="authStore.user ? '/account?tab=wishlist' : '/shop'"
            class="hidden sm:flex relative items-center justify-center p-2 rounded-full hover:bg-rose-50 dark:hover:bg-[#331A24] transition cursor-pointer text-gray-700 dark:text-gray-200 w-9 h-9 shrink-0"
            :title="localeStore.isAmharic ? 'የምኞት ዝርዝር' : 'Wishlist & Favorites'"
            aria-label="Wishlist & Favorites"
          >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span
              v-if="wishlistStore.count > 0"
              class="absolute -top-0.5 -right-0.5 w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-[#DE1B54] text-white text-[10px] sm:text-[11px] font-bold flex items-center justify-center shadow-xs"
            >
              {{ wishlistStore.count }}
            </span>
          </router-link>

          <!-- Quick Cart Drawer Trigger Button (Always Accessible) -->
          <button
            @click="cartStore.toggleDrawer()"
            data-testid="header-cart-btn"
            class="relative flex items-center justify-center p-1.5 xs:p-2 rounded-full hover:bg-rose-50 dark:hover:bg-[#331A24] transition cursor-pointer text-gray-700 dark:text-gray-200 w-8 h-8 xs:w-9 xs:h-9 shrink-0"
            title="Shopping Cart"
            aria-label="Shopping Cart"
          >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span
              v-if="cartStore.totalItems > 0"
              class="absolute -top-0.5 -right-0.5 w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-[#DE1B54] text-white text-[10px] sm:text-[11px] font-bold flex items-center justify-center shadow-xs transition-transform"
              :class="{ 'animate-cart-pop': cartBadgePop }"
            >
              {{ cartStore.totalItems }}
            </span>
          </button>

          <!-- Mobile Hamburger Trigger Button (visible < 1024px) -->
          <button
            @click="mobileNavOpen = !mobileNavOpen"
            data-testid="header-hamburger-btn"
            class="lg:hidden p-1.5 xs:p-2 rounded-xl text-gray-700 dark:text-gray-200 hover:text-[#DE1B54] hover:bg-rose-50 dark:hover:bg-[#331A24] transition cursor-pointer w-9 h-9 flex items-center justify-center shrink-0"
            title="Menu"
            aria-label="Open Navigation Menu"
            :aria-expanded="mobileNavOpen"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation Drawer -->
      <Transition name="sweet-mobile-drawer">
        <div
          v-if="mobileNavOpen"
          data-testid="mobile-nav-drawer"
          class="lg:hidden bg-white dark:bg-[#1C110C] border-t border-gray-100 dark:border-[#3D261B] px-4 sm:px-6 py-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] space-y-3 shadow-xl max-h-[82dvh] overflow-y-auto overscroll-contain"
        >
        <!-- Mobile Drawer Active Branch Banner -->
        <div class="p-3 bg-rose-50/60 dark:bg-[#25150E] rounded-2xl border border-rose-100 dark:border-[#3D261B] flex items-center justify-between gap-2 shadow-2xs">
          <div class="flex items-center space-x-2.5 min-w-0">
            <span class="text-lg text-[#DE1B54] shrink-0">📍</span>
            <div class="min-w-0">
              <div class="text-[10px] uppercase font-bold text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.t('select_branch') || 'Selected Branch' }}</div>
              <div class="text-xs font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] truncate">{{ activeBranchLabelShort }} Branch</div>
            </div>
          </div>
          <button
            @click="branchStore.openPicker(); mobileNavOpen = false"
            class="px-3 py-1.5 min-h-[36px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-[11px] font-bold rounded-xl shadow-xs cursor-pointer active:scale-95 shrink-0"
          >
            {{ localeStore.isAmharic ? 'ቀይር' : 'Change' }}
          </button>
        </div>

        <!-- Mobile Search Bar (Only on phones < 768px) -->
        <div class="md:hidden pb-1">
          <div class="relative w-full">
            <input
              v-model="searchKeyword"
              @keyup.enter="handleSearch(); mobileNavOpen = false"
              type="text"
              :placeholder="localeStore.t('search_placeholder')"
              class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#3D261B] rounded-full py-2.5 pl-4 pr-10 text-xs text-gray-800 dark:text-[#F7EFE9] placeholder:text-gray-400 focus:outline-none min-h-[42px]"
            />
            <button
              @click="handleSearch(); mobileNavOpen = false"
              class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-[#DE1B54] cursor-pointer"
              aria-label="Search"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Language & Theme Switcher Row -->
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-[#3D261B]">
          <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">🌐</span>
            <button
              @click="localeStore.toggleLocale()"
              class="flex items-center px-3 py-1.5 min-h-[38px] rounded-full border border-gray-200 dark:border-[#3D261B] text-xs font-bold transition cursor-pointer select-none bg-white dark:bg-[#1E120B]"
            >
              <span :class="!localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">EN</span>
              <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
              <span :class="localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">አማ</span>
            </button>
          </div>

          <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ themeStore.isDark ? 'Dark Mode' : 'Light Mode' }}</span>
            <button
              @click="themeStore.toggleTheme()"
              class="w-9 h-9 rounded-full border border-gray-200 dark:border-[#3D261B] flex items-center justify-center text-sm transition cursor-pointer select-none bg-white dark:bg-[#1E120B]"
              aria-label="Toggle Color Theme"
            >
              <span v-if="themeStore.isDark">☀️</span>
              <span v-else>🌙</span>
            </button>
          </div>
        </div>

        <!-- Main Mobile Navigation Links: Home, Shop (Touch Dropdown), About, Reviews, Contact -->
        <nav aria-label="Mobile Navigation" class="space-y-1">
          <router-link
            to="/"
            @click="mobileNavOpen = false"
            class="flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-xl text-sm font-bold transition-all duration-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54] active:scale-[0.99]"
            :class="activeNavKey === 'home' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#F7EFE9]'"
          >
            <span>{{ localeStore.t('home') }}</span>
            <span v-if="activeNavKey === 'home'" class="w-1.5 h-1.5 rounded-full bg-[#DE1B54]"></span>
          </router-link>

          <!-- Touch-Interactive Shop Dropdown Accordion -->
          <div>
            <button
              type="button"
              data-testid="mobile-shop-toggle"
              @click="mobileShopOpen = !mobileShopOpen"
              :aria-expanded="mobileShopOpen"
              class="w-full flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-xl text-sm font-bold transition-all duration-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54] cursor-pointer active:scale-[0.99]"
              :class="activeNavKey === 'shop' || mobileShopOpen ? 'text-[#DE1B54] bg-[#FFF5F8] dark:bg-[#2C1720]' : 'text-[#2A1810] dark:text-[#F7EFE9]'"
            >
              <span>{{ localeStore.t('shop') }}</span>
              <span
                class="text-xs inline-block transition-transform duration-250 ease-out"
                :class="{ 'rotate-180': mobileShopOpen }"
              >▾</span>
            </button>

            <div
              class="sweet-accordion pl-3 ml-3 border-l-2 border-rose-200 dark:border-[#DE1B54]/40"
              :class="{ 'sweet-accordion--open': mobileShopOpen }"
              data-testid="mobile-shop-submenu"
            >
              <div class="sweet-accordion-inner space-y-0.5 py-1.5">
                <router-link
                  to="/shop"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-semibold hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
                  :class="$route.path === '/shop' && !$route.query.category && !$route.query.yetsom && !$route.query.fasting ? 'text-[#DE1B54] font-bold bg-[#FFF0F4] dark:bg-[#341822]' : 'text-gray-800 dark:text-[#E8DED8]'"
                >
                  <span>✨</span>
                  <span>{{ localeStore.t('all_products') }}</span>
                </router-link>
                <router-link
                  to="/shop?category=Cakes"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-semibold hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
                  :class="$route.query.category === 'Cakes' ? 'text-[#DE1B54] font-bold bg-[#FFF0F4] dark:bg-[#341822]' : 'text-gray-800 dark:text-[#E8DED8]'"
                >
                  <span>🎂</span>
                  <span>{{ localeStore.t('cakes') }}</span>
                </router-link>
                <router-link
                  to="/shop?category=Bread"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-semibold hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
                  :class="$route.query.category === 'Bread' ? 'text-[#DE1B54] font-bold bg-[#FFF0F4] dark:bg-[#341822]' : 'text-gray-800 dark:text-[#E8DED8]'"
                >
                  <span>🥖</span>
                  <span>{{ localeStore.t('bread') }}</span>
                </router-link>
                <router-link
                  to="/shop?category=Pastries"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-semibold hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
                  :class="$route.query.category === 'Pastries' ? 'text-[#DE1B54] font-bold bg-[#FFF0F4] dark:bg-[#341822]' : 'text-gray-800 dark:text-[#E8DED8]'"
                >
                  <span>🥐</span>
                  <span>{{ localeStore.t('cupcakes_croissants') }}</span>
                </router-link>
                <router-link
                  to="/shop?yetsom=1"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-bold text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/30 hover:text-emerald-950"
                >
                  <span>🌿</span>
                  <span>{{ localeStore.t('yetsom') }}</span>
                </router-link>
                <router-link
                  to="/custom-cake"
                  @click="mobileNavOpen = false"
                  class="sweet-dropdown-item flex items-center space-x-2.5 px-3 py-2 min-h-[40px] rounded-xl text-xs font-bold text-[#DE1B54] hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#C51345]"
                >
                  <span>✍️</span>
                  <span>{{ localeStore.t('custom_cake') }}</span>
                </router-link>
              </div>
            </div>
          </div>

          <a
            href="/#about"
            @click.prevent="goToSection('about'); mobileNavOpen = false"
            class="flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-xl text-sm font-bold transition-all duration-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54] active:scale-[0.99]"
            :class="activeNavKey === 'about' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#F7EFE9]'"
          >
            <span>{{ localeStore.t('about') }}</span>
            <span v-if="activeNavKey === 'about'" class="w-1.5 h-1.5 rounded-full bg-[#DE1B54]"></span>
          </a>
          <a
            href="/#reviews"
            @click.prevent="goToSection('reviews'); mobileNavOpen = false"
            class="flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-xl text-sm font-bold transition-all duration-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54] active:scale-[0.99]"
            :class="activeNavKey === 'reviews' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#F7EFE9]'"
          >
            <span>{{ localeStore.t('reviews') }}</span>
            <span v-if="activeNavKey === 'reviews'" class="w-1.5 h-1.5 rounded-full bg-[#DE1B54]"></span>
          </a>
          <a
            href="/#contact"
            @click.prevent="goToSection('contact'); mobileNavOpen = false"
            class="flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-xl text-sm font-bold transition-all duration-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54] active:scale-[0.99] cursor-pointer"
            :class="activeNavKey === 'contact' ? 'bg-[#FFF0F4] dark:bg-[#341822] text-[#DE1B54]' : 'text-[#2A1810] dark:text-[#F7EFE9]'"
          >
            <span>{{ localeStore.t('contact') }}</span>
            <span v-if="activeNavKey === 'contact'" class="w-1.5 h-1.5 rounded-full bg-[#DE1B54]"></span>
          </a>
        </nav>

        <!-- Mobile Wishlist Quick Access + User Account & Portal Links -->
        <div class="pt-3 border-t border-gray-100 dark:border-[#3D261B] space-y-1 text-xs font-semibold">
          <router-link
            :to="authStore.user ? '/account?tab=wishlist' : '/shop'"
            @click="mobileNavOpen = false"
            class="flex items-center justify-between px-3 py-2 min-h-[40px] rounded-xl text-gray-700 dark:text-gray-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
          >
            <div class="flex items-center space-x-2">
              <span>❤️</span>
              <span>{{ localeStore.isAmharic ? 'የምኞት ዝርዝር' : 'Wishlist & Favorites' }}</span>
            </div>
            <span v-if="wishlistStore.count > 0" class="px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/60 text-[#DE1B54] text-[10px] font-bold">
              {{ wishlistStore.count }}
            </span>
          </router-link>

          <template v-if="authStore.user && !authStore.isStaff">
            <router-link
              to="/account"
              @click="mobileNavOpen = false"
              class="flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-gray-700 dark:text-gray-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
            >
              <span>👤</span>
              <span class="truncate">{{ localeStore.t('my_orders') }} ({{ authStore.user.full_name }})</span>
            </router-link>
          </template>

          <template v-if="authStore.user && authStore.isStaff">
            <router-link
              to="/admin/my-account"
              @click="mobileNavOpen = false"
              class="flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-[#DE1B54] font-bold hover:bg-rose-50/70 dark:hover:bg-[#2B1710]"
            >
              <span>💼</span>
              <span class="truncate">{{ localeStore.isAmharic ? 'የእኔ ሰራተኛ ፕሮፋይል' : 'My Staff Account' }} ({{ authStore.user.full_name }})</span>
            </router-link>
          </template>

          <router-link
            v-if="!authStore.user"
            to="/login"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-gray-700 dark:text-gray-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
          >
            <span>🔑</span>
            <span>{{ localeStore.t('sign_in') }}</span>
          </router-link>
          <router-link
            v-if="!authStore.user"
            to="/register"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-gray-700 dark:text-gray-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
          >
            <span>✨</span>
            <span>{{ localeStore.t('create_account') }}</span>
          </router-link>
          <router-link
            to="/admin/overview"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-gray-700 dark:text-gray-200 hover:bg-rose-50/70 dark:hover:bg-[#2B1710] hover:text-[#DE1B54]"
          >
            <span>⚙️</span>
            <span>{{ localeStore.t('staff_portal') }}</span>
          </router-link>
          <button
            v-if="authStore.user"
            @click="handleLogout(); mobileNavOpen = false"
            class="w-full text-left flex items-center space-x-2 px-3 py-2 min-h-[40px] rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
          >
            <span>🚪</span>
            <span>{{ localeStore.t('logout') }}</span>
          </button>
        </div>
        </div>
      </Transition>
    </header>

    <!-- Main View Slot with Smooth Reusable Page Transition -->
    <main class="flex-1 w-full min-h-[75vh] bg-[#FAF7F5] dark:bg-[#170E0A]">
      <router-view v-slot="{ Component, route: currentRoute }">
        <PageTransition :component="Component" :transition-key="currentRoute.path" />
      </router-view>
    </main>

    <!-- SweetBakes Real GPS Contact Us Section (Above Footer) -->
    <ContactSection id="contact" v-if="shouldShowContactAboveFooter" class="print:hidden" />

    <!-- Organic Seamless Wave Transition into Deep Chocolate Footer -->
    <div class="w-full overflow-hidden leading-none select-none pointer-events-none -mb-px print:hidden">
      <svg
        class="w-full h-8 sm:h-12 lg:h-14 text-[#27160E] fill-current block"
        viewBox="0 0 1440 64"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path d="M0,32 C240,64 480,16 720,32 C960,48 1200,16 1440,32 L1440,64 L0,64 Z"></path>
      </svg>
    </div>

    <!-- Footer: Deep Chocolate Brown #27160E -->
    <footer class="bg-[#27160E] text-[#E8DED8] pt-8 sm:pt-10 pb-8 relative w-full print:hidden">
      <div class="sweetbakes-container grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-8 lg:gap-8 xl:gap-10">
        <!-- Col 1: SweetBakes Brand & Slogan (4 cols desktop, 3 cols tablet) -->
        <div class="sm:col-span-2 md:col-span-3 lg:col-span-4 space-y-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
              🧁
            </div>
            <div>
              <div class="text-3xl font-logo font-bold text-white leading-none">
                <template v-if="businessInfo?.site_name">{{ businessInfo.site_name }}</template>
                <template v-else>Sweet<span class="text-[#DE1B54]">Bakes</span></template>
              </div>
              <div class="text-[10px] sm:text-xs tracking-wider text-[#AFA39B] uppercase mt-0.5">
                {{ businessInfo?.site_tagline || 'Cakes · Bread · Happiness' }}
              </div>
            </div>
          </div>

          <p class="text-sm text-[#C7BBB3] leading-relaxed max-w-sm">
            {{ localeStore.t('footer_mission') }}
          </p>

          <!-- Social Media Icons (Facebook, Instagram, Telegram, TikTok) from Database -->
          <div class="flex items-center space-x-3 pt-2 text-[#D8CDC5]">
            <a :href="businessInfo?.social?.facebook || 'https://facebook.com'" target="_blank" rel="noopener" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" /></svg>
            </a>
            <a :href="businessInfo?.social?.instagram || 'https://instagram.com'" target="_blank" rel="noopener" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" /></svg>
            </a>
            <a :href="businessInfo?.social?.telegram || 'https://t.me'" target="_blank" rel="noopener" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
            </a>
            <a :href="businessInfo?.social?.tiktok || 'https://tiktok.com'" target="_blank" rel="noopener" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" /></svg>
            </a>
          </div>
        </div>

        <!-- Col 2: Quick Links (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">{{ localeStore.t('quick_links') }}</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li>
              <router-link to="/" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('home') }}</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('shop') }}</span>
              </router-link>
            </li>
            <li>
              <a href="/#about" @click.prevent="goToSection('about')" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1 cursor-pointer">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('about') }}</span>
              </a>
            </li>
            <li>
              <a href="/#reviews" @click.prevent="goToSection('reviews')" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1 cursor-pointer">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('reviews') }}</span>
              </a>
            </li>
            <li>
              <a href="/#contact" @click.prevent="goToSection('contact')" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1 cursor-pointer">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('contact') }}</span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 3: Our Services (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">{{ localeStore.t('our_services') }}</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li>
              <router-link to="/shop?category=Cakes" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('cake_orders') }}</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop?category=Bread" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('bread_pastries') }}</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('delivery_service') }}</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('pickup_service') }}</span>
              </router-link>
            </li>
            <li>
              <router-link to="/branches" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>{{ localeStore.t('branch_locations') }}</span>
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 4: Our Branches (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">{{ localeStore.t('our_branches') }}</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>{{ localeStore.isAmharic ? 'ዋና ቅርንጫፍ - አዲስ አበባ' : 'Main Branch - Addis Ababa' }}</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>{{ localeStore.isAmharic ? 'ቦሌ ቅርንጫፍ' : 'Bole Branch' }}</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>{{ localeStore.isAmharic ? 'መገናኛ ቅርንጫፍ' : 'Megenagna Branch' }}</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>{{ localeStore.isAmharic ? 'ፒያሳ ቅርንጫፍ' : 'Piassa Branch' }}</span>
            </li>
          </ul>
        </div>

        <!-- Col 5: Newsletter (2 cols desktop, 3 cols tablet) -->
        <div class="sm:col-span-2 md:col-span-3 lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">{{ localeStore.t('newsletter') }}</h4>
          <p class="text-[11px] text-[#C7BBB3] leading-relaxed">
            {{ localeStore.t('newsletter_sub') }}
          </p>
          <div class="space-y-2 max-w-sm">
            <input
              v-model="newsletterEmail"
              type="email"
              :placeholder="localeStore.t('enter_email')"
              class="w-full bg-white dark:bg-[#1E120B] text-gray-800 dark:text-gray-100 rounded-xl px-3.5 py-2.5 text-xs placeholder:text-gray-400 dark:placeholder:text-[#8C786B] border border-transparent dark:border-[#3D261B] focus:outline-none focus:ring-2 focus:ring-[#DE1B54]"
            />
            <button
              @click="handleSubscribe"
              class="w-full py-2.5 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs hover:shadow-md cursor-pointer flex items-center justify-center space-x-2 active:scale-95"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
              </svg>
              <span>{{ localeStore.t('subscribe_btn') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Bottom Copyright & FAQ Bar -->
      <div class="sweetbakes-container mt-12 pt-6 border-t border-[#382015] flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#AFA39B] gap-2">
        <p>{{ localeStore.t('copyright') }}</p>
        <div class="flex items-center space-x-3">
          <router-link to="/faq" class="hover:text-white transition">{{ localeStore.t('privacy_policy') }}</router-link>
          <span>|</span>
          <router-link to="/faq" class="hover:text-white transition">{{ localeStore.t('terms_service') }}</router-link>
        </div>
      </div>

      <!-- Floating Scroll to Top Button (Bottom-Right) -->
      <button
        @click="scrollToTop"
        class="fixed bottom-4 right-3 sm:bottom-6 sm:right-6 mb-[env(safe-area-inset-bottom)] mr-[env(safe-area-inset-right)] z-30 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#DE1B54] hover:bg-[#C51345] text-white shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer ring-4 ring-white/10"
        title="Back to Top"
        aria-label="Back to Top"
      >
        <span class="text-base font-bold">&uarr;</span>
      </button>
    </footer>

    <!-- Slide-Out Quick Cart Drawer (Mini-Cart) -->
    <CartDrawer class="print:hidden" />

    <!-- Branch Picker Modal (Searchable, All Addis Branches) -->
    <BranchPickerModal class="print:hidden" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onBeforeUnmount, defineAsyncComponent } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useBranchStore } from '../stores/branch';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useThemeStore } from '../stores/theme';
import { useLocaleStore } from '../stores/locale';
import { useWishlistStore } from '../stores/wishlist';
import PageTransition from '../components/PageTransition.vue';
import { prefersReducedMotion } from '../composables/usePageTransition';
import api from '../api';

// Standard Asynchronous Code-Splitting for Heavy Components
const CartDrawer = defineAsyncComponent(() => import('../components/CartDrawer.vue'));
const ContactSection = defineAsyncComponent(() => import('../components/ContactSection.vue'));
const BranchPickerModal = defineAsyncComponent(() => import('../components/BranchPickerModal.vue'));

const router = useRouter();
const route = useRoute();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();
const themeStore = useThemeStore();
const localeStore = useLocaleStore();
const wishlistStore = useWishlistStore();

const searchKeyword = ref('');
const newsletterEmail = ref('');
const accountMenuOpen = ref(false);
const branchMenuOpen = ref(false);
const mobileNavOpen = ref(false);
const mobileShopOpen = ref(false);
const shopDropdownOpen = ref(false);
const businessInfo = ref(null);
const cartBadgePop = ref(false);

// Navigation Button Interactions & Sliding Active Indicator
const headerRef = ref(null);
const desktopNavRef = ref(null);
const shopWrapperRef = ref(null);
const navItemRefs = {
  home: null,
  shop: null,
  about: null,
  reviews: null,
  contact: null,
};
const pressedNavKey = ref(null);
let pressedNavTimer = null;
let shopCloseTimer = null;
let navResizeObserver = null;

function setNavRef(key, el) {
  navItemRefs[key] = el;
}

const activeNavKey = computed(() => {
  if (route.hash === '#about') return 'about';
  if (route.hash === '#reviews') return 'reviews';
  if (route.hash === '#contact' || route.path === '/contact') return 'contact';
  if (route.path.startsWith('/shop')) return 'shop';
  if ((route.path === '/' || route.path === '/home') && !route.hash) return 'home';
  return null;
});

const activeBar = reactive({
  left: 0,
  width: 0,
  opacity: 0,
  animated: false,
});

const activeBarInlineStyle = computed(() => ({
  transform: `translateX(${activeBar.left}px)`,
  width: `${activeBar.width}px`,
  opacity: activeBar.opacity,
  transition: activeBar.animated ? undefined : 'none',
}));

function getOffsetWithinNav(targetEl, navEl) {
  let left = 0;
  let current = targetEl;
  while (current && current !== navEl) {
    left += current.offsetLeft;
    current = current.offsetParent;
  }
  return {
    left: Math.round(left * 100) / 100,
    width: Math.round(targetEl.offsetWidth * 100) / 100,
  };
}

function updateActiveBarPosition({ animate = true } = {}) {
  const navEl = desktopNavRef.value;
  const key = activeNavKey.value;
  const rawTarget = key ? navItemRefs[key] : null;
  const targetEl = rawTarget?.$el ? rawTarget.$el : rawTarget;

  if (!navEl || !targetEl) {
    activeBar.opacity = 0;
    activeBar.animated = false;
    return;
  }

  if (navEl.offsetWidth === 0 || targetEl.offsetWidth === 0) {
    return;
  }

  // Measure static CSS layout box (offsetLeft/offsetWidth) so activeBar is never
  // distorted by scale(0.95) click feedback or translateY hover transforms.
  const { left: nextLeft, width: nextWidth } = getOffsetWithinNav(targetEl, navEl);

  if (!activeBar.opacity || !animate || prefersReducedMotion()) {
    activeBar.animated = false;
    activeBar.left = nextLeft;
    activeBar.width = nextWidth;
    requestAnimationFrame(() => {
      activeBar.opacity = 1;
      requestAnimationFrame(() => {
        activeBar.animated = !prefersReducedMotion();
      });
    });
  } else {
    activeBar.animated = true;
    activeBar.left = nextLeft;
    activeBar.width = nextWidth;
    activeBar.opacity = 1;
  }
}

function triggerNavPress(key) {
  pressedNavKey.value = key;
  clearTimeout(pressedNavTimer);
  pressedNavTimer = setTimeout(() => {
    pressedNavKey.value = null;
  }, 180);
}

function openShopDropdown() {
  clearTimeout(shopCloseTimer);
  shopDropdownOpen.value = true;
}

function scheduleCloseShopDropdown() {
  clearTimeout(shopCloseTimer);
  shopCloseTimer = setTimeout(() => {
    shopDropdownOpen.value = false;
  }, 130);
}

function closeShopDropdownImmediate() {
  clearTimeout(shopCloseTimer);
  shopDropdownOpen.value = false;
}

function handleGlobalPointerDown(event) {
  if (shopDropdownOpen.value && shopWrapperRef.value && !shopWrapperRef.value.contains(event.target)) {
    closeShopDropdownImmediate();
  }
  if (mobileNavOpen.value && headerRef.value && !headerRef.value.contains(event.target)) {
    mobileNavOpen.value = false;
  }
}

function handleGlobalKeyDown(event) {
  if (event.key === 'Escape') {
    closeShopDropdownImmediate();
    mobileNavOpen.value = false;
  }
}

watch([activeNavKey, () => localeStore.currentLocale], async () => {
  await nextTick();
  updateActiveBarPosition({ animate: true });
});

watch(() => route.fullPath, () => {
  mobileNavOpen.value = false;
  mobileShopOpen.value = false;
  accountMenuOpen.value = false;
  closeShopDropdownImmediate();
});

watch(() => cartStore.totalItems, (newVal, oldVal) => {
  if (newVal > (oldVal || 0)) {
    cartBadgePop.value = true;
    setTimeout(() => {
      cartBadgePop.value = false;
    }, 600);
  }
});

const shouldShowContactAboveFooter = computed(() => {
  return !['/contact', '/checkout'].includes(route.path);
});

const activeBranchLabel = computed(() => {
  const b = branchStore.selectedBranch;
  if (!b) {
    return localeStore.isAmharic ? 'ዋና ቅርንጫፍ - አዲስ አበባ' : 'Main Branch - Addis Ababa';
  }
  if (localeStore.isAmharic) {
    const amNames = {
      'Bole': 'ቦሌ',
      'Megenagna': 'መገናኛ',
      'Piassa': 'ፒያሳ',
      'Main Branch': 'ዋና ቅርንጫፍ'
    };
    const am = amNames[b.branch_name] || b.branch_name;
    return `${am} ቅርንጫፍ - አዲስ አበባ`;
  }
  return `${b.branch_name} - Addis Ababa`;
});

const activeBranchLabelShort = computed(() => {
  const b = branchStore.selectedBranch;
  if (!b) return 'Bole';
  if (localeStore.isAmharic) {
    const amNames = {
      'Bole': 'ቦሌ',
      'Megenagna': 'መገናኛ',
      'Piassa': 'ፒያሳ',
      'Main Branch': 'ዋና'
    };
    return amNames[b.branch_name] || b.branch_name;
  }
  return b.branch_name;
});

function selectHeaderBranch(b) {
  branchStore.setSelectedBranch(b.branch_id);
  branchMenuOpen.value = false;
  toastStore.info(`Branch switched to ${b.branch_name}`);
}

async function fetchBusinessInfo() {
  try {
    const res = await api.get('/business-info');
    if (res.data) {
      businessInfo.value = res.data;
    }
  } catch (err) {}
}

onMounted(() => {
  branchStore.fetchBranches();
  fetchBusinessInfo();
  wishlistStore.init();

  if (typeof document !== 'undefined') {
    document.addEventListener('pointerdown', handleGlobalPointerDown);
    document.addEventListener('keydown', handleGlobalKeyDown);
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(() => updateActiveBarPosition({ animate: false }));
    }
  }

  nextTick(() => {
    updateActiveBarPosition({ animate: false });
    if (typeof ResizeObserver !== 'undefined' && desktopNavRef.value) {
      navResizeObserver = new ResizeObserver(() => updateActiveBarPosition({ animate: false }));
      navResizeObserver.observe(desktopNavRef.value);
    }
  });

  if (window.location.hash) {
    setTimeout(() => {
      const el = document.querySelector(window.location.hash);
      if (el) el.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    }, 300);
  }
});

onBeforeUnmount(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('pointerdown', handleGlobalPointerDown);
    document.removeEventListener('keydown', handleGlobalKeyDown);
  }
  if (navResizeObserver) {
    navResizeObserver.disconnect();
    navResizeObserver = null;
  }
  clearTimeout(pressedNavTimer);
  clearTimeout(shopCloseTimer);
});

function handleSearch() {
  if (searchKeyword.value.trim()) {
    router.push({ path: '/shop', query: { search: searchKeyword.value.trim() } });
  }
}

async function handleSubscribe() {
  if (!newsletterEmail.value || !newsletterEmail.value.includes('@')) {
    toastStore.error('Please enter a valid email address.');
    return;
  }
  try {
    await api.post('/newsletter/subscribe', { email: newsletterEmail.value.trim() });
    toastStore.success('Thank you for subscribing to SweetBakes news & special offers!');
    newsletterEmail.value = '';
  } catch (err) {
    toastStore.error(err.response?.data?.message || 'Subscription failed. Please try again.');
  }
}

function goToSection(sectionId) {
  const targetHash = `#${sectionId}`;
  if (route.path !== '/' && route.path !== '/home') {
    router.push({ path: '/', hash: targetHash });
  } else if (route.hash !== targetHash) {
    router.push({ path: route.path, hash: targetHash });
  } else {
    const el = document.getElementById(sectionId);
    if (el) {
      const top = el.getBoundingClientRect().top + window.scrollY - 84;
      window.scrollTo({
        top: Math.max(0, top),
        behavior: prefersReducedMotion() ? 'auto' : 'smooth',
      });
    }
  }
}

function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: prefersReducedMotion() ? 'auto' : 'smooth',
  });
}

function handleLogout() {
  authStore.logout();
  accountMenuOpen.value = false;
  router.push('/login');
}
</script>
