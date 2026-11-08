<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">
          {{ isSuperAdmin ? 'Central Multi-Branch Inventory' : `${userBranchName} Branch Inventory` }}
        </h2>
        <p class="text-xs text-[#7A6B60] mt-0.5">
          {{ isSuperAdmin ? 'Real-time physical stock across all branches and live ledger tracking.' : `Strict branch stock ledger for ${userBranchName}. Customers can only order what is available at this branch.` }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
        <button
          @click="showReconcileModal = true"
          class="px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-xl transition shadow-2xs flex items-center space-x-1.5 cursor-pointer"
        >
          <span>⚖️</span>
          <span>Verify History &amp; Balance</span>
        </button>

        <button
          @click="activeSubTab = activeSubTab === 'ledger' ? 'stock' : 'ledger'"
          class="px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-xl transition shadow-2xs flex items-center space-x-1.5 cursor-pointer"
        >
          <span>📋</span>
          <span>{{ activeSubTab === 'ledger' ? 'View Stock Grid' : 'Transaction Ledger' }}</span>
        </button>

        <button
          @click="exportCSV"
          class="px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-xl transition shadow-2xs cursor-pointer"
        >
          Export CSV
        </button>

        <!-- Functional + Adjust Stock Button -->
        <button
          v-if="canAdjustStock"
          @click="openAdjustModal()"
          class="px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs font-bold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-xl transition shadow-xs flex items-center space-x-1 cursor-pointer"
        >
          <span>+</span>
          <span>Adjust Stock</span>
        </button>
        <span
          v-else
          class="px-3 py-1.5 text-xs font-medium text-[#7A6B60] bg-gray-100 border border-gray-200 rounded-xl flex items-center space-x-1"
        >
          <span>🔒</span>
          <span>View-Only Access (Adjustments restricted to Branch Manager)</span>
        </span>
      </div>
    </div>

    <!-- Confirmation Toast / Message after Stock Adjustment -->
    <div
      v-if="adjustmentSuccessToast"
      class="p-4 bg-[#EBF7EB] border border-[#C5E9C6] rounded-2xl text-xs text-[#1E7422] flex items-start justify-between shadow-xs animate-in fade-in"
    >
      <div class="flex items-start space-x-3">
        <span class="text-xl">✅</span>
        <div>
          <div class="font-bold text-sm">Stock Adjustment Recorded Successfully</div>
          <div class="text-[#2B612C] mt-0.5">{{ adjustmentSuccessToast }}</div>
          <div v-if="lastAdjustmentDetails" class="text-[11px] text-[#427C44] mt-1 space-x-2">
            <span><strong>Branch:</strong> {{ lastAdjustmentDetails.branch }}</span>
            <span>&middot;</span>
            <span><strong>Product:</strong> {{ lastAdjustmentDetails.product }}</span>
            <span>&middot;</span>
            <span><strong>Previous:</strong> {{ lastAdjustmentDetails.previous_quantity }}</span>
            <span>➔</span>
            <span><strong>New:</strong> {{ lastAdjustmentDetails.new_quantity }}</span>
            <span>&middot;</span>
            <span><strong>Reason:</strong> "{{ lastAdjustmentDetails.reason }}"</span>
          </div>
        </div>
      </div>
      <button @click="adjustmentSuccessToast = ''" class="text-[#2B612C] font-bold text-base hover:text-black cursor-pointer ml-3">&times;</button>
    </div>

    <!-- Branch Filter: Super Admin can switch branches; Branch staff/managers strictly see assigned branch -->
    <div v-if="isSuperAdmin" class="flex items-center space-x-2 overflow-x-auto pb-1">
      <button
        @click="selectBranch('all')"
        class="px-4 py-1.5 text-xs font-semibold rounded-full transition cursor-pointer"
        :class="selectedBranch === 'all' ? 'bg-[#984518] text-white shadow-xs' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#F2EAE0]'"
      >
        All branches
      </button>
      <button
        v-for="branch in branches"
        :key="branch.branch_id"
        @click="selectBranch(branch.branch_id)"
        class="px-4 py-1.5 text-xs font-semibold rounded-full transition cursor-pointer"
        :class="selectedBranch === branch.branch_id ? 'bg-[#984518] text-white shadow-xs' : 'bg-white border border-[#DDD2C5] text-[#5A4B41] hover:bg-[#F2EAE0]'"
      >
        {{ branch.branch_name }}
        <span v-if="branch.branch_type === 'Main'" class="ml-1 text-[10px] px-1.5 py-0.2 bg-red-100 text-red-700 rounded-full font-bold">Main</span>
      </button>
    </div>

    <!-- Strict Branch Scoped Badge for Branch Managers & Staff -->
    <div v-else class="flex items-center space-x-2 pb-1">
      <span class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-full text-xs font-bold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1] shadow-2xs">
        <span>🔒</span>
        <span>Strict Branch Privacy: Showing {{ userBranchName }} Branch Inventory Only</span>
      </span>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64] mb-1">Units on hand</div>
        <div class="text-3xl font-black text-[#241710]">{{ summary.units_on_hand.toLocaleString() }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Physical units at {{ isSuperAdmin ? `${summary.branches_count} branches` : userBranchName }}</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64] mb-1">Reserved</div>
        <div class="text-3xl font-black text-[#D97706]">{{ summary.reserved }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Allocated to open orders (15m hold)</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64] mb-1">Low-stock items</div>
        <div class="text-3xl font-black text-[#EA580C]">{{ summary.low_stock_items }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">At or below reorder level (alerts sent)</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64] mb-1">Sold out</div>
        <div class="text-3xl font-black text-[#DC2626]">{{ summary.sold_out }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Shown as sold out, not hidden</div>
      </div>
    </div>

    <!-- VIEW 1: Stock Grid & Side Adjust Form -->
    <div v-if="activeSubTab === 'stock'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Stock Table (Col span 2) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF] flex items-center justify-between">
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">
            {{ isSuperAdmin ? 'Stock by Branch & Product' : `${userBranchName} Stock List` }}
          </h3>
          <span class="text-xs text-[#7A6B60]">
            {{ canAdjustStock ? 'Click any product row to adjust' : 'Real-time inventory levels' }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[580px] text-left text-xs">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th v-if="isSuperAdmin" class="py-3 px-5">Branch</th>
                <th class="py-3 px-4">Product</th>
                <th class="py-3 px-4 text-center">On hand</th>
                <th class="py-3 px-4 text-center">Reserved</th>
                <th class="py-3 px-4 text-center">Available</th>
                <th class="py-3 px-4 text-center">Reorder at</th>
                <th class="py-3 px-5 text-center">Status</th>
                <th v-if="canAdjustStock" class="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-if="!items || items.length === 0">
                <td :colspan="isSuperAdmin ? 8 : 7" class="py-12 text-center text-sm text-[#7A6B60]">
                  No inventory records yet. Once branches and products are created, adjust or assign stock here.
                </td>
              </tr>
              <tr
                v-for="item in items"
                :key="item.inventory_id"
                class="hover:bg-[#FCFAF7] transition cursor-pointer"
                @click="populateAdjust(item)"
              >
                <td v-if="isSuperAdmin" class="py-3.5 px-5 font-semibold text-[#382C24]">
                  {{ item.branch_name }}
                </td>
                <td class="py-3.5 px-4 text-[#4A3B32] font-product font-bold">{{ item.product_name }}</td>
                <td class="py-3.5 px-4 text-center font-bold text-[#2A1D15]">{{ item.on_hand }}</td>
                <td class="py-3.5 px-4 text-center text-[#D97706] font-semibold">{{ item.reserved }}</td>
                <td class="py-3.5 px-4 text-center font-black text-[#241710]">{{ item.available }}</td>
                <td class="py-3.5 px-4 text-center text-[#75665C]">{{ item.reorder_at }}</td>
                <td class="py-3.5 px-5 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-[11px] font-bold rounded-full"
                    :class="{
                      'bg-[#EBF7EB] text-[#227226] border border-[#C5E9C6]': item.status === 'Healthy',
                      'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': item.status === 'Low',
                      'bg-[#FEECEC] text-[#B91C1C] border border-[#FECACA]': item.status === 'Sold out'
                    }"
                  >
                    {{ item.status }}
                  </span>
                </td>
                <td v-if="canAdjustStock" class="py-3.5 px-4 text-right" @click.stop>
                  <button
                    @click="openAdjustForItem(item)"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1] hover:bg-[#BA5420] hover:text-white rounded-lg transition cursor-pointer"
                  >
                    Adjust
                  </button>
                </td>
              </tr>
              <tr v-if="items.length === 0">
                <td :colspan="isSuperAdmin ? 8 : 7" class="py-12 text-center text-xs text-[#7A6B60]">
                  No inventory records found for this branch selection.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-[#FCFAF7] border-t border-[#EFE8DF] text-xs text-[#8A7B71] flex items-center justify-between">
          <span>Formula: <strong>Available = On hand − Reserved</strong>.</span>
          <span class="text-[11px] font-bold text-[#BA5420]">Total products: {{ items.length }}</span>
        </div>
      </div>

      <!-- Right Card: Quick Adjust Form -->
      <div id="adjust-panel" class="bg-white border border-[#E9E0D4] rounded-2xl shadow-2xs p-6 space-y-4">
        <div class="border-b border-[#F2EAE0] pb-3">
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">+ Quick Stock Adjustment</h3>
          <p class="text-xs text-[#7A6B60] mt-0.5">Increases or decreases inventory and records every detail in the audit log.</p>
        </div>

        <div v-if="!canAdjustStock" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#7A6B60] space-y-2 text-center">
          <span class="text-2xl block">🔒</span>
          <p class="font-semibold text-[#2A1D15]">Stock Adjustment Restricted</p>
          <p>Only the assigned Branch Manager and Super Admin are authorized to record inventory adjustments.</p>
        </div>

        <form v-else @submit.prevent="submitAdjustment" class="space-y-4">
          <!-- Branch -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Branch</label>
            <div v-if="!isSuperAdmin" class="p-2.5 bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl text-xs font-bold text-[#2A1D15]">
              📍 {{ userBranchName }} Branch (Assigned)
            </div>
            <select
              v-else
              v-model="adjustForm.branch_id"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420] focus:outline-none"
              required
            >
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }} {{ b.branch_type === 'Main' ? '(Main)' : '' }}
              </option>
            </select>
          </div>

          <!-- Product -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Product</label>
            <select
              v-model="adjustForm.product_id"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420] focus:outline-none"
              required
            >
              <option v-for="p in distinctProducts" :key="p.product_id" :value="p.product_id">
                {{ p.product_name }}
              </option>
            </select>
          </div>

          <!-- Increase / Decrease Choice -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Action</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="setAdjustMode('add')"
                class="py-2 px-3 text-xs font-bold rounded-xl border transition text-center cursor-pointer"
                :class="adjustMode === 'add' ? 'bg-[#EBF7EB] border-[#A7F3D0] text-[#059669]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#7A6B60]'"
              >
                + Add Stock
              </button>
              <button
                type="button"
                @click="setAdjustMode('remove')"
                class="py-2 px-3 text-xs font-bold rounded-xl border transition text-center cursor-pointer"
                :class="adjustMode === 'remove' ? 'bg-[#FEECEC] border-[#FECACA] text-[#DC2626]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#7A6B60]'"
              >
                − Remove Stock
              </button>
            </div>
          </div>

          <!-- Classification -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Operation Type</label>
            <select
              v-model="adjustForm.type"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420] focus:outline-none font-medium"
              required
            >
              <template v-if="adjustMode === 'add'">
                <option value="production">Production received (Main branch only)</option>
                <option value="restock">Direct Restock / Inflow</option>
                <option value="manual_adjustment">Manual adjustment (Count addition)</option>
              </template>
              <template v-else>
                <option value="damaged">Damaged stock (write-off)</option>
                <option value="expired">Expired perishable (shelf-life write-off)</option>
                <option value="manual_adjustment">Manual adjustment (Count reduction)</option>
              </template>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">
              Quantity to {{ adjustMode === 'add' ? 'Add' : 'Remove' }}
            </label>
            <input
              v-model.number="adjustQuantityInput"
              type="number"
              min="1"
              placeholder="e.g. 5"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420] focus:outline-none"
              required
            />
          </div>

          <!-- Real-Time Calculation Preview -->
          <div class="p-3 bg-[#FAF7F5] rounded-xl border border-[#F2D7DC] text-xs space-y-1">
            <div class="flex justify-between">
              <span class="text-[#7A6B60]">Current On Hand:</span>
              <span class="font-bold text-[#2A1D15]">{{ selectedItemOnHand }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#7A6B60]">Adjustment Delta:</span>
              <span class="font-black" :class="effectiveQtyDelta >= 0 ? 'text-[#059669]' : 'text-[#DC2626]'">
                {{ effectiveQtyDelta >= 0 ? `+${effectiveQtyDelta}` : effectiveQtyDelta }}
              </span>
            </div>
            <div class="flex justify-between border-t border-[#F2D7DC] pt-1">
              <span class="text-[#7A6B60]">Projected New On Hand:</span>
              <span class="font-black" :class="projectedOnHand < selectedItemReserved ? 'text-red-600' : 'text-[#059669]'">
                {{ projectedOnHand }}
              </span>
            </div>
            <div v-if="selectedItemReserved > 0" class="text-[11px] text-[#D97706] pt-0.5">
              Reserved in open orders: {{ selectedItemReserved }}
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Reason (Mandatory)</label>
            <textarea
              v-model="adjustForm.reason"
              rows="2"
              placeholder="Enter mandatory reason for this adjustment..."
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420] focus:outline-none"
              required
            ></textarea>
            <div class="flex flex-wrap gap-1 mt-1.5">
              <button
                type="button"
                v-for="preset in reasonPresets"
                :key="preset"
                @click="adjustForm.reason = preset"
                class="text-[10px] bg-[#FAF0E6] hover:bg-[#F0DFD0] text-[#BA5420] px-2 py-0.5 rounded-full border border-[#F0D5C1] cursor-pointer"
              >
                + {{ preset }}
              </button>
            </div>
          </div>

          <div v-if="projectedOnHand < selectedItemReserved" class="p-3 bg-[#FEECEC] border border-[#FECACA] rounded-xl text-xs text-[#B91C1C]">
            ⚠️ Rule Violation: On hand ({{ projectedOnHand }}) cannot be reduced below reserved quantity ({{ selectedItemReserved }}).
          </div>

          <div v-if="errorMessage" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-xl text-xs text-[#B91C1C]">
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            :disabled="submitting || (projectedOnHand < selectedItemReserved) || projectedOnHand < 0 || !adjustQuantityInput || !adjustForm.reason"
            class="w-full py-2.5 px-4 bg-[#BA5420] hover:bg-[#9E4314] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
          >
            {{ submitting ? 'Updating Stock & Ledger...' : 'Save Stock Adjustment' }}
          </button>
        </form>
      </div>
    </div>

    <!-- VIEW 2: Complete Transaction Ledger -->
    <div v-else class="bg-white border border-[#E9E0D4] rounded-2xl shadow-2xs overflow-hidden space-y-4 p-5">
      <div class="flex items-center justify-between border-b border-[#F2EAE0] pb-4">
        <div>
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">
            {{ isSuperAdmin ? 'Business-Wide Inventory Transaction Ledger' : `${userBranchName} Stock Transaction Ledger` }}
          </h3>
          <p class="text-xs text-[#7A6B60] mt-0.5">Immutable audit record of all stock adjustments, reserves, sales, and write-offs.</p>
        </div>
        <button
          @click="loadTransactions"
          class="px-3.5 py-1.5 bg-[#FAF7F5] border border-[#D8CDC0] hover:bg-[#F4ECE3] text-xs font-semibold rounded-xl cursor-pointer"
        >
          ↻ Refresh Ledger
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-left text-xs">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-4">Date &amp; Time</th>
              <th v-if="isSuperAdmin" class="py-3 px-4">Branch</th>
              <th class="py-3 px-4">Product</th>
              <th class="py-3 px-4 text-center">Operation</th>
              <th class="py-3 px-4 text-center">Signed Quantity</th>
              <th class="py-3 px-4">Reason / Reference</th>
              <th class="py-3 px-4">Recorded By</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="tx in transactionsList" :key="tx.transaction_id" class="hover:bg-[#FCFAF7]">
              <td class="py-3 px-4 text-[#7A6B60] font-mono text-[11px]">{{ formatDateTime(tx.created_at) }}</td>
              <td v-if="isSuperAdmin" class="py-3 px-4 font-bold text-[#382C24]">{{ tx.branch?.branch_name }}</td>
              <td class="py-3 px-4 font-product font-bold text-[#2A1D15]">{{ tx.product?.product_name }}</td>
              <td class="py-3 px-4 text-center">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="txBadgeClass(tx.transaction_type)">
                  {{ tx.transaction_type }}
                </span>
              </td>
              <td class="py-3 px-4 text-center font-black" :class="tx.quantity >= 0 ? 'text-[#059669]' : 'text-[#DC2626]'">
                {{ tx.quantity > 0 ? `+${tx.quantity}` : tx.quantity }}
              </td>
              <td class="py-3 px-4 text-[#4A3B32]">
                <div class="font-medium">{{ tx.reason }}</div>
                <div class="text-[10px] text-[#8A7B71]" v-if="tx.reference_type">{{ tx.reference_type }} #{{ tx.reference_id || '' }}</div>
              </td>
              <td class="py-3 px-4 text-[#5A4B41] font-semibold">{{ tx.creator?.full_name || 'System' }}</td>
            </tr>
            <tr v-if="transactionsList.length === 0">
              <td :colspan="isSuperAdmin ? 7 : 6" class="py-10 text-center text-xs text-[#7A6B60]">
                No transaction logs recorded yet for this branch.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Interactive Adjust Stock Modal -->
    <div
      v-if="showAdjustModal"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
    >
      <div class="bg-white rounded-3xl border border-[#D8CDC0] max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl animate-in zoom-in-95 max-h-[92vh] overflow-y-auto">
        <div class="flex items-start justify-between border-b border-[#F2EAE0] pb-3">
          <div>
            <span class="text-[11px] font-bold text-[#BA5420] uppercase tracking-wider">Inventory Management</span>
            <h3 class="font-serif font-black text-xl text-[#2A1D15]">+ Adjust Branch Stock</h3>
          </div>
          <button @click="showAdjustModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitAdjustment" class="space-y-4">
          <!-- Branch Selector (Super Admin) or Locked Display (Branch Manager) -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Branch</label>
            <div v-if="!isSuperAdmin" class="p-2.5 bg-[#FAF7F5] border border-[#DDD2C5] rounded-xl text-xs font-bold text-[#2A1D15] flex items-center space-x-1.5">
              <span>📍</span>
              <span>{{ userBranchName }} Branch (Assigned)</span>
            </div>
            <select
              v-else
              v-model="adjustForm.branch_id"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            >
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }} {{ b.branch_type === 'Main' ? '(Main)' : '' }}
              </option>
            </select>
          </div>

          <!-- Product Picker -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Product</label>
            <select
              v-model="adjustForm.product_id"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            >
              <option v-for="p in distinctProducts" :key="p.product_id" :value="p.product_id">
                {{ p.product_name }}
              </option>
            </select>
          </div>

          <!-- Direction: Add or Remove -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Adjustment Action</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="setAdjustMode('add')"
                class="py-2 px-3 text-xs font-bold rounded-xl border transition text-center cursor-pointer"
                :class="adjustMode === 'add' ? 'bg-[#EBF7EB] border-[#A7F3D0] text-[#059669]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#7A6B60]'"
              >
                + Increase Stock
              </button>
              <button
                type="button"
                @click="setAdjustMode('remove')"
                class="py-2 px-3 text-xs font-bold rounded-xl border transition text-center cursor-pointer"
                :class="adjustMode === 'remove' ? 'bg-[#FEECEC] border-[#FECACA] text-[#DC2626]' : 'bg-[#FAF7F5] border-[#D8CDC0] text-[#7A6B60]'"
              >
                − Decrease Stock
              </button>
            </div>
          </div>

          <!-- Specific Classification -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">Classification</label>
            <select
              v-model="adjustForm.type"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            >
              <template v-if="adjustMode === 'add'">
                <option value="production">Production Received (Main Branch Fresh Bake)</option>
                <option value="restock">Direct Restock / Inflow</option>
                <option value="manual_adjustment">Manual Adjustment (Count Addition)</option>
              </template>
              <template v-else>
                <option value="damaged">Damaged Stock (Dropped / Packaging Defect)</option>
                <option value="expired">Expired Perishable (Shelf-Life Exceeded)</option>
                <option value="manual_adjustment">Manual Adjustment (Count Reduction)</option>
              </template>
            </select>
          </div>

          <!-- Quantity Input -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">
              Quantity to {{ adjustMode === 'add' ? 'Add' : 'Remove' }}
            </label>
            <input
              v-model.number="adjustQuantityInput"
              type="number"
              min="1"
              placeholder="e.g. 5"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            />
          </div>

          <!-- Real-Time Calculation Preview -->
          <div class="p-3.5 bg-[#FAF7F5] rounded-xl border border-[#E9E0D4] text-xs space-y-1.5">
            <div class="flex justify-between">
              <span class="text-[#7A6B60]">Current On Hand:</span>
              <span class="font-bold text-[#2A1D15]">{{ selectedItemOnHand }} units</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#7A6B60]">Adjustment Delta:</span>
              <span class="font-black" :class="effectiveQtyDelta >= 0 ? 'text-[#059669]' : 'text-[#DC2626]'">
                {{ effectiveQtyDelta >= 0 ? `+${effectiveQtyDelta}` : effectiveQtyDelta }} units
              </span>
            </div>
            <div class="flex justify-between border-t border-[#E9E0D4] pt-1.5">
              <span class="font-bold text-[#2A1D15]">New Projected Quantity:</span>
              <span class="font-black text-sm" :class="projectedOnHand < selectedItemReserved ? 'text-red-600' : 'text-[#059669]'">
                {{ projectedOnHand }} units
              </span>
            </div>
            <div v-if="selectedItemReserved > 0" class="text-[11px] text-[#D97706]">
              ⚠️ Reserved for active orders: {{ selectedItemReserved }} units
            </div>
          </div>

          <!-- Mandatory Reason -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#65554B] mb-1">
              Reason for Adjustment (Mandatory)
            </label>
            <textarea
              v-model="adjustForm.reason"
              rows="2"
              placeholder="e.g. Daily morning fresh batch, Damaged in packaging, Physical shelf reconciliation"
              class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            ></textarea>
            <!-- Quick Reason Presets -->
            <div class="flex flex-wrap gap-1 mt-1.5">
              <button
                type="button"
                v-for="preset in reasonPresets"
                :key="preset"
                @click="adjustForm.reason = preset"
                class="text-[10px] bg-[#FAF0E6] hover:bg-[#F0DFD0] text-[#BA5420] px-2 py-0.5 rounded-full border border-[#F0D5C1] cursor-pointer"
              >
                + {{ preset }}
              </button>
            </div>
          </div>

          <div v-if="projectedOnHand < selectedItemReserved" class="p-3 bg-[#FEECEC] border border-[#FECACA] rounded-xl text-xs text-[#B91C1C]">
            ⚠️ Rule Violation: Stock on hand ({{ projectedOnHand }}) cannot be reduced below reserved quantity ({{ selectedItemReserved }}).
          </div>

          <div v-if="errorMessage" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-xl text-xs text-[#B91C1C]">
            {{ errorMessage }}
          </div>

          <div class="flex items-center justify-end space-x-2.5 pt-2">
            <button
              type="button"
              @click="showAdjustModal = false"
              class="px-4 py-2 border border-[#D8CDC0] text-[#5A4B41] text-xs font-semibold rounded-xl hover:bg-[#FAF7F5] cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting || (projectedOnHand < selectedItemReserved) || !adjustForm.reason || projectedOnHand < 0 || !adjustQuantityInput"
              class="px-5 py-2 bg-[#BA5420] hover:bg-[#9E4314] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
              {{ submitting ? 'Updating Stock & Logging...' : 'Save & Record Adjustment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reconcile Verification Modal -->
    <div
      v-if="showReconcileModal"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
    >
      <div class="bg-white rounded-3xl border border-[#D8CDC0] max-w-2xl w-full p-4 sm:p-6 space-y-4 shadow-2xl animate-in zoom-in-95 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between border-b border-[#F2EAE0] pb-3">
          <div>
            <span class="text-[11px] font-bold text-[#BA5420] uppercase tracking-wider">Rule 1 &middot; Stock Reconciliation</span>
            <h3 class="font-serif font-black text-xl text-[#2A1D15]">Transaction History Ledger Audit</h3>
          </div>
          <button @click="showReconcileModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
        </div>

        <p class="text-xs text-[#5A4B41] leading-relaxed">
          The system verifies that on-hand stock figures for each product and branch can always be rebuilt from the signed history of inventory transactions.
        </p>

        <div v-if="loadingReconcile" class="py-6 text-center text-xs text-[#8A7B71]">
          Auditing transaction ledger...
        </div>

        <div v-else class="space-y-2 max-h-72 overflow-y-auto">
          <div
            v-for="rec in reconcileItems"
            :key="`${rec.branch_id}-${rec.product_id}`"
            class="p-3 bg-[#FAF7F5] rounded-xl border border-[#E9E0D4] flex items-center justify-between text-xs"
          >
            <div>
              <div class="font-product font-bold text-[#2A1D15]">{{ rec.branch_name }} &middot; {{ rec.product_name }}</div>
              <div class="text-[11px] text-[#7A6B60] mt-0.5">
                On Hand: <strong>{{ rec.current_on_hand }}</strong> &middot; Reserved: <strong>{{ rec.reserved_quantity }}</strong> &middot; Available: <strong>{{ rec.available_quantity }}</strong>
              </div>
            </div>
            <div class="text-right">
              <span class="px-2.5 py-1 bg-[#ECFDF5] text-[#059669] border border-[#A7F3D0] rounded-full font-bold text-[10px]">
                ✓ Reconciled Consistent
              </span>
            </div>
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <button
            @click="showReconcileModal = false"
            class="px-5 py-2 bg-[#2A1D15] text-white text-xs font-semibold rounded-xl cursor-pointer"
          >
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
const items = ref([]);
const distinctProducts = ref([]);
const activeSubTab = ref('stock'); // 'stock' or 'ledger'
const transactionsList = ref([]);

const showReconcileModal = ref(false);
const showAdjustModal = ref(false);
const loadingReconcile = ref(false);
const reconcileItems = ref([]);

// Role Scoping
const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isManager = computed(() => ['Branch Manager', 'Branch Admin', 'Main Branch Manager'].includes(authStore.user?.role) || (authStore.user?.role && authStore.user.role.includes('Manager')));
const canAdjustStock = computed(() => isSuperAdmin.value || isManager.value);
const userBranchId = computed(() => authStore.user?.branch_id || (branches.value[0]?.branch_id ?? 1));
const userBranchName = computed(() => authStore.user?.branch?.branch_name || (branches.value[0]?.branch_name) || 'My Branch');

const summary = ref({
  units_on_hand: 0,
  reserved: 0,
  low_stock_items: 0,
  sold_out: 0,
  branches_count: 1,
});

const adjustMode = ref('remove'); // 'add' or 'remove'
const adjustQuantityInput = ref(2);

const adjustForm = ref({
  branch_id: 1,
  product_id: 1,
  type: 'damaged',
  reason: 'Shelf life inspection write-off',
});

const reasonPresets = [
  'Shelf life inspection write-off',
  'Physical shelf count correction',
  'Daily morning bakery restock',
  'Damaged box during store handling',
  'Production batch received',
];

const submitting = ref(false);
const errorMessage = ref('');
const adjustmentSuccessToast = ref('');
const lastAdjustmentDetails = ref(null);

const setAdjustMode = (mode) => {
  adjustMode.value = mode;
  if (mode === 'add') {
    adjustForm.value.type = 'restock';
  } else {
    adjustForm.value.type = 'damaged';
  }
};

const effectiveQtyDelta = computed(() => {
  const qty = Math.abs(adjustQuantityInput.value || 0);
  if (adjustMode.value === 'remove' || adjustForm.value.type === 'damaged' || adjustForm.value.type === 'expired') {
    return -qty;
  }
  return qty;
});

const selectedItemOnHand = computed(() => {
  const match = items.value.find(
    (i) => i.branch_id === adjustForm.value.branch_id && i.product_id === adjustForm.value.product_id
  );
  return match ? match.on_hand : 0;
});

const selectedItemReserved = computed(() => {
  const match = items.value.find(
    (i) => i.branch_id === adjustForm.value.branch_id && i.product_id === adjustForm.value.product_id
  );
  return match ? match.reserved : 0;
});

const projectedOnHand = computed(() => {
  return selectedItemOnHand.value + effectiveQtyDelta.value;
});

const loadInventory = async () => {
  try {
    const branchParam = isSuperAdmin.value ? selectedBranch.value : userBranchId.value;
    const res = await api.get('/admin/inventory', {
      params: { branch_id: branchParam }
    });
    branches.value = res.data.branches || [];
    items.value = res.data.items || [];
    summary.value = res.data.summary || summary.value;

    // Build distinct products
    const seen = new Set();
    distinctProducts.value = [];
    res.data.items.forEach((item) => {
      if (!seen.has(item.product_id)) {
        seen.add(item.product_id);
        distinctProducts.value.push({ product_id: item.product_id, product_name: item.product_name });
      }
    });

    if (items.value.length > 0 && !distinctProducts.value.find(p => p.product_id === adjustForm.value.product_id)) {
      adjustForm.value.product_id = items.value[0].product_id;
    }
  } catch (err) {
    console.error('Failed to load inventory', err);
  }
};

const loadTransactions = async () => {
  try {
    const branchParam = isSuperAdmin.value
      ? (selectedBranch.value !== 'all' ? selectedBranch.value : null)
      : userBranchId.value;
    const res = await api.get('/admin/inventory/transactions', {
      params: { branch_id: branchParam }
    });
    transactionsList.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load transactions', err);
  }
};

const loadReconcile = async () => {
  loadingReconcile.value = true;
  try {
    const branchParam = isSuperAdmin.value
      ? (selectedBranch.value !== 'all' ? selectedBranch.value : null)
      : userBranchId.value;
    const res = await api.get('/admin/inventory/reconcile', {
      params: { branch_id: branchParam }
    });
    reconcileItems.value = res.data.items || [];
  } catch (err) {
    console.error('Failed to load reconcile data', err);
  } finally {
    loadingReconcile.value = false;
  }
};

const selectBranch = (branchId) => {
  selectedBranch.value = branchId;
  loadInventory();
  if (activeSubTab.value === 'ledger') {
    loadTransactions();
  }
};

const openAdjustModal = () => {
  if (!isSuperAdmin.value) {
    adjustForm.value.branch_id = userBranchId.value;
  } else if (selectedBranch.value !== 'all') {
    adjustForm.value.branch_id = selectedBranch.value;
  } else if (branches.value.length > 0) {
    adjustForm.value.branch_id = branches.value[0].branch_id;
  }
  showAdjustModal.value = true;
};

const openAdjustForItem = (item) => {
  adjustForm.value.branch_id = item.branch_id;
  adjustForm.value.product_id = item.product_id;
  adjustMode.value = 'remove';
  adjustForm.value.type = 'damaged';
  adjustQuantityInput.value = 2;
  adjustForm.value.reason = 'Shelf life inspection write-off';
  showAdjustModal.value = true;
};

const populateAdjust = (item) => {
  adjustForm.value.branch_id = item.branch_id;
  adjustForm.value.product_id = item.product_id;
  focusAdjustPanel();
};

const submitAdjustment = async () => {
  submitting.value = true;
  errorMessage.value = '';

  const payload = {
    branch_id: isSuperAdmin.value ? adjustForm.value.branch_id : userBranchId.value,
    product_id: adjustForm.value.product_id,
    type: adjustForm.value.type,
    quantity: effectiveQtyDelta.value,
    reason: adjustForm.value.reason,
  };

  try {
    const res = await api.post('/admin/inventory/adjust', payload);
    const details = res.data.details || {};
    lastAdjustmentDetails.value = details;

    const deltaSign = details.delta > 0 ? `+${details.delta}` : `${details.delta}`;
    adjustmentSuccessToast.value = `${details.product || 'Product'} at ${details.branch || 'Branch'}: ${details.previous_quantity} ➔ ${details.new_quantity} units (${deltaSign}). Reason: "${details.reason}".`;

    showAdjustModal.value = false;
    await loadInventory();
    if (activeSubTab.value === 'ledger') {
      await loadTransactions();
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to adjust stock.';
  } finally {
    submitting.value = false;
  }
};

const focusAdjustPanel = () => {
  const el = document.getElementById('adjust-panel');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const exportCSV = () => {
  const headers = ['Branch', 'Product', 'On Hand', 'Reserved', 'Available', 'Reorder At', 'Status'];
  const rows = items.value.map(i => [
    `"${i.branch_name}"`,
    `"${i.product_name}"`,
    i.on_hand,
    i.reserved,
    i.available,
    i.reorder_at,
    `"${i.status}"`
  ]);
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `inventory_${selectedBranch.value}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

function formatDateTime(dt) {
  if (!dt) return '—';
  return new Date(dt).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function txBadgeClass(type) {
  switch (type) {
    case 'production':
      return 'bg-blue-100 text-blue-800';
    case 'transfer_dispatch':
      return 'bg-amber-100 text-amber-800';
    case 'transfer_receive':
      return 'bg-emerald-100 text-emerald-800';
    case 'order_placed':
      return 'bg-purple-100 text-purple-800';
    case 'order_completed':
      return 'bg-green-100 text-green-800';
    case 'order_cancelled':
      return 'bg-red-100 text-red-800';
    case 'damaged':
    case 'expired':
      return 'bg-rose-100 text-rose-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

onMounted(async () => {
  if (!isSuperAdmin.value && userBranchId.value) {
    selectedBranch.value = userBranchId.value;
    adjustForm.value.branch_id = userBranchId.value;
  }
  await loadInventory();
  await loadTransactions();
  await loadReconcile();
});
</script>
