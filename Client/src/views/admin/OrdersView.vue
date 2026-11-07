<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-2">
        <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">
          {{ isBranchScoped ? `${userBranchName} Branch Orders` : 'All Orders' }}
        </h2>
        <span
          v-if="isBranchScoped"
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FAF3EC] text-[#BA5420] border border-[#EACBB8]"
        >
          📍 {{ userBranchName }} Only
        </span>
      </div>
      <button
        @click="exportCSV"
        class="w-full sm:w-auto px-4 py-2 text-sm font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-sm cursor-pointer"
      >
        Export CSV
      </button>
    </div>

    <!-- Alert Banner -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-lg text-xs text-[#B91C1C] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#B91C1C] font-bold ml-2">&times;</button>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1">
      <button
        @click="setStatusFilter('All')"
        class="px-4 py-1.5 text-xs font-semibold rounded-full transition"
        :class="activeStatus === 'All' ? 'bg-[#984518] text-white shadow-sm' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#F2EAE0]'"
      >
        All
      </button>
      <button
        @click="setStatusFilter('Pending')"
        class="px-4 py-1.5 text-xs font-semibold rounded-full transition flex items-center space-x-1"
        :class="activeStatus === 'Pending' ? 'bg-[#984518] text-white shadow-sm' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#F2EAE0]'"
      >
        <span>Pending</span>
        <span class="opacity-80">({{ pendingCount }})</span>
      </button>
      <button
        v-for="st in ['Confirmed', 'Preparing', 'Ready', 'Out for delivery', 'Completed', 'Cancelled']"
        :key="st"
        @click="setStatusFilter(st)"
        class="px-4 py-1.5 text-xs font-semibold rounded-full transition"
        :class="activeStatus === st ? 'bg-[#984518] text-white shadow-sm' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#F2EAE0]'"
      >
        {{ st }}
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-white p-3.5 border border-[#E9E0D4] rounded-xl shadow-sm">
      <div>
        <label class="block text-[11px] font-semibold text-[#8C7D73] uppercase mb-1">Search</label>
        <input
          v-model="filters.search"
          @input="applyFilters"
          type="text"
          placeholder="Order number, customer or phone"
          class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-3 py-1.5 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
        />
      </div>

      <div>
        <label class="block text-[11px] font-semibold text-[#8C7D73] uppercase mb-1">Branch</label>
        <div
          v-if="isBranchScoped"
          class="w-full bg-[#FAF3EC] border border-[#EACBB8] rounded-lg px-3 py-1.5 text-xs font-bold text-[#BA5420] flex items-center space-x-1.5 truncate"
        >
          <span>📍</span>
          <span>{{ userBranchName }} Branch</span>
        </div>
        <select
          v-else
          v-model="filters.branch_id"
          @change="applyFilters"
          class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-3 py-1.5 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] cursor-pointer"
        >
          <option value="all">All branches</option>
          <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
            {{ b.branch_name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-[11px] font-semibold text-[#8C7D73] uppercase mb-1">Method</label>
        <select
          v-model="filters.method"
          @change="applyFilters"
          class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-3 py-1.5 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
        >
          <option value="all">Delivery and pickup</option>
          <option value="Delivery">Delivery only</option>
          <option value="Pickup">Pickup only</option>
        </select>
      </div>

      <div>
        <label class="block text-[11px] font-semibold text-[#8C7D73] uppercase mb-1">Date</label>
        <select
          v-model="filters.date"
          @change="applyFilters"
          class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-3 py-1.5 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
        >
          <option value="today">Today</option>
          <option value="all">All time</option>
        </select>
      </div>
    </div>

    <!-- Main Content Grid: Table + Side Order Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Orders Table (Col span 2) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-4">Order</th>
                <th class="py-3 px-3">Branch</th>
                <th class="py-3 px-3">Customer</th>
                <th class="py-3 px-3">Method</th>
                <th class="py-3 px-3">Payment</th>
                <th class="py-3 px-3">Total</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-4 text-right">Placed</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-if="orders.length === 0">
                <td colspan="8" class="py-8 text-center text-xs text-[#8A7B71]">No orders found for this view.</td>
              </tr>
              <tr
                v-for="ord in orders"
                :key="ord.order_id"
                @click="selectOrder(ord)"
                class="hover:bg-[#FCFAF7] transition cursor-pointer"
                :class="selectedOrder?.order_id === ord.order_id ? 'bg-[#FAF3EC]' : ''"
              >
                <td class="py-3 px-4 font-mono text-xs font-semibold text-[#382C24]">{{ ord.order_number }}</td>
                <td class="py-3 px-3 text-xs text-[#4A3B32]">{{ ord.branch?.branch_name }}</td>
                <td class="py-3 px-3 text-xs text-[#5A4B41]">
                  <div class="font-medium text-[#241710]">{{ ord.customer_name || ord.user?.full_name || 'Customer' }}</div>
                  <div v-if="ord.customer_phone || ord.user?.phone" class="text-[10px] text-[#8A7B71]">{{ ord.customer_phone || ord.user?.phone }}</div>
                </td>
                <td class="py-3 px-3 text-xs text-[#6A5A50]">{{ ord.fulfillment_method }}</td>
                <td class="py-3 px-3 text-xs text-[#6A5A50]">
                  {{ ord.payment ? `${ord.payment.payment_method} · ${ord.payment.payment_status}` : '—' }}
                </td>
                <td class="py-3 px-3 text-xs font-semibold text-[#241710]">
                  ETB {{ Number(ord.total_amount).toLocaleString() }}
                </td>
                <td class="py-3 px-3 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full"
                    :class="{
                      'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': ord.order_status === 'Pending',
                      'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': ord.order_status === 'Confirmed' || ord.order_status === 'Completed',
                      'bg-[#EBF3FB] text-[#1565C0] border border-[#BBDEFB]': ord.order_status === 'Preparing' || ord.order_status === 'Ready' || ord.order_status === 'Out for delivery',
                      'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2]': ord.order_status === 'Cancelled'
                    }"
                  >
                    {{ ord.order_status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right text-xs text-[#8A7B71]">
                  {{ formatTime(ord.placed_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Side Panel: Order Details -->
      <div v-if="selectedOrder" class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">
              {{ selectedOrder.order_number }} · {{ selectedOrder.branch?.branch_name }}
            </h3>
            <p class="text-[11px] text-[#8A7B71] leading-relaxed mt-1">
              {{ selectedOrder.fulfillment_method }} · {{ selectedOrder.payment?.payment_method }} · {{ selectedOrder.payment?.payment_status }}
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
            👤 {{ selectedOrder.customer_name || selectedOrder.user?.full_name || 'Customer' }}
          </div>
          <div v-if="selectedOrder.customer_phone || selectedOrder.user?.phone" class="text-[#6A5A50] text-[11px]">
            📞 {{ selectedOrder.customer_phone || selectedOrder.user?.phone }}
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
            <span class="text-[#382C24] font-medium">{{ item.product_name }}</span>
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
const isBranchScoped = computed(() => ['Branch Admin', 'Branch Staff'].includes(authStore.user?.role));
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
});

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
