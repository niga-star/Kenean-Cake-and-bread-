<template>
  <div class="space-y-8 max-w-7xl mx-auto font-poppins pb-12">
    <!-- ======================================================== -->
    <!-- TOP BAKERY HERO BANNER (Customer Page Beauty Aesthetic)  -->
    <!-- ======================================================== -->
    <div class="relative overflow-hidden bg-gradient-to-br from-[#26150D] via-[#382015] to-[#1C0F09] text-white rounded-3xl p-6 sm:p-8 lg:p-9 shadow-xl border border-[#482B1E]">
      <!-- Ambient Bakery Glow Orbs -->
      <div class="absolute -right-12 -bottom-12 w-72 h-72 bg-[#DE1B54]/15 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute right-32 -top-12 w-56 h-56 bg-[#BA5420]/20 rounded-full blur-2xl pointer-events-none"></div>
      <div class="absolute left-1/3 bottom-0 w-48 h-48 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <span class="px-3.5 py-1 rounded-full text-xs sm:text-sm font-bold uppercase tracking-wider bg-white/10 text-rose-200 border border-white/15 backdrop-blur-sm flex items-center space-x-1.5 shadow-2xs">
              <span>{{ data.is_branch_view ? '📍' : '👑' }}</span>
              <span>{{ data.is_branch_view ? `${data.branch?.branch_name} Branch Portal` : 'Executive Central Cockpit' }}</span>
            </span>
            <span v-if="data.branch?.branch_code" class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#FAF0E6]/20 text-amber-200 border border-amber-300/30">
              {{ data.branch?.branch_code }}
            </span>
            <span class="text-xs sm:text-sm text-amber-200/90 font-medium">
              &bull; {{ currentDateDisplay }}
            </span>
          </div>

          <h1 class="text-2xl sm:text-3xl md:text-4xl font-serif font-black tracking-tight text-[#FAF4ED] leading-tight">
            {{ greetingText }}, <span class="text-[#FBBF24]">{{ currentUserName }}</span>! 👋
          </h1>

          <p class="text-sm sm:text-base text-[#D8CDC5] max-w-3xl font-normal leading-relaxed">
            {{ data.is_branch_view 
              ? `Real-time operational dashboard for ${data.branch?.branch_name}. Manage today's order preparation, custom cake fulfillment, and branch stock levels.` 
              : 'Business-wide executive oversight. Monitor consolidated revenue, verify branch manager reports, track custom cakes, and inspect regional performance across all branches.' }}
          </p>
        </div>

        <!-- Quick Hero Actions -->
        <div class="flex flex-wrap items-center gap-3 shrink-0">
          <template v-if="data.is_branch_view">
            <router-link
              to="/admin/orders"
              class="px-4 py-2.5 sm:px-5 sm:py-3 text-sm font-bold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-xl transition shadow-md flex items-center space-x-2 cursor-pointer"
            >
              <span>🛍️</span>
              <span>Branch Orders ({{ data.orders_today || 0 }})</span>
            </router-link>
            <router-link
              to="/admin/custom-cakes"
              class="px-4 py-2.5 sm:px-5 sm:py-3 text-sm font-bold bg-white/10 hover:bg-white/20 text-[#FAF4ED] border border-white/20 rounded-xl transition backdrop-blur-sm flex items-center space-x-2 cursor-pointer"
            >
              <span>🎂</span>
              <span>Custom Cakes</span>
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/admin/staff-reports"
              class="px-4 py-2.5 sm:px-5 sm:py-3 text-sm font-bold bg-[#DE1B54] hover:bg-[#C21647] text-white rounded-xl transition shadow-md flex items-center space-x-2 cursor-pointer"
            >
              <span>📋</span>
              <span>Reports Desk</span>
            </router-link>
            <router-link
              to="/admin/branches"
              class="px-4 py-2.5 sm:px-5 sm:py-3 text-sm font-bold bg-white/10 hover:bg-white/20 text-[#FAF4ED] border border-white/20 rounded-xl transition backdrop-blur-sm flex items-center space-x-2 cursor-pointer"
            >
              <span>🏢</span>
              <span>Manage Branches</span>
            </router-link>
          </template>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 1. BRANCH DASHBOARD VIEW (Branch Manager & Branch Staff) -->
    <!-- ======================================================== -->
    <div v-if="data.is_branch_view" class="space-y-8">
      <!-- 4 High-Impact Branch Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Branch Revenue Today</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg border border-emerald-200">
              💰
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-serif font-black text-[#241710]">
              ETB {{ Number(data.revenue_today || 0).toLocaleString() }}
            </div>
            <div class="text-xs sm:text-sm text-[#2E7D32] font-semibold mt-1 flex items-center space-x-1">
              <span>↗</span>
              <span>From confirmed &amp; delivered orders</span>
            </div>
          </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Orders Today</span>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-[#DE1B54] flex items-center justify-center text-lg border border-rose-200">
              🛍️
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-serif font-black text-[#241710]">
              {{ data.orders_today || 0 }}
            </div>
            <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">
              {{ data.pending_orders || 0 }} pending &middot; {{ data.preparing_orders || 0 }} prep &middot; {{ data.ready_orders || 0 }} ready
            </div>
          </div>
        </div>

        <!-- Available Stock Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Available Stock</span>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg border border-amber-200">
              📦
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-serif font-black text-[#241710]">
              {{ data.stock_summary?.available || 0 }}
            </div>
            <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">
              {{ data.stock_summary?.units_on_hand || 0 }} on hand &middot; {{ data.stock_summary?.reserved || 0 }} reserved
            </div>
          </div>
        </div>

        <!-- Incoming Stock Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Incoming Shipments</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg border border-blue-200">
              🚚
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-serif font-black text-[#241710]">
              {{ data.incoming_units || 0 }}
            </div>
            <div class="text-xs sm:text-sm text-[#BA5420] font-bold mt-1">
              {{ data.incoming_transfers || 0 }} shipment(s) in transit
            </div>
          </div>
        </div>
      </div>

      <!-- Main Two Column Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left: Recent Orders & Stock Table (Col span 2) -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Recent Orders Table -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs overflow-hidden">
            <div class="p-6 border-b border-[#EFE8DF] flex items-center justify-between">
              <div>
                <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Recent Branch Orders</h3>
                <p class="text-xs sm:text-sm text-[#7A6B60] mt-0.5">Real-time orders placed at {{ data.branch?.branch_name }}.</p>
              </div>
              <router-link to="/admin/orders" class="text-sm font-bold text-[#BA5420] hover:underline flex items-center space-x-1">
                <span>View all orders</span>
                <span>→</span>
              </router-link>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full min-w-[580px] text-left">
                <thead class="bg-[#FAF7F5] text-xs font-bold text-[#7A6B60] uppercase tracking-wider border-b border-[#EFE8DF]">
                  <tr>
                    <th class="py-3.5 px-5">Order #</th>
                    <th class="py-3.5 px-4">Customer</th>
                    <th class="py-3.5 px-4">Method</th>
                    <th class="py-3.5 px-4">Total</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-5 text-right">Time</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EDE4] text-sm">
                  <tr v-if="!data.recent_orders || data.recent_orders.length === 0">
                    <td colspan="6" class="py-8 text-center text-sm text-[#7A6B60]">No orders placed at this branch yet today.</td>
                  </tr>
                  <tr v-for="ord in data.recent_orders" :key="ord.order_id" class="hover:bg-[#FCFAF7] transition">
                    <td class="py-4 px-5 font-mono font-bold text-[#382C24]">{{ ord.order_number }}</td>
                    <td class="py-4 px-4 font-semibold text-[#2A1D15]">{{ ord.customer }}</td>
                    <td class="py-4 px-4 text-[#6A5A50] font-medium">{{ ord.method }}</td>
                    <td class="py-4 px-4 font-bold text-[#241710]">ETB {{ Number(ord.total).toLocaleString() }}</td>
                    <td class="py-4 px-4 text-center">
                      <span
                        class="inline-block px-3 py-1 text-xs font-bold rounded-full"
                        :class="{
                          'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': ord.status === 'Pending',
                          'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': ord.status === 'Confirmed' || ord.status === 'Completed',
                          'bg-[#EBF3FB] text-[#1565C0] border border-[#BBDEFB]': ord.status === 'Preparing' || ord.status === 'Ready' || ord.status === 'Out for delivery',
                          'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2]': ord.status === 'Cancelled'
                        }"
                      >
                        {{ ord.status }}
                      </span>
                    </td>
                    <td class="py-4 px-5 text-right text-xs text-[#7A6B60] font-medium">{{ ord.placed }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock Availability Table -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs overflow-hidden">
            <div class="p-6 border-b border-[#EFE8DF] flex items-center justify-between">
              <div>
                <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Branch Stock Status</h3>
                <p class="text-xs sm:text-sm text-[#7A6B60] mt-0.5">Physical units on hand vs reserved in active customer carts.</p>
              </div>
              <router-link to="/admin/inventory" class="text-sm font-bold text-[#BA5420] hover:underline flex items-center space-x-1">
                <span>Manage inventory</span>
                <span>→</span>
              </router-link>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full min-w-[580px] text-left">
                <thead class="bg-[#FAF7F5] text-xs font-bold text-[#7A6B60] uppercase tracking-wider border-b border-[#EFE8DF]">
                  <tr>
                    <th class="py-3.5 px-5">Product</th>
                    <th class="py-3.5 px-4 text-center">On Hand</th>
                    <th class="py-3.5 px-4 text-center">Reserved</th>
                    <th class="py-3.5 px-4 text-center font-bold">Available</th>
                    <th class="py-3.5 px-4 text-center">Reorder At</th>
                    <th class="py-3.5 px-5 text-right">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EDE4] text-sm">
                  <tr v-for="it in (data.stock_items || []).slice(0, 6)" :key="it.product_id" class="hover:bg-[#FCFAF7] transition">
                    <td class="py-4 px-5 font-bold text-[#2A1810]">{{ it.product_name }}</td>
                    <td class="py-4 px-4 text-center text-[#55463C] font-semibold">{{ it.on_hand }}</td>
                    <td class="py-4 px-4 text-center text-[#D97706] font-semibold">{{ it.reserved }}</td>
                    <td class="py-4 px-4 text-center font-black text-[#241710]">{{ it.available }}</td>
                    <td class="py-4 px-4 text-center text-[#7A6B60]">{{ it.reorder_level }}</td>
                    <td class="py-4 px-5 text-right">
                      <span
                        class="inline-block px-3 py-1 text-xs font-bold rounded-full"
                        :class="{
                          'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': it.status === 'Healthy',
                          'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': it.status === 'Low',
                          'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2]': it.status === 'Sold out'
                        }"
                      >
                        {{ it.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Needs Attention Feed & Quick Actions (Col span 1) -->
        <div class="space-y-6">
          <!-- Needs Attention Alerts -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs p-6 space-y-4">
            <div class="flex items-center space-x-2">
              <span class="text-xl">⚠️</span>
              <h3 class="font-serif font-bold text-lg text-[#2A1D15]">Needs Attention</h3>
            </div>

            <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-sm text-[#7A6B60] py-6 text-center font-medium bg-[#FAF7F5] rounded-2xl border border-dashed border-[#E0D5C7]">
              ✨ All systems operational. No urgent branch alerts.
            </div>

            <div class="space-y-3">
              <div
                v-for="(item, idx) in data.needs_attention"
                :key="idx"
                class="p-4 rounded-2xl border text-sm font-semibold leading-relaxed shadow-2xs"
                :class="{
                  'bg-[#FDEAEA] text-[#C62828] border-[#FFCDD2]': item.type === 'disputed',
                  'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]': item.type === 'in_transit' || item.type === 'low_stock',
                  'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': item.type === 'request'
                }"
              >
                {{ item.text }}
              </div>
            </div>
          </div>

          <!-- Branch Quick Operations Links -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs p-6 space-y-4">
            <h3 class="font-serif font-bold text-lg text-[#2A1D15]">Branch Operations</h3>
            <div class="space-y-2.5 text-sm">
              <router-link
                to="/admin/orders"
                class="flex items-center justify-between p-3.5 bg-[#FAF7F5] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-2xl font-bold text-[#382C24] transition shadow-2xs group"
              >
                <span>🛍️ Manage Branch Orders</span>
                <span class="text-[#BA5420] group-hover:translate-x-1 transition-transform">→</span>
              </router-link>

              <router-link
                to="/admin/custom-cakes"
                class="flex items-center justify-between p-3.5 bg-[#FAF7F5] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-2xl font-bold text-[#382C24] transition shadow-2xs group"
              >
                <span>🎂 Review Custom Cake Requests</span>
                <span class="text-[#BA5420] group-hover:translate-x-1 transition-transform">→</span>
              </router-link>

              <router-link
                to="/admin/products-prices"
                class="flex items-center justify-between p-3.5 bg-[#FAF7F5] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-2xl font-bold text-[#382C24] transition shadow-2xs group"
              >
                <span>🏷️ Set Branch Product Prices</span>
                <span class="text-[#BA5420] group-hover:translate-x-1 transition-transform">→</span>
              </router-link>

              <router-link
                to="/admin/distribution"
                class="flex items-center justify-between p-3.5 bg-[#FAF7F5] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-2xl font-bold text-[#382C24] transition shadow-2xs group"
              >
                <span>📥 Incoming Stock Transfers</span>
                <span class="text-[#BA5420] group-hover:translate-x-1 transition-transform">→</span>
              </router-link>

              <router-link
                to="/admin/staff-reports"
                class="flex items-center justify-between p-3.5 bg-[#FAF7F5] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-2xl font-bold text-[#382C24] transition shadow-2xs group"
              >
                <span>📋 Submit / Review Staff Reports</span>
                <span class="text-[#BA5420] group-hover:translate-x-1 transition-transform">→</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. CENTRAL DASHBOARD VIEW (Super Admin Executive View)   -->
    <!-- ======================================================== -->
    <div v-else class="space-y-8">
      <!-- Executive Filter Controls Card -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E9E0D4] shadow-xs">
        <div>
          <div class="flex items-center gap-2.5">
            <h2 class="text-xl sm:text-2xl font-serif font-black text-[#2A1D15]">Executive Intelligence Center</h2>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1]">
              Central Business View
            </span>
          </div>
          <p class="text-xs sm:text-sm text-[#7A6B60] mt-1">
            Consolidated reporting across all branches, manager submissions, custom cakes pipeline, and price modifications.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Branch Selector -->
          <div class="flex items-center space-x-2">
            <label class="text-xs sm:text-sm font-bold text-[#65554B]">Branch:</label>
            <select
              v-model="selectedBranch"
              @change="loadOverview"
              class="bg-[#FAF7F5] border border-[#D8CDC0] rounded-xl px-3.5 py-2 text-xs sm:text-sm font-bold text-[#2A1810] shadow-2xs focus:ring-2 focus:ring-[#BA5420]"
            >
              <option value="all">Whole Business (All Branches)</option>
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }} ({{ b.branch_code }})
              </option>
            </select>
          </div>

          <!-- Date Selector -->
          <div class="flex items-center space-x-2">
            <label class="text-xs sm:text-sm font-bold text-[#65554B]">Date:</label>
            <input
              type="date"
              v-model="selectedDate"
              @change="loadOverview"
              class="bg-[#FAF7F5] border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-[#2A1810] shadow-2xs focus:ring-2 focus:ring-[#BA5420]"
            />
            <button
              v-if="selectedDate"
              @click="clearDate"
              title="Reset to today"
              class="text-xs sm:text-sm font-bold text-[#8A7B71] hover:text-[#BA5420] p-1.5 cursor-pointer"
            >
              ✕
            </button>
          </div>

          <router-link
            to="/admin/staff-reports"
            class="px-4 py-2 text-xs sm:text-sm font-bold bg-[#FAF7F5] border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-xl transition shadow-2xs"
          >
            📋 Reports Desk
          </router-link>
        </div>
      </div>

      <!-- 5 High-Level Executive Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Revenue Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-2">
          <div class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Revenue {{ selectedDate ? 'on ' + selectedDate : 'Today' }}</div>
          <div class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#241710]">
            ETB {{ Number(data.revenue_today || 0).toLocaleString() }}
          </div>
          <div class="text-xs sm:text-sm text-[#2E7D32] font-semibold mt-1">Across all active branches</div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-2">
          <div class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Orders Today</div>
          <div class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#241710]">
            {{ data.orders_today || 0 }}
          </div>
          <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">{{ data.pending_orders || 0 }} pending fulfillment</div>
        </div>

        <!-- Active Branches -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-2">
          <div class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Active Branches</div>
          <div class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#241710]">
            {{ data.active_branches || 0 }}
          </div>
          <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">Main + regional bakeries</div>
        </div>

        <!-- Custom Cakes -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-2">
          <div class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Active Custom Cakes</div>
          <div class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#BA5420]">
            {{ data.custom_cakes_summary?.total_active || 0 }}
          </div>
          <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">{{ data.custom_cakes_summary?.submitted || 0 }} awaiting quote</div>
        </div>

        <!-- Units in Transit -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-2">
          <div class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#7D6E64]">Units In Transit</div>
          <div class="text-2xl sm:text-3xl lg:text-4xl font-serif font-black text-[#241710]">
            {{ data.units_in_transit || 0 }}
          </div>
          <div class="text-xs sm:text-sm text-[#7A6B60] font-medium mt-1">Inter-branch shipments</div>
        </div>
      </div>

      <!-- Custom Cake Orders Pipeline -->
      <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#EFE8DF] pb-4">
          <div class="flex items-center space-x-2.5">
            <span class="text-2xl">🎂</span>
            <div>
              <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Custom Cake Orders Pipeline</h3>
              <p class="text-xs sm:text-sm text-[#7A6B60] font-normal">Managed directly by Branch Managers and Branch Sellers.</p>
            </div>
          </div>
          <span class="text-sm font-bold text-[#BA5420] bg-[#FAF0E6] px-3.5 py-1.5 rounded-xl border border-[#F0D5C1] self-start sm:self-auto">
            Total In System: {{ (data.custom_cakes_summary?.total_active || 0) + (data.custom_cakes_summary?.delivered || 0) }}
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 pt-1 text-center">
          <div class="p-4 bg-[#FFF9E6] border border-[#FEE39E] rounded-2xl shadow-2xs">
            <div class="text-xs sm:text-sm font-bold text-[#A67503]">1. Submitted</div>
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#241710] my-1">{{ data.custom_cakes_summary?.submitted || 0 }}</div>
            <div class="text-xs text-[#A67503] font-medium">Awaiting manager quote</div>
          </div>
          <div class="p-4 bg-[#EBF3FB] border border-[#BBDEFB] rounded-2xl shadow-2xs">
            <div class="text-xs sm:text-sm font-bold text-[#1565C0]">2. Quoted</div>
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#241710] my-1">{{ data.custom_cakes_summary?.quoted || 0 }}</div>
            <div class="text-xs text-[#1565C0] font-medium">Awaiting payment</div>
          </div>
          <div class="p-4 bg-[#F0FDF4] border border-[#BBF7D0] rounded-2xl shadow-2xs">
            <div class="text-xs sm:text-sm font-bold text-[#166534]">3. Confirmed</div>
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#241710] my-1">{{ data.custom_cakes_summary?.confirmed || 0 }}</div>
            <div class="text-xs text-[#166534] font-medium">Scheduled for baking</div>
          </div>
          <div class="p-4 bg-[#FAF5FF] border border-[#E9D5FF] rounded-2xl shadow-2xs">
            <div class="text-xs sm:text-sm font-bold text-[#6B21A8]">4. Ready</div>
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#241710] my-1">{{ data.custom_cakes_summary?.ready || 0 }}</div>
            <div class="text-xs text-[#6B21A8] font-medium">Ready at branch</div>
          </div>
          <div class="p-4 bg-[#F3F4F6] border border-[#E5E7EB] rounded-2xl shadow-2xs">
            <div class="text-xs sm:text-sm font-bold text-[#4B5563]">5. Delivered</div>
            <div class="text-2xl sm:text-3xl font-serif font-black text-[#241710] my-1">{{ data.custom_cakes_summary?.delivered || 0 }}</div>
            <div class="text-xs text-[#4B5563] font-medium">Fulfilled</div>
          </div>
        </div>
      </div>

      <!-- Branch Performance Comparison Table -->
      <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs overflow-hidden">
        <div class="p-6 border-b border-[#EFE8DF] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Branch Performance Comparison</h3>
            <p class="text-xs sm:text-sm text-[#7A6B60] mt-0.5">Overview of managers, staff size, orders, revenue and online shopping availability.</p>
          </div>
          <router-link
            to="/admin/branches"
            class="text-sm font-bold text-[#BA5420] hover:text-[#9E4314] flex items-center space-x-1"
          >
            <span>Manage branches &amp; managers</span>
            <span>→</span>
          </router-link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[760px] text-left">
            <thead class="bg-[#FAF7F5] text-xs font-bold text-[#7A6B60] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-4 px-5">Branch</th>
                <th class="py-4 px-4">Manager</th>
                <th class="py-4 px-4 text-center">Staff</th>
                <th class="py-4 px-4 text-center">Orders</th>
                <th class="py-4 px-5 text-center">Revenue</th>
                <th class="py-4 px-4 text-center">Active Cakes</th>
                <th class="py-4 px-4 text-center">Stock Health</th>
                <th class="py-4 px-4 text-center">Storefront Access</th>
                <th class="py-4 px-5 text-right">Transfers</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4] text-sm">
              <tr
                v-for="bt in data.branches_today"
                :key="bt.branch_id"
                class="hover:bg-[#FCFAF7] transition"
                :class="{ 'bg-[#FFF9F5]': selectedBranch === bt.branch_id }"
              >
                <td class="py-4 px-5 font-bold text-[#2A1810]">
                  {{ bt.branch_name }}
                  <span class="block text-xs font-normal text-[#8A7B71] font-mono mt-0.5">{{ bt.branch_code }}</span>
                </td>
                <td class="py-4 px-4 text-[#382C24]">
                  <span class="font-semibold text-sm">{{ bt.manager_name }}</span>
                </td>
                <td class="py-4 px-4 text-center text-[#55463C] font-bold text-sm">
                  {{ bt.staff_count }}
                </td>
                <td class="py-4 px-4 text-center text-[#2A1810] font-bold text-sm">{{ bt.orders }}</td>
                <td class="py-4 px-5 text-center font-bold text-base text-[#241710]">ETB {{ Number(bt.revenue).toLocaleString() }}</td>
                <td class="py-4 px-4 text-center">
                  <span
                    class="px-3 py-1 rounded-full font-bold text-xs"
                    :class="bt.active_cakes > 0 ? 'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]' : 'text-[#8A7B71]'"
                  >
                    {{ bt.active_cakes }}
                  </span>
                </td>
                <td class="py-4 px-4 text-center">
                  <span
                    class="inline-block px-3 py-1 font-bold rounded-full border text-xs"
                    :class="{
                      'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]': bt.stock_health === 'Healthy',
                      'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]': bt.stock_health.includes('low') || bt.stock_health.includes('sold')
                    }"
                  >
                    {{ bt.stock_health }}
                  </span>
                </td>
                <td class="py-4 px-4 text-center">
                  <span
                    class="inline-block px-3 py-1 text-xs font-bold rounded-full border"
                    :class="bt.is_shopping_enabled !== false ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FAF0F0] text-[#B84B42] border-[#E8C4C4]'"
                  >
                    {{ bt.is_shopping_enabled !== false ? '✓ Enabled' : '✕ Disabled' }}
                  </span>
                </td>
                <td class="py-4 px-5 text-right font-semibold text-[#65554B]">
                  {{ bt.open_transfers }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Two Column: Branch Manager Submitted Reports + Recent Price Adjustments -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- Reports Submitted by Branch Managers -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs overflow-hidden">
          <div class="p-6 border-b border-[#EFE8DF] flex items-center justify-between">
            <div>
              <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Branch Manager Reports</h3>
              <p class="text-xs sm:text-sm text-[#7A6B60] mt-0.5">Submitted by branch leadership for executive review.</p>
            </div>
            <router-link
              to="/admin/staff-reports"
              class="text-sm font-bold text-[#BA5420] hover:underline"
            >
              All Reports →
            </router-link>
          </div>

          <div v-if="!data.submitted_reports || data.submitted_reports.length === 0" class="p-10 text-center text-sm text-[#8A7B71]">
            No reports submitted for the selected filter.
          </div>

          <div v-else class="divide-y divide-[#F3EDE4] max-h-[400px] overflow-y-auto">
            <div
              v-for="rep in data.submitted_reports"
              :key="rep.report_id"
              @click="openReportModal(rep)"
              class="p-5 hover:bg-[#FCFAF7] transition cursor-pointer flex items-start justify-between gap-4"
            >
              <div class="space-y-1.5 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-sm sm:text-base font-bold text-[#2A1D15] truncate">{{ rep.title }}</span>
                  <span
                    class="px-2.5 py-0.5 text-xs font-bold rounded-full border"
                    :class="{
                      'bg-[#FAF0E6] text-[#BA5420] border-[#F0D5C1]': rep.report_type === 'Price Update',
                      'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]': rep.report_type === 'Daily Sales',
                      'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': rep.report_type === 'Order Activity' || rep.report_type === 'Custom Cake Report',
                      'bg-[#F3F4F6] text-[#4B5563] border-[#E5E7EB]': !['Price Update', 'Daily Sales', 'Order Activity', 'Custom Cake Report'].includes(rep.report_type)
                    }"
                  >
                    {{ rep.report_type }}
                  </span>
                </div>
                <p class="text-xs sm:text-sm text-[#6A5A50] line-clamp-2 leading-relaxed">
                  {{ rep.content }}
                </p>
                <div class="text-xs text-[#7A6B60] flex items-center gap-3 pt-1">
                  <span class="font-bold text-[#4A3B32]">🏢 {{ rep.branch_name }}</span>
                  <span>👤 {{ rep.author_name }}</span>
                  <span>🕒 {{ rep.date }}</span>
                </div>
              </div>

              <span
                class="px-3 py-1 text-xs font-bold rounded-full border shrink-0"
                :class="{
                  'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]': rep.status === 'Submitted',
                  'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]': rep.status === 'Reviewed' || rep.status === 'Resolved',
                  'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': rep.status === 'Forwarded to Super Admin'
                }"
              >
                {{ rep.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Recent Product & Price Adjustments Audit -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs overflow-hidden">
          <div class="p-6 border-b border-[#EFE8DF] flex items-center justify-between">
            <div>
              <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Branch Price Updates</h3>
              <p class="text-xs sm:text-sm text-[#7A6B60] mt-0.5">Recorded price modifications by Branch Managers.</p>
            </div>
            <router-link
              to="/admin/audit-logs"
              class="text-sm font-bold text-[#BA5420] hover:underline"
            >
              Full Audit Trail →
            </router-link>
          </div>

          <div v-if="!data.recent_price_changes || data.recent_price_changes.length === 0" class="p-10 text-center text-sm text-[#8A7B71]">
            No price change records found for this period.
          </div>

          <div v-else class="divide-y divide-[#F3EDE4] max-h-[400px] overflow-y-auto">
            <div
              v-for="pc in data.recent_price_changes"
              :key="pc.log_id"
              class="p-5 hover:bg-[#FCFAF7] transition flex items-start justify-between gap-4 text-sm"
            >
              <div class="space-y-1.5">
                <div class="font-bold text-base text-[#2A1D15]">{{ pc.record }}</div>
                <div class="flex items-center gap-2 text-xs sm:text-sm">
                  <span class="text-[#8A7B71] line-through">ETB {{ pc.old_value }}</span>
                  <span class="text-[#BA5420] font-bold">→</span>
                  <span class="font-bold text-[#2E7D32]">ETB {{ pc.new_value }}</span>
                </div>
                <div class="text-xs text-[#7A6B60] flex items-center gap-2 pt-0.5">
                  <span>👤 {{ pc.author }}</span>
                  <span>&bull;</span>
                  <span>🕒 {{ pc.time }}</span>
                </div>
              </div>

              <span class="px-3 py-1 text-xs font-bold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1] rounded-xl shrink-0">
                Audit Verified
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Sales Chart & System Attention Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- Stacked Bar Chart Card -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs p-6 sm:p-7 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#EFE8DF] pb-4">
            <div>
              <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Sales by Branch, This Week</h3>
              <p class="text-xs sm:text-sm text-[#7A6B60]">Real volume comparisons from live sales database.</p>
            </div>
            <div class="flex items-center space-x-4 text-xs sm:text-sm font-semibold">
              <span class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-[#BA5420]"></span>
                <span class="text-[#65554B]">Cakes</span>
              </span>
              <span class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-[#E4A373]"></span>
                <span class="text-[#65554B]">Bread &amp; Pastry</span>
              </span>
            </div>
          </div>

          <div class="overflow-x-auto pb-2">
            <div class="h-64 min-w-[320px] flex items-end justify-between px-3 sm:px-6 pt-6 border-b border-[#EFE8DF]">
              <div
                v-for="sb in data.sales_by_branch"
                :key="sb.branch"
                class="flex flex-col items-center space-y-2 w-14 sm:w-18 shrink-0"
              >
                <div class="w-10 sm:w-14 rounded-t-lg overflow-hidden flex flex-col justify-end bg-transparent h-48">
                  <!-- Cakes -->
                  <div
                    class="w-full bg-[#BA5420] transition-all duration-500 rounded-t-md"
                    :style="{ height: `${Math.min(100, Math.round(((sb.cakes || 1) / 22000) * 100))}%` }"
                    :title="`Cakes: ETB ${sb.cakes}`"
                  ></div>
                  <!-- Bread -->
                  <div
                    class="w-full bg-[#E4A373] transition-all duration-500"
                    :style="{ height: `${Math.min(100, Math.round(((sb.bread || 1) / 22000) * 100))}%` }"
                    :title="`Bread: ETB ${sb.bread}`"
                  ></div>
                </div>
                <span class="text-xs sm:text-sm font-bold text-[#5A4A3E] text-center truncate max-w-[70px]">{{ sb.branch }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Needs Attention Feed -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-xs p-6 sm:p-7 space-y-4">
          <div class="flex items-center space-x-2 border-b border-[#EFE8DF] pb-4">
            <span class="text-2xl">⚠️</span>
            <div>
              <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1D15]">Operations Watchlist</h3>
              <p class="text-xs sm:text-sm text-[#7A6B60]">Real-time system health checks and pending transfer alerts.</p>
            </div>
          </div>

          <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-sm text-[#7A6B60] py-8 text-center font-medium bg-[#FAF7F5] rounded-2xl border border-dashed border-[#E0D5C7]">
            ✨ All branches healthy. No urgent alerts.
          </div>

          <div class="space-y-3">
            <div
              v-for="(item, idx) in data.needs_attention"
              :key="idx"
              class="p-4 rounded-2xl border text-sm font-semibold leading-relaxed shadow-2xs"
              :class="{
                'bg-[#FDEAEA] text-[#C62828] border-[#FFCDD2]': item.type === 'disputed',
                'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]': item.type === 'in_transit' || item.type === 'low_stock',
                'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': item.type === 'request'
              }"
            >
              {{ item.text }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- REPORT DETAIL MODAL                           -->
    <!-- ============================================== -->
    <div
      v-if="activeReport"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
      @click.self="activeReport = null"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 space-y-5 shadow-2xl border border-[#E9E0D4] animate-in zoom-in-95">
        <div class="flex items-start justify-between border-b border-[#EFE8DF] pb-4">
          <div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1]">
              {{ activeReport.report_type }}
            </span>
            <h3 class="font-serif font-black text-xl text-[#2A1D15] mt-2.5">{{ activeReport.title }}</h3>
          </div>
          <button @click="activeReport = null" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold cursor-pointer">✕</button>
        </div>

        <div class="space-y-4 text-sm text-[#4A3B32]">
          <div class="grid grid-cols-2 gap-3 bg-[#FAF7F5] p-4 rounded-2xl border border-[#E9E0D4]">
            <div><span class="text-xs text-[#8A7B71] block">Branch:</span> <span class="font-bold text-[#2A1D15]">{{ activeReport.branch_name }}</span></div>
            <div><span class="text-xs text-[#8A7B71] block">Author:</span> <span class="font-bold text-[#2A1D15]">{{ activeReport.author_name }} ({{ activeReport.author_role }})</span></div>
            <div><span class="text-xs text-[#8A7B71] block">Submitted Date:</span> <span class="font-bold text-[#2A1D15]">{{ activeReport.date }}</span></div>
            <div><span class="text-xs text-[#8A7B71] block">Review Status:</span> <span class="font-bold text-[#2E7D32]">{{ activeReport.status }}</span></div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#8A7B71] mb-1.5">Report Description &amp; Findings</label>
            <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 leading-relaxed whitespace-pre-wrap max-h-60 overflow-y-auto text-sm text-[#2A1D15]">
              {{ activeReport.content }}
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-3 border-t border-[#EFE8DF]">
          <button
            @click="activeReport = null"
            class="px-5 py-2.5 text-sm font-bold bg-[#2A1D15] text-white rounded-xl hover:bg-[#3D2C20] transition cursor-pointer shadow-xs"
          >
            Close Report
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

const currentUserName = computed(() => authStore.user?.full_name || 'Bakery Leader');

const greetingText = computed(() => {
  const hr = new Date().getHours();
  if (hr < 12) return 'Good morning';
  if (hr < 17) return 'Good afternoon';
  return 'Good evening';
});

const currentDateDisplay = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
});

const data = ref({
  is_branch_view: false,
  revenue_today: 0,
  orders_today: 0,
  pending_orders: 0,
  active_branches: 0,
  units_in_transit: 0,
  sales_by_branch: [],
  needs_attention: [],
  branches_today: [],
  submitted_reports: [],
  custom_cakes_summary: {
    submitted: 0,
    quoted: 0,
    confirmed: 0,
    ready: 0,
    delivered: 0,
    total_active: 0
  },
  recent_price_changes: []
});

const openReportModal = (rep) => {
  activeReport.value = rep;
};

const clearDate = () => {
  selectedDate.value = '';
  loadOverview();
};

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
