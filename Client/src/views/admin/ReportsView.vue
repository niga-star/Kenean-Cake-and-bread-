<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-12 font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-[#EFE8DE] pb-4">
      <div>
        <div class="flex items-center space-x-2">
          <span class="text-2xl">📊</span>
          <h1 class="dash-title text-xl sm:text-2xl md:text-3xl">
            Reports &amp; Analytics
          </h1>
        </div>
        <p class="dash-subtitle mt-1">
          Multi-branch performance metrics, distribution tracking, inventory levels, and financial reconciliation.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
        <button
          @click="sendDailySummary"
          :disabled="sendingSummary"
          class="dash-btn-secondary text-xs sm:text-sm"
          title="Send daily sales and low-stock email summary to owner"
        >
          <span>✉️</span>
          <span>{{ sendingSummary ? 'Dispatching...' : 'Email Summary' }}</span>
        </button>

        <button
          @click="exportCSV"
          class="dash-btn-secondary text-xs sm:text-sm"
        >
          <span>📥</span>
          <span>Export CSV</span>
        </button>

        <button
          @click="exportPDF"
          class="dash-btn-primary text-xs sm:text-sm"
        >
          <span>🖨️</span>
          <span>Print / PDF</span>
        </button>
      </div>
    </div>

    <!-- Scoped Branch Notice if not Super Admin -->
    <div
      v-if="isBranchScoped"
      class="p-3.5 bg-[#FFFBEB] border border-[#FDE68A] rounded-2xl flex items-center justify-between text-xs text-[#92400E] shadow-2xs"
    >
      <div class="flex items-center space-x-2.5">
        <span class="text-base">🔒</span>
        <span><strong>Strict Branch Privacy:</strong> You are viewing reports exclusively for <strong>{{ userBranchName }} Branch</strong>. Cross-branch comparisons are restricted to Super Admin.</span>
      </div>
      <span class="text-[11px] font-bold px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full border border-amber-300">Branch Scoped</span>
    </div>

    <!-- 4 High-Level Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-1">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Total Revenue</div>
        <div class="text-3xl font-serif font-black text-[#241710]">
          ETB {{ Number(metrics.revenue || 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#059669] font-medium flex items-center space-x-1 pt-1">
          <span>↗</span>
          <span>{{ metrics.revenue_trend || '+12% vs previous period' }}</span>
        </div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-1">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Total Orders</div>
        <div class="text-3xl font-serif font-black text-[#241710]">
          {{ metrics.orders || 0 }}
        </div>
        <div class="text-xs text-[#7A6B60] font-medium pt-1">
          {{ metrics.orders_sub || 'Across branches' }}
        </div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-1">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Average Order Value</div>
        <div class="text-3xl font-serif font-black text-[#241710]">
          ETB {{ Number(metrics.avg_order || 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#059669] font-medium flex items-center space-x-1 pt-1">
          <span>↗</span>
          <span>{{ metrics.avg_order_trend || '+3%' }}</span>
        </div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-1">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Units Sold</div>
        <div class="text-3xl font-serif font-black text-[#241710]">
          {{ metrics.units_sold || 0 }}
        </div>
        <div class="text-xs text-[#7A6B60] font-medium pt-1">
          {{ metrics.units_sub || 'Cakes & bread' }}
        </div>
      </div>
    </div>

    <!-- Quick Date Filters & Detailed Parameters -->
    <div class="bg-white p-4 border border-[#E9E0D4] rounded-2xl shadow-2xs space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F2EAE0] pb-3">
        <div class="flex items-center space-x-2">
          <span class="text-xs font-bold uppercase tracking-wider text-[#7D6E64]">Date Filter:</span>
          <div class="flex flex-wrap gap-1.5">
            <button
              type="button"
              @click="setDateFilter('today')"
              class="px-3 py-1 text-xs font-bold rounded-lg border transition cursor-pointer"
              :class="quickDateFilter === 'today' ? 'bg-[#BA5420] text-white border-[#BA5420]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#5A4B41] hover:bg-[#F4ECE3]'"
            >
              Today
            </button>
            <button
              type="button"
              @click="setDateFilter('this_week')"
              class="px-3 py-1 text-xs font-bold rounded-lg border transition cursor-pointer"
              :class="quickDateFilter === 'this_week' ? 'bg-[#BA5420] text-white border-[#BA5420]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#5A4B41] hover:bg-[#F4ECE3]'"
            >
              This Week
            </button>
            <button
              type="button"
              @click="setDateFilter('this_month')"
              class="px-3 py-1 text-xs font-bold rounded-lg border transition cursor-pointer"
              :class="quickDateFilter === 'this_month' ? 'bg-[#BA5420] text-white border-[#BA5420]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#5A4B41] hover:bg-[#F4ECE3]'"
            >
              This Month
            </button>
            <button
              type="button"
              @click="setDateFilter('custom')"
              class="px-3 py-1 text-xs font-bold rounded-lg border transition cursor-pointer"
              :class="quickDateFilter === 'custom' ? 'bg-[#BA5420] text-white border-[#BA5420]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#5A4B41] hover:bg-[#F4ECE3]'"
            >
              Custom Date Range
            </button>
          </div>
        </div>

        <div v-if="isBranchScoped" class="text-xs font-bold text-[#BA5420] flex items-center space-x-1.5">
          <span>📍</span>
          <span>Assigned: {{ userBranchName }} Branch</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pt-1">
        <!-- Branch Filter -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Branch</label>
          <div v-if="isBranchScoped" class="p-2 bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl text-xs font-bold text-[#2D231D]">
            {{ userBranchName }} Branch
          </div>
          <select
            v-else
            v-model="filters.branch_id"
            @change="reloadAll"
            class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] font-medium"
          >
            <option value="all">All active branches</option>
            <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
              {{ b.branch_name }} Branch
            </option>
          </select>
        </div>

        <!-- Category Filter -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Category</label>
          <select
            v-model="filters.category_id"
            @change="reloadAll"
            class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] font-medium"
          >
            <option value="all">All categories</option>
            <option v-for="c in categories" :key="c.category_id" :value="c.category_id">
              {{ c.category_name }}
            </option>
          </select>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Date From</label>
          <input
            v-model="filters.from_date"
            @change="quickDateFilter = 'custom'; reloadAll()"
            type="date"
            class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
          />
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Date To</label>
          <input
            v-model="filters.to_date"
            @change="quickDateFilter = 'custom'; reloadAll()"
            type="date"
            class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
          />
        </div>
      </div>
    </div>

    <!-- 9 Report Tabs matching Section 10 -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-[#EAE1D5]">
      <button
        v-for="tab in reportTabs"
        :key="tab.id"
        @click="selectTab(tab.id)"
        class="px-4 py-2 text-xs font-bold rounded-xl transition whitespace-nowrap flex items-center space-x-1.5 shadow-2xs"
        :class="activeTabId === tab.id ? 'bg-[#881337] text-white shadow-xs' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#FAF7F5]'"
      >
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- Contextual Explanation Banner -->
    <div class="bg-[#FFF1F2] border border-[#FECDD3] rounded-2xl p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-2.5 text-xs text-[#881337]">
      <div>
        <div class="font-bold flex items-center space-x-1.5">
          <span>💡</span>
          <span>{{ currentTabMeta?.label }} Report</span>
        </div>
        <p class="text-[#7A3644] mt-0.5 leading-relaxed">
          <strong>Questions it answers:</strong> {{ currentTabMeta?.description }}
        </p>
      </div>
      <span class="text-[11px] font-semibold text-[#9F1239] self-start sm:self-auto shrink-0 bg-white px-2.5 py-1 rounded-lg border border-[#FBCFE8]">
        {{ reportRows.length }} record(s) loaded
      </span>
    </div>

    <!-- Special KPI Cards for Consolidated Product Sales & Pricing (Super Admin Oversight) -->
    <div v-if="activeTabId === 'consolidated_products' && consolidatedSummary" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="dash-card p-4">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Daily Products Sold</div>
          <div class="text-2xl sm:text-3xl font-serif font-black text-[#1E7422] mt-1">
            {{ consolidatedSummary.combined_daily_units || 0 }} <span class="text-xs font-sans font-medium text-[#7D6E64]">units today</span>
          </div>
          <div class="text-xs text-[#7A6B60] mt-0.5">Across all branches</div>
        </div>
        <div class="dash-card p-4">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Weekly Products Sold</div>
          <div class="text-2xl sm:text-3xl font-serif font-black text-[#1565C0] mt-1">
            {{ consolidatedSummary.combined_weekly_units || 0 }} <span class="text-xs font-sans font-medium text-[#7D6E64]">units this week</span>
          </div>
          <div class="text-xs text-[#7A6B60] mt-0.5">Combined 7-day volume</div>
        </div>
        <div class="dash-card p-4">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Daily Revenue Today</div>
          <div class="text-2xl sm:text-3xl font-serif font-black text-[#2A1D15] mt-1">
            ETB {{ Number(consolidatedSummary.combined_daily_revenue || 0).toLocaleString() }}
          </div>
          <div class="text-xs text-[#1E7422] font-semibold mt-0.5">Live daily sales</div>
        </div>
        <div class="dash-card p-4">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Weekly Revenue</div>
          <div class="text-2xl sm:text-3xl font-serif font-black text-[#BA5420] mt-1">
            ETB {{ Number(consolidatedSummary.combined_weekly_revenue || 0).toLocaleString() }}
          </div>
          <div class="text-xs text-[#BA5420] font-semibold mt-0.5">Cumulative week sales</div>
        </div>
      </div>

      <!-- Branch Sales Performance Breakdown Cards -->
      <div v-if="consolidatedSummary.branch_performance && consolidatedSummary.branch_performance.length > 0" class="dash-card p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="font-serif font-bold text-sm text-[#2A1D15]">Individual Branch Sales Performance &amp; Comparison</h3>
          <span class="text-xs text-[#7D6E64]">{{ consolidatedSummary.branch_performance.length }} branches reporting</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="bp in consolidatedSummary.branch_performance"
            :key="bp.branch_id"
            class="p-3 bg-[#FAF7F2] border border-[#E9E0D4] rounded-xl flex items-center justify-between"
          >
            <div>
              <div class="font-bold text-xs text-[#2A1D15] flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                <span>{{ bp.branch_name }}</span>
              </div>
              <div class="text-[11px] text-[#7A6B60] mt-0.5">
                Today: <strong class="text-[#1E7422]">{{ bp.daily_units }}</strong> units (ETB {{ Number(bp.daily_revenue).toLocaleString() }})
              </div>
              <div class="text-[11px] text-[#7A6B60]">
                Week: <strong class="text-[#1565C0]">{{ bp.weekly_units }}</strong> units (ETB {{ Number(bp.weekly_revenue).toLocaleString() }})
              </div>
            </div>
            <div class="text-right">
              <div class="text-xs font-serif font-black text-[#BA5420]">ETB {{ Number(bp.total_revenue).toLocaleString() }}</div>
              <div class="text-[10px] text-[#7A6B60]">Total Revenue</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Report Table Area -->
    <div class="bg-white border border-[#E9E0D4] rounded-3xl shadow-2xs overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="py-16 text-center text-xs text-[#7A6B60] space-y-2">
        <div class="w-6 h-6 border-2 border-[#BA5420] border-t-transparent rounded-full animate-spin mx-auto"></div>
        <div>Querying live MySQL transactions...</div>
      </div>

      <!-- Empty State -->
      <div v-else-if="reportRows.length === 0" class="py-16 text-center text-xs text-[#7A6B60] space-y-2">
        <div class="text-3xl">📋</div>
        <div class="font-bold text-[#38121A]">No report records found for the selected filter parameters.</div>
        <div>Try expanding the date range or selecting another branch.</div>
      </div>

      <!-- Dynamic Data Table with Safe Horizontal Scroll on Mobile -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[620px]">
          <thead class="bg-[#FAF7F5] text-[11px] font-bold text-[#7A6B60] tracking-wider uppercase border-b border-[#F2D7DC]">
            <tr>
              <th
                v-for="col in reportColumns"
                :key="col"
                class="py-3.5 px-5"
                :class="col.includes('Revenue') || col.includes('Amount') || col.includes('Value') || col.includes('Order Value') ? 'text-right' : (col.includes('Orders') || col.includes('Units') || col.includes('Sent') || col.includes('Received') || col.includes('Count') ? 'text-center' : '')"
              >
                {{ col }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F8E7EA]">
            <!-- 1. Branch Performance Rows -->
            <template v-if="activeTabId === 'branch_performance'">
              <tr v-for="r in reportRows" :key="r.branch_id" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A] flex items-center space-x-2">
                  <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                  <span>{{ r.branch }}</span>
                </td>
                <td class="py-3.5 px-5 text-center font-semibold text-[#4B1A24]">{{ r.orders }}</td>
                <td class="py-3.5 px-5 text-center font-semibold text-[#4B1A24]">{{ r.units }}</td>
                <td class="py-3.5 px-5 text-right font-black text-[#881337]">ETB {{ Number(r.revenue).toLocaleString() }}</td>
                <td class="py-3.5 px-5 text-right font-semibold text-[#7A6B60]">ETB {{ Number(r.avg_order).toLocaleString() }}</td>
              </tr>
            </template>

            <!-- 2. Product Performance Rows -->
            <template v-else-if="activeTabId === 'product_performance'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-product font-bold text-[#38121A]">{{ r.product }}</td>
                <td class="py-3.5 px-5 font-medium text-[#7A6B60]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 text-[#881337] font-semibold">{{ r.category }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#261814]">{{ r.units }}</td>
                <td class="py-3.5 px-5 text-right font-black text-[#881337]">ETB {{ Number(r.revenue).toLocaleString() }}</td>
              </tr>
            </template>

            <!-- 3. Distribution Rows -->
            <template v-else-if="activeTabId === 'distribution'">
              <tr v-for="r in reportRows" :key="r.transfer_id" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#881337]">{{ r.transfer }}</td>
                <td class="py-3.5 px-5 font-medium text-[#38121A]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#4B1A24]">{{ r.sent }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#059669]">{{ r.received }}</td>
                <td class="py-3.5 px-5 text-center font-bold" :class="r.missing > 0 ? 'text-[#DC2626]' : 'text-gray-400'">
                  {{ r.missing }}
                </td>
                <td class="py-3.5 px-5 text-center font-bold" :class="r.damaged > 0 ? 'text-[#D97706]' : 'text-gray-400'">
                  {{ r.damaged }}
                </td>
                <td class="py-3.5 px-5 text-[#7A6B60]">{{ r.days_to_receipt }}</td>
                <td class="py-3.5 px-5">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                    :class="r.status === 'Received' ? 'bg-[#ECFDF5] text-[#059669]' : (r.status === 'Disputed' ? 'bg-[#FEF2F2] text-[#DC2626]' : 'bg-[#EFF6FF] text-[#2563EB]')"
                  >
                    {{ r.status }}
                  </span>
                </td>
              </tr>
            </template>

            <!-- 4. Inventory Rows -->
            <template v-else-if="activeTabId === 'inventory'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 font-product font-bold text-[#261814]">{{ r.product }}</td>
                <td class="py-3.5 px-5 text-[#7A6B60]">{{ r.category }}</td>
                <td class="py-3.5 px-5 text-center font-semibold text-[#4B1A24]">{{ r.on_hand }}</td>
                <td class="py-3.5 px-5 text-center font-semibold text-[#D97706]">{{ r.reserved }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#059669]">{{ r.available }}</td>
                <td class="py-3.5 px-5 text-center text-[#7A6B60]">{{ r.reorder_level }}</td>
                <td class="py-3.5 px-5">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                    :class="r.status === 'Needs replenishment' ? 'bg-[#FEE2E2] text-[#DC2626] animate-pulse' : 'bg-[#ECFDF5] text-[#059669]'"
                  >
                    {{ r.status }}
                  </span>
                </td>
              </tr>
            </template>

            <!-- 5. Waste Rows -->
            <template v-else-if="activeTabId === 'waste'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 font-medium text-[#261814]">{{ r.product }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#DC2626]">{{ r.quantity }} units</td>
                <td class="py-3.5 px-5 text-[#7A6B60]">{{ r.reason }}</td>
                <td class="py-3.5 px-5 text-right font-black text-[#DC2626]">ETB {{ Number(r.value).toLocaleString() }}</td>
                <td class="py-3.5 px-5 text-[#9CA3AF] text-[11px]">{{ r.date }}</td>
              </tr>
            </template>

            <!-- 6. Discrepancy Rows -->
            <template v-else-if="activeTabId === 'discrepancy'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#881337]">{{ r.transfer }}</td>
                <td class="py-3.5 px-5 font-medium text-[#38121A]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 font-semibold text-[#261814]">{{ r.line }}</td>
                <td class="py-3.5 px-5 font-bold text-[#DC2626]">{{ r.difference }}</td>
                <td class="py-3.5 px-5 text-[#7A6B60] italic">{{ r.reason }}</td>
                <td class="py-3.5 px-5">
                  <span class="px-2 py-0.5 bg-[#FFFBEB] text-[#B45309] rounded-md font-semibold text-[10px]">
                    {{ r.resolution }}
                  </span>
                </td>
                <td class="py-3.5 px-5 text-[#9CA3AF] text-[11px]">{{ r.date }}</td>
              </tr>
            </template>

            <!-- 7. Delivery Rows -->
            <template v-else-if="activeTabId === 'delivery'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A]">{{ r.branch }}</td>
                <td class="py-3.5 px-5 font-semibold flex items-center space-x-1.5" :class="r.method === 'Delivery' ? 'text-[#2563EB]' : 'text-[#059669]'">
                  <span>{{ r.method === 'Delivery' ? '🚚' : '🏪' }}</span>
                  <span>{{ r.method }}</span>
                </td>
                <td class="py-3.5 px-5 text-center font-bold text-[#261814]">{{ r.count }} orders</td>
                <td class="py-3.5 px-5 text-center text-[#7A6B60] font-medium">{{ r.avg_time }}</td>
                <td class="py-3.5 px-5 text-right font-black text-[#881337]">ETB {{ Number(r.revenue).toLocaleString() }}</td>
              </tr>
            </template>

            <!-- 8. Payments Rows -->
            <template v-else-if="activeTabId === 'payments'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A] flex items-center space-x-1.5">
                  <span>💳</span>
                  <span>{{ r.method }}</span>
                </td>
                <td class="py-3.5 px-5">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                    :class="r.status === 'Paid' ? 'bg-[#ECFDF5] text-[#059669]' : (r.status === 'Refunded' ? 'bg-[#FEF2F2] text-[#DC2626]' : 'bg-[#FFFBEB] text-[#D97706]')"
                  >
                    {{ r.status }}
                  </span>
                </td>
                <td class="py-3.5 px-5 text-center font-bold text-[#261814]">{{ r.count }} transactions</td>
                <td class="py-3.5 px-5 text-right font-black text-[#881337]">ETB {{ Number(r.amount).toLocaleString() }}</td>
              </tr>
            </template>

            <!-- Consolidated Product Sales & Pricing Rows (Super Admin Oversight) -->
            <template v-else-if="activeTabId === 'consolidated_products'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FAF6F0] transition">
                <td class="py-3.5 px-5 font-bold text-[#2A1D15]">{{ r.product }}</td>
                <td class="py-3.5 px-5 text-[#8A7B71]">{{ r.category }}</td>
                <td class="py-3.5 px-5 font-semibold text-[#BA5420] flex items-center space-x-1.5">
                  <span>📍</span>
                  <span>{{ r.branch }}</span>
                </td>
                <td class="py-3.5 px-5 text-right font-serif font-bold text-[#2A1D15]">ETB {{ Number(r.selling_price).toLocaleString() }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#1E7422]">{{ r.daily_units }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#1565C0]">{{ r.weekly_units }}</td>
                <td class="py-3.5 px-5 text-center font-bold text-[#2A1D15]">{{ r.total_units }}</td>
                <td class="py-3.5 px-5 text-right font-semibold text-[#1E7422]">ETB {{ Number(r.daily_revenue).toLocaleString() }}</td>
                <td class="py-3.5 px-5 text-right font-semibold text-[#1565C0]">ETB {{ Number(r.weekly_revenue).toLocaleString() }}</td>
                <td class="py-3.5 px-5 text-right font-serif font-black text-[#BA5420]">ETB {{ Number(r.total_revenue).toLocaleString() }}</td>
              </tr>
            </template>

            <!-- Manager Branch Reports Rows (Super Admin Executive Review) -->
            <template v-else-if="activeTabId === 'manager_reports'">
              <tr v-for="r in reportRows" :key="r.report_id" class="hover:bg-[#FAF6F0] transition">
                <td class="py-3.5 px-5 font-bold text-[#2A1D15] flex items-center space-x-1.5">
                  <span>📍</span>
                  <span>{{ r.branch }}</span>
                </td>
                <td class="py-3.5 px-5">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FAF0E6] text-[#BA5420]">
                    {{ r.report_type }}
                  </span>
                </td>
                <td class="py-3.5 px-5">
                  <div class="font-bold text-[#2A1D15]">{{ r.submitter }}</div>
                  <div class="text-[10px] text-[#8A7B71]">{{ r.role }}</div>
                </td>
                <td class="py-3.5 px-5">
                  <div class="font-bold text-[#2A1D15]">{{ r.title }}</div>
                  <div class="text-[11px] text-[#7A6B60] line-clamp-1">{{ r.content }}</div>
                </td>
                <td class="py-3.5 px-5 text-[#8A7B71] text-[11px] whitespace-nowrap">{{ r.date }}</td>
                <td class="py-3.5 px-5">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                    :class="r.status === 'Resolved' || r.status === 'Reviewed' ? 'bg-[#EAF5EA] text-[#2E7D32]' : 'bg-[#FFF3E0] text-[#E65100]'"
                  >
                    {{ r.status }}
                  </span>
                </td>
                <td class="py-3.5 px-5 text-right">
                  <button
                    @click="openManagerReportModal(r)"
                    class="px-3 py-1 bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] font-semibold text-xs rounded-md shadow-2xs transition cursor-pointer"
                  >
                    Review
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Review Manager Report Modal for Super Admin -->
    <div
      v-if="reviewingReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
    >
      <div class="bg-white border border-[#E9E0D4] rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-start justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#BA5420] bg-[#FAF0E6] px-2.5 py-0.5 rounded-full border border-[#F0D5C1]">
              {{ reviewingReport.report_type }} · {{ reviewingReport.branch }}
            </span>
            <h3 class="font-serif font-bold text-lg text-[#2A1D15] mt-1.5">{{ reviewingReport.title }}</h3>
            <p class="text-xs text-[#7A6B60]">Submitted by {{ reviewingReport.submitter }} ({{ reviewingReport.role }}) on {{ reviewingReport.date }}</p>
          </div>
          <button @click="reviewingReport = null" class="text-gray-400 hover:text-gray-700 text-lg cursor-pointer">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#8A7B71] mb-1">Report Details</label>
            <div class="bg-[#FAF7F2] p-3.5 rounded-xl text-[#3A2E26] whitespace-pre-wrap leading-relaxed border border-[#EFE4D6] max-h-56 overflow-y-auto font-sans">
              {{ reviewingReport.content }}
            </div>
          </div>

          <div v-if="reviewingReport.manager_notes && reviewingReport.manager_notes !== '—'" class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
            <span class="font-bold text-purple-900 block mb-0.5">Manager Notes:</span>
            <p class="text-purple-800 text-[11px]">{{ reviewingReport.manager_notes }}</p>
          </div>

          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#8A7B71] mb-1">Super Admin Notes</label>
            <textarea
              v-model="superAdminNotes"
              rows="2"
              placeholder="Add verification notes or instructions for the branch manager..."
              class="w-full bg-[#FAF7F2] border border-[#DDD2C5] rounded-xl p-2.5 text-xs text-[#2A1D15] focus:ring-1 focus:ring-[#BA5420]"
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-[#EFE8DF]">
          <button
            @click="reviewingReport = null"
            class="px-4 py-2 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition cursor-pointer"
          >
            Close
          </button>
          <div class="flex items-center space-x-2">
            <button
              @click="submitReportReview('Reviewed')"
              :disabled="isSubmittingReview"
              class="px-4 py-2 text-xs font-semibold bg-[#2A1D15] hover:bg-[#1A120D] text-white rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
            >
              {{ isSubmittingReview ? 'Saving...' : 'Mark as Reviewed' }}
            </button>
            <button
              @click="submitReportReview('Resolved')"
              :disabled="isSubmittingReview"
              class="px-4 py-2 text-xs font-semibold bg-[#059669] hover:bg-[#047857] text-white rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
            >
              {{ isSubmittingReview ? 'Saving...' : 'Resolve Report' }}
            </button>
          </div>
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
const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isBranchScoped = computed(() => !isSuperAdmin.value);
const userBranchId = computed(() => authStore.user?.branch_id || (branches.value[0]?.branch_id ?? 1));
const userBranchName = computed(() => authStore.user?.branch?.branch_name || (branches.value[0]?.branch_name) || 'My Branch');

// Report tabs definition (Audit removed; Consolidated Sales & Pricing and Manager Reports added)
const reportTabs = computed(() => [
  { id: 'branch_performance', label: 'Branch Performance', icon: '🏢', description: 'Which branch has the highest revenue, most orders, top-selling cakes and bread?' },
  { id: 'consolidated_products', label: 'Consolidated Sales & Pricing', icon: '🧁', description: 'Monitor daily and weekly products sold, product selling prices, daily/weekly revenue, and compare branch performance.' },
  { id: 'manager_reports', label: isSuperAdmin.value ? 'Branch Reports (from Managers)' : 'Manager Reports Desk', icon: '📑', description: 'Review operational and sales reports submitted to Super Admin by Branch Managers.' },
  { id: 'product_performance', label: 'Product Performance', icon: '🎂', description: 'Which products sell best, and where?' },
  { id: 'distribution', label: 'Distribution', icon: '🚚', description: 'How much was sent, received, missing, damaged, still in transit?' },
  { id: 'inventory', label: 'Inventory', icon: '📦', description: 'What is on hand, reserved and available per branch, and what needs replenishment?' },
  { id: 'waste', label: 'Waste', icon: '🗑️', description: 'How much stock was damaged or expired, and why?' },
  { id: 'discrepancy', label: 'Discrepancy', icon: '⚠️', description: 'Which transfers had differences and who resolved them?' },
  { id: 'delivery', label: 'Delivery', icon: '🛵', description: 'How are deliveries and pickups split, and how long do they take?' },
  { id: 'payments', label: 'Payments', icon: '💳', description: 'Revenue by method, failures, refunds.' },
]);

const activeTabId = ref('branch_performance');
const branches = ref([]);
const categories = ref([]);
const loading = ref(false);
const sendingSummary = ref(false);
const consolidatedSummary = ref(null);

// Review Report Modal
const reviewingReport = ref(null);
const superAdminNotes = ref('');
const isSubmittingReview = ref(false);

function openManagerReportModal(report) {
  reviewingReport.value = report;
  superAdminNotes.value = '';
}

async function submitReportReview(status) {
  if (!reviewingReport.value) return;
  isSubmittingReview.value = true;
  try {
    await api.post(`/admin/staff-reports/${reviewingReport.value.report_id}/review`, {
      status,
      manager_notes: superAdminNotes.value || undefined,
    });
    reviewingReport.value.status = status;
    reviewingReport.value = null;
    await reloadAll();
  } catch (err) {
    alert('Failed to update report status: ' + (err.response?.data?.message || err.message));
  } finally {
    isSubmittingReview.value = false;
  }
}

const quickDateFilter = ref('this_month');

const filters = ref({
  branch_id: isBranchScoped.value ? userBranchId.value : 'all',
  category_id: 'all',
  product_id: 'all',
  from_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  to_date: new Date().toISOString().split('T')[0],
});

const setDateFilter = (filterKey) => {
  quickDateFilter.value = filterKey;
  const now = new Date();
  if (filterKey === 'today') {
    const todayStr = now.toISOString().split('T')[0];
    filters.value.from_date = todayStr;
    filters.value.to_date = todayStr;
  } else if (filterKey === 'this_week') {
    const d = new Date();
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    const monday = new Date(d.setDate(diff));
    filters.value.from_date = monday.toISOString().split('T')[0];
    filters.value.to_date = new Date().toISOString().split('T')[0];
  } else if (filterKey === 'this_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    filters.value.from_date = firstDay.toISOString().split('T')[0];
    filters.value.to_date = new Date().toISOString().split('T')[0];
  }
  reloadAll();
};

const metrics = ref({
  revenue: 0,
  revenue_trend: '+12% vs previous period',
  orders: 0,
  orders_sub: '0 pending',
  avg_order: 0,
  avg_order_trend: '+3%',
  units_sold: 0,
  units_sub: 'Cakes & bread',
});

const reportColumns = ref([]);
const reportRows = ref([]);

const currentTabMeta = computed(() => {
  return reportTabs.value.find(t => t.id === activeTabId.value);
});

async function selectTab(tabId) {
  activeTabId.value = tabId;
  await loadActiveReport();
}

async function loadSummary() {
  try {
    const res = await api.get('/admin/reports/summary', { params: filters.value });
    if (res.data?.metrics) {
      metrics.value = res.data.metrics;
    }
  } catch (err) {
    console.error('Failed to load reports summary', err);
  }
}

async function loadActiveReport() {
  loading.value = true;
  try {
    const res = await api.get('/admin/reports/data', {
      params: {
        type: activeTabId.value,
        ...filters.value,
      },
    });
    reportColumns.value = res.data.columns || [];
    reportRows.value = res.data.rows || [];
    consolidatedSummary.value = res.data.summary || null;
  } catch (err) {
    console.error(`Failed to load report data for ${activeTabId.value}`, err);
    reportColumns.value = [];
    reportRows.value = [];
    consolidatedSummary.value = null;
  } finally {
    loading.value = false;
  }
}

async function reloadAll() {
  await Promise.all([loadSummary(), loadActiveReport()]);
}

async function sendDailySummary() {
  sendingSummary.value = true;
  try {
    const res = await api.post('/admin/reports/send-daily-summary');
    alert(`Success: ${res.data.message}\nDate: ${res.data.summary.date}\nRevenue: ETB ${res.data.summary.revenue_today}\nOrders: ${res.data.summary.orders_today}\nLow-stock alerts: ${res.data.summary.low_stock_count}`);
  } catch (err) {
    alert('Failed to dispatch daily summary: ' + (err.response?.data?.message || err.message));
  } finally {
    sendingSummary.value = false;
  }
}

function exportCSV() {
  if (reportRows.value.length === 0) {
    alert('No data to export.');
    return;
  }

  const cols = reportColumns.value;
  // Convert rows into array of values matching cols
  const csvRows = [cols.map(c => `"${c}"`).join(',')];

  reportRows.value.forEach(row => {
    const values = Object.values(row).map(val => {
      const sanitized = String(val ?? '').replace(/"/g, '""');
      return `"${sanitized}"`;
    });
    csvRows.push(values.join(','));
  });

  const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `sweetbakes_${activeTabId.value}_${filters.value.to_date}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function exportPDF() {
  window.print();
}

onMounted(async () => {
  try {
    const [bRes, cRes] = await Promise.all([
      api.get('/branches'),
      api.get('/categories'),
    ]);
    branches.value = bRes.data;
    categories.value = cRes.data;
  } catch (e) {}

  if (isBranchScoped.value && userBranchId.value) {
    filters.value.branch_id = userBranchId.value;
  }

  await reloadAll();
});
</script>
