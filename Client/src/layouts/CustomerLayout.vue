<template>
  <div class="min-h-screen bg-white text-[#29231F] flex flex-col font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]">
    <!-- Top Bar: Dark Charcoal #1A1412 -->
    <div class="bg-[#1A1412] text-[#E8DED8] text-xs py-2 px-4 sm:px-8 border-b border-[#281E19]">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-2">
        <!-- Left: Location, Phone, Email -->
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 text-xs text-[#D8CDC5]">
          <!-- Branch Location Dropdown Trigger -->
          <div class="relative">
            <button
              @click="branchMenuOpen = !branchMenuOpen"
              class="flex items-center space-x-1.5 hover:text-white transition font-medium cursor-pointer"
            >
              <span class="text-rose-400">📍</span>
              <span>{{ activeBranchLabel }}</span>
              <span class="text-[10px] opacity-70">▾</span>
            </button>

            <!-- Branch Dropdown Popover -->
            <div
              v-if="branchMenuOpen"
              class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50 text-[#29231F] animate-in fade-in"
            >
              <div class="px-4 py-1.5 border-b border-gray-100">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Select Bakery Branch</span>
              </div>
              <div class="space-y-1 max-h-60 overflow-y-auto p-1 text-xs">
                <button
                  v-for="b in branchStore.branches"
                  :key="b.branch_id"
                  @click="selectHeaderBranch(b)"
                  class="w-full text-left p-2 rounded-xl transition flex items-center justify-between cursor-pointer"
                  :class="branchStore.selectedBranchId === b.branch_id ? 'bg-rose-50 text-[#DE1B54] font-bold' : 'hover:bg-gray-50 text-gray-700'"
                >
                  <div class="truncate">
                    <div class="font-semibold">{{ b.branch_name }}</div>
                    <div class="text-[10px] text-gray-400 truncate">{{ b.address }}</div>
                  </div>
                  <span v-if="branchStore.selectedBranchId === b.branch_id" class="text-[#DE1B54] font-bold text-sm">✓</span>
                </button>
              </div>
            </div>
          </div>

          <span class="text-[#4A3D36]">|</span>

          <a href="tel:+251911123456" class="flex items-center space-x-1 hover:text-white transition">
            <span>📞</span>
            <span>+251 911 123 456</span>
          </a>

          <span class="text-[#4A3D36]">|</span>

          <a href="mailto:hello@sweetbakes.com" class="flex items-center space-x-1 hover:text-white transition">
            <span>✉️</span>
            <span>hello@sweetbakes.com</span>
          </a>
        </div>

        <!-- Right: Follow Us Social Icons -->
        <div class="flex items-center space-x-3 text-xs text-[#D8CDC5]">
          <span class="text-[#B3A49C] text-[11px]">Follow Us:</span>
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
    <header class="sticky top-0 z-40 bg-white border-b border-[#F0EAE3] shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
        <!-- SweetBakes Logo -->
        <router-link to="/" class="flex items-center space-x-3 shrink-0 group">
          <!-- Cupcake Icon -->
          <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
            <span class="text-2xl">🧁</span>
          </div>
          <div>
            <div class="text-2xl sm:text-[26px] font-serif font-black tracking-tight text-[#2A1810] leading-none">
              Sweet<span class="text-[#DE1B54]">Bakes</span>
            </div>
            <div class="text-[9px] sm:text-[10px] tracking-wider text-[#8C7E77] font-medium uppercase mt-0.5">
              Cakes &middot; Bread &middot; Happiness
            </div>
          </div>
        </router-link>

        <!-- Center Nav Links -->
        <nav class="hidden lg:flex items-center space-x-8 text-sm font-semibold text-[#2A1810]">
          <!-- Home -->
          <router-link
            to="/"
            class="relative py-2 transition-colors hover:text-[#DE1B54]"
            :class="($route.path === '/' || $route.path === '/home') && !$route.hash ? 'text-[#DE1B54] font-bold border-b-2 border-[#DE1B54]' : ''"
          >
            Home
          </router-link>

          <!-- Shop Dropdown -->
          <div class="relative group py-2" @mouseleave="shopDropdownOpen = false">
            <router-link
              to="/shop"
              class="flex items-center space-x-1.5 transition-colors hover:text-[#DE1B54]"
              :class="$route.path.startsWith('/shop') ? 'text-[#DE1B54] font-bold border-b-2 border-[#DE1B54]' : ''"
              @click="shopDropdownOpen = false"
            >
              <span>Shop</span>
              <span class="text-xs opacity-75">▾</span>
            </router-link>

            <!-- Dropdown Menu: Exact Match to Mockup Popup -->
            <div
              class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100/90 p-2 hidden group-hover:block z-50 animate-in fade-in zoom-in-95 duration-150"
            >
              <!-- 1. All Products -->
              <router-link
                to="/shop"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition"
                :class="$route.path === '/shop' && !$route.query.category && !$route.query.yetsom && !$route.query.fasting ? 'bg-[#FFF0F4] text-[#DE1B54] font-bold' : 'text-[#2A1810] hover:bg-[#FFF5F8] hover:text-[#DE1B54]'"
              >
                <span class="text-base">✨</span>
                <span>All Products</span>
              </router-link>

              <!-- 2. Cakes -->
              <router-link
                to="/shop?category=Cakes"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition"
                :class="$route.query.category === 'Cakes' ? 'bg-[#FFF0F4] text-[#DE1B54] font-bold' : 'text-[#2A1810] hover:bg-[#FFF5F8] hover:text-[#DE1B54]'"
              >
                <span class="text-base">🎂</span>
                <span>Cakes</span>
              </router-link>

              <!-- 3. Fresh Bread -->
              <router-link
                to="/shop?category=Bread"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition"
                :class="$route.query.category === 'Bread' ? 'bg-[#FFF0F4] text-[#DE1B54] font-bold' : 'text-[#2A1810] hover:bg-[#FFF5F8] hover:text-[#DE1B54]'"
              >
                <span class="text-base">🥖</span>
                <span>Fresh Bread</span>
              </router-link>

              <!-- 4. Cupcakes & Croissants -->
              <router-link
                to="/shop?category=Pastries"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition"
                :class="$route.query.category === 'Pastries' ? 'bg-[#FFF0F4] text-[#DE1B54] font-bold' : 'text-[#2A1810] hover:bg-[#FFF5F8] hover:text-[#DE1B54]'"
              >
                <span class="text-base">🥐</span>
                <span>Cupcakes &amp; Croissants</span>
              </router-link>

              <!-- 5. Ye'Tsom (Fasting / ጾም) -->
              <router-link
                to="/shop?yetsom=1"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-bold transition"
                :class="$route.query.yetsom === '1' || $route.query.fasting === '1' ? 'bg-emerald-100/70 text-[#075e43] font-black' : 'text-[#0b6b4e] hover:bg-emerald-50 hover:text-[#06503b]'"
              >
                <span class="text-base">🌿</span>
                <span>Ye'Tsom (Fasting / ጾም)</span>
              </router-link>

              <!-- 6. Custom Cake Studio -->
              <router-link
                to="/custom-cake"
                @click="shopDropdownOpen = false"
                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-[13px] font-bold transition"
                :class="$route.path === '/custom-cake' ? 'bg-[#FFF0F4] text-[#DE1B54] font-black' : 'text-[#DE1B54] hover:bg-[#FFF5F8] hover:text-[#C51345]'"
              >
                <span class="text-base">✍️</span>
                <span>Custom Cake Studio</span>
              </router-link>
            </div>
          </div>

          <!-- About Anchor / Page -->
          <a
            href="/#about"
            @click.prevent="goToSection('about')"
            class="relative py-2 transition-colors hover:text-[#DE1B54]"
            :class="$route.hash === '#about' ? 'text-[#DE1B54] font-bold border-b-2 border-[#DE1B54]' : ''"
          >
            About
          </a>

          <!-- Reviews Anchor -->
          <a
            href="/#reviews"
            @click.prevent="goToSection('reviews')"
            class="relative py-2 transition-colors hover:text-[#DE1B54]"
            :class="$route.hash === '#reviews' ? 'text-[#DE1B54] font-bold border-b-2 border-[#DE1B54]' : ''"
          >
            Reviews
          </a>

          <!-- Contact -->
          <router-link
            to="/contact"
            class="relative py-2 transition-colors hover:text-[#DE1B54]"
            :class="$route.path === '/contact' ? 'text-[#DE1B54] font-bold border-b-2 border-[#DE1B54]' : ''"
          >
            Contact
          </router-link>
        </nav>

        <!-- Right Side: Search, Account & Cart -->
        <div class="flex items-center space-x-3 sm:space-x-4">
          <!-- Search Input Pill -->
          <div class="relative hidden sm:block w-48 lg:w-60">
            <input
              v-model="searchKeyword"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Search products..."
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-full py-2 pl-4 pr-9 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none transition shadow-2xs"
            />
            <button
              @click="handleSearch"
              class="absolute right-3 top-2.5 text-gray-400 hover:text-[#DE1B54] transition cursor-pointer"
              title="Search"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>

          <!-- User Menu Dropdown -->
          <div class="relative">
            <button
              @click="accountMenuOpen = !accountMenuOpen"
              class="flex items-center space-x-1 p-2 sm:px-3 sm:py-2 rounded-full border border-gray-200 hover:border-[#DE1B54] text-xs font-semibold text-gray-700 transition shadow-2xs cursor-pointer"
              title="User Account"
            >
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="hidden md:inline">{{ authStore.user ? authStore.user.full_name.split(' ')[0] : '' }}</span>
              <span class="text-[10px] opacity-60">▾</span>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="accountMenuOpen"
              class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 animate-in fade-in"
            >
              <div v-if="authStore.user" class="px-4 py-2 border-b border-gray-100">
                <div class="font-bold text-xs text-gray-800 truncate">{{ authStore.user.full_name }}</div>
                <div class="text-[10px] text-[#DE1B54] font-semibold">{{ authStore.user.role }}</div>
              </div>

              <div class="py-1 text-xs font-medium">
                <router-link
                  v-if="authStore.user"
                  to="/account"
                  @click="accountMenuOpen = false"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-rose-50 hover:text-[#DE1B54]"
                >
                  <span>👤 My Orders & Account</span>
                </router-link>

                <router-link
                  v-if="!authStore.user"
                  to="/login"
                  @click="accountMenuOpen = false"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-rose-50 hover:text-[#DE1B54]"
                >
                  <span>🔑 Sign In</span>
                </router-link>

                <router-link
                  v-if="!authStore.user"
                  to="/register"
                  @click="accountMenuOpen = false"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-rose-50 hover:text-[#DE1B54]"
                >
                  <span>✨ Create Account</span>
                </router-link>

                <router-link
                  to="/admin/overview"
                  @click="accountMenuOpen = false"
                  class="flex items-center px-4 py-2 text-gray-700 font-bold hover:bg-rose-50 border-t border-gray-100 mt-1 pt-2"
                >
                  <span>⚙️ Staff Portal</span>
                </router-link>

                <button
                  v-if="authStore.user"
                  @click="handleLogout"
                  class="w-full text-left flex items-center px-4 py-2 text-red-600 hover:bg-red-50 border-t border-gray-100 mt-1 cursor-pointer"
                >
                  <span>🚪 Log Out</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Cart Drawer Trigger Button -->
          <button
            @click="cartStore.toggleDrawer()"
            class="relative flex items-center p-2 rounded-full hover:bg-rose-50 transition cursor-pointer text-gray-700"
            title="Shopping Cart"
          >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <!-- Red/Pink Badge with Cart Count -->
            <span
              class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-[#DE1B54] text-white text-[11px] font-bold flex items-center justify-center shadow-xs"
            >
              {{ cartStore.totalItems || 2 }}
            </span>
          </button>

          <!-- Mobile Hamburger Trigger Button -->
          <button
            @click="mobileNavOpen = !mobileNavOpen"
            class="lg:hidden p-2 rounded-xl text-gray-700 hover:text-[#DE1B54] hover:bg-rose-50 transition cursor-pointer"
            title="Menu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation Drawer -->
      <div
        v-if="mobileNavOpen"
        class="lg:hidden bg-white border-t border-gray-100 px-5 py-4 space-y-3 shadow-lg animate-in slide-in-from-top duration-200"
      >
        <router-link
          to="/"
          @click="mobileNavOpen = false"
          class="block py-2 text-sm font-bold text-[#2A1810] hover:text-[#DE1B54]"
        >
          Home
        </router-link>

        <div class="space-y-1.5 pl-3 border-l-2 border-rose-200">
          <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 py-1">Shop Categories</div>
          <router-link
            to="/shop"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-semibold text-gray-800 hover:text-[#DE1B54]"
          >
            <span>✨</span>
            <span>All Products</span>
          </router-link>
          <router-link
            to="/shop?category=Cakes"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-semibold text-gray-800 hover:text-[#DE1B54]"
          >
            <span>🎂</span>
            <span>Cakes</span>
          </router-link>
          <router-link
            to="/shop?category=Bread"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-semibold text-gray-800 hover:text-[#DE1B54]"
          >
            <span>🥖</span>
            <span>Fresh Bread</span>
          </router-link>
          <router-link
            to="/shop?category=Pastries"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-semibold text-gray-800 hover:text-[#DE1B54]"
          >
            <span>🥐</span>
            <span>Cupcakes &amp; Croissants</span>
          </router-link>
          <router-link
            to="/shop?yetsom=1"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950"
          >
            <span>🌿</span>
            <span>Ye'Tsom (Fasting / ጾም)</span>
          </router-link>
          <router-link
            to="/custom-cake"
            @click="mobileNavOpen = false"
            class="flex items-center space-x-2 py-1.5 text-xs font-bold text-[#DE1B54] hover:text-[#C51345]"
          >
            <span>✍️</span>
            <span>Custom Cake Studio</span>
          </router-link>
        </div>

        <a
          href="/#about"
          @click="goToSection('about'); mobileNavOpen = false"
          class="block py-2 text-sm font-semibold text-[#2A1810] hover:text-[#DE1B54]"
        >
          About
        </a>
        <a
          href="/#reviews"
          @click="goToSection('reviews'); mobileNavOpen = false"
          class="block py-2 text-sm font-semibold text-[#2A1810] hover:text-[#DE1B54]"
        >
          Reviews
        </a>
        <router-link
          to="/contact"
          @click="mobileNavOpen = false"
          class="block py-2 text-sm font-semibold text-[#2A1810] hover:text-[#DE1B54]"
        >
          Contact
        </router-link>
      </div>
    </header>

    <!-- Main View Slot -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer: Deep Chocolate Brown #27160E -->
    <footer class="bg-[#27160E] text-[#E8DED8] pt-14 pb-8 border-t border-[#382015] relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
        <!-- Col 1: SweetBakes Brand & Slogan (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-2xl">
              🧁
            </div>
            <div>
              <div class="text-2xl font-serif font-black text-white leading-none">
                Sweet<span class="text-[#DE1B54]">Bakes</span>
              </div>
              <div class="text-[9px] tracking-wider text-[#AFA39B] uppercase mt-0.5">
                Cakes &middot; Bread &middot; Happiness
              </div>
            </div>
          </div>

          <p class="text-xs text-[#C7BBB3] leading-relaxed max-w-sm">
            Making life sweeter, one cake and bread at a time.
          </p>

          <!-- Social Media Icons (Facebook, Instagram, YouTube, TikTok) -->
          <div class="flex items-center space-x-3 pt-2 text-[#D8CDC5]">
            <a href="#" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" /></svg>
            </a>
            <a href="#" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" /></svg>
            </a>
            <a href="#" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
            </a>
            <a href="#" class="w-8 h-8 rounded-full border border-white/20 hover:border-white hover:text-white flex items-center justify-center transition">
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.97-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" /></svg>
            </a>
          </div>
        </div>

        <!-- Col 2: Quick Links (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">Quick Links</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li>
              <router-link to="/" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Home</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Shop</span>
              </router-link>
            </li>
            <li>
              <a href="/#about" @click.prevent="goToSection('about')" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1 cursor-pointer">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>About Us</span>
              </a>
            </li>
            <li>
              <a href="/#reviews" @click.prevent="goToSection('reviews')" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1 cursor-pointer">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Reviews</span>
              </a>
            </li>
            <li>
              <router-link to="/contact" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Contact</span>
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 3: Our Services (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">Our Services</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li>
              <router-link to="/shop?category=Cakes" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Cake Orders</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop?category=Bread" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Bread &amp; Pastries</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Delivery</span>
              </router-link>
            </li>
            <li>
              <router-link to="/shop" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Pickup</span>
              </router-link>
            </li>
            <li>
              <router-link to="/branches" class="hover:text-white transition-all flex items-center space-x-1.5 hover:translate-x-1">
                <span class="text-[#DE1B54] font-bold">&gt;</span>
                <span>Branch Locations</span>
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 4: Our Branches (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">Our Branches</h4>
          <ul class="space-y-2 text-xs text-[#D8CDC5]">
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>Main Branch - Addis Ababa</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>Bole Branch</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>Megenagna Branch</span>
            </li>
            <li class="flex items-center space-x-1.5 hover:text-white transition cursor-pointer">
              <span class="text-[#DE1B54]">📍</span>
              <span>Piassa Branch</span>
            </li>
          </ul>
        </div>

        <!-- Col 5: Newsletter (2 cols) -->
        <div class="lg:col-span-2 space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-white">Newsletter</h4>
          <p class="text-[11px] text-[#C7BBB3] leading-relaxed">
            Subscribe to our newsletter for special offers!
          </p>
          <div class="space-y-2">
            <input
              v-model="newsletterEmail"
              type="email"
              placeholder="Your email address"
              class="w-full bg-white text-gray-800 rounded-xl px-3.5 py-2.5 text-xs placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#DE1B54]"
            />
            <button
              @click="handleSubscribe"
              class="w-full py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs hover:shadow-md cursor-pointer flex items-center justify-center space-x-2 active:scale-95"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
              </svg>
              <span>Subscribe</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Bottom Copyright & FAQ Bar -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-6 border-t border-[#382015] flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#AFA39B] gap-2">
        <p>&copy; 2025 SweetBakes. All rights reserved.</p>
        <div class="flex items-center space-x-3">
          <router-link to="/faq" class="hover:text-white transition">Privacy Policy</router-link>
          <span>|</span>
          <router-link to="/faq" class="hover:text-white transition">Terms of Service</router-link>
        </div>
      </div>

      <!-- Floating Scroll to Top Button (Bottom-Right) -->
      <button
        @click="scrollToTop"
        class="fixed bottom-6 right-6 z-30 w-11 h-11 rounded-full bg-[#DE1B54] hover:bg-[#C51345] text-white shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer ring-4 ring-white/10"
        title="Back to Top"
      >
        <span class="text-base font-bold">&uarr;</span>
      </button>
    </footer>

    <!-- Slide-Out Quick Cart Drawer (Mini-Cart) -->
    <CartDrawer />

    <!-- Toast Notification Container -->
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../stores/branch';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import ToastNotification from '../components/ToastNotification.vue';
import CartDrawer from '../components/CartDrawer.vue';

const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();

const searchKeyword = ref('');
const newsletterEmail = ref('');
const accountMenuOpen = ref(false);
const branchMenuOpen = ref(false);
const mobileNavOpen = ref(false);
const shopDropdownOpen = ref(false);

const activeBranchLabel = computed(() => {
  const b = branchStore.selectedBranch;
  if (!b) return 'Main Branch - Addis Ababa';
  return `${b.branch_name} - Addis Ababa`;
});

function selectHeaderBranch(b) {
  branchStore.setSelectedBranch(b.branch_id);
  branchMenuOpen.value = false;
  toastStore.info(`Branch switched to ${b.branch_name}`);
}

onMounted(() => {
  branchStore.fetchBranches();
});

function handleSearch() {
  if (searchKeyword.value.trim()) {
    router.push({ path: '/shop', query: { search: searchKeyword.value.trim() } });
  }
}

function handleSubscribe() {
  if (!newsletterEmail.value || !newsletterEmail.value.includes('@')) {
    toastStore.error('Please enter a valid email address.');
    return;
  }
  toastStore.success('Thank you for subscribing to SweetBakes!');
  newsletterEmail.value = '';
}

function goToSection(sectionId) {
  if (window.location.pathname !== '/') {
    router.push({ path: '/', hash: `#${sectionId}` });
  } else {
    const el = document.getElementById(sectionId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  }
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function handleLogout() {
  authStore.logout();
  accountMenuOpen.value = false;
  router.push('/login');
}
</script>
