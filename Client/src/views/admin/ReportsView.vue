<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-12 font-sans">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-[#EAE1D5] pb-4">
      <div>
        <div class="flex items-center space-x-2">
          <span class="text-2xl">📊</span>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-serif font-black text-[#2A1D15]">
            Reports &amp; Analytics
          </h1>
        </div>
        <p class="text-xs text-[#7A6B60] mt-1">
          Multi-branch performance metrics, distribution tracking, inventory levels, and financial reconciliation.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
        <button
          @click="sendDailySummary"
          :disabled="sendingSummary"
          class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-bold bg-[#FAF7F5] border border-[#D8CDC0] hover:bg-[#FFF1F2] text-[#881337] rounded-xl transition flex items-center justify-center space-x-1.5 shadow-2xs cursor-pointer"
          title="Send daily sales and low-stock email summary to owner"
        >
          <span>✉️</span>
          <span>{{ sendingSummary ? 'Dispatching...' : 'Email Summary' }}</span>
        </button>

        <button
          @click="exportCSV"
          class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-bold bg-white border border-[#D8CDC0] hover:bg-[#FAF7F5] text-[#4A3B32] rounded-xl transition flex items-center justify-center space-x-1.5 shadow-2xs cursor-pointer"
        >
          <span>📥</span>
          <span>Export CSV</span>
        </button>

        <button
          @click="exportPDF"
          class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-bold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-xl transition flex items-center justify-center space-x-1.5 shadow-xs cursor-pointer"
        >
          <span>🖨️</span>
          <span>Print / PDF</span>
        </button>
      </div>
    </div>

    <!-- Scoped Branch Notice if Branch Admin -->
    <div
      v-if="isBranchAdmin"
      class="p-3 bg-[#FFFBEB] border border-[#FDE68A] rounded-2xl flex items-center justify-between text-xs text-[#92400E]"
    >
      <div class="flex items-center space-x-2">
        <span>🔒</span>
        <span>Branch Scoped View: You are viewing reports strictly for <strong>{{ userBranchName }} Branch</strong>. Central comparisons are restricted to Super Admin.</span>
      </div>
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

    <!-- Filters Row -->
    <div class="bg-white p-4 border border-[#E9E0D4] rounded-2xl shadow-2xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      <!-- Branch Filter -->
      <div>
        <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Branch</label>
        <select
          v-model="filters.branch_id"
          :disabled="isBranchAdmin"
          @change="reloadAll"
          class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] disabled:bg-gray-100 disabled:cursor-not-allowed font-medium"
        >
          <option v-if="!isBranchAdmin" value="all">All active branches</option>
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
          @change="reloadAll"
          type="date"
          class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
        />
      </div>

      <!-- Date To -->
      <div>
        <label class="block text-[11px] font-bold text-[#8C7D73] uppercase mb-1">Date To</label>
        <input
          v-model="filters.to_date"
          @change="reloadAll"
          type="date"
          class="w-full bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
        />
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
                <td class="py-3.5 px-5 font-bold text-[#38121A]">{{ r.product }}</td>
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
                <td class="py-3.5 px-5 font-medium text-[#261814]">{{ r.product }}</td>
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

            <!-- 9. Audit Rows -->
            <template v-else-if="activeTabId === 'audit'">
              <tr v-for="(r, idx) in reportRows" :key="idx" class="hover:bg-[#FFF1F2]/40 transition">
                <td class="py-3.5 px-5 font-bold text-[#38121A]">{{ r.user }}</td>
                <td class="py-3.5 px-5 font-semibold text-[#881337]">{{ r.action }}</td>
                <td class="py-3.5 px-5 text-[#7A6B60] font-mono text-[11px]">{{ r.record }}</td>
                <td class="py-3.5 px-5 text-[#9CA3AF] text-[11px]">{{ r.time }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';

// Report tabs definition per Section 10 specs
const reportTabs = [
  { id: 'branch_performance', label: 'Branch performance', icon: '🏢', description: 'Which branch has the highest revenue, most orders, top-selling cakes and bread?' },
  { id: 'product_performance', label: 'Product performance', icon: '🎂', description: 'Which products sell best, and where?' },
  { id: 'distribution', label: 'Distribution', icon: '🚚', description: 'How much was sent, received, missing, damaged, still in transit?' },
  { id: 'inventory', label: 'Inventory', icon: '📦', description: 'What is on hand, reserved and available per branch, and what needs replenishment?' },
  { id: 'waste', label: 'Waste', icon: '🗑️', description: 'How much stock was damaged or expired, and why?' },
  { id: 'discrepancy', label: 'Discrepancy', icon: '⚠️', description: 'Which transfers had differences and who resolved them?' },
  { id: 'delivery', label: 'Delivery', icon: '🛵', description: 'How are deliveries and pickups split, and how long do they take?' },
  { id: 'payments', label: 'Payments', icon: '💳', description: 'Revenue by method, failures, refunds.' },
  { id: 'audit', label: 'Audit', icon: '🛡️', description: 'Who performed sensitive actions?' },
];

const activeTabId = ref('branch_performance');
const branches = ref([]);
const categories = ref([]);
const loading = ref(false);
const sendingSummary = ref(false);

const rawUser = localStorage.getItem('user');
const currentUser = rawUser ? JSON.parse(rawUser) : null;
const isBranchAdmin = computed(() => currentUser?.role === 'Branch Admin');
const userBranchName = computed(() => currentUser?.branch_name || 'Bole');

const filters = ref({
  branch_id: isBranchAdmin.value ? (currentUser?.branch_id || 2) : 'all',
  category_id: 'all',
  product_id: 'all',
  from_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  to_date: new Date().toISOString().split('T')[0],
});

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
  return reportTabs.find(t => t.id === activeTabId.value);
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
  } catch (err) {
    console.error(`Failed to load report data for ${activeTabId.value}`, err);
    reportColumns.value = [];
    reportRows.value = [];
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

  await reloadAll();
});
</script>
