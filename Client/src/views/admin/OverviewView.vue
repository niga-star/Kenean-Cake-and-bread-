<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- ========================================== -->
    <!-- BRANCH DASHBOARD VIEW (Branch Admin/Staff) -->
    <!-- ========================================== -->
    <div v-if="data.is_branch_view" class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">
              {{ data.branch?.branch_name }} Branch Dashboard
            </h2>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#EAE2D8] text-[#55463C] border border-[#D5C7B8] font-poppins">
              {{ data.branch?.branch_code }}
            </span>
          </div>
          <p class="text-xs text-[#7A6B60] mt-1 font-poppins">
            Locked to {{ data.branch?.branch_name }}. Address: {{ data.branch?.address }} · Phone: {{ data.branch?.phone }}
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <router-link
            to="/admin/distribution"
            class="px-3.5 py-2 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-sm"
          >
            📥 Stock Receiving ({{ data.incoming_transfers || 0 }})
          </router-link>
          <router-link
            to="/admin/orders"
            class="px-3.5 py-2 text-xs font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm"
          >
            🛍️ Branch Orders ({{ data.orders_today || 0 }})
          </router-link>
          <router-link
            to="/admin/custom-cakes"
            class="px-3.5 py-2 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-sm"
          >
            🎂 Custom Cakes
          </router-link>
        </div>
      </div>

      <!-- 4 Branch Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Branch revenue today</div>
          <div class="text-3xl font-bold text-[#241710]">ETB {{ Number(data.revenue_today || 0).toLocaleString() }}</div>
          <div class="text-xs text-[#2E7D32] mt-1">From completed & active orders</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Orders today</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.orders_today || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">
            {{ data.pending_orders || 0 }} pending · {{ data.preparing_orders || 0 }} preparing · {{ data.ready_orders || 0 }} ready
          </div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Available stock to sell</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.stock_summary?.available || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">
            {{ data.stock_summary?.units_on_hand || 0 }} on hand · {{ data.stock_summary?.reserved || 0 }} reserved
          </div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Incoming stock</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.incoming_units || 0 }}</div>
          <div class="text-xs text-[#BA5420] mt-1 font-semibold">
            {{ data.incoming_transfers || 0 }} transfer(s) in transit
          </div>
        </div>
      </div>

      <!-- Two Column Layout: Left Tables + Right Alerts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Left: Recent Orders & Stock (Col span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Recent Orders Table -->
          <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#EFE8DF] flex items-center justify-between">
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Recent Branch Orders</h3>
              <router-link to="/admin/orders" class="text-xs font-semibold text-[#BA5420] hover:underline font-poppins">
                View all →
              </router-link>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full min-w-[540px] text-left text-xs">
                <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] uppercase border-b border-[#EFE8DF]">
                  <tr>
                    <th class="py-2.5 px-4">Order</th>
                    <th class="py-2.5 px-3">Customer</th>
                    <th class="py-2.5 px-3">Method</th>
                    <th class="py-2.5 px-3">Total</th>
                    <th class="py-2.5 px-3 text-center">Status</th>
                    <th class="py-2.5 px-4 text-right">Placed</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EDE4]">
                  <tr v-if="!data.recent_orders || data.recent_orders.length === 0">
                    <td colspan="6" class="py-6 text-center text-xs text-[#8A7B71]">No orders placed at this branch yet today.</td>
                  </tr>
                  <tr v-for="ord in data.recent_orders" :key="ord.order_id" class="hover:bg-[#FCFAF7]">
                    <td class="py-2.5 px-4 font-mono font-semibold text-[#382C24]">{{ ord.order_number }}</td>
                    <td class="py-2.5 px-3 text-[#4A3B32]">{{ ord.customer }}</td>
                    <td class="py-2.5 px-3 text-[#6A5A50]">{{ ord.method }}</td>
                    <td class="py-2.5 px-3 font-semibold text-[#241710]">ETB {{ Number(ord.total).toLocaleString() }}</td>
                    <td class="py-2.5 px-3 text-center">
                      <span
                        class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full"
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
                    <td class="py-2.5 px-4 text-right text-[#8A7B71]">{{ ord.placed }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock Availability Table -->
          <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#EFE8DF] flex items-center justify-between">
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Stock Status</h3>
              <router-link to="/admin/inventory" class="text-xs font-semibold text-[#BA5420] hover:underline font-poppins">
                Full inventory →
              </router-link>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full min-w-[540px] text-left text-xs">
                <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] uppercase border-b border-[#EFE8DF]">
                  <tr>
                    <th class="py-2.5 px-4">Product</th>
                    <th class="py-2.5 px-3 text-center">On Hand</th>
                    <th class="py-2.5 px-3 text-center">Reserved</th>
                    <th class="py-2.5 px-3 text-center font-bold">Available</th>
                    <th class="py-2.5 px-3 text-center">Reorder At</th>
                    <th class="py-2.5 px-4 text-right">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EDE4]">
                  <tr v-for="it in (data.stock_items || []).slice(0, 6)" :key="it.product_id" class="hover:bg-[#FCFAF7]">
                    <td class="py-2.5 px-4 font-bold text-[#382C24]">{{ it.product_name }}</td>
                    <td class="py-2.5 px-3 text-center text-[#55463C]">{{ it.on_hand }}</td>
                    <td class="py-2.5 px-3 text-center text-[#7A6B60]">{{ it.reserved }}</td>
                    <td class="py-2.5 px-3 text-center font-bold text-[#241710]">{{ it.available }}</td>
                    <td class="py-2.5 px-3 text-center text-[#7A6B60]">{{ it.reorder_level }}</td>
                    <td class="py-2.5 px-4 text-right">
                      <span
                        class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full"
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

        <!-- Right: Needs Attention & Actions (Col span 1) -->
        <div class="space-y-6">
          <!-- Needs Attention Feed -->
          <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 space-y-3">
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Needs Attention</h3>

            <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-xs text-[#8A7B71] py-4 text-center font-poppins">
              No urgent alerts for your branch.
            </div>

            <div class="space-y-2">
              <div
                v-for="(item, idx) in data.needs_attention"
                :key="idx"
                class="p-3 rounded-lg border text-xs font-medium leading-relaxed font-poppins"
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

          <!-- Branch Quick Actions -->
          <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 space-y-3">
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Operations</h3>
            <div class="space-y-2 text-xs">
              <router-link
                to="/admin/orders"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                🛍️ Manage Branch Orders
              </router-link>
              <router-link
                to="/admin/custom-cakes"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                🎂 Review Custom Cake Requests & Quotes
              </router-link>
              <router-link
                to="/admin/products-prices"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                🏷️ Set Branch Product Prices
              </router-link>
              <router-link
                to="/admin/distribution"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                📥 Confirm incoming stock transfers
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- CENTRAL DASHBOARD VIEW (Super Admin Executive) -->
    <!-- ============================================== -->
    <div v-else class="space-y-6">
      <!-- Header with Filter Controls -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-[#E9E0D4] shadow-sm">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#2A1D15]">Executive Overview</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1]">
              Central Reporting
            </span>
          </div>
          <p class="text-xs text-[#7A6B60] mt-1 font-poppins">
            Cross-branch operational intelligence, manager reports, custom cake pipeline & performance comparisons.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Branch Selector -->
          <div class="flex items-center space-x-1.5">
            <label class="text-xs font-semibold text-[#65554B]">Branch:</label>
            <select
              v-model="selectedBranch"
              @change="loadOverview"
              class="bg-[#FAF7F2] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs font-semibold text-[#4A3B32] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="all">Whole Business (All Branches)</option>
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }} ({{ b.branch_code }})
              </option>
            </select>
          </div>

          <!-- Date Selector -->
          <div class="flex items-center space-x-1.5">
            <label class="text-xs font-semibold text-[#65554B]">Date:</label>
            <input
              type="date"
              v-model="selectedDate"
              @change="loadOverview"
              class="bg-[#FAF7F2] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs font-medium text-[#4A3B32] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#BA5420]"
            />
            <button
              v-if="selectedDate"
              @click="clearDate"
              title="Reset to today"
              class="text-xs text-[#8A7B71] hover:text-[#BA5420] px-1"
            >
              ✕
            </button>
          </div>

          <router-link
            to="/admin/staff-reports"
            class="px-3.5 py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-sm"
          >
            📋 Reports Desk
          </router-link>
        </div>
      </div>

      <!-- 5 Executive Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Revenue {{ selectedDate ? 'on ' + selectedDate : 'today' }}</div>
          <div class="text-2xl lg:text-3xl font-bold text-[#241710]">ETB {{ Number(data.revenue_today || 0).toLocaleString() }}</div>
          <div class="text-xs text-[#2E7D32] mt-1 font-semibold">Across all active branches</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Orders today</div>
          <div class="text-2xl lg:text-3xl font-bold text-[#241710]">{{ data.orders_today || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">{{ data.pending_orders || 0 }} pending fulfillment</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Active Branches</div>
          <div class="text-2xl lg:text-3xl font-bold text-[#241710]">{{ data.active_branches || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">Main plus regional stores</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Active Custom Cakes</div>
          <div class="text-2xl lg:text-3xl font-bold text-[#BA5420]">{{ data.custom_cakes_summary?.total_active || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">{{ data.custom_cakes_summary?.submitted || 0 }} awaiting manager quote</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Units in Transit</div>
          <div class="text-2xl lg:text-3xl font-bold text-[#241710]">{{ data.units_in_transit || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">Inter-branch shipments</div>
        </div>
      </div>

      <!-- Custom Cake Pipeline Summary Bar -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="text-lg">🎂</span>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">Custom Cake Orders Pipeline</h3>
            <span class="text-xs text-[#7A6B60] font-normal">Managed directly by Branch Managers & Staff</span>
          </div>
          <span class="text-xs font-semibold text-[#BA5420]">
            Total In System: {{ (data.custom_cakes_summary?.total_active || 0) + (data.custom_cakes_summary?.delivered || 0) }}
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1 text-center">
          <div class="p-3 bg-[#FFF9E6] border border-[#FEE39E] rounded-lg">
            <div class="text-xs font-semibold text-[#A67503]">1. Submitted</div>
            <div class="text-xl font-bold text-[#241710] mt-0.5">{{ data.custom_cakes_summary?.submitted || 0 }}</div>
            <div class="text-[10px] text-[#A67503]">Awaiting manager quote</div>
          </div>
          <div class="p-3 bg-[#EBF3FB] border border-[#BBDEFB] rounded-lg">
            <div class="text-xs font-semibold text-[#1565C0]">2. Quoted</div>
            <div class="text-xl font-bold text-[#241710] mt-0.5">{{ data.custom_cakes_summary?.quoted || 0 }}</div>
            <div class="text-[10px] text-[#1565C0]">Awaiting customer payment</div>
          </div>
          <div class="p-3 bg-[#F0FDF4] border border-[#BBF7D0] rounded-lg">
            <div class="text-xs font-semibold text-[#166534]">3. Confirmed</div>
            <div class="text-xl font-bold text-[#241710] mt-0.5">{{ data.custom_cakes_summary?.confirmed || 0 }}</div>
            <div class="text-[10px] text-[#166534]">Scheduled for baking</div>
          </div>
          <div class="p-3 bg-[#FAF5FF] border border-[#E9D5FF] rounded-lg">
            <div class="text-xs font-semibold text-[#6B21A8]">4. Ready</div>
            <div class="text-xl font-bold text-[#241710] mt-0.5">{{ data.custom_cakes_summary?.ready || 0 }}</div>
            <div class="text-[10px] text-[#6B21A8]">Ready at branch</div>
          </div>
          <div class="p-3 bg-[#F3F4F6] border border-[#E5E7EB] rounded-lg">
            <div class="text-xs font-semibold text-[#4B5563]">5. Delivered</div>
            <div class="text-xl font-bold text-[#241710] mt-0.5">{{ data.custom_cakes_summary?.delivered || 0 }}</div>
            <div class="text-[10px] text-[#4B5563]">Successfully fulfilled</div>
          </div>
        </div>
      </div>

      <!-- Branch Performance Comparison Table -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Performance Comparison</h3>
            <p class="text-xs text-[#7A6B60] mt-0.5">Overview of managers, staff size, sales and shopping availability.</p>
          </div>
          <router-link
            to="/admin/branches"
            class="text-xs font-semibold text-[#BA5420] hover:text-[#9E4314] font-poppins"
          >
            Manage branches & permissions →
          </router-link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-5">Branch</th>
                <th class="py-3 px-4">Manager</th>
                <th class="py-3 px-3 text-center">Staff</th>
                <th class="py-3 px-3 text-center">Orders</th>
                <th class="py-3 px-4 text-center">Revenue</th>
                <th class="py-3 px-3 text-center">Active Cakes</th>
                <th class="py-3 px-3 text-center">Stock Health</th>
                <th class="py-3 px-4 text-center">Shopping Access</th>
                <th class="py-3 px-4 text-right">Transfers</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr
                v-for="bt in data.branches_today"
                :key="bt.branch_id"
                class="hover:bg-[#FCFAF7] transition"
                :class="{ 'bg-[#FFF9F5]': selectedBranch === bt.branch_id }"
              >
                <td class="py-3.5 px-5 font-semibold text-[#382C24]">
                  {{ bt.branch_name }}
                  <span class="block text-[10px] font-normal text-[#8A7B71] font-mono">{{ bt.branch_code }}</span>
                </td>
                <td class="py-3.5 px-4 text-xs text-[#4A3B32]">
                  <span class="font-medium text-[#2A1D15]">{{ bt.manager_name }}</span>
                </td>
                <td class="py-3.5 px-3 text-center text-xs text-[#55463C] font-semibold">
                  {{ bt.staff_count }}
                </td>
                <td class="py-3.5 px-3 text-center text-[#55463C] font-medium">{{ bt.orders }}</td>
                <td class="py-3.5 px-4 text-center font-bold text-[#241710]">ETB {{ Number(bt.revenue).toLocaleString() }}</td>
                <td class="py-3.5 px-3 text-center text-xs">
                  <span
                    class="px-2 py-0.5 rounded-full font-semibold"
                    :class="bt.active_cakes > 0 ? 'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]' : 'text-[#8A7B71]'"
                  >
                    {{ bt.active_cakes }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-center text-xs">
                  <span
                    class="inline-block px-2.5 py-0.5 font-semibold rounded-full border text-[11px]"
                    :class="{
                      'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]': bt.stock_health === 'Healthy',
                      'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]': bt.stock_health.includes('low') || bt.stock_health.includes('sold')
                    }"
                  >
                    {{ bt.stock_health }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                    :class="bt.is_shopping_enabled !== false ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FAF0F0] text-[#B84B42] border-[#E8C4C4]'"
                  >
                    {{ bt.is_shopping_enabled !== false ? '✓ Enabled' : '✕ Disabled' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right text-xs font-medium text-[#65554B]">
                  {{ bt.open_transfers }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Two Column: Branch Manager Submitted Reports + Recent Price Adjustments -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Reports Submitted by Branch Managers -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden space-y-0">
          <div class="p-5 border-b border-[#EFE8DF] flex items-center justify-between">
            <div>
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Manager Reports</h3>
              <p class="text-xs text-[#7A6B60] mt-0.5">Submitted by branch leadership for executive review.</p>
            </div>
            <router-link
              to="/admin/staff-reports"
              class="text-xs font-semibold text-[#BA5420] hover:underline font-poppins"
            >
              All Reports →
            </router-link>
          </div>

          <div v-if="!data.submitted_reports || data.submitted_reports.length === 0" class="p-8 text-center text-xs text-[#8A7B71]">
            No reports submitted for the selected filter.
          </div>

          <div v-else class="divide-y divide-[#F3EDE4] max-h-[360px] overflow-y-auto">
            <div
              v-for="rep in data.submitted_reports"
              :key="rep.report_id"
              @click="openReportModal(rep)"
              class="p-4 hover:bg-[#FCFAF7] transition cursor-pointer flex items-start justify-between gap-3"
            >
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-bold text-[#2A1D15] truncate">{{ rep.title }}</span>
                  <span
                    class="px-2 py-0.5 text-[10px] font-semibold rounded-full border"
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
                <p class="text-xs text-[#6A5A50] line-clamp-2 leading-relaxed">
                  {{ rep.content }}
                </p>
                <div class="text-[11px] text-[#8A7B71] flex items-center gap-3 pt-0.5">
                  <span class="font-medium text-[#4A3B32]">🏢 {{ rep.branch_name }}</span>
                  <span>👤 {{ rep.author_name }}</span>
                  <span>🕒 {{ rep.date }}</span>
                </div>
              </div>

              <span
                class="px-2.5 py-1 text-[10px] font-semibold rounded-full border shrink-0"
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
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden space-y-0">
          <div class="p-5 border-b border-[#EFE8DF] flex items-center justify-between">
            <div>
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Price Updates</h3>
              <p class="text-xs text-[#7A6B60] mt-0.5">Recorded price modifications by Branch Managers.</p>
            </div>
            <router-link
              to="/admin/audit-log"
              class="text-xs font-semibold text-[#BA5420] hover:underline font-poppins"
            >
              Full Audit Trail →
            </router-link>
          </div>

          <div v-if="!data.recent_price_changes || data.recent_price_changes.length === 0" class="p-8 text-center text-xs text-[#8A7B71]">
            No price change records found for this period.
          </div>

          <div v-else class="divide-y divide-[#F3EDE4] max-h-[360px] overflow-y-auto">
            <div
              v-for="pc in data.recent_price_changes"
              :key="pc.log_id"
              class="p-4 hover:bg-[#FCFAF7] transition flex items-start justify-between gap-3 text-xs"
            >
              <div class="space-y-1">
                <div class="font-semibold text-[#2A1D15]">{{ pc.record }}</div>
                <div class="flex items-center gap-2 text-[11px]">
                  <span class="text-[#8A7B71] line-through">ETB {{ pc.old_value }}</span>
                  <span class="text-[#BA5420]">→</span>
                  <span class="font-bold text-[#2E7D32]">ETB {{ pc.new_value }}</span>
                </div>
                <div class="text-[11px] text-[#8A7B71] flex items-center gap-2 pt-0.5">
                  <span>👤 {{ pc.author }}</span>
                  <span>•</span>
                  <span>🕒 {{ pc.time }}</span>
                </div>
              </div>

              <span class="px-2 py-0.5 text-[10px] font-semibold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1] rounded-md shrink-0">
                Audit Verified
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Middle Grid: Sales by branch this week (Stacked Bar Chart) + Needs attention -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Stacked Bar Chart Box -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-4 sm:p-6 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Sales by branch, this week</h3>
            <div class="flex items-center space-x-4 text-xs font-poppins">
              <span class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-[#BA5420]"></span>
                <span class="text-[#65554B]">Cakes</span>
              </span>
              <span class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded bg-[#E4A373]"></span>
                <span class="text-[#65554B]">Bread and pastry</span>
              </span>
            </div>
          </div>

          <!-- Vertical Stacked Bars with Horizontal Scroll for Small Screens -->
          <div class="overflow-x-auto pb-2">
            <div class="h-64 min-w-[280px] flex items-end justify-between px-2 sm:px-6 pt-6 border-b border-[#EFE8DF]">
              <div
                v-for="sb in data.sales_by_branch"
                :key="sb.branch"
                class="flex flex-col items-center space-y-2 w-12 sm:w-16 shrink-0"
              >
                <div class="w-10 sm:w-12 rounded-t-md overflow-hidden flex flex-col justify-end bg-transparent h-48">
                  <!-- Cakes (top block) -->
                  <div
                    class="w-full bg-[#BA5420] transition-all duration-500"
                    :style="{ height: `${Math.min(100, Math.round(((sb.cakes || 1) / 22000) * 100))}%` }"
                  ></div>
                  <!-- Bread (bottom block) -->
                  <div
                    class="w-full bg-[#E4A373] transition-all duration-500"
                    :style="{ height: `${Math.min(100, Math.round(((sb.bread || 1) / 22000) * 100))}%` }"
                  ></div>
                </div>
                <span class="text-[11px] sm:text-xs font-semibold text-[#5A4A3E] text-center truncate max-w-[60px]">{{ sb.branch }}</span>
              </div>
            </div>
          </div>

          <p class="text-[11px] text-[#8F7F74] pt-1">
            Real values calculated from branch sales in MySQL database.
          </p>
        </div>

        <!-- Needs Attention Feed -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-3.5">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Needs attention</h3>

          <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-xs text-[#8A7B71] py-4 text-center font-poppins">
            All branches healthy. No urgent alerts.
          </div>

          <div class="space-y-2.5">
            <div
              v-for="(item, idx) in data.needs_attention"
              :key="idx"
              class="p-3.5 rounded-lg border text-xs font-medium leading-relaxed transition font-poppins"
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
    <!-- REPORT DETAIL MODAL -->
    <!-- ============================================== -->
    <div
      v-if="activeReport"
      class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
      @click.self="activeReport = null"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-[#E9E0D4]">
        <div class="flex items-start justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1]">
              {{ activeReport.report_type }}
            </span>
            <h3 class="font-serif font-bold text-lg text-[#2A1D15] mt-2">{{ activeReport.title }}</h3>
          </div>
          <button @click="activeReport = null" class="text-[#8A7B71] hover:text-[#2A1D15] text-lg font-bold">✕</button>
        </div>

        <div class="space-y-3 text-xs text-[#4A3B32]">
          <div class="grid grid-cols-2 gap-2 bg-[#FAF7F2] p-3 rounded-lg border border-[#E9E0D4]">
            <div><span class="text-[#8A7B71]">Branch:</span> <span class="font-semibold text-[#2A1D15]">{{ activeReport.branch_name }}</span></div>
            <div><span class="text-[#8A7B71]">Author:</span> <span class="font-semibold text-[#2A1D15]">{{ activeReport.author_name }} ({{ activeReport.author_role }})</span></div>
            <div><span class="text-[#8A7B71]">Date:</span> <span class="font-semibold text-[#2A1D15]">{{ activeReport.date }}</span></div>
            <div><span class="text-[#8A7B71]">Status:</span> <span class="font-semibold text-[#2E7D32]">{{ activeReport.status }}</span></div>
          </div>

          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#8A7B71] mb-1">Report Content</label>
            <div class="bg-white border border-[#E9E0D4] rounded-lg p-3 leading-relaxed whitespace-pre-wrap max-h-56 overflow-y-auto">
              {{ activeReport.content }}
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-[#EFE8DF]">
          <button
            @click="activeReport = null"
            class="px-4 py-2 text-xs font-semibold bg-[#2A1D15] text-white rounded-lg hover:bg-[#3D2C20] transition"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';

const branches = ref([]);
const selectedBranch = ref('all');
const selectedDate = ref('');
const activeReport = ref(null);

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