<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-16 font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#EFE8DE] pb-5">
      <div>
        <div class="flex items-center space-x-2.5">
          <span class="text-2xl">📝</span>
          <h1 class="font-serif text-2xl sm:text-3xl font-black text-[#2B1810]">
            Daily Sales Report
          </h1>
        </div>
        <p class="text-sm text-[#7D6E64] mt-1">
          Branch Seller order &amp; custom cake sales recording. Submit completed reports to your Branch Manager for verification.
        </p>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center space-x-2 bg-[#F3ECE1] p-1 rounded-2xl border border-[#E5DACD] self-start sm:self-auto">
        <button
          @click="activeTab = 'form'"
          class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer"
          :class="activeTab === 'form' ? 'bg-[#3D251E] text-white shadow-xs' : 'text-[#6E5D52] hover:text-[#2B1810]'"
        >
          ✏️ Record Daily Sales
        </button>
        <button
          @click="activeTab = 'history'"
          class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer"
          :class="activeTab === 'history' ? 'bg-[#3D251E] text-white shadow-xs' : 'text-[#6E5D52] hover:text-[#2B1810]'"
        >
          📋 My Submitted Reports ({{ reportsHistory.length }})
        </button>
      </div>
    </div>

    <!-- Alert / Banner when editing a Returned Report -->
    <div
      v-if="editingReportId && isReturned"
      class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start space-x-3 text-rose-900 shadow-2xs"
    >
      <span class="text-xl">⚠️</span>
      <div class="space-y-1">
        <div class="text-sm font-bold">This report was returned by your Branch Manager for correction</div>
        <div class="text-xs bg-white/70 p-2.5 rounded-xl border border-rose-200 font-medium">
          <strong>Manager Note:</strong> {{ currentManagerNotes || 'Please double check product quantities and payment amounts.' }}
        </div>
        <div class="text-xs text-rose-700">Please review the indicated items below, make necessary adjustments, and click "Submit to Branch Manager".</div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: RECORD DAILY SALES FORM                                    -->
    <!-- ================================================================= -->
    <div v-show="activeTab === 'form'" class="space-y-6">

      <!-- SECTION A: GENERAL INFORMATION -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-base">🏢</span>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
              A. General Information (Auto-Assigned)
            </h2>
          </div>
          <button
            type="button"
            @click="autoFillToday"
            :disabled="loadingAutoFill"
            class="px-3 py-1.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-[#3D251E] text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
            title="Import all orders & custom cakes recorded in the system today for your branch"
          >
            <span>⚡</span>
            <span>{{ loadingAutoFill ? 'Importing...' : 'Auto-Fill Today\'s Orders' }}</span>
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
          <!-- Branch Name -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Branch Name</label>
            <div class="px-3.5 py-2.5 bg-[#FAF7F2] border border-[#E5DCD0] rounded-xl font-bold text-[#2B1810] truncate">
              {{ branchName || 'Loading branch...' }}
            </div>
          </div>

          <!-- Seller Name -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Seller Name</label>
            <div class="px-3.5 py-2.5 bg-[#FAF7F2] border border-[#E5DCD0] rounded-xl font-bold text-[#2B1810] truncate">
              {{ sellerName || 'Loading seller...' }}
            </div>
          </div>

          <!-- Report Date -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Report Date *</label>
            <input
              v-model="form.report_date"
              type="date"
              @change="onDateChange"
              class="w-full px-3.5 py-2 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
            />
          </div>

          <!-- Report Reference Number -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Reference Number</label>
            <div class="px-3.5 py-2.5 bg-[#FAF7F2] border border-[#E5DCD0] rounded-xl font-mono text-[11px] font-bold text-[#3D251E] truncate">
              {{ form.report_reference || 'Generating ref...' }}
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION B: REGULAR ORDERS -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-base">🍞</span>
            <div>
              <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
                B. Regular Bakery Orders
              </h2>
              <p class="text-[11px] text-[#7D6E64]">Record all bread, pastries, cake slices, and bakery retail items sold.</p>
            </div>
          </div>
          <button
            type="button"
            @click="addRegularOrderRow"
            class="px-3 py-1.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1 shadow-2xs"
          >
            <span>+</span>
            <span>Add Regular Order</span>
          </button>
        </div>

        <!-- Table of Regular Orders -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
                <th class="py-2.5 px-2">Order #</th>
                <th class="py-2.5 px-2">Product / Order Name *</th>
                <th class="py-2.5 px-2">Category</th>
                <th class="py-2.5 px-2 w-20">Qty *</th>
                <th class="py-2.5 px-2 w-28">Unit Price (ETB) *</th>
                <th class="py-2.5 px-2 w-28">Total Amount (ETB)</th>
                <th class="py-2.5 px-2 w-28">Order Status</th>
                <th class="py-2.5 px-2 w-28">Payment Status</th>
                <th class="py-2.5 px-2">Customer Info</th>
                <th class="py-2.5 px-2 w-10 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F2ECE4]">
              <tr v-if="form.orders.length === 0">
                <td colspan="10" class="py-6 text-center text-[#9E8E82] text-xs">
                  No regular orders added yet. Click "+ Add Regular Order" or "Auto-Fill Today's Orders".
                </td>
              </tr>
              <tr v-for="(row, idx) in form.orders" :key="idx" class="hover:bg-[#FAF7F2]/60 transition">
                <td class="py-2 px-2">
                  <input
                    v-model="row.order_number"
                    type="text"
                    placeholder="ORD-001"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-mono font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.product_name"
                    type="text"
                    placeholder="e.g. Croissant / Baguette"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <select
                    v-model="row.category"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  >
                    <option value="Pastry">Pastry</option>
                    <option value="Cake">Cake</option>
                    <option value="Bread">Bread</option>
                    <option value="Cookies">Cookies</option>
                    <option value="Beverage">Beverage</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Other">Other</option>
                  </select>
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model.number="row.quantity"
                    type="number"
                    min="1"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model.number="row.unit_price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <!-- Total Amount: Auto-Calculated = Qty * Unit Price -->
                <td class="py-2 px-2 text-right font-bold text-[#2B1810]">
                  ETB {{ (Number(row.quantity || 0) * Number(row.unit_price || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </td>
                <td class="py-2 px-2">
                  <select
                    v-model="row.order_status"
                    class="w-full px-1.5 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#3D251E]"
                  >
                    <option value="Completed">Completed</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Pending">Pending</option>
                    <option value="Cancelled">Cancelled</option>
                  </select>
                </td>
                <td class="py-2 px-2">
                  <select
                    v-model="row.payment_status"
                    class="w-full px-1.5 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#3D251E]"
                  >
                    <option value="Paid">Paid</option>
                    <option value="Partially Paid">Partially Paid</option>
                    <option value="Unpaid">Unpaid</option>
                  </select>
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.customer_info"
                    type="text"
                    placeholder="Walk-in / Abebe (091...)"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2 text-center">
                  <button
                    type="button"
                    @click="removeRegularOrderRow(idx)"
                    class="text-rose-500 hover:text-rose-700 font-bold text-sm px-1.5 py-0.5 rounded transition cursor-pointer"
                    title="Remove row"
                  >
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECTION C: CUSTOM CAKE ORDERS -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-base">🎂</span>
            <div>
              <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
                C. Custom Cake Orders &amp; Advances
              </h2>
              <p class="text-[11px] text-[#7D6E64]">Record custom-designed cakes, advance deposits, and remaining balances.</p>
            </div>
          </div>
          <button
            type="button"
            @click="addCustomCakeRow"
            class="px-3 py-1.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1 shadow-2xs"
          >
            <span>+</span>
            <span>Add Custom Cake</span>
          </button>
        </div>

        <!-- Table of Custom Cake Orders -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse min-w-[900px]">
            <thead>
              <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
                <th class="py-2.5 px-2">Cake Order #</th>
                <th class="py-2.5 px-2">Customer *</th>
                <th class="py-2.5 px-2">Cake Category</th>
                <th class="py-2.5 px-2">Flavor &amp; Design</th>
                <th class="py-2.5 px-2 w-20">Size/Wt</th>
                <th class="py-2.5 px-2 w-16">Qty</th>
                <th class="py-2.5 px-2 w-24">Unit Price</th>
                <th class="py-2.5 px-2 w-24">Total Price</th>
                <th class="py-2.5 px-2 w-24">Advance Paid</th>
                <th class="py-2.5 px-2 w-24">Balance Due</th>
                <th class="py-2.5 px-2 w-28">Delivery Date</th>
                <th class="py-2.5 px-2 w-24">Status</th>
                <th class="py-2.5 px-2 w-10 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F2ECE4]">
              <tr v-if="form.custom_cakes.length === 0">
                <td colspan="13" class="py-6 text-center text-[#9E8E82] text-xs">
                  No custom cake orders recorded yet. Click "+ Add Custom Cake" or "Auto-Fill Today's Orders".
                </td>
              </tr>
              <tr v-for="(row, idx) in form.custom_cakes" :key="idx" class="hover:bg-[#FAF7F2]/60 transition">
                <td class="py-2 px-2">
                  <input
                    v-model="row.cake_order_number"
                    type="text"
                    placeholder="CCR-001"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-mono font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.customer_name"
                    type="text"
                    placeholder="Customer name"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <select
                    v-model="row.cake_category"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  >
                    <option value="Birthday">Birthday</option>
                    <option value="Wedding">Wedding</option>
                    <option value="Anniversary">Anniversary</option>
                    <option value="Graduation">Graduation</option>
                    <option value="Custom Celebration">Custom Celebration</option>
                  </select>
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.cake_type_design"
                    type="text"
                    placeholder="Chocolate Ganache - Floral"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.size_weight"
                    type="text"
                    placeholder="2 kg"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-center font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model.number="row.quantity"
                    type="number"
                    min="1"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model.number="row.unit_price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <!-- Total Cake Price: Auto-Calculated = Qty * Unit Price -->
                <td class="py-2 px-2 text-right font-bold text-[#2B1810]">
                  ETB {{ (Number(row.quantity || 0) * Number(row.unit_price || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model.number="row.advance_payment"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs text-right font-bold text-[#059669] focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <!-- Remaining Balance: Auto-Calculated = Total Price - Advance Payment -->
                <td class="py-2 px-2 text-right font-bold text-[#D97706]">
                  ETB {{ Math.max(0, (Number(row.quantity || 0) * Number(row.unit_price || 0)) - Number(row.advance_payment || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </td>
                <td class="py-2 px-2">
                  <input
                    v-model="row.delivery_date"
                    type="date"
                    class="w-full px-2 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-medium focus:ring-1 focus:ring-[#3D251E]"
                  />
                </td>
                <td class="py-2 px-2">
                  <select
                    v-model="row.order_status"
                    class="w-full px-1.5 py-1 bg-white border border-[#E2D8CC] rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#3D251E]"
                  >
                    <option value="Confirmed">Confirmed</option>
                    <option value="In Production">In Production</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>
                  </select>
                </td>
                <td class="py-2 px-2 text-center">
                  <button
                    type="button"
                    @click="removeCustomCakeRow(idx)"
                    class="text-rose-500 hover:text-rose-700 font-bold text-sm px-1.5 py-0.5 rounded transition cursor-pointer"
                    title="Remove row"
                  >
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECTION D: DAILY SALES SUMMARY (AUTOMATICALLY CALCULATED - READ ONLY) -->
      <div class="bg-[#FBF8F4] border-2 border-[#E5DACD] rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center space-x-2 border-b border-[#E8DDD0] pb-3">
          <span class="text-xl">📊</span>
          <div>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
              D. Daily Sales Summary (Automatically Calculated &amp; Protected)
            </h2>
            <p class="text-[11px] text-[#7D6E64]">Totals are automatically derived from order line items and cannot be manipulated.</p>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 text-xs">
          <!-- Regular Orders Count -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Regular Orders</div>
            <div class="text-xl font-serif font-black text-[#2B1810] mt-1">{{ summaryTotals.regularOrdersCount }}</div>
            <div class="text-[11px] text-[#6E5D52] mt-0.5">{{ summaryTotals.regularProductsSold }} items sold</div>
          </div>

          <!-- Regular Revenue -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Regular Revenue</div>
            <div class="text-xl font-serif font-black text-[#2B1810] mt-1">
              ETB {{ summaryTotals.regularOrderRevenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-[11px] text-[#059669] font-medium mt-0.5">Retail bakery sales</div>
          </div>

          <!-- Custom Cakes Count -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Custom Cakes</div>
            <div class="text-xl font-serif font-black text-[#2B1810] mt-1">{{ summaryTotals.customCakeOrdersCount }}</div>
            <div class="text-[11px] text-[#6E5D52] mt-0.5">{{ summaryTotals.customCakesSold }} cakes ordered</div>
          </div>

          <!-- Custom Cake Revenue -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Custom Cake Value</div>
            <div class="text-xl font-serif font-black text-[#2B1810] mt-1">
              ETB {{ summaryTotals.customCakeRevenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-[11px] text-[#6E5D52] mt-0.5">Total booked value</div>
          </div>

          <!-- Advances Paid -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#059669]">Advance Payments</div>
            <div class="text-xl font-serif font-black text-[#059669] mt-1">
              ETB {{ summaryTotals.totalAdvancePayments.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-[11px] text-[#059669] font-medium mt-0.5">Deposits collected</div>
          </div>

          <!-- Outstanding Balances -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#D97706]">Outstanding Balance</div>
            <div class="text-xl font-serif font-black text-[#D97706] mt-1">
              ETB {{ summaryTotals.totalOutstandingBalances.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-[11px] text-[#D97706] font-medium mt-0.5">Due on collection</div>
          </div>

          <!-- Total Payments Received Today -->
          <div class="bg-white border border-[#EBE1D5] rounded-xl p-3.5 shadow-2xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#2B1810]">Cash/Payments In</div>
            <div class="text-xl font-serif font-black text-[#2B1810] mt-1">
              ETB {{ summaryTotals.totalPaymentsReceived.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-[11px] text-[#7D6E64] mt-0.5">Regular + Advances</div>
          </div>

          <!-- Combined Daily Sales Revenue (Highlight) -->
          <div class="col-span-2 sm:col-span-3 lg:col-span-3 bg-gradient-to-r from-[#3D251E] to-[#57392C] text-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
              <div class="text-[11px] font-bold uppercase tracking-widest text-[#D8CDC5]">Combined Daily Sales Revenue</div>
              <div class="text-2xl sm:text-3xl font-serif font-black text-amber-200 mt-0.5">
                ETB {{ summaryTotals.combinedDailyRevenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              </div>
              <div class="text-xs text-[#E5DACD] mt-0.5">Regular Orders ({{ summaryTotals.regularOrdersCount }}) + Custom Cakes ({{ summaryTotals.customCakeOrdersCount }})</div>
            </div>
            <span class="text-3xl hidden sm:inline select-none">🏆</span>
          </div>
        </div>
      </div>

      <!-- SECTION E: SELLER SUBMISSION ACTIONS -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs text-[#7D6E64]">
          <span class="font-bold text-[#2B1810]">Reporting Policy:</span> Once submitted, the report is securely transferred to your Branch Manager for verification and cannot be modified unless returned with notes.
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <button
            type="button"
            @click="saveReport('draft')"
            :disabled="saving"
            class="px-5 py-2.5 bg-[#FAF7F2] hover:bg-[#EFE8DE] text-[#3D251E] font-bold text-xs sm:text-sm rounded-xl border border-[#D8CDC5] transition cursor-pointer"
          >
            {{ saving && submitAction === 'draft' ? 'Saving...' : '💾 Save as Draft' }}
          </button>

          <button
            type="button"
            @click="saveReport('submit')"
            :disabled="saving"
            class="px-6 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer shadow-xs flex items-center space-x-2"
          >
            <span>{{ saving && submitAction === 'submit' ? 'Submitting...' : '📤 Submit to Branch Manager' }}</span>
          </button>
        </div>
      </div>

    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: MY SUBMITTED REPORTS HISTORY                               -->
    <!-- ================================================================= -->
    <div v-show="activeTab === 'history'" class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F4EFEA] pb-3">
        <div>
          <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
            My Daily Sales Reports History
          </h2>
          <p class="text-xs text-[#7D6E64]">View the verification status of your daily sales submissions.</p>
        </div>
        <button
          @click="fetchReportsHistory"
          class="px-3 py-1.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer"
        >
          🔄 Refresh
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
              <th class="py-3 px-3">Reference #</th>
              <th class="py-3 px-3">Report Date</th>
              <th class="py-3 px-3 text-center">Regular Orders</th>
              <th class="py-3 px-3 text-center">Custom Cakes</th>
              <th class="py-3 px-3 text-right">Combined Revenue</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-3">Manager Notes</th>
              <th class="py-3 px-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F2ECE4]">
            <tr v-if="reportsHistory.length === 0">
              <td colspan="8" class="py-8 text-center text-[#9E8E82] text-xs">
                No past daily reports submitted yet.
              </td>
            </tr>
            <tr v-for="rep in reportsHistory" :key="rep.id" class="hover:bg-[#FAF7F2]/60 transition">
              <td class="py-3 px-3 font-mono font-bold text-[#3D251E]">
                {{ rep.report_reference }}
              </td>
              <td class="py-3 px-3 font-semibold text-[#2B1810]">
                {{ formatDate(rep.report_date) }}
              </td>
              <td class="py-3 px-3 text-center">
                <span class="font-bold text-[#2B1810]">{{ rep.regular_orders_count }}</span>
                <span class="text-[#7D6E64] text-[10px] block">({{ rep.regular_products_sold }} items)</span>
              </td>
              <td class="py-3 px-3 text-center">
                <span class="font-bold text-[#2B1810]">{{ rep.custom_cake_orders_count }}</span>
                <span class="text-[#7D6E64] text-[10px] block">({{ rep.custom_cakes_sold }} cakes)</span>
              </td>
              <td class="py-3 px-3 text-right font-serif font-black text-[#2B1810]">
                ETB {{ Number(rep.combined_daily_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-block"
                  :class="{
                    'bg-amber-50 text-amber-800 border-amber-200': rep.status === 'Pending Verification',
                    'bg-emerald-50 text-emerald-800 border-emerald-200': rep.status === 'Approved',
                    'bg-rose-50 text-rose-800 border-rose-200': rep.status === 'Returned for Correction',
                    'bg-stone-100 text-stone-700 border-stone-200': rep.status === 'Draft'
                  }"
                >
                  {{ rep.status }}
                </span>
              </td>
              <td class="py-3 px-3 text-xs max-w-xs truncate" :title="rep.manager_notes">
                <span v-if="rep.manager_notes" class="text-rose-700 font-medium">
                  "{{ rep.manager_notes }}"
                </span>
                <span v-else class="text-[#B29E91] italic">—</span>
              </td>
              <td class="py-3 px-3 text-center space-x-2">
                <button
                  @click="viewReportDetails(rep.id)"
                  class="text-xs text-[#3D251E] font-bold hover:underline cursor-pointer"
                >
                  View Details
                </button>
                <button
                  v-if="['Draft', 'Returned for Correction'].includes(rep.status)"
                  @click="editReport(rep.id)"
                  class="text-xs text-amber-700 font-bold hover:underline cursor-pointer"
                >
                  Edit / Fix
                </button>
                <button
                  v-if="rep.status === 'Draft'"
                  @click="deleteDraft(rep.id)"
                  class="text-xs text-rose-700 font-bold hover:underline cursor-pointer"
                >
                  Delete Draft
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- DETAIL MODAL -->
    <div
      v-if="selectedReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="selectedReport = null"
    >
      <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 space-y-5 border border-[#E9E0D4] shadow-xl">
        <div class="flex items-center justify-between border-b border-[#F2ECE4] pb-3">
          <div>
            <h3 class="font-serif font-black text-xl text-[#2B1810]">
              {{ selectedReport.report_reference }}
            </h3>
            <p class="text-xs text-[#7D6E64]">
              Branch: {{ selectedReport.branch?.branch_name }} | Date: {{ formatDate(selectedReport.report_date) }} | Status: <strong>{{ selectedReport.status }}</strong>
            </p>
          </div>
          <button
            @click="selectedReport = null"
            class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold p-1 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Manager note if present -->
        <div v-if="selectedReport.manager_notes" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900">
          <strong>Manager Note:</strong> {{ selectedReport.manager_notes }}
        </div>

        <!-- Regular Orders -->
        <div class="space-y-2">
          <div class="font-bold text-xs uppercase tracking-wider text-[#2B1810]">Regular Orders ({{ selectedReport.orders?.length || 0 }})</div>
          <div class="overflow-x-auto border border-[#E9E0D4] rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px]">
                <tr>
                  <th class="p-2">Order #</th>
                  <th class="p-2">Item</th>
                  <th class="p-2">Category</th>
                  <th class="p-2 text-right">Qty</th>
                  <th class="p-2 text-right">Unit Price</th>
                  <th class="p-2 text-right">Total</th>
                  <th class="p-2">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F2ECE4]">
                <tr v-for="o in selectedReport.orders" :key="o.id">
                  <td class="p-2 font-mono">{{ o.order_number }}</td>
                  <td class="p-2 font-semibold">{{ o.product_name }}</td>
                  <td class="p-2">{{ o.category }}</td>
                  <td class="p-2 text-right">{{ o.quantity }}</td>
                  <td class="p-2 text-right">ETB {{ Number(o.unit_price).toFixed(2) }}</td>
                  <td class="p-2 text-right font-bold">ETB {{ Number(o.total_amount).toFixed(2) }}</td>
                  <td class="p-2">{{ o.order_status }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Custom Cakes -->
        <div class="space-y-2">
          <div class="font-bold text-xs uppercase tracking-wider text-[#2B1810]">Custom Cakes ({{ selectedReport.custom_cakes?.length || 0 }})</div>
          <div class="overflow-x-auto border border-[#E9E0D4] rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px]">
                <tr>
                  <th class="p-2">Cake #</th>
                  <th class="p-2">Customer</th>
                  <th class="p-2">Design &amp; Size</th>
                  <th class="p-2 text-right">Price</th>
                  <th class="p-2 text-right">Advance</th>
                  <th class="p-2 text-right">Balance</th>
                  <th class="p-2">Delivery</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F2ECE4]">
                <tr v-for="c in selectedReport.custom_cakes" :key="c.id">
                  <td class="p-2 font-mono">{{ c.cake_order_number }}</td>
                  <td class="p-2 font-semibold">{{ c.customer_name }}</td>
                  <td class="p-2">{{ c.cake_type_design }} ({{ c.size_weight }})</td>
                  <td class="p-2 text-right font-bold">ETB {{ Number(c.total_price).toFixed(2) }}</td>
                  <td class="p-2 text-right text-emerald-700 font-bold">ETB {{ Number(c.advance_payment).toFixed(2) }}</td>
                  <td class="p-2 text-right text-amber-700 font-bold">ETB {{ Number(c.remaining_balance).toFixed(2) }}</td>
                  <td class="p-2">{{ formatDate(c.delivery_date) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-[#FAF7F2] p-4 rounded-xl border border-[#E9E0D4]">
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Regular Revenue</div>
            <div class="font-bold text-sm text-[#2B1810]">ETB {{ Number(selectedReport.regular_order_revenue).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Custom Cake Revenue</div>
            <div class="font-bold text-sm text-[#2B1810]">ETB {{ Number(selectedReport.custom_cake_revenue).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Total Payments In</div>
            <div class="font-bold text-sm text-emerald-700">ETB {{ Number(selectedReport.total_payments_received).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Combined Daily Revenue</div>
            <div class="font-bold text-base text-[#3D251E]">ETB {{ Number(selectedReport.combined_daily_revenue).toFixed(2) }}</div>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="selectedReport = null"
            class="px-5 py-2 bg-[#3D251E] text-white text-xs font-bold rounded-xl cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

const authStore = useAuthStore();

const activeTab = ref('form');
const saving = ref(false);
const submitAction = ref('submit');
const loadingAutoFill = ref(false);
const editingReportId = ref(null);
const currentManagerNotes = ref('');
const isReturned = ref(false);

const branchName = computed(() => authStore.user?.branch?.branch_name || 'My Branch');
const sellerName = computed(() => authStore.user?.full_name || authStore.user?.name || 'Seller');

// Form state
const form = reactive({
  report_date: new Date().toISOString().substring(0, 10),
  report_reference: '',
  orders: [],
  custom_cakes: [],
});

const reportsHistory = ref([]);
const selectedReport = ref(null);

// Summary calculations derived in real-time
const summaryTotals = computed(() => {
  const regOrdersCount = form.orders.length;
  const regProductsSold = form.orders.reduce((acc, row) => acc + (Number(row.quantity) || 0), 0);
  const regOrderRevenue = form.orders.reduce((acc, row) => {
    return acc + ((Number(row.quantity) || 0) * (Number(row.unit_price) || 0));
  }, 0);

  const customCakeOrdersCount = form.custom_cakes.length;
  const customCakesSold = form.custom_cakes.reduce((acc, row) => acc + (Number(row.quantity) || 0), 0);
  const customCakeRevenue = form.custom_cakes.reduce((acc, row) => {
    return acc + ((Number(row.quantity) || 0) * (Number(row.unit_price) || 0));
  }, 0);

  const totalAdvancePayments = form.custom_cakes.reduce((acc, row) => acc + (Number(row.advance_payment) || 0), 0);
  const totalOutstandingBalances = form.custom_cakes.reduce((acc, row) => {
    const total = (Number(row.quantity) || 0) * (Number(row.unit_price) || 0);
    const adv = Number(row.advance_payment) || 0;
    return acc + Math.max(0, total - adv);
  }, 0);

  const combinedDailyRevenue = regOrderRevenue + customCakeRevenue;
  const totalPaymentsReceived = regOrderRevenue + totalAdvancePayments;

  return {
    regularOrdersCount: regOrdersCount,
    regularProductsSold: regProductsSold,
    regularOrderRevenue: regOrderRevenue,
    customCakeOrdersCount: customCakeOrdersCount,
    customCakesSold: customCakesSold,
    customCakeRevenue: customCakeRevenue,
    totalAdvancePayments: totalAdvancePayments,
    totalOutstandingBalances: totalOutstandingBalances,
    combinedDailyRevenue: combinedDailyRevenue,
    totalPaymentsReceived: totalPaymentsReceived,
  };
});

// Row modifiers
function addRegularOrderRow() {
  const nextNum = 'ORD-' + (form.orders.length + 1).toString().padStart(3, '0');
  form.orders.push({
    order_number: nextNum,
    product_name: '',
    category: 'Pastry',
    quantity: 1,
    unit_price: 0,
    order_status: 'Completed',
    payment_status: 'Paid',
    customer_info: '',
  });
}

function removeRegularOrderRow(index) {
  form.orders.splice(index, 1);
}

function addCustomCakeRow() {
  const nextNum = 'CCR-' + (form.custom_cakes.length + 1).toString().padStart(3, '0');
  form.custom_cakes.push({
    cake_order_number: nextNum,
    customer_name: '',
    cake_category: 'Birthday',
    cake_type_design: '',
    size_weight: '2 kg',
    quantity: 1,
    unit_price: 0,
    advance_payment: 0,
    delivery_date: form.report_date,
    order_status: 'Confirmed',
    payment_status: 'Partially Paid',
  });
}

function removeCustomCakeRow(index) {
  form.custom_cakes.splice(index, 1);
}

function formatDate(d) {
  if (!d) return '—';
  try {
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch (e) {
    return d;
  }
}

// 1-Click Auto-Fill Today's Branch Orders
async function autoFillToday() {
  loadingAutoFill.value = true;
  try {
    const res = await api.get(`/admin/reports/seller/today-orders?date=${form.report_date}`);
    if (res.data && res.data.success) {
      if (!editingReportId.value && res.data.suggested_reference) {
        form.report_reference = res.data.suggested_reference;
      }

      // Merge or load rows
      if (res.data.regular_orders && res.data.regular_orders.length > 0) {
        form.orders = res.data.regular_orders;
      } else if (form.orders.length === 0) {
        addRegularOrderRow();
      }

      if (res.data.custom_cakes && res.data.custom_cakes.length > 0) {
        form.custom_cakes = res.data.custom_cakes;
      }
    }
  } catch (e) {
    console.error('Failed to auto-fill orders:', e);
  } finally {
    loadingAutoFill.value = false;
  }
}

async function onDateChange() {
  if (!editingReportId.value) {
    await autoFillToday();
  }
}

// Save or Submit
async function saveReport(action) {
  if (form.orders.length === 0 && form.custom_cakes.length === 0) {
    alert('Please record at least one regular order or custom cake before submitting.');
    return;
  }

  // Validate required fields
  for (let i = 0; i < form.orders.length; i++) {
    const o = form.orders[i];
    if (!o.order_number || !o.product_name) {
      alert(`Regular order row #${i + 1} requires an order number and product name.`);
      return;
    }
  }

  for (let i = 0; i < form.custom_cakes.length; i++) {
    const c = form.custom_cakes[i];
    if (!c.cake_order_number || !c.customer_name) {
      alert(`Custom cake row #${i + 1} requires a cake order number and customer name.`);
      return;
    }
  }

  if (action === 'submit') {
    const confirmMsg = `Submit Daily Sales Report for ETB ${summaryTotals.value.combinedDailyRevenue.toFixed(2)} to your Branch Manager for verification?`;
    if (!confirm(confirmMsg)) return;
  }

  saving.value = true;
  submitAction.value = action;

  try {
    const payload = {
      action: action,
      report_date: form.report_date,
      report_reference: form.report_reference,
      orders: form.orders,
      custom_cakes: form.custom_cakes,
    };

    let res;
    if (editingReportId.value) {
      res = await api.put(`/admin/reports/seller/daily-reports/${editingReportId.value}`, payload);
    } else {
      res = await api.post('/admin/reports/seller/daily-reports', payload);
    }

    if (res.data && res.data.success) {
      alert(res.data.message || (action === 'submit' ? 'Submitted to Branch Manager successfully!' : 'Draft saved.'));
      await fetchReportsHistory();

      // Reset form if submitted
      if (action === 'submit') {
        editingReportId.value = null;
        isReturned.value = false;
        form.orders = [];
        form.custom_cakes = [];
        activeTab.value = 'history';
      }
    }
  } catch (err) {
    console.error('Failed to submit report:', err);
    alert(err.response?.data?.message || 'Error saving daily report. Please try again.');
  } finally {
    saving.value = false;
  }
}

// Fetch history
async function fetchReportsHistory() {
  try {
    const res = await api.get('/admin/reports/seller/daily-reports');
    if (res.data && res.data.success) {
      reportsHistory.value = res.data.data?.data || res.data.data || [];
    }
  } catch (e) {
    console.error('Failed to fetch reports history:', e);
  }
}

// View details modal
async function viewReportDetails(id) {
  try {
    const res = await api.get(`/admin/reports/seller/daily-reports/${id}`);
    if (res.data && res.data.success) {
      selectedReport.value = res.data.report;
    }
  } catch (e) {
    alert('Failed to load report details.');
  }
}

// Edit existing draft or returned report
async function editReport(id) {
  try {
    const res = await api.get(`/admin/reports/seller/daily-reports/${id}`);
    if (res.data && res.data.success) {
      const rep = res.data.report;
      editingReportId.value = rep.id;
      isReturned.value = rep.status === 'Returned for Correction';
      currentManagerNotes.value = rep.manager_notes || '';
      form.report_date = rep.report_date?.substring(0, 10) || new Date().toISOString().substring(0, 10);
      form.report_reference = rep.report_reference;
      form.orders = rep.orders?.map(o => ({ ...o })) || [];
      form.custom_cakes = rep.custom_cakes?.map(c => ({ ...c })) || [];
      activeTab.value = 'form';
    }
  } catch (e) {
    alert('Failed to load report for editing.');
  }
}

async function deleteDraft(id) {
  if (!confirm('Are you sure you want to delete this draft daily report? Submitted reports cannot be deleted.')) {
    return;
  }
  try {
    const res = await axios.delete(`/reports/seller/daily-reports/${id}`);
    if (res.data.success) {
      alert('Draft report deleted successfully.');
      await fetchReportsHistory();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete report.');
  }
}

onMounted(async () => {
  await autoFillToday();
  await fetchReportsHistory();
});
</script>

