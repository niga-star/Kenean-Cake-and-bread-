<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins print:hidden">
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
        <span
          v-if="isManager"
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300"
        >
          👁️ Read-Only Supervision
        </span>
      </div>
      <div class="flex flex-wrap items-center gap-2.5">
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

          <div class="flex items-center space-x-2">
            <!-- Staff POS Print Receipt Button -->
            <button
              @click="printReceipt(selectedOrder)"
              class="px-3 py-1.5 bg-white border border-[#D8CDC0] hover:border-[#BA5420] hover:text-[#BA5420] text-xs font-bold text-[#382C24] rounded-xl transition shadow-2xs flex items-center space-x-1.5 cursor-pointer"
              title="Print Customer Order Receipt"
            >
              <span>🖨️</span>
              <span>Print Receipt</span>
            </button>

            <!-- Super Admin Reassign Branch Button -->
            <button
              v-if="isSuperAdmin && selectedOrder.order_status !== 'Completed' && selectedOrder.order_status !== 'Cancelled'"
              @click="openReassignModal"
              class="text-[11px] text-[#BA5420] font-semibold hover:underline"
            >
              Change branch
            </button>
          </div>
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

        <!-- Action Buttons: Branch Seller & Staff Order Confirmation Lifecycle (v4.1 Section 1 & 3: Managers are Read-Only) -->
        <div v-if="isManager" class="pt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 text-center font-medium">
          👁️ <strong>Branch Manager Supervisory Mode (Read-Only)</strong>: Status changes and customer handovers are performed by Branch Sellers and Staff.
        </div>
        <div v-else class="pt-3 flex space-x-3">
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

  <!-- Dedicated Staff POS / Counter Order Receipt (Print Media Only) -->
  <div
    v-if="selectedOrder"
    id="order-receipt-container"
    class="hidden print:block bg-white text-[#1A1412] font-sans antialiased"
    aria-label="SweetBakes Official Order Receipt"
  >
    <div class="receipt-card max-w-[760px] mx-auto p-5 sm:p-6 bg-white border border-[#D4C8BE] rounded-2xl">
      
      <!-- 1. Header: Bakery Identity & Receipt Document Title -->
      <div class="flex items-start justify-between border-b-2 border-[#3D251E] pb-3 mb-3">
        <div class="space-y-0.5">
          <div class="flex items-center space-x-2">
            <span class="text-2xl leading-none">🧁</span>
            <span class="font-serif font-black text-2xl tracking-tight text-[#3D251E]">SweetBakes</span>
            <span class="text-xs uppercase font-sans font-black tracking-widest text-[#BA5420] ml-1">Bakery</span>
          </div>
          <p class="text-[11px] text-[#5C4A3E] font-medium tracking-wide">
            Artisan Cakes &middot; Fresh Pastries &middot; Handcrafted Daily
          </p>
        </div>

        <div class="text-right space-y-1">
          <div class="inline-block border-2 border-[#3D251E] px-3 py-0.5 rounded text-xs font-black tracking-widest text-[#3D251E] uppercase">
            ORDER RECEIPT
          </div>
          <div class="text-[11px] font-bold text-[#3D251E]">
            Status: 
            <span 
              class="uppercase tracking-wider px-2 py-0.5 rounded text-[10px] font-bold inline-block"
              :class="{
                'bg-emerald-100 text-emerald-900 border border-emerald-300': ['Confirmed', 'Ready', 'Completed'].includes(selectedOrder.order_status),
                'bg-amber-100 text-amber-900 border border-amber-300': ['Pending', 'Preparing'].includes(selectedOrder.order_status),
                'bg-rose-100 text-rose-900 border border-rose-300': selectedOrder.order_status === 'Cancelled',
              }"
            >
              {{ selectedOrder.order_status || 'Confirmed' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Branch & Order Reference Grid -->
      <div class="grid grid-cols-2 gap-4 text-xs border-b border-[#E6DED3] pb-3 mb-3">
        <div class="space-y-0.5">
          <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Fulfilling Bakery Branch</div>
          <div class="font-bold text-[#3D251E] text-sm">
            {{ selectedOrder.branch?.branch_name || userBranchName }}
            <span v-if="selectedOrder.branch?.branch_type === 'Main'" class="text-[10px] font-normal text-[#BA5420]">(Main HQ)</span>
          </div>
          <div class="text-[#5C4A3E] text-[11px]">
            📍 {{ selectedOrder.branch?.address || 'Addis Ababa, Ethiopia' }}
          </div>
          <div class="text-[#5C4A3E] text-[11px]">
            📞 {{ selectedOrder.branch?.phone || '+251 911 123 456' }} &middot; ✉️ {{ selectedOrder.branch?.email || 'info@sweetbakes.com' }}
          </div>
        </div>

        <div class="space-y-0.5 text-right">
          <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Order Identification</div>
          <div class="font-mono font-black text-base text-[#3D251E]">
            #{{ selectedOrder.order_number }}
          </div>
          <div class="text-[#5C4A3E] text-[11px]">
            <span class="font-semibold text-[#8A796E]">Date Placed:</span> {{ formatTime(selectedOrder.placed_at || selectedOrder.created_at) }}
          </div>
          <div class="text-[#8A796E] text-[10px]">
            Receipt Printed: {{ printTimestamp }}
          </div>
        </div>
      </div>

      <!-- 3. Customer & Fulfillment Details Box -->
      <div class="grid grid-cols-2 gap-4 bg-[#FAF7F2] border border-[#E6DED3] rounded-xl p-3 text-xs mb-3">
        <div class="space-y-0.5">
          <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Customer Details</div>
          <div class="font-bold text-[#3D251E] text-sm">
            {{ getCustomerDisplayName(selectedOrder) }}
          </div>
          <div class="text-[#5C4A3E] text-[11px] font-medium">
            📞 {{ getCustomerPhone(selectedOrder) || 'N/A' }}
          </div>
          <div v-if="selectedOrder.user?.email || selectedOrder.customer_email" class="text-[#5C4A3E] text-[11px] truncate">
            ✉️ {{ selectedOrder.user?.email || selectedOrder.customer_email }}
          </div>
        </div>

        <div class="space-y-0.5">
          <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Fulfillment Mode</div>
          <div class="font-bold text-[#3D251E] text-xs">
            <span v-if="selectedOrder.fulfillment_method === 'Delivery'" class="text-[#BA5420]">
              🚀 Doorstep Courier Delivery
            </span>
            <span v-else class="text-[#047857]">
              🛍️ In-Store Counter Pickup
            </span>
          </div>
          <div class="text-[#5C4A3E] text-[11px] leading-snug pt-0.5">
            <span class="font-semibold text-[#3D251E]">
              {{ selectedOrder.fulfillment_method === 'Pickup' ? 'Pickup Location:' : 'Delivery Address:' }}
            </span>
            <span class="block text-[#5C4A3E] mt-0.5">{{ selectedOrder.address?.street || selectedOrder.delivery_address || 'Addis Ababa, Ethiopia' }}</span>
          </div>
        </div>
      </div>

      <!-- 4. Itemized Items Table -->
      <div class="mb-3">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b-2 border-[#3D251E] text-[10px] font-bold uppercase tracking-wider text-[#3D251E]">
              <th class="py-1.5 px-2 w-8 text-center">#</th>
              <th class="py-1.5 px-2">Item Description</th>
              <th class="py-1.5 px-3 text-right">Unit Price</th>
              <th class="py-1.5 px-3 text-center">Qty</th>
              <th class="py-1.5 px-2 text-right">Line Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E6DED3]">
            <tr 
              v-for="(item, idx) in selectedOrder.items" 
              :key="item.order_item_id || idx"
              class="text-[#3D251E]"
            >
              <td class="py-1.5 px-2 text-center text-[#8A796E] font-mono text-[11px]">{{ idx + 1 }}</td>
              <td class="py-1.5 px-2">
                <div class="font-bold text-[11px]">{{ item.product_name || item.product?.product_name }}</div>
              </td>
              <td class="py-1.5 px-3 text-right font-mono text-[#5C4A3E] text-[11px]">
                ETB {{ Number(item.unit_price || (item.subtotal / (item.quantity || 1)) || 0).toFixed(2) }}
              </td>
              <td class="py-1.5 px-3 text-center font-bold text-[11px]">
                {{ item.quantity }}
              </td>
              <td class="py-1.5 px-2 text-right font-mono font-bold text-[#3D251E] text-[11px]">
                ETB {{ Number(item.subtotal || 0).toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 5. Payment Details & Financial Totals Breakdown -->
      <div class="grid grid-cols-12 gap-3 border-t-2 border-[#3D251E] pt-2.5 mb-3">
        <div class="col-span-7 space-y-1 text-xs text-[#5C4A3E]">
          <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Payment Information</div>
          <div class="flex items-center space-x-1.5 text-[11px]">
            <span class="font-semibold text-[#3D251E]">Method:</span>
            <span>{{ selectedOrder.payment?.payment_method || 'Cash / Counter' }}</span>
          </div>
          <div class="flex items-center space-x-1.5 text-[11px]">
            <span class="font-semibold text-[#3D251E]">Status:</span>
            <span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10px]">
              {{ (selectedOrder.payment?.payment_status || 'Paid').toUpperCase() }} ✓
            </span>
          </div>
          <div v-if="selectedOrder.payment?.transaction_reference" class="flex items-center space-x-1.5 text-[10px]">
            <span class="font-semibold text-[#3D251E]">Ref:</span>
            <span class="font-mono text-[#3D251E]">{{ selectedOrder.payment.transaction_reference }}</span>
          </div>
        </div>

        <div class="col-span-5 space-y-1 text-xs">
          <div class="flex justify-between items-center text-[#5C4A3E] text-[11px]">
            <span>Subtotal:</span>
            <span class="font-mono font-semibold">
              ETB {{ Number(selectedOrder.total_amount - (selectedOrder.delivery_fee || 0)).toFixed(2) }}
            </span>
          </div>

          <div class="flex justify-between items-center text-[#5C4A3E] text-[11px]">
            <span>Delivery Fee:</span>
            <span class="font-mono font-semibold">
              {{ Number(selectedOrder.delivery_fee) > 0 ? `ETB ${Number(selectedOrder.delivery_fee).toFixed(2)}` : 'ETB 0.00 (Free)' }}
            </span>
          </div>

          <div class="border-t border-[#3D251E] pt-1 mt-1 flex justify-between items-center">
            <span class="text-xs font-black text-[#3D251E] uppercase">Grand Total:</span>
            <span class="text-sm font-black font-mono text-[#3D251E]">
              ETB {{ Number(selectedOrder.total_amount).toFixed(2) }}
            </span>
          </div>
        </div>
      </div>

      <!-- 6. Receipt Footer & Thank-You Message -->
      <div class="border-t border-dashed border-[#E6DED3] pt-2.5 text-center space-y-0.5">
        <div class="text-xs font-serif font-bold text-[#3D251E]">
          ❤️ Thank you for choosing SweetBakes Bakery!
        </div>
        <p class="text-[10px] text-[#7A6B60] max-w-lg mx-auto leading-tight">
          We appreciate your business and hope you enjoy our freshly baked delicacies. For inquiries, contact the branch quoting your Order Number.
        </p>
        <div class="text-[9px] text-[#A6978C] uppercase tracking-wider pt-0.5">
          SweetBakes Bakery Management &middot; Addis Ababa, Ethiopia &middot; www.sweetbakes.com
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const authStore = useAuthStore();
const dialog = useDialogStore();
const toast = useToastStore();
const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isManager = computed(() => ['Branch Manager', 'Main Branch Manager', 'Branch Admin'].includes(authStore.user?.role));
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
    const msg = `Order ${ord.order_number} status updated to ${newStatus}!`;
    successMsg.value = msg;
    toast.success(msg);
    await loadOrders();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to update order status';
    toast.error(msg);
  }
};

const cancelOrder = async (ord) => {
  const reason = await dialog.prompt({
    title: `Cancel Order #${ord.order_number}`,
    message: 'Please enter a reason for cancelling this order and releasing its stock reservation:',
    defaultValue: 'Cancelled by branch manager with stock release',
    placeholder: 'Cancellation justification...',
    confirmText: 'Cancel Order',
    variant: 'danger',
  });
  if (reason === null) return;
  try {
    const res = await api.post(`/orders/${ord.order_id}/cancel`, { reason });
    selectedOrder.value = res.data.order;
    const msg = `Order ${ord.order_number} cancelled and reservation released.`;
    successMsg.value = msg;
    toast.success(msg);
    await loadOrders();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to cancel order';
    toast.error(msg);
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
    toast.warning('Please provide a reason for the audit trail.');
    return;
  }

  try {
    const res = await api.patch(`/admin/orders/${selectedOrder.value.order_id}/branch`, reassignForm.value);
    selectedOrder.value = res.data;
    const msg = `Order ${selectedOrder.value.order_number} reassigned to ${res.data.branch?.branch_name}!`;
    successMsg.value = msg;
    toast.success(msg);
    reassignModalOpen.value = false;
    await loadOrders();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to reassign branch';
    toast.error(msg);
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

const printTimestamp = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});

function printReceipt(order) {
  if (order) {
    selectedOrder.value = order;
  }
  const originalTitle = document.title;
  if (selectedOrder.value?.order_number) {
    document.title = `SweetBakes-Receipt-${selectedOrder.value.order_number}`;
  }
  window.print();
  setTimeout(() => {
    document.title = originalTitle;
  }, 1000);
}
</script>

<style>
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm;
  }

  html, body {
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
    min-height: 100% !important;
    background: #ffffff !important;
    color: #1a1412 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Hide admin layout and screen components */
  aside,
  header,
  nav,
  .print\:hidden,
  .no-print {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
  }

  #order-receipt-container {
    display: block !important;
    visibility: visible !important;
    position: static !important;
    width: 100% !important;
    max-width: 760px !important;
    margin: 0 auto !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    background: #ffffff !important;
    color: #1a1412 !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    page-break-after: avoid !important;
    page-break-before: avoid !important;
  }

  #order-receipt-container .receipt-card {
    border: 1px solid #d4c8be !important;
    border-radius: 12px !important;
    box-shadow: none !important;
    background-color: #ffffff !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
}
</style>
