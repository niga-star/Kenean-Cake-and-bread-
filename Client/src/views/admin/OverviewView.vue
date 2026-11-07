<template>
  <div class="space-y-6 max-w-7xl mx-auto">
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
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#EAE2D8] text-[#55463C] border border-[#D5C7B8]">
              {{ data.branch?.branch_code }}
            </span>
          </div>
          <p class="text-xs text-[#7A6B60] mt-1">
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
            🛍️ My Orders ({{ data.orders_today || 0 }})
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
              <h3 class="font-semibold text-sm text-[#2A1D15]">Recent Branch Orders</h3>
              <router-link to="/admin/orders" class="text-xs font-semibold text-[#BA5420] hover:underline">
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
              <h3 class="font-semibold text-sm text-[#2A1D15]">Branch Stock Status</h3>
              <router-link to="/admin/inventory" class="text-xs font-semibold text-[#BA5420] hover:underline">
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
                    <td class="py-2.5 px-4 font-medium text-[#382C24]">{{ it.product_name }}</td>
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
            <h3 class="font-semibold text-sm text-[#2A1D15]">Needs Attention</h3>

            <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-xs text-[#8A7B71] py-4 text-center">
              No urgent alerts for your branch.
            </div>

            <div class="space-y-2">
              <div
                v-for="(item, idx) in data.needs_attention"
                :key="idx"
                class="p-3 rounded-lg border text-xs font-medium leading-relaxed"
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
            <h3 class="font-semibold text-sm text-[#2A1D15]">Branch Operations</h3>
            <div class="space-y-2 text-xs">
              <router-link
                to="/admin/distribution"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                📥 Confirm incoming stock transfers
              </router-link>
              <router-link
                to="/admin/distribution"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                📋 Request stock from Main Branch
              </router-link>
              <router-link
                to="/admin/orders"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                🛍️ Manage incoming customer orders
              </router-link>
              <router-link
                to="/admin/inventory"
                class="block p-2.5 bg-[#FAF7F2] hover:bg-[#F2ECE3] border border-[#E6DDD2] rounded-lg font-medium text-[#382C24] transition"
              >
                ⚠️ Record damaged or expired stock
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- CENTRAL DASHBOARD VIEW (Super Admin / Main Mgr) -->
    <!-- ============================================== -->
    <div v-else class="space-y-6">
      <!-- Header with Filter Controls -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-2xl font-serif font-bold text-[#2A1D15]">Overview</h2>
          <p class="text-xs text-[#7A6B60] mt-0.5">Whole business central view across all branches.</p>
        </div>

        <div class="flex items-center space-x-3">
          <select
            v-model="selectedBranch"
            @change="loadOverview"
            class="bg-white border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs font-semibold text-[#4A3B32] shadow-sm"
          >
            <option value="all">All branches</option>
            <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
              {{ b.branch_name }}
            </option>
          </select>

          <router-link
            to="/admin/transfers/new"
            class="px-4 py-2 text-sm font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm"
          >
            New transfer
          </router-link>
        </div>
      </div>

      <!-- 4 Summary Stat Cards matching PDF Page 2 / 12 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Revenue today</div>
          <div class="text-3xl font-bold text-[#241710]">ETB {{ Number(data.revenue_today || 0).toLocaleString() }}</div>
          <div class="text-xs text-[#2E7D32] mt-1">+12% vs yesterday</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Orders today</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.orders_today || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">{{ data.pending_orders || 0 }} pending</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Active branches</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.active_branches || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">Main plus 3 regular</div>
        </div>

        <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
          <div class="text-xs font-medium text-[#7D6E64] mb-1">Units in transit</div>
          <div class="text-3xl font-bold text-[#241710]">{{ data.units_in_transit || 0 }}</div>
          <div class="text-xs text-[#8A7B71] mt-1">Dispatched to branches</div>
        </div>
      </div>

      <!-- Middle Grid: Sales by branch this week (Stacked Bar Chart) + Needs attention -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Stacked Bar Chart Box -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-4 sm:p-6 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="font-semibold text-base text-[#2A1D15]">Sales by branch, this week</h3>
            <div class="flex items-center space-x-4 text-xs">
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
          <h3 class="font-semibold text-base text-[#2A1D15]">Needs attention</h3>

          <div v-if="!data.needs_attention || data.needs_attention.length === 0" class="text-xs text-[#8A7B71] py-4 text-center">
            All branches healthy. No urgent alerts.
          </div>

          <div class="space-y-2.5">
            <div
              v-for="(item, idx) in data.needs_attention"
              :key="idx"
              class="p-3.5 rounded-lg border text-xs font-medium leading-relaxed transition"
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

      <!-- Branches Today Table matching PDF Page 2 / 12 -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF] flex items-center justify-between">
          <h3 class="font-semibold text-base text-[#2A1D15]">Branches today</h3>
          <router-link
            to="/admin/branches"
            class="text-xs font-semibold text-[#BA5420] hover:text-[#9E4314]"
          >
            Manage branches
          </router-link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-5">Branch</th>
                <th class="py-3 px-4 text-center">Orders</th>
                <th class="py-3 px-4 text-center">Revenue</th>
                <th class="py-3 px-4 text-center">Stock Health</th>
                <th class="py-3 px-5 text-right">Open Transfers</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="bt in data.branches_today" :key="bt.branch_id" class="hover:bg-[#FCFAF7] transition">
                <td class="py-3.5 px-5 font-medium text-[#382C24]">{{ bt.branch }}</td>
                <td class="py-3.5 px-4 text-center text-[#55463C]">{{ bt.orders }}</td>
                <td class="py-3.5 px-4 text-center font-bold text-[#241710]">ETB {{ Number(bt.revenue).toLocaleString() }}</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': bt.stock_health === 'Healthy',
                      'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': bt.stock_health.includes('low') || bt.stock_health.includes('sold')
                    }"
                  >
                    {{ bt.stock_health }}
                  </span>
                </td>
                <td class="py-3.5 px-5 text-right text-xs font-medium text-[#65554B]">
                  {{ bt.open_transfers }}
                </td>
              </tr>
            </tbody>
          </table>
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
});

const loadOverview = async () => {
  try {
    const params = {};
    if (selectedBranch.value && selectedBranch.value !== 'all') {
      params.branch_id = selectedBranch.value;
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
