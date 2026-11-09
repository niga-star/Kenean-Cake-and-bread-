<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-2">
        <h2 class="dash-title text-xl sm:text-2xl">
          {{ isBranchScoped ? `${userBranchName} Branch Orders` : 'All Orders' }}
        </h2>
        <span
          v-if="isBranchScoped"
          class="dash-badge-pending"
        >
          📍 {{ userBranchName }} Only
        </span>
      </div>
      <div class="flex flex-wrap items-center gap-2.5">
        <router-link
          to="/admin/staff-reports"
          class="dash-btn-secondary text-xs sm:text-sm"
        >
          <span>📋</span>
          <span>Submit Order Report</span>
        </router-link>
        <button
          @click="exportCSV"
          class="dash-btn-secondary text-xs sm:text-sm"
        >
          Export CSV
        </button>
      </div>
    </div>

    <!-- Alert Banner -->
    <div v-if="successMsg" class="p-3 bg-[#EDF7ED] border border-[#C8E6C9] rounded-xl text-xs text-[#2E7D32] flex items-center justify-between font-semibold">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#2E7D32] font-bold ml-2 cursor-pointer">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FDEAEA] border border-[#FFCDD2] rounded-xl text-xs text-[#C62828] flex items-center justify-between font-semibold">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#C62828] font-bold ml-2 cursor-pointer">&times;</button>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1">
      <button
        @click="setStatusFilter('All')"
        class="transition cursor-pointer"
        :class="activeStatus === 'All' ? 'dash-pill-active' : 'dash-pill-inactive'"
      >
        All
      </button>
      <button
        @click="setStatusFilter('Pending')"
        class="transition cursor-pointer flex items-center space-x-1"
        :class="activeStatus === 'Pending' ? 'dash-pill-active' : 'dash-pill-inactive'"
      >
        <span>Pending</span>
        <span class="opacity-80">({{ pendingCount }})</span>
      </button>
      <button
        v-for="st in ['Confirmed', 'Preparing', 'Ready', 'Out for delivery', 'Completed', 'Cancelled']"
        :key="st"
        @click="setStatusFilter(st)"
        class="transition cursor-pointer"
        :class="activeStatus === st ? 'dash-pill-active' : 'dash-pill-inactive'"
      >
        {{ st }}
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 dash-card p-4">
      <div>
        <label class="dash-label block mb-1">Search</label>
        <input
          v-model="filters.search"
          @input="applyFilters"
          type="text"
          placeholder="Order number, customer or phone"
          class="dash-input"
        />
      </div>

      <div>
        <label class="dash-label block mb-1">Branch</label>
        <div
          v-if="isBranchScoped"
          class="dash-badge-neutral w-full py-2 px-3 text-xs justify-start truncate"
        >
          <span>📍</span>
          <span>{{ userBranchName }} Branch</span>
        </div>
        <select
          v-else
          v-model="filters.branch_id"
          @change="applyFilters"
          class="dash-select"
        >
          <option value="all">All branches</option>
          <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
            {{ b.branch_name }}
          </option>
        </select>
      </div>

      <div>
        <label class="dash-label block mb-1">Method</label>
        <select
          v-model="filters.method"
          @change="applyFilters"
          class="dash-select"
        >
          <option value="all">Delivery and pickup</option>
          <option value="Delivery">Delivery only</option>
          <option value="Pickup">Pickup only</option>
        </select>
      </div>

      <div>
        <label class="dash-label block mb-1">Date Filter</label>
        <select
          v-model="filters.date"
          @change="applyFilters"
          class="dash-select"
        >
          <option value="all">All Orders</option>
          <option value="today">Today</option>
          <option value="this_week">This Week</option>
          <option value="this_month">This Month</option>
          <option value="custom">Custom Date Range</option>
        </select>
      </div>

      <!-- Custom Date Range Pickers (Step 6) -->
      <div v-if="filters.date === 'custom'" class="sm:col-span-4 flex flex-wrap items-center gap-3 pt-2.5 border-t border-[#EFE8DF] bg-[#FAF7F2] p-3 rounded-lg">
        <div class="flex items-center space-x-2">
          <label class="text-xs font-semibold text-[#65554B]">From:</label>
          <input
            v-model="filters.start_date"
            @change="applyFilters"
            type="date"
            class="bg-white border border-[#DDD2C5] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
          />
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-xs font-semibold text-[#65554B]">To:</label>
          <input
            v-model="filters.end_date"
            @change="applyFilters"
            type="date"
            class="bg-white border border-[#DDD2C5] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
          />
        </div>
        <button
          type="button"
          @click="clearCustomDates"
          class="text-xs font-semibold text-[#BA5420] hover:underline cursor-pointer"
        >
          Reset Custom Dates
        </button>
      </div>
    </div>

    <!-- Main Content Grid: Table + Side Order Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Orders Table (Col span 2) -->
      <div class="lg:col-span-2 dash-card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="dash-table min-w-[640px]">
            <thead>
              <tr>
                <th class="dash-th">Order</th>
                <th class="dash-th">Branch</th>
                <th class="dash-th">Customer</th>
                <th class="dash-th">Method</th>
                <th class="dash-th">Payment</th>
                <th class="dash-th">Total</th>
                <th class="dash-th text-center">Status</th>
                <th class="dash-th text-right">Placed</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F4EFEA]">
              <tr v-if="orders.length === 0">
                <td colspan="8" class="dash-td text-center py-8 text-[#7D6E64]">No orders found for this view.</td>
              </tr>
              <tr
                v-for="ord in orders"
                :key="ord.order_id"
                @click="selectOrder(ord)"
                class="dash-tr cursor-pointer"
                :class="selectedOrder?.order_id === ord.order_id ? 'bg-[#FAF7F2]' : ''"
              >
                <td class="dash-td font-mono font-bold text-[#2B1810]">{{ ord.order_number }}</td>
                <td class="dash-td text-[#4A3A30]">{{ ord.branch?.branch_name }}</td>
                <td class="dash-td">
                  <div class="font-bold text-[#2B1810]">{{ getCustomerDisplayName(ord) }}</div>
                  <div v-if="getCustomerPhone(ord)" class="text-[11px] text-[#7D6E64]">{{ getCustomerPhone(ord) }}</div>
                </td>
                <td class="dash-td text-[#4A3A30]">{{ ord.fulfillment_method }}</td>
                <td class="dash-td text-[#4A3A30]">
                  {{ ord.payment ? `${ord.payment.payment_method} · ${ord.payment.payment_status}` : '—' }}
                </td>
                <td class="dash-td font-bold font-mono text-[#2B1810]">
                  ETB {{ Number(ord.total_amount).toLocaleString() }}
                </td>
                <td class="dash-td text-center">
                  <span
                    class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block"
                    :class="{
                      'dash-badge-pending': ord.order_status === 'Pending',
                      'dash-badge-preparing': ord.order_status === 'Preparing',
                      'dash-badge-delivery': ord.order_status === 'Ready' || ord.order_status === 'Out for delivery',
                      'dash-badge-delivered': ord.order_status === 'Confirmed' || ord.order_status === 'Completed',
                      'dash-badge-danger': ord.order_status === 'Cancelled'
                    }"
                  >
                    {{ ord.order_status }}
                  </span>
                </td>
                <td class="dash-td text-right text-[11px] font-mono text-[#7D6E64]">
                  {{ formatTime(ord.placed_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Side Panel: Order Details -->
      <div v-if="selectedOrder" class="dash-card p-6 space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="dash-section-title text-base sm:text-lg">
              {{ getCustomerDisplayName(selectedOrder) }} – Order {{ selectedOrder.order_number }}
            </h3>
            <p class="text-[11px] text-[#8A7B71] leading-relaxed mt-1">
              {{ selectedOrder.branch?.branch_name }} · {{ selectedOrder.fulfillment_method }} · {{ selectedOrder.payment?.payment_method }} · {{ selectedOrder.payment?.payment_status }}
            </p>
          </div>

          <!-- Super Admin Reassign Branch Button -->
          <button
            v-if="isSuperAdmin && selectedOrder.order_status !== 'Completed' && selectedOrder.order_status !== 'Cancelled'"
            @click="openReassignModal"
            class="text-[11px] text-[#BA5420] font-semibold hover:underline"
          >
            Change branch
          </button>
        </div>

        <!-- Customer Contact Details -->
        <div class="bg-[#FAF7F5] p-3 rounded-xl border border-[#EFE8DF] space-y-1 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold tracking-wider text-[#8A7B71]">Customer Contact</span>
            <span class="text-[10px] px-2 py-0.2 bg-[#387A54]/10 text-[#387A54] rounded font-bold">Verified Order</span>
          </div>
          <div class="font-bold text-[#2A1D15]">
            👤 {{ getCustomerDisplayName(selectedOrder) }}
          </div>
          <div v-if="getCustomerPhone(selectedOrder)" class="text-[#6A5A50] text-[11px]">
            📞 {{ getCustomerPhone(selectedOrder) }}
          </div>
          <div v-if="selectedOrder.user?.email" class="text-[#7D6E64] text-[11px]">
            ✉️ {{ selectedOrder.user.email }}
          </div>
          <div v-if="selectedOrder.address?.street || selectedOrder.delivery_address" class="text-[#6A5A50] text-[11px]">
            📍 {{ selectedOrder.address?.street || selectedOrder.delivery_address }}
          </div>
        </div>

        <div class="text-[11px] bg-[#FCFAF7] border border-[#EFE8DF] p-2.5 rounded-lg text-[#7A6B60] leading-tight">
          * Branch assignment is mandatory and cannot be changed after payment without Super Admin action.
        </div>

        <!-- Items Table -->
        <div class="border-t border-b border-[#EFE8DF] py-3 space-y-2">
          <div class="flex items-center justify-between text-[11px] font-bold text-[#88786B] uppercase">
            <span>Item</span>
            <div class="flex space-x-6">
              <span>Qty</span>
              <span>Price</span>
            </div>
          </div>

          <div
            v-for="item in selectedOrder.items"
            :key="item.order_item_id"
            class="flex items-center justify-between text-xs py-1"
          >
            <span class="text-[#382C24] font-product font-bold text-xs sm:text-sm">{{ item.product_name }}</span>
            <div class="flex items-center space-x-6 text-right">
              <span class="text-[#6A5A50] w-6 text-center">{{ item.quantity }}</span>
              <span class="font-semibold text-[#241710] w-16">ETB {{ Number(item.subtotal).toLocaleString() }}</span>
            </div>
          </div>

          <div v-if="Number(selectedOrder.delivery_fee) > 0" class="flex items-center justify-between text-xs py-1 text-[#6A5A50]">
            <span>Delivery Fee</span>
            <span class="font-semibold text-[#241710]">ETB {{ Number(selectedOrder.delivery_fee).toLocaleString() }}</span>
          </div>

          <div class="flex items-center justify-between text-sm font-bold text-[#241710] pt-2 border-t border-[#F0E9DF]">
            <span>Total</span>
            <span class="text-[#BA5420]">ETB {{ Number(selectedOrder.total_amount).toLocaleString() }}</span>
          </div>
        </div>

        <!-- Status History Timeline -->
        <div class="space-y-2">
          <div class="text-xs font-bold text-[#2A1D15]">Status history</div>
          <div class="space-y-1.5 text-xs text-[#7A6B60] max-h-40 overflow-y-auto">
            <div
              v-for="hist in (selectedOrder.status_history || selectedOrder.statusHistory || [])"
              :key="hist.history_id"
              class="flex items-start space-x-2"
            >
              <span class="text-[#BA5420]">•</span>
              <div>
                <span class="font-medium text-[#382C24]">{{ hist.new_status }}</span>
                <span> → {{ hist.note || 'status updated' }}, {{ formatTime(hist.changed_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons: Branch Manager & Staff Order Confirmation Lifecycle -->
        <div class="pt-3 flex space-x-3">
          <button
            v-if="selectedOrder.order_status === 'Pending'"
            @click="updateOrderStatus(selectedOrder, 'Confirmed')"
            class="flex-1 py-2.5 px-3 bg-[#BA5420] hover:bg-[#9E4314] text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>✓</span>
            <span>Confirm Order</span>
          </button>
          <button
            v-else-if="selectedOrder.order_status === 'Confirmed'"
            @click="updateOrderStatus(selectedOrder, 'Preparing')"
            class="flex-1 py-2.5 px-3 bg-[#BA5420] hover:bg-[#9E4314] text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>🍳</span>
            <span>Start Preparing</span>
          </button>
          <button
            v-else-if="selectedOrder.order_status === 'Preparing'"
            @click="updateOrderStatus(selectedOrder, 'Ready')"
            class="flex-1 py-2.5 px-3 bg-[#BA5420] hover:bg-[#9E4314] text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>🎂</span>
            <span>Mark Ready</span>
          </button>
          <button
            v-else-if="selectedOrder.order_status === 'Ready'"
            @click="updateOrderStatus(selectedOrder, selectedOrder.fulfillment_method === 'Delivery' ? 'Out for delivery' : 'Completed')"
            class="flex-1 py-2.5 px-3 bg-[#2E7D32] hover:bg-[#256628] text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>{{ selectedOrder.fulfillment_method === 'Delivery' ? '🚚' : '✓' }}</span>
            <span>{{ selectedOrder.fulfillment_method === 'Delivery' ? 'Dispatch for Delivery' : 'Complete & Handover' }}</span>
          </button>
          <button
            v-else-if="selectedOrder.order_status === 'Out for delivery'"
            @click="updateOrderStatus(selectedOrder, 'Completed')"
            class="flex-1 py-2.5 px-3 bg-[#2E7D32] hover:bg-[#256628] text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>✓</span>
            <span>Mark Delivered</span>
          </button>

          <button
            v-if="selectedOrder.order_status !== 'Cancelled' && selectedOrder.order_status !== 'Completed'"
            @click="cancelOrder(selectedOrder)"
            class="px-4 py-2 bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#6A5A50] text-xs font-semibold rounded-xl transition cursor-pointer"
          >
            Cancel Order
          </button>
        </div>
      </div>
    </div>

    <!-- Reassign Branch Modal (Super Admin Action) -->
    <div v-if="reassignModalOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center p-3 sm:p-4 z-50">
      <div class="bg-white border border-[#E7DFD5] rounded-xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-base font-serif font-bold text-[#2A1D15]">
          Reassign Order Branch: {{ selectedOrder?.order_number }}
        </h3>
        <p class="text-xs text-[#7A6B60]">
          Currently fulfilling from {{ selectedOrder?.branch?.branch_name }}. Stock reservations will be transferred inside a database transaction.
        </p>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">New Branch</label>
            <select
              v-model="reassignForm.new_branch_id"
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7]"
            >
              <option
                v-for="b in branches"
                :key="b.branch_id"
                :value="b.branch_id"
                :disabled="b.branch_id === selectedOrder?.branch_id"
              >
                {{ b.branch_name }} ({{ b.branch_code }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Reason for Reassignment</label>
            <textarea
              v-model="reassignForm.reason"
              rows="3"
              placeholder="e.g. Original branch experienced oven breakdown; customer approved fulfillment from Bole."
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7]"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-2">
          <button
            @click="reassignModalOpen = false"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg"
          >
            Cancel
          </button>
          <button
            @click="submitReassignBranch"
            class="px-4 py-2 text-xs font-medium bg-[#BA5420] text-white hover:bg-[#9E4314] rounded-lg shadow-sm"
          >
            Confirm Reassignment
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
const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isBranchScoped = computed(() => authStore.user?.role !== 'Super Admin');
const userBranchId = computed(() => authStore.user?.branch_id || authStore.user?.branch?.branch_id);
const userBranchName = computed(() => authStore.user?.branch?.branch_name || 'My');

const orders = ref([]);
const branches = ref([]);
const pendingCount = ref(0);
const activeStatus = ref('All');
const selectedOrder = ref(null);
const successMsg = ref('');
const errorMsg = ref('');

const reassignModalOpen = ref(false);
const reassignForm = ref({
  new_branch_id: null,
  reason: '',
});

const filters = ref({
  search: '',
  branch_id: 'all',
  method: 'all',
  date: 'all',
  start_date: '',
  end_date: '',
});

function getCustomerDisplayName(ord) {
  if (!ord) return 'Customer';
  if (ord.user && ord.user.role === 'Customer' && ord.user.full_name) {
    return ord.user.full_name;
  }
  const name = ord.customer_name || ord.user?.full_name;
  if (name && !name.toLowerCase().includes('abebe')) {
    return name;
  }
  return ord.user?.full_name || ord.customer_name || 'Customer';
}

function getCustomerPhone(ord) {
  if (!ord) return '';
  return ord.customer_phone || ord.user?.phone || '';
}

const clearCustomDates = () => {
  filters.value.start_date = '';
  filters.value.end_date = '';
  filters.value.date = 'all';
  loadOrders();
};

const loadOrders = async () => {
  try {
    const branchParam = isBranchScoped.value ? userBranchId.value : filters.value.branch_id;
    const res = await api.get('/admin/orders', {
      params: {
        status: activeStatus.value,
        branch_id: branchParam,
        method: filters.value.method,
        search: filters.value.search,
        date: filters.value.date,
        start_date: filters.value.start_date,
        end_date: filters.value.end_date,
      }
    });
    orders.value = res.data.orders;
    pendingCount.value = res.data.pending_count;

    if (!selectedOrder.value && orders.value.length > 0) {
      selectedOrder.value = orders.value[0];
    } else if (selectedOrder.value) {
      selectedOrder.value = orders.value.find((o) => o.order_id === selectedOrder.value.order_id) || orders.value[0];
    }
  } catch (err) {
    console.error('Failed to load orders', err);
    errorMsg.value = 'Failed to load orders.';
  }
};

const setStatusFilter = (st) => {
  activeStatus.value = st;
  loadOrders();
};

const applyFilters = () => {
  loadOrders();
};

const selectOrder = (ord) => {
  selectedOrder.value = ord;
};

const updateOrderStatus = async (ord, newStatus) => {
  try {
    const res = await api.patch(`/admin/orders/${ord.order_id}/status`, {
      order_status: newStatus,
      note: `Updated to ${newStatus} by admin`,
    });
    selectedOrder.value = res.data;
    successMsg.value = `Order ${ord.order_number} status updated to ${newStatus}!`;
    await loadOrders();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update order status');
  }
};

const cancelOrder = async (ord) => {
  const reason = prompt(`Enter reason for cancelling order ${ord.order_number}:`, 'Cancelled by branch manager with stock release');
  if (reason === null) return;
  try {
    const res = await api.post(`/orders/${ord.order_id}/cancel`, { reason });
    selectedOrder.value = res.data.order;
    successMsg.value = `Order ${ord.order_number} cancelled and reservation released.`;
    await loadOrders();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to cancel order');
  }
};

const openReassignModal = () => {
  if (!selectedOrder.value) return;
  reassignForm.value.new_branch_id = branches.value.find((b) => b.branch_id !== selectedOrder.value.branch_id)?.branch_id || null;
  reassignForm.value.reason = 'Customer agreed to branch switch due to immediate fulfillment availability.';
  reassignModalOpen.value = true;
};

const submitReassignBranch = async () => {
  if (!selectedOrder.value || !reassignForm.value.new_branch_id) return;
  if (!reassignForm.value.reason.trim()) {
    alert('Please provide a reason for the audit trail.');
    return;
  }

  try {
    const res = await api.patch(`/admin/orders/${selectedOrder.value.order_id}/branch`, reassignForm.value);
    selectedOrder.value = res.data;
    successMsg.value = `Order ${selectedOrder.value.order_number} reassigned to ${res.data.branch?.branch_name}!`;
    reassignModalOpen.value = false;
    await loadOrders();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to reassign branch');
  }
};

const formatTime = (dt) => {
  if (!dt) return '—';
  try {
    const d = new Date(dt);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
  } catch {
    return '—';
  }
};

const exportCSV = () => {
  const headers = ['Order', 'Branch', 'Customer', 'Method', 'Total', 'Status', 'Placed'];
  const rows = orders.value.map(o => [
    `"${o.order_number}"`,
    `"${o.branch?.branch_name}"`,
    `"${o.customer_name || o.user?.full_name || 'Customer'}"`,
    `"${o.fulfillment_method}"`,
    o.total_amount,
    `"${o.order_status}"`,
    `"${o.placed_at}"`
  ]);
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `orders.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(async () => {
  if (isBranchScoped.value && userBranchId.value) {
    filters.value.branch_id = userBranchId.value;
  }
  filters.value.date = 'all';
  try {
    const bRes = await api.get('/branches');
    branches.value = bRes.data;
  } catch (e) {}
  loadOrders();
});
</script>
