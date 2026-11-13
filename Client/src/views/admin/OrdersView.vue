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
          v-if="canCreatePOSOrder"
          @click="openPOSModal"
          class="px-4 py-2 bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition flex items-center space-x-1.5 cursor-pointer active:scale-95"
        >
          <span>🧾</span>
          <span>New Counter Sale (POS)</span>
        </button>
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

    <!-- Dedicated Staff POS / Counter Sale Modal -->
    <div
      v-if="posModalOpen"
      class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-2 sm:p-4 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]"
    >
      <div
        class="bg-white dark:bg-[#1E120B] rounded-2xl shadow-2xl max-w-5xl w-full max-h-[92vh] flex flex-col border border-gray-200 dark:border-[#382318] overflow-hidden"
      >
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-gray-200 dark:border-[#382318] flex items-center justify-between bg-[#FAF7F5] dark:bg-[#25150E]">
          <div class="flex items-center space-x-2.5">
            <span class="text-xl">🧾</span>
            <div>
              <h3 class="text-base font-bold text-[#2A1810] dark:text-[#FAF3EE]">New Counter Walk-In Sale (POS)</h3>
              <p class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
                Fulfilling at: <strong class="text-[#DE1B54]">{{ currentBranchName }} Branch</strong>
              </p>
            </div>
          </div>
          <button
            @click="posModalOpen = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold p-1 cursor-pointer"
          >
            &times;
          </button>
        </div>

        <!-- Modal Body (Two Column: Catalog Left, Cart & Payment Right) -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-12 gap-5">
          <!-- Left Column: Catalog & Selection (7 cols) -->
          <div class="lg:col-span-7 flex flex-col space-y-3.5">
            <!-- Search & Category Filters -->
            <div class="flex flex-col sm:flex-row gap-2">
              <div class="relative flex-1">
                <input
                  v-model="posSearch"
                  type="text"
                  placeholder="Search products by name..."
                  class="w-full text-xs bg-gray-50 dark:bg-[#170E0A] border border-gray-200 dark:border-[#382318] rounded-xl px-3.5 py-2 text-[#29231F] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
                />
                <button
                  v-if="posSearch"
                  @click="posSearch = ''"
                  class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs cursor-pointer"
                >
                  &times;
                </button>
              </div>

              <select
                v-model="posSelectedCategory"
                class="text-xs bg-gray-50 dark:bg-[#170E0A] border border-gray-200 dark:border-[#382318] rounded-xl px-3 py-2 text-[#29231F] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
              >
                <option v-for="cat in posCategories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>

            <!-- Loading Skeleton -->
            <div v-if="posLoading" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
              <div v-for="n in 6" :key="n" class="h-24 bg-gray-100 dark:bg-[#25150E] rounded-xl animate-pulse"></div>
            </div>

            <!-- Products Grid -->
            <div v-else-if="filteredPOSProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-[50vh] overflow-y-auto pr-1">
              <div
                v-for="p in filteredPOSProducts"
                :key="p.product_id"
                @click="addToPOSCart(p)"
                class="p-2.5 border rounded-xl flex flex-col justify-between transition cursor-pointer text-left"
                :class="p.available > 0 
                  ? 'border-gray-200 dark:border-[#382318] bg-white dark:bg-[#25150E] hover:border-[#DE1B54] hover:shadow-xs' 
                  : 'border-gray-100 dark:border-[#2A160F] bg-gray-50 dark:bg-[#1A0E08] opacity-60 cursor-not-allowed'"
              >
                <div>
                  <div class="flex items-start justify-between gap-1">
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] line-clamp-2 leading-tight">{{ p.product_name }}</span>
                    <span v-if="p.is_fasting" class="text-[9px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-bold px-1 rounded shrink-0">የጾም</span>
                  </div>
                  <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-0.5">{{ p.category?.category_name || 'Bakery' }}</span>
                </div>
                <div class="mt-2 pt-1 border-t border-gray-100 dark:border-[#382318] flex items-center justify-between text-xs">
                  <span class="font-black text-[#DE1B54]">ETB {{ Number(p.price).toFixed(2) }}</span>
                  <span
                    class="text-[10px] font-semibold px-1.5 py-0.2 rounded"
                    :class="p.available > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700'"
                  >
                    {{ p.available > 0 ? `${p.available} left` : 'Out' }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-10 text-gray-400 text-xs">
              No products found for this search.
            </div>
          </div>

          <!-- Right Column: Cart, Tendering & Actions (5 cols) -->
          <div class="lg:col-span-5 bg-gray-50 dark:bg-[#170E0A] rounded-xl p-3.5 sm:p-4 flex flex-col justify-between border border-gray-200 dark:border-[#382318] space-y-3">
            <div class="space-y-3">
              <div class="flex items-center justify-between border-b border-gray-200 dark:border-[#382318] pb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">Current Sale Items</span>
                <button
                  v-if="posCart.length > 0"
                  @click="posCart = []"
                  class="text-[11px] text-rose-500 hover:underline cursor-pointer"
                >
                  Clear Cart
                </button>
              </div>

              <!-- Cart Item List -->
              <div v-if="posCart.length > 0" class="max-h-[28vh] overflow-y-auto space-y-2 pr-1">
                <div
                  v-for="(item, idx) in posCart"
                  :key="item.product_id"
                  class="flex items-center justify-between text-xs bg-white dark:bg-[#25150E] p-2 rounded-lg border border-gray-200 dark:border-[#382318]"
                >
                  <div class="min-w-0 flex-1 pr-2">
                    <p class="font-bold text-[#2A1810] dark:text-[#FAF3EE] truncate">{{ item.product_name }}</p>
                    <p class="text-[10px] text-gray-500">ETB {{ item.unit_price.toFixed(2) }} each</p>
                  </div>
                  <div class="flex items-center space-x-1.5 shrink-0">
                    <button
                      @click="updatePOSQty(idx, -1)"
                      class="w-5 h-5 rounded bg-gray-100 dark:bg-[#170E0A] hover:bg-gray-200 text-gray-700 dark:text-gray-200 font-bold flex items-center justify-center cursor-pointer"
                    >
                      -
                    </button>
                    <span class="w-6 text-center font-bold text-xs">{{ item.quantity }}</span>
                    <button
                      @click="updatePOSQty(idx, 1)"
                      :disabled="item.quantity >= item.available"
                      class="w-5 h-5 rounded bg-gray-100 dark:bg-[#170E0A] hover:bg-gray-200 text-gray-700 dark:text-gray-200 font-bold flex items-center justify-center cursor-pointer disabled:opacity-40"
                    >
                      +
                    </button>
                    <span class="w-16 text-right font-black text-[#DE1B54]">ETB {{ (item.quantity * item.unit_price).toFixed(2) }}</span>
                    <button
                      @click="removeFromPOSCart(idx)"
                      class="text-gray-400 hover:text-rose-500 ml-1 text-sm font-bold cursor-pointer"
                    >
                      &times;
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-400 text-xs">
                🛒 Cart is empty. Click any item on the left to add.
              </div>
            </div>

            <!-- Customer & Payment Form -->
            <div class="space-y-2.5 pt-2 border-t border-gray-200 dark:border-[#382318] text-xs">
              <!-- Customer Info -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-[10px] font-bold text-gray-500 uppercase">Customer Name</label>
                  <input
                    v-model="posCustomerName"
                    type="text"
                    placeholder="Walk-in Guest"
                    class="w-full bg-white dark:bg-[#25150E] border border-gray-200 dark:border-[#382318] rounded px-2 py-1 text-xs"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-gray-500 uppercase">Customer Phone</label>
                  <input
                    v-model="posCustomerPhone"
                    type="tel"
                    placeholder="09... (optional)"
                    class="w-full bg-white dark:bg-[#25150E] border border-gray-200 dark:border-[#382318] rounded px-2 py-1 text-xs"
                  />
                </div>
              </div>

              <!-- Payment Method Selection -->
              <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Payment Method</label>
                <div class="grid grid-cols-3 gap-1.5">
                  <button
                    type="button"
                    @click="posPaymentMethod = 'Cash'"
                    class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                    :class="posPaymentMethod === 'Cash' ? 'bg-[#DE1B54] text-white border-[#DE1B54]' : 'bg-white dark:bg-[#25150E] border-gray-200 dark:border-[#382318] text-gray-700 dark:text-gray-300'"
                  >
                    💵 Cash
                  </button>
                  <button
                    type="button"
                    @click="posPaymentMethod = 'Card'"
                    class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                    :class="posPaymentMethod === 'Card' ? 'bg-[#DE1B54] text-white border-[#DE1B54]' : 'bg-white dark:bg-[#25150E] border-gray-200 dark:border-[#382318] text-gray-700 dark:text-gray-300'"
                  >
                    💳 Card / POS
                  </button>
                  <button
                    type="button"
                    @click="posPaymentMethod = 'Telebirr'"
                    class="py-1.5 rounded-lg border text-xs font-bold transition cursor-pointer"
                    :class="posPaymentMethod === 'Telebirr' ? 'bg-[#DE1B54] text-white border-[#DE1B54]' : 'bg-white dark:bg-[#25150E] border-gray-200 dark:border-[#382318] text-gray-700 dark:text-gray-300'"
                  >
                    📱 Telebirr
                  </button>
                </div>
              </div>

              <!-- Cash Tender Calculator -->
              <div v-if="posPaymentMethod === 'Cash'" class="p-2.5 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 rounded-xl space-y-2">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-emerald-900 dark:text-emerald-300">Amount Tendered (ETB):</span>
                  <input
                    v-model.number="posAmountTendered"
                    type="number"
                    min="0"
                    step="5"
                    class="w-24 bg-white dark:bg-[#1E120B] border border-emerald-300 rounded px-2 py-0.5 text-right font-bold text-emerald-900 dark:text-emerald-200"
                  />
                </div>
                <!-- Quick Tender Helpers -->
                <div class="flex items-center justify-end space-x-1">
                  <button
                    type="button"
                    @click="setExactTender"
                    class="px-1.5 py-0.5 bg-white dark:bg-[#1E120B] border border-emerald-300 rounded text-[10px] font-bold text-emerald-800 dark:text-emerald-200 cursor-pointer"
                  >
                    Exact
                  </button>
                  <button
                    type="button"
                    @click="addTenderAmount(100)"
                    class="px-1.5 py-0.5 bg-white dark:bg-[#1E120B] border border-emerald-300 rounded text-[10px] font-bold text-emerald-800 dark:text-emerald-200 cursor-pointer"
                  >
                    +100
                  </button>
                  <button
                    type="button"
                    @click="addTenderAmount(500)"
                    class="px-1.5 py-0.5 bg-white dark:bg-[#1E120B] border border-emerald-300 rounded text-[10px] font-bold text-emerald-800 dark:text-emerald-200 cursor-pointer"
                  >
                    +500
                  </button>
                </div>
                <div class="flex items-center justify-between text-xs font-bold text-emerald-900 dark:text-emerald-200 pt-1 border-t border-emerald-200/60 dark:border-emerald-900/40">
                  <span>Change to Return:</span>
                  <span class="text-sm">ETB {{ posChangeDue.toFixed(2) }}</span>
                </div>
              </div>

              <!-- Subtotal & Discount -->
              <div class="space-y-1 pt-1">
                <div class="flex justify-between text-gray-500">
                  <span>Subtotal:</span>
                  <span>ETB {{ posSubtotal.toFixed(2) }}</span>
                </div>
                <div class="flex items-center justify-between text-gray-500">
                  <span>Discount (ETB):</span>
                  <input
                    v-model.number="posDiscount"
                    type="number"
                    min="0"
                    class="w-20 bg-white dark:bg-[#25150E] border border-gray-200 dark:border-[#382318] rounded px-1.5 py-0.5 text-right text-xs"
                  />
                </div>
                <div class="flex justify-between text-sm font-black text-[#2A1810] dark:text-[#FAF3EE] pt-1 border-t border-gray-200 dark:border-[#382318]">
                  <span>Total Payable:</span>
                  <span class="text-[#DE1B54]">ETB {{ posTotal.toFixed(2) }}</span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-2 pt-2">
                <button
                  type="button"
                  @click="posModalOpen = false"
                  class="flex-1 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-200 dark:bg-[#25150E] hover:bg-gray-300 rounded-xl transition cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="submitPOSOrder"
                  :disabled="posSubmitting || posCart.length === 0"
                  class="flex-2 py-2 text-xs font-bold text-white bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 rounded-xl transition cursor-pointer flex items-center justify-center space-x-1.5 shadow-sm"
                >
                  <span v-if="posSubmitting">Recording...</span>
                  <span v-else>Complete Sale & Print 🖨️</span>
                </button>
              </div>
            </div>
          </div>
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

const canCreatePOSOrder = computed(() =>
  ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Branch Staff'].includes(authStore.user?.role)
);
const currentBranchId = computed(() => userBranchId.value || branches.value[0]?.branch_id || 1);
const currentBranchName = computed(() => userBranchName.value || branches.value.find(b => b.branch_id === currentBranchId.value)?.branch_name || 'Main');

const posModalOpen = ref(false);
const posLoading = ref(false);
const posSubmitting = ref(false);
const posProducts = ref([]);
const posSearch = ref('');
const posSelectedCategory = ref('All');
const posCart = ref([]);
const posCustomerName = ref('Walk-in Guest');
const posCustomerPhone = ref('');
const posPaymentMethod = ref('Cash');
const posAmountTendered = ref(0);
const posDiscount = ref(0);
const posNotes = ref('');

const posCategories = computed(() => {
  const cats = new Set(['All']);
  posProducts.value.forEach(p => {
    if (p.category?.category_name) cats.add(p.category.category_name);
  });
  return Array.from(cats);
});

const filteredPOSProducts = computed(() => {
  return posProducts.value.filter(p => {
    const matchSearch = !posSearch.value || p.product_name.toLowerCase().includes(posSearch.value.toLowerCase());
    const matchCat = posSelectedCategory.value === 'All' || p.category?.category_name === posSelectedCategory.value;
    return matchSearch && matchCat;
  });
});

const posSubtotal = computed(() => {
  return posCart.value.reduce((sum, item) => sum + item.quantity * item.unit_price, 0);
});

const posTotal = computed(() => {
  return Math.max(0, posSubtotal.value - (Number(posDiscount.value) || 0));
});

const posChangeDue = computed(() => {
  if (posPaymentMethod.value !== 'Cash') return 0;
  return Math.max(0, (Number(posAmountTendered.value) || 0) - posTotal.value);
});

function openPOSModal() {
  posModalOpen.value = true;
  posCart.value = [];
  posCustomerName.value = 'Walk-in Guest';
  posCustomerPhone.value = '';
  posPaymentMethod.value = 'Cash';
  posDiscount.value = 0;
  posAmountTendered.value = 0;
  loadPOSProducts();
}

async function loadPOSProducts() {
  posLoading.value = true;
  try {
    const branchId = currentBranchId.value;
    const res = await api.get('/products', {
      params: { branch_id: branchId }
    });
    const raw = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    posProducts.value = raw.map(p => {
      const bp = p.branch_prices?.find(b => b.branch_id === branchId);
      const price = bp ? Number(bp.price) : Number(p.base_price || p.price || 0);
      const available = p.available_stock !== undefined ? p.available_stock : (p.branch_inventory?.available_quantity ?? 50);
      return {
        product_id: p.product_id,
        product_name: p.product_name,
        price,
        available: available > 0 ? available : 0,
        category: p.category,
        is_fasting: p.is_fasting,
      };
    });
  } catch (err) {
    toast.error('Failed to load products for POS counter sale.');
  } finally {
    posLoading.value = false;
  }
}

function addToPOSCart(prod) {
  if (prod.available <= 0) {
    toast.warning('Product is out of stock at this branch.');
    return;
  }
  const existing = posCart.value.find(i => i.product_id === prod.product_id);
  if (existing) {
    if (existing.quantity < prod.available) {
      existing.quantity += 1;
    } else {
      toast.warning(`Only ${prod.available} items available in stock.`);
    }
  } else {
    posCart.value.push({
      product_id: prod.product_id,
      product_name: prod.product_name,
      unit_price: prod.price,
      quantity: 1,
      available: prod.available,
    });
  }
  if (posPaymentMethod.value === 'Cash' && posAmountTendered.value < posTotal.value) {
    posAmountTendered.value = posTotal.value;
  }
}

function updatePOSQty(idx, delta) {
  const item = posCart.value[idx];
  if (!item) return;
  const newQty = item.quantity + delta;
  if (newQty <= 0) {
    posCart.value.splice(idx, 1);
  } else if (newQty > item.available) {
    toast.warning(`Maximum available stock is ${item.available}.`);
  } else {
    item.quantity = newQty;
  }
  if (posPaymentMethod.value === 'Cash' && posAmountTendered.value < posTotal.value) {
    posAmountTendered.value = posTotal.value;
  }
}

function removeFromPOSCart(idx) {
  posCart.value.splice(idx, 1);
  if (posPaymentMethod.value === 'Cash' && posAmountTendered.value < posTotal.value) {
    posAmountTendered.value = posTotal.value;
  }
}

function setExactTender() {
  posAmountTendered.value = posTotal.value;
}

function addTenderAmount(val) {
  posAmountTendered.value = (Number(posAmountTendered.value) || 0) + val;
}

async function submitPOSOrder() {
  if (posCart.value.length === 0) {
    toast.error('Cart is empty. Please select products first.');
    return;
  }
  if (posPaymentMethod.value === 'Cash' && (Number(posAmountTendered.value) || 0) < posTotal.value) {
    toast.error(`Amount tendered (ETB ${posAmountTendered.value}) is less than total payable (ETB ${posTotal.value}).`);
    return;
  }

  posSubmitting.value = true;
  try {
    const payload = {
      branch_id: currentBranchId.value,
      items: posCart.value.map(i => ({
        product_id: i.product_id,
        quantity: i.quantity,
        unit_price: i.unit_price,
      })),
      payment_method: posPaymentMethod.value,
      amount_tendered: posPaymentMethod.value === 'Cash' ? Number(posAmountTendered.value) : posTotal.value,
      discount_amount: Number(posDiscount.value) || 0,
      customer_name: posCustomerName.value || 'Walk-in Guest',
      customer_phone: posCustomerPhone.value || null,
      notes: posNotes.value || 'POS counter sale',
    };

    const res = await api.post('/admin/orders/pos', payload);
    toast.success(res.data.message || 'Counter sale recorded successfully!');
    if (res.data.low_stock_alerts?.length) {
      toast.warning(`Low stock alert: ${res.data.low_stock_alerts.map(a => a.product_name).join(', ')}`);
    }

    posModalOpen.value = false;
    await loadOrders();

    if (res.data.order) {
      selectedOrder.value = res.data.order;
      setTimeout(() => {
        printReceipt(res.data.order);
      }, 300);
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to process POS counter sale.';
    toast.error(msg);
  } finally {
    posSubmitting.value = false;
  }
}

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
