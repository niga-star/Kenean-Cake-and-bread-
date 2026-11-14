<template>
  <div class="flex h-screen bg-[#FAF7F2] dark:bg-[#120B07] text-[#2B1810] dark:text-[#FAF4ED] font-sans antialiased overflow-hidden relative admin-dashboard selection:bg-[#F6EFE6] dark:selection:bg-[#3D251E] selection:text-[#3D251E] dark:selection:text-amber-200">
    <!-- Mobile Backdrop Overlay (< lg screens) -->
    <transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden print:hidden"
        aria-hidden="true"
      ></div>
    </transition>

    <!-- Warm Luxury Chocolate Bakery Sidebar: Responsive Drawer on Mobile (< lg), Static on Desktop (lg+) -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 lg:w-72 bg-[#3D251E] dark:bg-[#160E09] flex flex-col justify-between shrink-0 select-none border-r border-[#4D3227] dark:border-[#271810] shadow-2xl lg:shadow-none transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 font-sans print:hidden"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex-1 flex flex-col min-h-0">
        <!-- Brand Logo Header + Mobile Close Button -->
        <div class="px-5 sm:px-6 py-5 border-b border-[#4D3227] dark:border-[#271810] flex items-center justify-between shrink-0">
          <div class="flex items-center space-x-3">
            <div class="relative w-12 h-12 rounded-full bg-[#FAF5EE] p-0.5 border border-[#DEBE92] shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
              <img
                src="/images/kenean-logo.png"
                alt="Kenean Cake & Bread"
                class="w-full h-full object-contain rounded-full"
              />
            </div>
            <router-link :to="homePath" @click="sidebarOpen = false" class="block leading-tight group">
              <div class="flex items-baseline space-x-2">
                <span class="text-2xl sm:text-[26px] font-black text-[#FAF4ED] tracking-tight group-hover:text-amber-200 transition">
                  {{ localeStore.isAmharic ? 'ከነአን' : 'Kenean' }}
                </span>
                <span class="text-xs sm:text-[13px] font-bold text-amber-300">
                  {{ localeStore.isAmharic ? 'ኬክና ዳቦ' : 'Bakery' }}
                </span>
              </div>
              <p class="text-xs sm:text-[12px] uppercase font-bold tracking-wider text-[#D8CDC5] mt-1">
                {{ localeStore.isAmharic ? 'የአስተዳደር ክፍል' : 'Management Portal' }}
              </p>
            </router-link>
          </div>
          <!-- Close Button on Mobile (< lg) -->
          <button
            @click="sidebarOpen = false"
            class="lg:hidden p-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
            aria-label="Close sidebar"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Navigation Menu with Touch-Friendly Links & Unified Reference Styling -->
        <nav class="px-3.5 py-4 space-y-3.5 overflow-y-auto flex-1">
          <!-- Mobile Super Admin Persona Simulator (< lg screens) -->
          <div
            v-if="authStore.canSimulatePersona && authStore.availableRoles && authStore.availableRoles.length > 1"
            class="lg:hidden p-3.5 bg-[#4D3227] dark:bg-[#1E120B] rounded-2xl border border-[#634234] dark:border-[#382216] space-y-2.5 mb-2.5"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wide flex items-center gap-1.5">
                <span>🎭</span>
                <span>{{ localeStore.t('dash_simulate_role') }}</span>
              </span>
              <span v-if="authStore.isSimulating" class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-400 text-amber-950">
                Active
              </span>
            </div>
            <select
              :value="selectedRoleKey"
              @change="handleRoleChange($event.target.value); sidebarOpen = false"
              class="w-full bg-[#352017] dark:bg-[#140C07] border border-[#634234] dark:border-[#382216] text-[#FAF4ED] text-sm font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
            >
              <option
                v-for="(r, idx) in authStore.availableRoles"
                :key="idx"
                :value="idx"
              >
                {{ formatPersonaLabel(r) }}
              </option>
            </select>
            <div v-if="authStore.isSimulating" class="flex justify-end pt-1">
              <button
                @click="handleExitSimulation(); sidebarOpen = false"
                class="text-xs sm:text-sm bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold px-3 py-1.5 rounded-xl transition cursor-pointer shadow-xs"
              >
                {{ localeStore.t('dash_exit') }} {{ localeStore.isAmharic ? 'ወደ ዋና አስተዳዳሪ' : 'to Super Admin' }}
              </button>
            </div>
          </div>

          <!-- Overview Direct Link -->
          <div v-if="canAccess('overview')">
            <router-link
              to="/admin/overview"
              @click="sidebarOpen = false"
              class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200 group"
              :class="$route.path === '/admin/overview' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
            >
              <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span>{{ isBranchScoped ? localeStore.t('dash_branch_overview') : localeStore.t('dash_overview') }}</span>
            </router-link>
          </div>

          <!-- BUSINESS Section -->
          <div v-if="canAccess('branches') || canAccess('users') || canAccess('products')">
            <div class="admin-category-header px-4 pt-3 pb-1.5 text-xs sm:text-[13px] font-bold tracking-wide text-[#E2D4C7] dark:text-[#C8B6A6] uppercase">{{ localeStore.t('dash_business') }}</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('branches')"
                to="/admin/branches"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/branches') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>{{ localeStore.t('dash_branches') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('users')"
                to="/admin/users"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/users') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>{{ isBranchScoped ? localeStore.t('dash_branch_staff') : localeStore.t('dash_users_staff') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('salaries')"
                to="/admin/salaries"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/salaries') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ localeStore.t('dash_salaries') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('products')"
                to="/admin/products"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/products') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>{{ localeStore.t('dash_products_prices') }}</span>
              </router-link>
            </div>
          </div>

          <!-- STOCK Section -->
          <div>
            <div class="admin-category-header px-4 pt-3 pb-1.5 text-xs sm:text-[13px] font-bold tracking-wide text-[#E2D4C7] dark:text-[#C8B6A6] uppercase">{{ localeStore.t('dash_stock_inventory') }}</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('inventory')"
                to="/admin/inventory"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/inventory' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>{{ isBranchScoped ? localeStore.t('dash_my_inventory') : localeStore.t('dash_inventory') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('distribution')"
                to="/admin/distribution"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/distribution' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                  </svg>
                  <span>{{ isBranchScoped ? localeStore.t('dash_stock_receiving') : localeStore.t('dash_distribution') }}</span>
                </div>
              </router-link>

              <router-link
                v-if="canAccess('transfers/new')"
                to="/admin/transfers/new"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/transfers/new') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>{{ localeStore.t('dash_new_transfer') }}</span>
              </router-link>
            </div>
          </div>

          <!-- ORDERS & SALES Section (Only visible to roles with retail order/sales duties) -->
          <div v-if="canAccess('orders') || canAccess('custom-cakes') || canAccess('payments') || canAccess('seller-reports') || canAccess('manager-reports') || canAccess('reports')">
            <div class="admin-category-header px-4 pt-3 pb-1.5 text-xs sm:text-[13px] font-bold tracking-wide text-[#E2D4C7] dark:text-[#C8B6A6] uppercase">{{ localeStore.t('dash_orders_sales') }}</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('orders')"
                to="/admin/orders"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/orders' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>{{ currentRole === 'Branch Staff' ? localeStore.t('dash_order_preparation') : (['Branch Manager', 'Main Branch Manager', 'Branch Admin'].includes(currentRole) ? localeStore.t('dash_branch_orders') : localeStore.t('dash_orders')) }}</span>
              </router-link>

              <router-link
                v-if="canAccess('custom-cakes')"
                to="/admin/custom-cakes"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/custom-cakes' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.5a3.5 3.5 0 01-7 0 3.5 3.5 0 01-7 0M12 4v4m-4-2h8m-9 9.5V20a1 1 0 001 1h16a1 1 0 001-1v-4.5" />
                </svg>
                <span>{{ ['Branch Manager', 'Main Branch Manager', 'Branch Admin'].includes(currentRole) ? localeStore.t('dash_branch_custom_cakes') : localeStore.t('dash_custom_cakes') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('payments')"
                to="/admin/payments"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/payments' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>{{ isBranchScoped ? localeStore.t('dash_branch_sales') : localeStore.t('dash_payments') }}</span>
              </router-link>

              <!-- Branch Seller: Daily Sales Report Form -->
              <router-link
                v-if="canAccess('seller-reports')"
                to="/admin/seller-reports"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/seller-reports') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>{{ localeStore.t('dash_seller_reports') }}</span>
              </router-link>

              <!-- Branch Manager: Seller Reports Review & Verification -->
              <router-link
                v-if="canAccess('manager-reports')"
                to="/admin/manager-reports"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/manager-reports') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ localeStore.t('dash_manager_reports') }}</span>
              </router-link>
            </div>
          </div>

          <!-- SYSTEM Section -->
          <div v-if="canAccess('notifications') || canAccess('settings') || canAccess('my-account')">
            <div class="admin-category-header px-4 pt-3 pb-1.5 text-xs sm:text-[13px] font-bold tracking-wide text-[#E2D4C7] dark:text-[#C8B6A6] uppercase">{{ localeStore.t('dash_settings_account') }}</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('my-account')"
                to="/admin/my-account"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/my-account') ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ localeStore.t('dash_my_account') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('notifications')"
                to="/admin/notifications"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/notifications' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span>{{ localeStore.t('dash_notifications') }}</span>
                </div>
                <span v-if="unreadCount > 0" class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-[#DE1B54] text-white text-xs font-bold flex items-center justify-center shadow-xs">{{ unreadCount }}</span>
              </router-link>

              <router-link
                v-if="canAccess('settings')"
                to="/admin/settings"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-3 text-[15.5px] sm:text-base font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/settings' ? 'bg-[#57392C] text-white shadow-xs font-bold' : 'text-[#D8CDC5] hover:bg-white/5 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ localeStore.t('dash_settings') }}</span>
              </router-link>
            </div>
          </div>

        </nav>
      </div>

      <!-- Bottom User Profile Card with Role & Branch -->
      <div class="p-4 border-t border-[#4D3227] dark:border-[#271810] bg-[#301B14] dark:bg-[#100A06] flex items-center justify-between shrink-0">
        <router-link
          to="/admin/my-account"
          class="flex items-center space-x-3 min-w-0 pr-2 hover:opacity-95 transition group cursor-pointer"
          title="View My Account"
        >
          <div class="w-10 h-10 rounded-full overflow-hidden bg-[#57392C] shrink-0 border border-[#6E4636] shadow-xs flex items-center justify-center text-amber-200 text-xs font-bold font-serif">
            <img
              v-if="authStore.user?.avatar_url"
              :src="authStore.user.avatar_url"
              alt="Avatar"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ authStore.user?.full_name ? authStore.user.full_name.substring(0, 2).toUpperCase() : 'KN' }}</span>
          </div>
          <div class="min-w-0">
            <div class="text-sm sm:text-base font-bold text-[#FAF4ED] truncate leading-tight group-hover:text-amber-200 transition">
              {{ authStore.user?.full_name || (localeStore.isAmharic ? 'የስርዓቱ ተጠቃሚ' : 'Admin User') }}
            </div>
            <div class="text-xs sm:text-[13px] text-[#D8CDC5] truncate mt-1 leading-tight">
              <span class="text-amber-300 font-semibold">{{ localeStore.tRole(displayRole) }}</span>
              <span v-if="authStore.user?.branch"> &middot; {{ localeStore.tBranch(authStore.user.branch.branch_name) }}</span>
            </div>
          </div>
        </router-link>

        <button
          @click="handleLogout"
          class="p-2.5 text-[#D8CDC5] hover:text-white hover:bg-white/10 rounded-xl transition cursor-pointer shrink-0"
          :title="localeStore.t('dash_sign_out')"
          :aria-label="localeStore.t('dash_sign_out')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top Bakery Header Bar: 100% Responsive Single-Row Design -->
      <header class="h-16 bg-white/95 dark:bg-[#160E09]/95 backdrop-blur-md border-b border-[#EFE8DE] dark:border-[#271810] px-3 sm:px-5 lg:px-6 flex items-center justify-between gap-3 shrink-0 shadow-2xs dark:shadow-[0_1px_4px_rgba(0,0,0,0.3)] print:hidden relative overflow-hidden">
        <!-- Left Section: Mobile Drawer Trigger & User Identity | Desktop Persona & Role -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 overflow-hidden">
          <!-- Mobile Hamburger Toggle (< lg) -->
          <button
            @click="sidebarOpen = !sidebarOpen"
            type="button"
            class="lg:hidden p-2 -ml-1 rounded-xl text-[#4A3B32] dark:text-[#C8B6A6] hover:bg-[#FAF6F0] dark:hover:bg-[#25150D] hover:text-[#3D251E] dark:hover:text-white transition cursor-pointer shrink-0 focus:outline-none"
            title="Open navigation menu"
            aria-label="Open navigation menu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Mobile User Identity Pill (< lg) -->
          <div class="lg:hidden flex items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-full bg-[#FAF5EE] border border-[#DEBE92] p-0.5 shrink-0 flex items-center justify-center shadow-2xs">
              <img src="/images/kenean-logo.png" alt="Kenean" class="w-full h-full object-contain rounded-full" />
            </div>
            <div class="min-w-0">
              <div class="text-xs font-bold text-[#2B1810] dark:text-[#FAF4ED] truncate leading-tight">
                {{ authStore.user?.full_name || 'Staff' }}
              </div>
              <div class="text-[10.5px] font-semibold text-[#8C4A28] dark:text-[#E8A585] truncate leading-tight flex items-center gap-1">
                <span v-if="['Super Admin', 'Main Branch Manager'].includes(currentRole)">👑</span>
                <span class="truncate">{{ localeStore.tRole(displayRole) }}</span>
              </div>
            </div>
          </div>

          <!-- Desktop Persona Simulator (lg+) -->
          <div
            v-if="authStore.canSimulatePersona && authStore.availableRoles && authStore.availableRoles.length > 1"
            class="hidden lg:flex items-center gap-2 text-xs sm:text-sm min-w-0 max-w-[280px] xl:max-w-[340px]"
          >
            <span class="text-[#7D6E64] dark:text-[#A69284] font-semibold text-xs shrink-0 whitespace-nowrap">
              {{ localeStore.t('dash_simulate_role') }}
            </span>
            <div class="min-w-0 flex-1">
              <select
                :value="selectedRoleKey"
                @change="handleRoleChange($event.target.value)"
                class="w-full bg-[#FAF7F2] dark:bg-[#20130C] border border-[#E2D8CC] dark:border-[#382216] text-[#2B1810] dark:text-[#FAF4ED] text-xs font-semibold rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#3D251E]/20 dark:focus:ring-rose-500/20 focus:border-[#3D251E] dark:focus:border-[#DE1B54] cursor-pointer shadow-2xs truncate"
              >
                <option
                  v-for="(r, idx) in authStore.availableRoles"
                  :key="idx"
                  :value="idx"
                >
                  {{ formatPersonaLabel(r) }}
                </option>
              </select>
            </div>
          </div>

          <!-- Desktop Badges: Clean, Non-colliding, Gracefully Hidden on Tight Screens -->
          <div class="hidden lg:flex items-center gap-2 text-xs min-w-0">
            <!-- Active Role Badge -->
            <span
              class="text-xs px-2.5 py-1 rounded-full font-bold border shrink-0 shadow-2xs flex items-center gap-1 truncate max-w-[160px]"
              :class="{
                'bg-[#FEF7E6] dark:bg-[#2E1E09] text-[#B87A14] dark:text-[#F3BA59] border-[#FBEAC2] dark:border-[#523712]': ['Super Admin', 'Main Branch Manager', 'Branch Admin', 'Branch Manager'].includes(currentRole),
                'bg-[#FFE4E6] dark:bg-[#34111B] text-[#9F1239] dark:text-[#FCA5A5] border-[#FECDD3] dark:border-[#5C1D2D]': ['Branch Seller', 'Branch Seller / Cashier', 'Cashier'].includes(currentRole),
                'bg-[#FAF7F2] dark:bg-[#20130C] text-[#6B5A4E] dark:text-[#C8B6A6] border-[#E5DCD0] dark:border-[#382216]': !['Super Admin', 'Main Branch Manager', 'Branch Admin', 'Branch Manager', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier'].includes(currentRole)
              }"
            >
              <span v-if="['Super Admin', 'Main Branch Manager'].includes(currentRole)">👑</span>
              <span class="truncate">{{ localeStore.tRole(displayRole) }}</span>
            </span>

            <!-- Branch Badge (if branch assigned) -->
            <span
              v-if="authStore.user?.branch?.branch_name"
              class="hidden 2xl:inline-flex text-xs px-2.5 py-1 rounded-full font-bold bg-[#FAF7F2] dark:bg-[#20130C] text-[#6E5D52] dark:text-[#C8B6A6] border border-[#E5DACD] dark:border-[#382216] truncate max-w-[150px]"
            >
              📍 {{ localeStore.tBranch(authStore.user.branch.branch_name) }}
            </span>
          </div>
        </div>

        <!-- Right Section: Language Toggle, Theme Toggle, Notifications & Store Shortcut -->
        <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0 z-10">
          <!-- Bilingual Language Toggle Button: EN | አማ -->
          <button
            @click="localeStore.toggleLocale()"
            data-testid="admin-header-lang-toggle"
            class="flex items-center px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full border border-gray-200 dark:border-[#3D261B] hover:border-[#DE1B54] dark:hover:border-[#DE1B54] text-[11px] sm:text-xs font-bold transition-all duration-200 shadow-2xs cursor-pointer select-none bg-white dark:bg-[#20130C] text-[#3D251E] dark:text-[#FAF4ED] h-8 sm:h-9 whitespace-nowrap shrink-0"
            :title="localeStore.isAmharic ? 'Switch to English' : 'ወደ አማርኛ ቀይር'"
            aria-label="Language Switcher"
          >
            <span :class="!localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">EN</span>
            <span class="mx-0.5 sm:mx-1 text-gray-300 dark:text-gray-600">|</span>
            <span :class="localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400 dark:text-gray-500'">አማ</span>
          </button>

          <!-- Dedicated Light / Dark Theme Switcher Button -->
          <button
            @click="themeStore.toggleTheme()"
            data-testid="admin-header-theme-toggle"
            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-gray-200 dark:border-[#3D261B] hover:border-[#DE1B54] dark:hover:border-[#DE1B54] flex items-center justify-center text-xs sm:text-sm transition-all duration-200 shadow-2xs cursor-pointer select-none bg-white dark:bg-[#20130C] shrink-0"
            :title="themeStore.isDark ? (localeStore.isAmharic ? 'ወደ ብሩህ ገጽታ ቀይር (Light Mode)' : 'Switch to Light Mode') : (localeStore.isAmharic ? 'ወደ ጨለማ ገጽታ ቀይር (Dark Mode)' : 'Switch to Dark Mode')"
            aria-label="Theme Switcher"
          >
            <span v-if="themeStore.isDark" class="text-amber-300 text-sm sm:text-base">☀️</span>
            <span v-else class="text-gray-600 dark:text-gray-300 text-sm sm:text-base">🌙</span>
          </button>

          <!-- Notification Bell -->
          <router-link
            to="/admin/notifications"
            class="relative p-2 sm:p-2.5 text-[#6B5A4E] dark:text-[#C8B6A6] hover:text-[#3D251E] dark:hover:text-white hover:bg-[#FAF7F2] dark:hover:bg-[#24150D] rounded-xl transition cursor-pointer shrink-0"
            :title="localeStore.isAmharic ? 'ማሳወቂያዎች' : 'Notifications'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 w-4 h-4 bg-[#DE1B54] text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none shadow-xs">{{ unreadCount }}</span>
          </router-link>

          <!-- SweetBakes Client Storefront Shortcut -->
          <router-link
            to="/"
            target="_blank"
            class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-[#8C4A28] dark:text-[#E8A585] bg-[#FAF0E6] dark:bg-[#24150D] hover:bg-[#F5E5D5] dark:hover:bg-[#321C11] border border-[#EED7C5] dark:border-[#3E2316] px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl transition shadow-2xs group cursor-pointer shrink-0"
            :title="localeStore.isAmharic ? 'የደንበኛ መደብር በአዲስ ገጽ ይክፈቱ' : 'Open customer bakery store in new tab'"
          >
            <span class="text-sm">🍰</span>
            <span class="hidden md:inline whitespace-nowrap">{{ localeStore.t('dash_view_store') }}</span>
            <svg class="w-3.5 h-3.5 text-[#8C4A28] dark:text-[#E8A585] group-hover:translate-x-0.5 transition-transform hidden md:inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </router-link>
        </div>
      </header>

      <!-- Active Persona Simulation Banner: Visible on Mobile & Desktop -->
      <div
        v-if="authStore.isSimulating"
        class="bg-[#FEF7E6] dark:bg-[#2E1E09] border-b border-[#FBEAC2] dark:border-[#523712] px-3 sm:px-6 py-2 flex items-center justify-between text-xs font-semibold text-[#B87A14] dark:text-[#F3BA59] shrink-0"
      >
        <div class="flex items-center gap-2 truncate">
          <span class="text-sm">🎭</span>
          <span class="truncate">
            {{ localeStore.isAmharic ? 'በአሁኑ ሰዓት እየተመሰለ ያለው ሚና:' : 'Active Persona Simulation:' }}
            <strong class="font-bold underline ml-1">{{ localeStore.tRole(currentRole) }}</strong>
          </span>
        </div>
        <button
          @click="handleExitSimulation"
          class="ml-3 shrink-0 bg-[#DE1B54] hover:bg-[#B31340] text-white font-bold px-2.5 py-1 rounded-lg text-[11px] transition shadow-xs cursor-pointer flex items-center gap-1"
          :title="localeStore.isAmharic ? 'ወደ ዋና አስተዳዳሪ ተመለስ' : 'Return to Super Admin'"
        >
          <span>✕</span>
          <span>{{ localeStore.t('dash_exit') }}</span>
        </button>
      </div>

      <!-- View Slot with Warm Bakery Background & Smooth Page Transition -->
      <main id="admin-main-scroll" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#FAF7F2] dark:bg-[#120B07] text-[#2B1810] dark:text-[#FAF4ED]">
        <router-view v-slot="{ Component, route: currentRoute }">
          <PageTransition
            :component="Component"
            :transition-key="currentRoute.path"
            scroll-container-selector="#admin-main-scroll"
          />
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useLocaleStore } from '../stores/locale';
import { useThemeStore } from '../stores/theme';
import PageTransition from '../components/PageTransition.vue';
import api from '../api';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const localeStore = useLocaleStore();
const themeStore = useThemeStore();
const unreadCount = ref(0);
let notificationPollInterval = null;
let adminDomObserver = null;
let bodyModalObserver = null;
let isTranslating = false;
let translateTimeout = null;

const containsAmharic = (str) => /[\u1200-\u137F]/.test(str);

// Universal Admin DOM Translation Engine for All Dashboards, Modals & Pages
function translateAdminElement(root) {
  if (!root) return;
  const isAm = localeStore.isAmharic;

  // 1. Walk Text Nodes inside container
  const walker = document.createTreeWalker(
    root,
    NodeFilter.SHOW_TEXT,
    {
      acceptNode(node) {
        const parent = node.parentElement;
        if (!parent) return NodeFilter.FILTER_REJECT;
        const tag = parent.tagName;
        if (['SCRIPT', 'STYLE', 'SVG', 'PATH', 'CODE', 'PRE'].includes(tag)) {
          return NodeFilter.FILTER_REJECT;
        }
        if (!node.nodeValue || !node.nodeValue.trim()) {
          return NodeFilter.FILTER_REJECT;
        }
        return NodeFilter.FILTER_ACCEPT;
      }
    }
  );

  let node;
  while ((node = walker.nextNode())) {
    if (node._origText === undefined) {
      node._origText = node.nodeValue;
    } else if (!containsAmharic(node.nodeValue) && node.nodeValue !== node._origText && node.nodeValue !== node._lastTranslated) {
      node._origText = node.nodeValue;
    }
    if (isAm) {
      const translated = localeStore.translateText(node._origText);
      node._lastTranslated = translated;
      if (translated !== node.nodeValue) {
        node.nodeValue = translated;
      }
    } else {
      if (node._origText !== undefined && node.nodeValue !== node._origText) {
        node.nodeValue = node._origText;
      }
    }
  }

  // 2. Translate Select Options
  const options = root.querySelectorAll('option');
  options.forEach((opt) => {
    if (opt._origText === undefined) {
      opt._origText = opt.textContent;
    } else if (!containsAmharic(opt.textContent) && opt.textContent !== opt._origText && opt.textContent !== opt._lastTranslated) {
      opt._origText = opt.textContent;
    }
    if (isAm) {
      const translated = localeStore.translateText(opt._origText);
      opt._lastTranslated = translated;
      if (opt.textContent !== translated) {
        opt.textContent = translated;
      }
    } else {
      if (opt._origText !== undefined && opt.textContent !== opt._origText) {
        opt.textContent = opt._origText;
      }
    }
  });

  // 3. Translate Input and Textarea Placeholders
  const inputs = root.querySelectorAll('input[placeholder], textarea[placeholder]');
  inputs.forEach((input) => {
    if (input._origPlaceholder === undefined) {
      input._origPlaceholder = input.placeholder;
    } else if (!containsAmharic(input.placeholder) && input.placeholder !== input._origPlaceholder && input.placeholder !== input._lastTranslated) {
      input._origPlaceholder = input.placeholder;
    }
    if (isAm) {
      const translated = localeStore.translateText(input._origPlaceholder);
      input._lastTranslated = translated;
      if (input.placeholder !== translated) {
        input.placeholder = translated;
      }
    } else {
      if (input._origPlaceholder !== undefined && input.placeholder !== input._origPlaceholder) {
        input.placeholder = input._origPlaceholder;
      }
    }
  });

  // 4. Translate Tooltip Titles
  const titles = root.querySelectorAll('[title]');
  titles.forEach((el) => {
    if (el._origTitle === undefined) {
      el._origTitle = el.title;
    }
    if (isAm) {
      const translated = localeStore.translateText(el._origTitle);
      if (el.title !== translated) {
        el.title = translated;
      }
    } else {
      if (el._origTitle !== undefined && el.title !== el._origTitle) {
        el.title = el._origTitle;
      }
    }
  });
}

function translateAdminDom() {
  if (isTranslating || typeof document === 'undefined') return;
  isTranslating = true;
  try {
    const container = document.getElementById('admin-main-scroll');
    if (container) {
      translateAdminElement(container);
    }
    // Also process any modals or teleported dialogs attached to body
    const modals = document.querySelectorAll('body > .fixed, body > [role="dialog"], .admin-modal, div.fixed.z-50, div.fixed.z-60');
    modals.forEach((modal) => {
      translateAdminElement(modal);
    });
  } finally {
    isTranslating = false;
  }
}

function scheduleTranslation() {
  if (translateTimeout) cancelAnimationFrame(translateTimeout);
  translateTimeout = requestAnimationFrame(() => {
    translateAdminDom();
  });
}

// Watch language toggle to instantly translate or restore all page contents
watch(() => localeStore.isAmharic, () => {
  nextTick(() => {
    scheduleTranslation();
  });
});

const fetchUnreadNotifications = async () => {
  if (!authStore.isAuthenticated) return;
  try {
    const res = await api.get('/admin/notifications?limit=1');
    if (res.data && typeof res.data.unread_count === 'number') {
      unreadCount.value = res.data.unread_count;
    }
  } catch (e) {
    // Silent fail on polling errors
  }
};

onMounted(async () => {
  if (authStore.canSimulatePersona) {
    await authStore.fetchAvailableRoles();
  }
  await fetchUnreadNotifications();
  // Real-time polling every 12 seconds for staff and managers
  notificationPollInterval = setInterval(fetchUnreadNotifications, 12000);

  // Setup Observer for automatic translation of dynamic tables, modals, & tabs
  const container = document.getElementById('admin-main-scroll');
  if (container) {
    adminDomObserver = new MutationObserver(() => {
      if (!isTranslating) {
        scheduleTranslation();
      }
    });
    adminDomObserver.observe(container, {
      childList: true,
      subtree: true,
      characterData: true
    });
  }

  // Also observe body for teleported dialog modals opening/closing
  if (document.body) {
    bodyModalObserver = new MutationObserver((mutations) => {
      if (isTranslating) return;
      const hasAddedNodes = mutations.some((m) => m.addedNodes.length > 0);
      if (hasAddedNodes) {
        scheduleTranslation();
      }
    });
    bodyModalObserver.observe(document.body, {
      childList: true
    });
  }

  if (localeStore.isAmharic) {
    nextTick(() => {
      setTimeout(scheduleTranslation, 60);
    });
  }
});

onBeforeUnmount(() => {
  if (translateTimeout) {
    cancelAnimationFrame(translateTimeout);
    translateTimeout = null;
  }
  if (notificationPollInterval) {
    clearInterval(notificationPollInterval);
  }
  if (adminDomObserver) {
    adminDomObserver.disconnect();
    adminDomObserver = null;
  }
  if (bodyModalObserver) {
    bodyModalObserver.disconnect();
    bodyModalObserver = null;
  }
});

// Mobile sidebar drawer state
const sidebarOpen = ref(false);

// Auto-close sidebar and re-run translator on route change
watch(() => route.path, () => {
  sidebarOpen.value = false;
  nextTick(() => {
    setTimeout(scheduleTranslation, 80);
  });
});

const currentRole = computed(() => authStore.user?.role || 'Super Admin');

const userInitials = computed(() => {
  const name = authStore.user?.full_name;
  if (!name) return 'KN';
  const parts = name.trim().split(' ');
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase();
});

const displayRole = computed(() => {
  if (currentRole.value === 'Branch Admin') return 'Branch Manager';
  return currentRole.value;
});

const isBranchScoped = computed(() => {
  return [
    'Branch Manager',
    'Branch Admin',
    'Branch Staff',
    'Branch Seller',
    'Branch Seller / Cashier',
    'Cashier',
    'Servant',
    'Delivery Staff',
  ].includes(currentRole.value);
});

const isStaffMember = computed(() => {
  return [
    'Branch Staff',
    'Branch Seller',
    'Branch Seller / Cashier',
    'Cashier',
    'Servant',
    'Delivery Staff',
  ].includes(currentRole.value);
});

const homePath = computed(() => {
  if (isStaffMember.value) return '/admin/orders';
  return '/admin/overview';
});

const roleScope = computed(() => {
  if (localeStore.isAmharic) {
    if (currentRole.value === 'Super Admin') return 'መላው ንግድ (ሁሉም ቅርንጫፎች)';
    if (currentRole.value === 'Main Branch Manager') return 'ዋና ቅርንጫፍ እና ስርጭት';
    const branchName = localeStore.tBranch(authStore.user?.branch?.branch_name) || 'የተመደበ ቅርንጫፍ';
    if (['Branch Manager', 'Branch Admin'].includes(currentRole.value)) return `${branchName} ቅርንጫፍ`;
    if (isStaffMember.value) return `${branchName} ቅርንጫፍ (${localeStore.tRole(displayRole.value)})`;
    return 'የደንበኛ መደብር';
  }
  if (currentRole.value === 'Super Admin') return 'Whole business (All branches)';
  if (currentRole.value === 'Main Branch Manager') return 'Main Branch & Distribution';
  if (['Branch Manager', 'Branch Admin'].includes(currentRole.value)) return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch`;
  if (isStaffMember.value) return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch (${displayRole.value})`;
  return 'Customer Storefront';
});

const formatPersonaLabel = (roleItem) => {
  if (!roleItem) return '';
  if (!localeStore.isAmharic) return roleItem.label;
  const translatedRole = localeStore.tRole(roleItem.role) || roleItem.role;
  const branchName = roleItem.branch_name ? ` - ${localeStore.tBranch(roleItem.branch_name)}` : '';
  return `${translatedRole}${branchName}`;
};

// Role-based permission map (Super Admin focuses on central oversight; Branch Managers manage local products/prices, inventory, and staff reports; Sellers manage daily orders and counter sales)
const permissions = {
  'Super Admin': ['overview', 'branches', 'users', 'salaries', 'manager-reports', 'my-account', 'inventory', 'distribution', 'transfers/new', 'payments', 'notifications', 'settings'],
  'Main Branch Manager': ['overview', 'users', 'products', 'orders', 'custom-cakes', 'manager-reports', 'my-account', 'inventory', 'distribution', 'transfers/new', 'notifications'],
  'Branch Manager': ['overview', 'users', 'products', 'orders', 'custom-cakes', 'manager-reports', 'my-account', 'inventory', 'distribution', 'notifications'],
  'Branch Admin': ['overview', 'users', 'products', 'orders', 'custom-cakes', 'manager-reports', 'my-account', 'inventory', 'distribution', 'notifications'],
  'Branch Seller / Cashier': ['products', 'seller-reports', 'orders', 'custom-cakes', 'inventory', 'my-account', 'notifications'],
  'Branch Seller': ['products', 'seller-reports', 'orders', 'custom-cakes', 'inventory', 'my-account', 'notifications'],
  'Cashier': ['products', 'seller-reports', 'orders', 'custom-cakes', 'inventory', 'my-account', 'notifications'],
  'Servant': ['orders', 'inventory', 'my-account', 'notifications'],
  'Delivery Staff': ['orders', 'my-account', 'notifications'],
  'Branch Staff': ['products', 'inventory', 'distribution', 'orders', 'custom-cakes', 'my-account', 'notifications'],
  'Customer': [],
};

const canAccess = (feature) => {
  const allowed = permissions[currentRole.value] || [];
  return allowed.includes(feature);
};

const selectedRoleKey = computed(() => {
  const index = authStore.availableRoles.findIndex(
    (r) => r.role === authStore.user?.role && (!r.branch_code || r.branch_code === authStore.user?.branch?.branch_code)
  );
  return index >= 0 ? index : 0;
});

const handleRoleChange = async (index) => {
  const roleItem = authStore.availableRoles[index];
  if (roleItem) {
    await authStore.switchRole(roleItem);
    if (roleItem.role === 'Customer') {
      router.push('/');
    } else if (['Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'].includes(roleItem.role)) {
      router.push('/admin/orders');
    } else {
      router.push('/admin/overview');
    }
  }
};

async function handleExitSimulation() {
  await authStore.exitSimulation();
  router.push('/admin/overview');
}

function handleLogout() {
  authStore.logout();
  router.push('/login');
}
</script>

<style>
@media print {
  body, .admin-dashboard {
    height: auto !important;
    overflow: visible !important;
    background: #ffffff !important;
    color: #1a1412 !important;
  }
  aside, header, .print\:hidden {
    display: none !important;
    height: 0 !important;
    overflow: hidden !important;
  }
  main {
    padding: 0 !important;
    margin: 0 !important;
    overflow: visible !important;
    background: #ffffff !important;
  }
}
</style>
