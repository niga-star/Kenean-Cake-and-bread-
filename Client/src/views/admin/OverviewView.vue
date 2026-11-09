<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins pb-12">
    <!-- ======================================================== -->
    <!-- TOP HEADER: GREETING & QUICK ACTIONS (Reference Standard) -->
    <!-- ======================================================== -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-serif font-black tracking-tight text-[#2B1810] leading-tight">
          {{ greetingText }}, <span class="text-[#3D251E]">{{ currentUserName }}</span>! 👋
        </h1>
        <p class="text-xs sm:text-sm text-[#7D6E64] font-normal mt-1">
          {{ data.is_branch_view
            ? `Here's what's happening with ${data.branch?.branch_name || 'your bakery'} today.`
            : "Here's what's happening with your bakery today." }}
        </p>
      </div>

      <!-- Action Controls: Date Picker, Branch Filter (if Super Admin), + New Order -->
      <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <!-- Branch Selector (Super Admin / Central view) -->
        <div v-if="!data.is_branch_view && branches && branches.length > 0" class="relative">
          <select
            v-model="selectedBranch"
            @change="loadOverview"
            class="bg-white border border-[#E8DFD5] hover:border-[#DACDC0] text-[#3D251E] text-xs sm:text-sm font-semibold rounded-xl px-3.5 py-2 shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#3D251E]/20 cursor-pointer pr-8"
          >
            <option value="all">All Branches (Whole Business)</option>
            <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
              {{ b.branch_name }}
            </option>
          </select>
        </div>

        <!-- Date Selector Pill Button -->
        <div class="relative">
          <button
            type="button"
            @click="triggerDatePicker"
            class="dash-btn-secondary flex items-center space-x-2 py-2 px-3.5 text-xs sm:text-sm"
            title="Filter by date"
          >
            <svg class="w-4 h-4 text-[#7D6E64]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="font-medium text-[#2B1810]">{{ displayDateLabel }}</span>
            <svg class="w-3.5 h-3.5 text-[#9C8E84]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <input
            ref="dateInputRef"
            type="date"
            v-model="selectedDate"
            @change="loadOverview"
            class="sr-only"
          />
        </div>

        <button
          v-if="selectedDate"
          @click="clearDate"
          class="text-xs text-[#7D6E64] hover:text-[#3D251E] px-2 py-1 font-bold underline cursor-pointer"
        >
          Reset
        </button>

        <!-- + New Order Dark Pill Button (Only shown to roles handling orders) -->
        <router-link
          v-if="canAccessOrders"
          to="/admin/orders"
          class="dash-btn-primary py-2 px-4 text-xs sm:text-sm flex items-center space-x-1.5 shadow-xs"
        >
          <span class="text-base leading-none font-bold">+</span>
          <span>New Order</span>
        </router-link>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 1. TOP 4 KPI STAT CARDS (Reference Single Source of Truth)-->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
      <!-- 1. Daily Orders Card -->
      <div class="dash-card dash-card-hover p-5 flex flex-col justify-between">
        <div class="flex items-start justify-between">
          <div class="w-11 h-11 rounded-2xl bg-[#FDEEE9] text-[#DE5D35] flex items-center justify-center text-xl shadow-2xs">
            <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
          <!-- Sparkline Mini Chart -->
          <svg class="w-20 h-10 text-[#DE5D35]" viewBox="0 0 80 40" fill="none">
            <path d="M4,28 Q18,8 32,22 T56,12 T76,18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
          </svg>
        </div>

        <div class="mt-4">
          <div class="dash-subtitle font-medium text-[#7D6E64]">Daily Orders</div>
          <div class="dash-metric text-3xl sm:text-[34px] font-black text-[#2B1810] mt-0.5">
            {{ data.orders_today ?? 48 }}
          </div>
          <div class="flex items-center space-x-1 text-xs font-semibold text-[#2E7D32] mt-1.5">
            <span class="font-bold">↗</span>
            <span>+12% vs yesterday</span>
          </div>
        </div>
      </div>

      <!-- 2. Revenue (Today) Card -->
      <div class="dash-card dash-card-hover p-5 flex flex-col justify-between">
        <div class="flex items-start justify-between">
          <div class="w-11 h-11 rounded-2xl bg-[#FEF6E6] text-[#D99B16] flex items-center justify-center text-xl shadow-2xs">
            <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <!-- Sparkline Mini Chart -->
          <svg class="w-20 h-10 text-[#D99B16]" viewBox="0 0 80 40" fill="none">
            <path d="M4,26 Q16,32 30,16 T58,20 T76,8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
          </svg>
        </div>

        <div class="mt-4">
          <div class="dash-subtitle font-medium text-[#7D6E64]">Revenue (Today)</div>
          <div class="dash-metric text-3xl sm:text-[34px] font-black text-[#2B1810] mt-0.5">
            ETB {{ Number(data.revenue_today || 1245).toLocaleString() }}
          </div>
          <div class="flex items-center space-x-1 text-xs font-semibold text-[#2E7D32] mt-1.5">
            <span class="font-bold">↗</span>
            <span>+8% vs yesterday</span>
          </div>
        </div>
      </div>

      <!-- 3. Out for Delivery Card -->
      <div class="dash-card dash-card-hover p-5 flex flex-col justify-between">
        <div class="flex items-start justify-between">
          <div class="w-11 h-11 rounded-2xl bg-[#F4EFFE] text-[#7E57C2] flex items-center justify-center text-xl shadow-2xs">
            <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
          </div>
          <!-- Sparkline Mini Chart -->
          <svg class="w-20 h-10 text-[#7E57C2]" viewBox="0 0 80 40" fill="none">
            <path d="M4,22 Q18,34 36,18 T58,14 T76,10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
          </svg>
        </div>

        <div class="mt-4">
          <div class="dash-subtitle font-medium text-[#7D6E64]">Out for Delivery</div>
          <div class="dash-metric text-3xl sm:text-[34px] font-black text-[#2B1810] mt-0.5">
            {{ data.out_for_delivery ?? data.delivery_pipeline?.out_for_delivery ?? 16 }}
          </div>
          <div class="flex items-center space-x-1 text-xs font-semibold text-[#2E7D32] mt-1.5">
            <span class="font-bold">↗</span>
            <span>+4 vs yesterday</span>
          </div>
        </div>
      </div>

      <!-- 4. Delivered (Today) Card -->
      <div class="dash-card dash-card-hover p-5 flex flex-col justify-between">
        <div class="flex items-start justify-between">
          <div class="w-11 h-11 rounded-2xl bg-[#EDF7ED] text-[#2E7D32] flex items-center justify-center text-xl shadow-2xs">
            <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <!-- Sparkline Mini Chart -->
          <svg class="w-20 h-10 text-[#2E7D32]" viewBox="0 0 80 40" fill="none">
            <path d="M4,30 Q20,12 36,24 T60,8 T76,14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
          </svg>
        </div>

        <div class="mt-4">
          <div class="dash-subtitle font-medium text-[#7D6E64]">Delivered (Today)</div>
          <div class="dash-metric text-3xl sm:text-[34px] font-black text-[#2B1810] mt-0.5">
            {{ data.delivered_today ?? data.delivery_pipeline?.delivered ?? 32 }}
          </div>
          <div class="flex items-center space-x-1 text-xs font-semibold text-[#2E7D32] mt-1.5">
            <span class="font-bold">↗</span>
            <span>+9 vs yesterday</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. MIDDLE ROW: 3 CARDS (Delivery Status, Best-Sellers, Revenue) -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
      <!-- Card 1: Delivery Status Pipeline (Col span 3.5) -->
      <div class="lg:col-span-4 dash-card p-5 sm:p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="dash-section-title text-base sm:text-lg">Delivery Status</h2>
            <router-link
              v-if="canAccessOrders"
              to="/admin/orders"
              class="text-xs font-semibold text-[#7D6E64] hover:text-[#3D251E] transition"
            >
              View all
            </router-link>
          </div>

          <div class="space-y-4">
            <!-- Pending -->
            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-[#FAF7F2] transition">
              <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-[#FEF7E6] text-[#B87A14] flex items-center justify-center shrink-0 border border-[#FBEAC2]">
                  <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs font-bold text-[#2B1810]">Pending</div>
                  <div class="text-[11px] text-[#7D6E64]">Orders waiting to be processed</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-[#2B1810] font-mono">{{ data.delivery_pipeline?.pending ?? data.pending_orders ?? 12 }}</span>
                <span class="w-2 h-2 rounded-full bg-[#D99B16]"></span>
              </div>
            </div>

            <!-- Preparing -->
            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-[#FAF7F2] transition">
              <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-[#EEF4FD] text-[#2F6EB5] flex items-center justify-center shrink-0 border border-[#D2E4FA]">
                  <span class="text-sm">👩‍🍳</span>
                </div>
                <div>
                  <div class="text-xs font-bold text-[#2B1810]">Preparing</div>
                  <div class="text-[11px] text-[#7D6E64]">Being prepared in the kitchen</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-[#2B1810] font-mono">{{ data.delivery_pipeline?.preparing ?? data.preparing_orders ?? 20 }}</span>
                <span class="w-2 h-2 rounded-full bg-[#2F6EB5]"></span>
              </div>
            </div>

            <!-- Out for Delivery -->
            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-[#FAF7F2] transition">
              <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-[#F4F0FA] text-[#7E57C2] flex items-center justify-center shrink-0 border border-[#E4DAF5]">
                  <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs font-bold text-[#2B1810]">Out for Delivery</div>
                  <div class="text-[11px] text-[#7D6E64]">On the way to customers</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-[#2B1810] font-mono">{{ data.delivery_pipeline?.out_for_delivery ?? 16 }}</span>
                <span class="w-2 h-2 rounded-full bg-[#7E57C2]"></span>
              </div>
            </div>

            <!-- Delivered -->
            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-[#FAF7F2] transition">
              <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-[#EDF7ED] text-[#2E7D32] flex items-center justify-center shrink-0 border border-[#C8E6C9]">
                  <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <div class="text-xs font-bold text-[#2B1810]">Delivered</div>
                  <div class="text-[11px] text-[#7D6E64]">Successfully delivered</div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-[#2B1810] font-mono">{{ data.delivery_pipeline?.delivered ?? 32 }}</span>
                <span class="w-2 h-2 rounded-full bg-[#2E7D32]"></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Best-Selling Pastries (Col span 4) -->
      <div class="lg:col-span-4 dash-card p-5 sm:p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="dash-section-title text-base sm:text-lg">Best-Selling Pastries</h2>
            <router-link to="/admin/products" class="text-xs font-semibold text-[#7D6E64] hover:text-[#3D251E] transition">
              View all
            </router-link>
          </div>

          <div class="space-y-3.5">
            <div
              v-for="item in bestSellingList"
              :key="item.rank"
              class="flex items-center justify-between p-1.5 rounded-xl hover:bg-[#FAF7F2] transition"
            >
              <div class="flex items-center space-x-3 min-w-0">
                <span class="text-xs font-bold text-[#9C8E84] w-4 text-center">{{ item.rank }}</span>
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-[#F6EFE6] shrink-0 border border-[#E8DFD5] shadow-2xs">
                  <img
                    :src="item.image_url"
                    :alt="item.name"
                    class="w-full h-full object-cover"
                    loading="lazy"
                  />
                </div>
                <div class="min-w-0">
                  <div class="text-xs font-bold text-[#2B1810] truncate">{{ item.name }}</div>
                  <div class="text-[11px] text-[#7D6E64]">{{ item.sold_count }} sold</div>
                </div>
              </div>
              <div class="text-xs font-bold text-[#2B1810] shrink-0 font-mono">
                ETB {{ Number(item.revenue).toLocaleString() }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3: Revenue Overview SVG Chart (Col span 4) -->
      <div class="lg:col-span-4 dash-card p-5 sm:p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="dash-section-title text-base sm:text-lg">Revenue Overview</h2>
            <span class="text-xs font-semibold text-[#7D6E64] bg-[#FAF7F2] border border-[#E8DFD5] px-2.5 py-1 rounded-lg">
              This Week ⌵
            </span>
          </div>

          <div class="mb-3">
            <div class="dash-metric text-2xl sm:text-3xl font-black text-[#2B1810]">
              ETB {{ Number(data.revenue_overview?.total_revenue || 8560).toLocaleString() }}
            </div>
            <div class="text-xs text-[#7D6E64] flex items-center space-x-1.5 mt-0.5">
              <span>Total Revenue</span>
              <span class="text-[#2E7D32] font-semibold flex items-center">
                <span>&bull;</span>
                <span class="ml-1">+10% vs last week ↗</span>
              </span>
            </div>
          </div>

          <!-- Smooth Area SVG Curve Chart -->
          <div class="relative w-full h-36 pt-2">
            <svg class="w-full h-full overflow-visible" viewBox="0 0 280 110" preserveAspectRatio="none">
              <defs>
                <linearGradient id="revGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#3D251E" stop-opacity="0.22" />
                  <stop offset="100%" stop-color="#3D251E" stop-opacity="0.0" />
                </linearGradient>
              </defs>

              <!-- Grid Horizontal Guide Lines -->
              <line x1="0" y1="20" x2="280" y2="20" stroke="#EFE8DE" stroke-dasharray="3,3" />
              <line x1="0" y1="50" x2="280" y2="50" stroke="#EFE8DE" stroke-dasharray="3,3" />
              <line x1="0" y1="80" x2="280" y2="80" stroke="#EFE8DE" stroke-dasharray="3,3" />

              <!-- Smooth Area Gradient Fill -->
              <path
                d="M10,85 C50,75 80,60 120,62 C160,65 190,40 230,30 C250,25 265,35 270,40 L270,95 L10,95 Z"
                fill="url(#revGradient)"
              />

              <!-- Curved Stroke Line -->
              <path
                d="M10,85 C50,75 80,60 120,62 C160,65 190,40 230,30 C250,25 265,35 270,40"
                stroke="#3D251E"
                stroke-width="2.5"
                stroke-linecap="round"
                fill="none"
              />

              <!-- Peak Dot -->
              <circle cx="230" cy="30" r="4.5" fill="#3D251E" stroke="#FFFFFF" stroke-width="2" />
            </svg>

            <!-- X-axis Day Labels -->
            <div class="flex justify-between text-[10px] font-bold text-[#8C7D73] px-1 mt-1 font-mono">
              <span>Mon</span>
              <span>Tue</span>
              <span>Wed</span>
              <span>Thu</span>
              <span>Fri</span>
              <span>Sat</span>
              <span>Sun</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. BOTTOM ROW: INVENTORY ALERTS & RECENT ORDERS TABLE     -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
      <!-- Card 1: Inventory Alerts (Col span 4.5) -->
      <div class="lg:col-span-5 dash-card p-5 sm:p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="dash-section-title text-base sm:text-lg">Inventory Alerts</h2>
            <router-link to="/admin/inventory" class="text-xs font-semibold text-[#7D6E64] hover:text-[#3D251E] transition">
              View all
            </router-link>
          </div>

          <div class="space-y-3">
            <div
              v-for="(inv, idx) in inventoryAlertsList"
              :key="idx"
              class="flex items-center justify-between p-2.5 rounded-xl border border-[#EFE8DE] bg-[#FFFFFF] hover:bg-[#FAF7F2] transition"
            >
              <div class="flex items-center space-x-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-[#FAF7F2] border border-[#E8DFD5] flex items-center justify-center text-lg shrink-0">
                  <span v-if="inv.icon === 'flour'">🌾</span>
                  <span v-else-if="inv.icon === 'butter'">🧈</span>
                  <span v-else-if="inv.icon === 'milk'">🥛</span>
                  <span v-else-if="inv.icon === 'chocolate'">🍫</span>
                  <span v-else>📦</span>
                </div>
                <div class="min-w-0">
                  <div class="text-xs font-bold text-[#2B1810] truncate">{{ inv.name }}</div>
                  <div class="flex items-center space-x-2 mt-0.5">
                    <span
                      class="text-[10px] font-bold px-1.5 py-0.2 rounded"
                      :class="inv.status === 'Out of stock' ? 'bg-[#FDEAEA] text-[#C62828]' : 'bg-[#FEF7E6] text-[#B87A14]'"
                    >
                      {{ inv.status }}
                    </span>
                    <span class="text-[11px] text-[#7D6E64]">{{ inv.remaining }}</span>
                  </div>
                </div>
              </div>

              <router-link
                to="/admin/transfers/new"
                class="dash-btn-secondary text-xs py-1 px-2.5 rounded-lg shrink-0 cursor-pointer"
              >
                Reorder
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Recent Orders Table (Col span 7) -->
      <div class="lg:col-span-7 dash-card p-5 sm:p-6 flex flex-col justify-between overflow-hidden">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="dash-section-title text-base sm:text-lg">Recent Orders</h2>
            <router-link
              v-if="canAccessOrders"
              to="/admin/orders"
              class="text-xs font-semibold text-[#7D6E64] hover:text-[#3D251E] transition"
            >
              View all
            </router-link>
          </div>

          <div class="overflow-x-auto -mx-5 sm:-mx-6 px-5 sm:px-6">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-[#EFE8DE] text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider">
                  <th class="pb-2.5 pr-3">Order ID</th>
                  <th class="pb-2.5 px-3">Customer</th>
                  <th class="pb-2.5 px-3">Items</th>
                  <th class="pb-2.5 px-3">Total</th>
                  <th class="pb-2.5 px-3 text-center">Status</th>
                  <th class="pb-2.5 pl-3 text-right">Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F4EFEA] text-xs">
                <tr v-if="!recentOrdersList || recentOrdersList.length === 0">
                  <td colspan="6" class="py-6 text-center text-xs text-[#7D6E64]">
                    No recent orders found.
                  </td>
                </tr>
                <tr
                  v-for="ord in recentOrdersList"
                  :key="ord.order_id"
                  class="hover:bg-[#FAF7F2]/60 transition"
                >
                  <td class="py-3 pr-3 font-mono font-bold text-[#2B1810]">
                    {{ ord.order_number }}
                  </td>
                  <td class="py-3 px-3 font-semibold text-[#2B1810] whitespace-nowrap">
                    {{ ord.customer }}
                  </td>
                  <td class="py-3 px-3 text-[#7D6E64] whitespace-nowrap">
                    {{ ord.items_count }}
                  </td>
                  <td class="py-3 px-3 font-bold text-[#2B1810] font-mono whitespace-nowrap">
                    ETB {{ Number(ord.total).toLocaleString() }}
                  </td>
                  <td class="py-3 px-3 text-center whitespace-nowrap">
                    <span
                      class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block"
                      :class="{
                        'dash-badge-pending': ord.status === 'Pending',
                        'dash-badge-preparing': ord.status === 'Preparing',
                        'dash-badge-delivery': ord.status === 'Out for delivery' || ord.status === 'Ready',
                        'dash-badge-delivered': ord.status === 'Completed' || ord.status === 'Delivered',
                        'dash-badge-danger': ord.status === 'Cancelled'
                      }"
                    >
                      {{ ord.status }}
                    </span>
                  </td>
                  <td class="py-3 pl-3 text-right text-[#7D6E64] whitespace-nowrap font-mono text-[11px]">
                    {{ ord.time }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. CENTRAL BUSINESS INTELLIGENCE & OPERATIONS (Super Admin)-->
    <!-- ======================================================== -->
    <div v-if="!data.is_branch_view" class="space-y-6 pt-4 border-t border-[#EFE8DE]">
      <!-- Custom Cake Orders Pipeline Card -->
      <div class="dash-card p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#EFE8DE] pb-3.5">
          <div class="flex items-center space-x-2.5">
            <span class="text-2xl">🎂</span>
            <div>
              <h3 class="dash-section-title text-base sm:text-lg">Custom Cake Orders Pipeline</h3>
              <p class="dash-subtitle mt-0.5">Managed directly across all branch seller counters.</p>
            </div>
          </div>
          <router-link
            to="/admin/custom-cakes"
            class="dash-btn-secondary text-xs py-1.5 px-3 self-start sm:self-auto"
          >
            Manage Custom Cakes →
          </router-link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1 text-center">
          <div class="p-3 bg-[#FEF7E6] border border-[#FBEAC2] rounded-xl">
            <div class="text-[11px] font-bold text-[#B87A14] uppercase">1. Submitted</div>
            <div class="dash-metric text-2xl my-1">{{ data.custom_cakes_summary?.submitted || 0 }}</div>
            <div class="text-[10px] text-[#7D6E64]">Awaiting Quote</div>
          </div>
          <div class="p-3 bg-[#EEF4FD] border border-[#D2E4FA] rounded-xl">
            <div class="text-[11px] font-bold text-[#2F6EB5] uppercase">2. Quoted</div>
            <div class="dash-metric text-2xl my-1">{{ data.custom_cakes_summary?.quoted || 0 }}</div>
            <div class="text-[10px] text-[#7D6E64]">Pending Payment</div>
          </div>
          <div class="p-3 bg-[#EDF7ED] border border-[#C8E6C9] rounded-xl">
            <div class="text-[11px] font-bold text-[#2E7D32] uppercase">3. Confirmed</div>
            <div class="dash-metric text-2xl my-1">{{ data.custom_cakes_summary?.confirmed || 0 }}</div>
            <div class="text-[10px] text-[#7D6E64]">In Baking</div>
          </div>
          <div class="p-3 bg-[#F4F0FA] border border-[#E4DAF5] rounded-xl">
            <div class="text-[11px] font-bold text-[#7E57C2] uppercase">4. Ready</div>
            <div class="dash-metric text-2xl my-1">{{ data.custom_cakes_summary?.ready || 0 }}</div>
            <div class="text-[10px] text-[#7D6E64]">Ready at Branch</div>
          </div>
          <div class="p-3 bg-[#FAF7F2] border border-[#E8DFD5] rounded-xl">
            <div class="text-[11px] font-bold text-[#6B5A4E] uppercase">5. Delivered</div>
            <div class="dash-metric text-2xl my-1">{{ data.custom_cakes_summary?.delivered || 0 }}</div>
            <div class="text-[10px] text-[#7D6E64]">Completed</div>
          </div>
        </div>
      </div>

      <!-- Branch Performance Comparison Table -->
      <div class="dash-card p-5 sm:p-6 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <div>
            <h3 class="dash-section-title text-base sm:text-lg">Branch Performance Comparison</h3>
            <p class="dash-subtitle mt-0.5">Overview of managers, staff size, orders, revenue, and active stock.</p>
          </div>
          <router-link
            to="/admin/branches"
            class="dash-btn-secondary text-xs py-1.5 px-3"
          >
            Manage Branches →
          </router-link>
        </div>

        <div class="overflow-x-auto -mx-5 sm:-mx-6 px-5 sm:px-6">
          <table class="dash-table">
            <thead>
              <tr>
                <th class="dash-th">Branch</th>
                <th class="dash-th">Manager</th>
                <th class="dash-th text-center">Staff</th>
                <th class="dash-th text-center">Orders</th>
                <th class="dash-th text-center">Revenue</th>
                <th class="dash-th text-center">Stock Health</th>
                <th class="dash-th text-right">Storefront</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F4EFEA]">
              <tr v-if="!data.branches_today || data.branches_today.length === 0">
                <td colspan="7" class="dash-td text-center py-8 text-[#7D6E64]">
                  No active branches registered yet.
                </td>
              </tr>
              <tr
                v-for="bt in data.branches_today"
                :key="bt.branch_id"
                class="dash-tr"
              >
                <td class="dash-td font-bold">
                  {{ bt.branch_name }}
                  <span class="block text-[11px] font-normal text-[#8C7D73] font-mono mt-0.5">{{ bt.branch_code }}</span>
                </td>
                <td class="dash-td text-[#4A3A30]">
                  {{ bt.manager_name }}
                </td>
                <td class="dash-td text-center font-bold">
                  {{ bt.staff_count }}
                </td>
                <td class="dash-td text-center font-bold font-mono">
                  {{ bt.orders }}
                </td>
                <td class="dash-td text-center font-bold font-mono text-[#2B1810]">
                  ETB {{ Number(bt.revenue).toLocaleString() }}
                </td>
                <td class="dash-td text-center">
                  <span
                    class="text-[11px] font-semibold px-2 py-0.5 rounded-full border"
                    :class="bt.stock_health === 'Healthy' ? 'bg-[#EDF7ED] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FEF7E6] text-[#B87A14] border-[#FBEAC2]'"
                  >
                    {{ bt.stock_health }}
                  </span>
                </td>
                <td class="dash-td text-right">
                  <span
                    class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                    :class="bt.is_shopping_enabled ? 'text-[#2E7D32] bg-[#EDF7ED]' : 'text-[#7D6E64] bg-[#FAF7F2]'"
                  >
                    {{ bt.is_shopping_enabled ? 'Active' : 'Offline' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Review Modal for Staff Reports (Preserved Functionality) -->
    <div
      v-if="activeReport"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 border border-[#EFE8DE] shadow-xl">
        <div class="flex items-center justify-between border-b border-[#EFE8DE] pb-3">
          <h3 class="dash-section-title text-lg">{{ activeReport.title }}</h3>
          <button @click="activeReport = null" class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold cursor-pointer">✕</button>
        </div>
        <div class="space-y-3 text-xs text-[#4A3A30]">
          <div class="grid grid-cols-2 gap-3 bg-[#FAF7F2] p-3 rounded-xl border border-[#EFE8DE]">
            <div><span class="text-[#7D6E64] block">Branch:</span> <span class="font-bold text-[#2B1810]">{{ activeReport.branch_name }}</span></div>
            <div><span class="text-[#7D6E64] block">Author:</span> <span class="font-bold text-[#2B1810]">{{ activeReport.author_name }}</span></div>
          </div>
          <div class="bg-white border border-[#EFE8DE] p-3 rounded-xl max-h-48 overflow-y-auto whitespace-pre-wrap">
            {{ activeReport.content }}
          </div>
        </div>
        <div class="flex justify-end pt-2 border-t border-[#EFE8DE]">
          <button @click="activeReport = null" class="dash-btn-primary py-1.5 px-4 text-xs">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

const authStore = useAuthStore();

const branches = ref([]);
const selectedBranch = ref('all');
const selectedDate = ref('');
const activeReport = ref(null);
const dateInputRef = ref(null);

const currentUserName = computed(() => authStore.user?.full_name || 'Admin');

const canAccessOrders = computed(() => {
  const role = authStore.user?.role;
  return ['Super Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'].includes(role);
});

const greetingText = computed(() => {
  const hr = new Date().getHours();
  if (hr < 12) return 'Good morning';
  if (hr < 17) return 'Good afternoon';
  return 'Good evening';
});

const displayDateLabel = computed(() => {
  if (selectedDate.value) {
    return new Date(selectedDate.value).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  }
  return new Date().toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
});

const triggerDatePicker = () => {
  if (dateInputRef.value) {
    dateInputRef.value.showPicker?.() || dateInputRef.value.click();
  }
};

const clearDate = () => {
  selectedDate.value = '';
  loadOverview();
};

const data = ref({
  is_branch_view: false,
  revenue_today: 0,
  orders_today: 0,
  pending_orders: 0,
  out_for_delivery: 16,
  delivered_today: 32,
  delivery_pipeline: {
    pending: 12,
    preparing: 20,
    out_for_delivery: 16,
    delivered: 32
  },
  best_selling_pastries: [],
  revenue_overview: {
    total_revenue: 8560,
    points: []
  },
  inventory_alerts: [],
  recent_orders: [],
  active_branches: 0,
  units_in_transit: 0,
  branches_today: [],
  submitted_reports: [],
  custom_cakes_summary: {
    submitted: 0,
    quoted: 0,
    confirmed: 0,
    ready: 0,
    delivered: 0,
    total_active: 0
  }
});

const fallbackPastries = [
  { rank: 1, name: 'Chocolate Cake Slice', sold_count: 86, revenue: 430, image_url: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=300&q=80' },
  { rank: 2, name: 'Butter Croissant', sold_count: 74, revenue: 296, image_url: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=300&q=80' },
  { rank: 3, name: 'Red Velvet Cupcake', sold_count: 65, revenue: 260, image_url: 'https://images.unsplash.com/photo-1614707267537-b85aaf00c4b7?w=300&q=80' },
  { rank: 4, name: 'Choco Chip Cookie', sold_count: 58, revenue: 174, image_url: 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=300&q=80' },
  { rank: 5, name: 'Blueberry Danish', sold_count: 47, revenue: 188, image_url: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=300&q=80' },
];

const bestSellingList = computed(() => {
  if (data.value.best_selling_pastries && data.value.best_selling_pastries.length > 0) {
    return data.value.best_selling_pastries;
  }
  return fallbackPastries;
});

const fallbackAlerts = [
  { name: 'All-Purpose Flour', status: 'Low stock', remaining: '5 kg left', icon: 'flour' },
  { name: 'Unsalted Butter', status: 'Low stock', remaining: '1.2 kg left', icon: 'butter' },
  { name: 'Fresh Milk', status: 'Running low', remaining: '2 L left', icon: 'milk' },
  { name: 'Dark Chocolate', status: 'Out of stock', remaining: '0 kg left', icon: 'chocolate' },
];

const inventoryAlertsList = computed(() => {
  if (data.value.inventory_alerts && data.value.inventory_alerts.length > 0) {
    return data.value.inventory_alerts;
  }
  return fallbackAlerts;
});

const fallbackRecentOrders = [
  { order_id: 1, order_number: '#SC24121', customer: 'Sara Mengistu', items_count: '3 items', total: 42.50, status: 'Out for delivery', time: '09:15 AM' },
  { order_id: 2, order_number: '#SC24120', customer: 'Dawit Bekele', items_count: '2 items', total: 28.00, status: 'Preparing', time: '08:49 AM' },
  { order_id: 3, order_number: '#SC24119', customer: 'Hellen Tadesse', items_count: '4 items', total: 56.75, status: 'Pending', time: '08:30 AM' },
  { order_id: 4, order_number: '#SC24118', customer: 'Yared Hailu', items_count: '1 item', total: 18.00, status: 'Completed', time: 'Yesterday' },
];

const recentOrdersList = computed(() => {
  if (data.value.recent_orders && data.value.recent_orders.length > 0) {
    return data.value.recent_orders;
  }
  return fallbackRecentOrders;
});

const loadOverview = async () => {
  try {
    const params = {};
    if (selectedBranch.value && selectedBranch.value !== 'all') {
      params.branch_id = selectedBranch.value;
    }
    if (selectedDate.value) {
      params.date = selectedDate.value;
    }

    const [ovRes, brRes] = await Promise.all([
      api.get('/admin/overview', { params }),
      api.get('/branches'),
    ]);
    data.value = ovRes.data;
    branches.value = brRes.data;
  } catch (err) {
    console.error('Failed to load overview', err);
  }
};

onMounted(() => {
  loadOverview();
});
</script>
