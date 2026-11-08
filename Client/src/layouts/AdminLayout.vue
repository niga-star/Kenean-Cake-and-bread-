<template>
  <div class="flex h-screen bg-[#FAF7F5] text-[#29231F] font-poppins antialiased overflow-hidden relative admin-dashboard selection:bg-[#FCE7F0] selection:text-[#DE1B54]">
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
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
        aria-hidden="true"
      ></div>
    </transition>

    <!-- Dark Luxury Bakery Sidebar: Responsive Drawer on Mobile (< lg), Static on Desktop (lg+) -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 lg:w-72 bg-[#1A120D] flex flex-col justify-between shrink-0 select-none border-r border-[#2C1F16] shadow-2xl lg:shadow-none transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 font-poppins"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex-1 flex flex-col min-h-0">
        <!-- Brand Logo Header + Mobile Close Button -->
        <div class="px-5 sm:px-6 py-5 sm:py-6 border-b border-[#2C1F16]/80 flex items-center justify-between shrink-0">
          <div class="flex items-center space-x-3">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#DE1B54] via-[#BA5420] to-[#9E4314] text-white flex items-center justify-center font-bold text-xl shadow-md shrink-0">🧁</span>
            <router-link :to="homePath" @click="sidebarOpen = false" class="block leading-tight group">
              <h1 class="text-2xl sm:text-[26px] font-logo font-bold text-[#DE1B54] tracking-normal group-hover:opacity-95 transition">Sweet<span class="text-[#FAF4ED]">Bakes</span></h1>
              <p class="text-xs text-[#BBAAA0] tracking-wide font-poppins font-medium mt-0.5">Cakes · Bread · Happiness</p>
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

        <!-- Navigation Menu with Touch-Friendly Links & Larger Fonts -->
        <nav class="px-3.5 py-4 space-y-5 overflow-y-auto flex-1">
          <!-- Overview Direct Link -->
          <div v-if="canAccess('overview')">
            <router-link
              to="/admin/overview"
              @click="sidebarOpen = false"
              class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200 group"
              :class="$route.path === '/admin/overview' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
            >
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span>{{ isBranchScoped ? 'Branch Overview' : 'Overview' }}</span>
            </router-link>
          </div>

          <!-- BUSINESS Section -->
          <div v-if="canAccess('branches') || canAccess('users') || canAccess('products')">
            <div class="px-4 mb-2 text-xs font-bold tracking-wider text-[#A69588] uppercase">Business</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('branches')"
                to="/admin/branches"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/branches') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Branches</span>
              </router-link>

              <router-link
                v-if="canAccess('users')"
                to="/admin/users"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/users') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>{{ isBranchScoped ? 'Branch staff' : 'Users and staff' }}</span>
              </router-link>

              <router-link
                v-if="canAccess('salaries')"
                to="/admin/salaries"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/salaries') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Salaries &amp; wages</span>
              </router-link>

              <router-link
                v-if="canAccess('products')"
                to="/admin/products"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/products') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>Products and prices</span>
              </router-link>
            </div>
          </div>

          <!-- STOCK Section -->
          <div>
            <div class="px-4 mb-2 text-xs font-bold tracking-wider text-[#A69588] uppercase">Stock</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('inventory')"
                to="/admin/inventory"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/inventory' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>{{ isBranchScoped ? 'My Inventory' : 'Inventory by branch' }}</span>
              </router-link>

              <router-link
                v-if="canAccess('distribution')"
                to="/admin/distribution"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/distribution' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                  </svg>
                  <span>{{ isBranchScoped ? 'Stock Receiving' : 'Distribution' }}</span>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white text-xs font-bold flex items-center justify-center shadow-xs">2</span>
              </router-link>

              <router-link
                v-if="canAccess('transfers/new')"
                to="/admin/transfers/new"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/transfers/new') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>New transfer</span>
              </router-link>
            </div>
          </div>

          <!-- SALES Section -->
          <div>
            <div class="px-4 mb-2 text-xs font-bold tracking-wider text-[#A69588] uppercase">Sales</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('orders')"
                to="/admin/orders"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/orders' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>{{ currentRole === 'Branch Staff' ? 'Order Preparation' : (isBranchScoped ? 'My Orders' : 'All orders') }}</span>
              </router-link>

              <router-link
                v-if="canAccess('custom-cakes')"
                to="/admin/custom-cakes"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/custom-cakes' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.5a3.5 3.5 0 01-7 0 3.5 3.5 0 01-7 0M12 4v4m-4-2h8m-9 9.5V20a1 1 0 001 1h16a1 1 0 001-1v-4.5" />
                </svg>
                <span>{{ isBranchScoped ? 'Custom Cake Orders' : 'Custom cakes' }}</span>
              </router-link>

              <router-link
                v-if="canAccess('payments')"
                to="/admin/payments"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/payments' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>{{ isBranchScoped ? 'Branch Sales' : 'Payments' }}</span>
              </router-link>

              <router-link
                v-if="canAccess('reports')"
                to="/admin/reports"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/reports' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ isBranchScoped ? 'Branch Reports' : 'Reports' }}</span>
              </router-link>

              <router-link
                v-if="canAccess('staff-reports')"
                to="/admin/staff-reports"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/staff-reports') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Staff reports</span>
              </router-link>
            </div>
          </div>

          <!-- SYSTEM Section -->
          <div v-if="canAccess('notifications') || canAccess('audit-logs') || canAccess('settings') || canAccess('my-account')">
            <div class="px-4 mb-2 text-xs font-bold tracking-wider text-[#A69588] uppercase">System</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('my-account')"
                to="/admin/my-account"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path.startsWith('/admin/my-account') ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>My account</span>
              </router-link>

              <router-link
                v-if="canAccess('notifications')"
                to="/admin/notifications"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/notifications' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span>Notifications</span>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white text-xs font-bold flex items-center justify-center shadow-xs">3</span>
              </router-link>

              <router-link
                v-if="canAccess('audit-logs')"
                to="/admin/audit-logs"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/audit-logs' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Audit log</span>
              </router-link>

              <router-link
                v-if="canAccess('settings')"
                to="/admin/settings"
                @click="sidebarOpen = false"
                class="flex items-center px-4 py-2.5 text-[15px] font-semibold rounded-xl transition-all duration-200"
                :class="$route.path === '/admin/settings' ? 'bg-gradient-to-r from-[#BA5420] to-[#CD5C22] text-white shadow-md font-bold' : 'text-[#D8CDC5] hover:bg-white/10 hover:text-white'"
              >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Settings</span>
              </router-link>
            </div>
          </div>
        </nav>
      </div>

      <!-- Bottom User Profile Card with Role & Branch -->
      <div class="p-3.5 sm:p-4 border-t border-[#2C1F16] bg-[#140C08] flex items-center justify-between shrink-0">
        <router-link
          to="/admin/my-account"
          class="flex items-center space-x-3 min-w-0 pr-2 hover:opacity-95 transition group cursor-pointer"
          title="View My Account"
        >
          <div class="w-10 h-10 rounded-2xl overflow-hidden bg-[#BA5420] shrink-0 border border-amber-300/40 shadow-sm flex items-center justify-center text-white text-sm font-bold">
            <img
              src="/mockup/avatar_mekdes.jpg"
              alt="User Avatar"
              class="w-full h-full object-cover"
              onerror="this.style.display='none'"
            />
          </div>
          <div class="min-w-0">
            <div class="text-sm sm:text-[15px] font-bold text-[#F4EDE4] truncate leading-tight group-hover:text-[#DE1B54] transition">
              {{ authStore.user?.full_name || 'Admin User' }}
            </div>
            <div class="text-xs text-[#A69588] truncate mt-0.5 leading-tight">
              <span class="text-[#E4A373] font-semibold">{{ displayRole }}</span>
              <span v-if="authStore.user?.branch"> · {{ authStore.user.branch.branch_name }}</span>
            </div>
          </div>
        </router-link>

        <button
          @click="handleLogout"
          class="p-2.5 text-gray-400 hover:text-white hover:bg-white/10 rounded-xl transition cursor-pointer shrink-0"
          title="Sign out of portal"
          aria-label="Sign out"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Modern Bakery Header Bar -->
      <header class="min-h-[4.25rem] bg-white/95 backdrop-blur-md border-b border-[#EAE2D7] px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center justify-between gap-3 shrink-0 shadow-[0_1px_4px_rgba(0,0,0,0.03)]">
        <!-- Left: Hamburger Toggle + Persona Selector -->
        <div class="flex items-center space-x-3 flex-1 min-w-0">
          <!-- Hamburger Button (visible on < lg screens) -->
          <button
            @click="sidebarOpen = !sidebarOpen"
            type="button"
            class="lg:hidden p-2 rounded-xl text-[#4A3B32] hover:bg-[#F4ECE3] hover:text-[#BA5420] transition cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-[#BA5420]"
            title="Open navigation menu"
            aria-label="Open navigation menu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- 1. Super Admin Only: Persona Simulator -->
          <div v-if="authStore.canSimulatePersona" class="flex flex-wrap items-center gap-2.5 text-sm min-w-0">
            <span class="text-[#7A6B60] font-semibold text-xs sm:text-sm hidden sm:inline shrink-0">Simulate Role:</span>
            <select
              :value="selectedRoleKey"
              @change="handleRoleChange($event.target.value)"
              class="bg-[#FAF7F5] border border-[#DACDC0] text-[#2A1810] text-xs sm:text-sm font-bold rounded-xl px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-[#BA5420] cursor-pointer max-w-[180px] xs:max-w-[220px] sm:max-w-none shadow-2xs"
            >
              <option
                v-for="(r, idx) in authStore.availableRoles"
                :key="idx"
                :value="idx"
              >
                {{ r.label }}
              </option>
            </select>

            <!-- Simulating Indicator with Exit Button -->
            <span
              v-if="authStore.isSimulating"
              class="text-xs sm:text-sm px-3 py-1 rounded-full font-bold bg-amber-50 text-amber-900 border border-amber-300 flex items-center space-x-1.5 shrink-0 shadow-2xs"
            >
              <span class="truncate max-w-[120px] sm:max-w-none">Simulating: {{ currentRole }}</span>
              <button
                @click="handleExitSimulation"
                class="text-[11px] bg-amber-200 hover:bg-amber-300 text-amber-950 font-bold px-2 py-0.5 rounded-md transition cursor-pointer ml-1"
                title="Return to Super Admin"
              >
                Exit ✕
              </button>
            </span>

            <span
              v-else
              class="hidden md:inline-flex text-xs sm:text-sm px-3.5 py-1 rounded-full font-bold border bg-[#FFF3E0] text-[#E65100] border-[#FFE0B2] truncate shadow-2xs"
            >
              Scope: {{ roleScope }}
            </span>
          </div>

          <!-- 2. Non-Super Admin: Strictly Read-Only Role & Scope Badge -->
          <div v-else class="flex flex-wrap items-center gap-2.5 text-sm min-w-0">
            <div class="flex items-center space-x-2 min-w-0">
              <span class="text-xs sm:text-sm text-[#7A6B60] font-medium hidden sm:inline">Logged in as:</span>
              <span class="text-sm font-bold text-[#2A1810] truncate max-w-[140px] sm:max-w-none">{{ authStore.user?.full_name || 'Staff' }}</span>
            </div>

            <span
              class="text-xs sm:text-sm px-3 py-1 rounded-full font-bold border shrink-0 shadow-2xs"
              :class="{
                'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': currentRole === 'Main Branch Manager',
                'bg-[#FAF3EC] text-[#BA5420] border-[#EACBB8]': currentRole === 'Branch Admin' || currentRole === 'Branch Manager',
                'bg-[#F3EFE9] text-[#7A6B60] border-[#DDD3C7]': currentRole === 'Branch Staff',
                'bg-gray-100 text-gray-800 border-gray-200': !['Main Branch Manager', 'Branch Admin', 'Branch Manager', 'Branch Staff'].includes(currentRole)
              }"
            >
              {{ displayRole }}
            </span>

            <span class="hidden md:inline-flex text-xs sm:text-sm px-3.5 py-1 rounded-full font-semibold bg-[#FAF7F5] text-[#55463C] border border-[#E7DFD5] truncate shadow-2xs">
              Scope: {{ roleScope }}
            </span>
          </div>
        </div>

        <!-- Right: Notifications & Direct Link to Customer Storefront -->
        <div class="flex items-center space-x-3 shrink-0">
          <!-- Notification Bell -->
          <router-link
            to="/admin/notifications"
            class="relative p-2.5 text-[#5A4A3E] hover:text-[#BA5420] hover:bg-[#FAF4ED] rounded-xl transition cursor-pointer"
            title="Notifications"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-[#DE1B54] text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none shadow-xs">3</span>
          </router-link>

          <!-- SweetBakes Client Storefront Shortcut (Beauty link) -->
          <router-link
            to="/"
            class="inline-flex items-center space-x-2 text-xs sm:text-sm font-bold text-[#DE1B54] bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 sm:px-4 py-2 rounded-xl transition shadow-xs group"
            title="View Live Customer Bakery Storefront"
          >
            <span>🧁</span>
            <span class="hidden sm:inline">View Bakery Store</span>
            <span class="sm:hidden">Store</span>
            <svg class="w-4 h-4 text-rose-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </router-link>
        </div>
      </header>

      <!-- View Slot with Warm Bakery Background & Elevated Padding -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#FAF7F5]">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

// Mobile sidebar drawer state
const sidebarOpen = ref(false);

// Auto-close sidebar on route change for seamless mobile navigation
watch(() => route.path, () => {
  sidebarOpen.value = false;
});

const currentRole = computed(() => authStore.user?.role || 'Super Admin');

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
  if (currentRole.value === 'Super Admin') return 'Whole business (All branches)';
  if (currentRole.value === 'Main Branch Manager') return 'Main Branch & Distribution';
  if (['Branch Manager', 'Branch Admin'].includes(currentRole.value)) return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch`;
  if (isStaffMember.value) return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch (${displayRole.value})`;
  return 'Customer Storefront';
});

// Role-based permission map
const permissions = {
  'Super Admin': ['overview', 'branches', 'users', 'salaries', 'staff-reports', 'my-account', 'inventory', 'distribution', 'transfers/new', 'payments', 'reports', 'notifications', 'settings', 'audit-logs'],
  'Main Branch Manager': ['overview', 'users', 'products', 'staff-reports', 'my-account', 'inventory', 'distribution', 'transfers/new', 'orders', 'custom-cakes', 'payments', 'reports', 'notifications', 'audit-logs'],
  'Branch Manager': ['overview', 'users', 'products', 'staff-reports', 'my-account', 'inventory', 'distribution', 'orders', 'custom-cakes', 'reports', 'notifications', 'audit-logs'],
  'Branch Admin': ['overview', 'users', 'products', 'staff-reports', 'my-account', 'inventory', 'distribution', 'orders', 'custom-cakes', 'reports', 'notifications', 'audit-logs'],
  'Branch Seller / Cashier': ['products', 'orders', 'custom-cakes', 'inventory', 'staff-reports', 'my-account', 'notifications'],
  'Branch Seller': ['products', 'orders', 'custom-cakes', 'inventory', 'staff-reports', 'my-account', 'notifications'],
  'Cashier': ['products', 'orders', 'custom-cakes', 'inventory', 'staff-reports', 'my-account', 'notifications'],
  'Servant': ['orders', 'inventory', 'staff-reports', 'my-account', 'notifications'],
  'Delivery Staff': ['orders', 'staff-reports', 'my-account', 'notifications'],
  'Branch Staff': ['products', 'inventory', 'distribution', 'orders', 'staff-reports', 'my-account', 'notifications'],
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
