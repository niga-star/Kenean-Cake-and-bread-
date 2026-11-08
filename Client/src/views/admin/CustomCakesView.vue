<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header with Branch Selector and Status Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-serif font-bold text-[#2A1D15]">Custom Cake Requests</h2>
        <p class="text-xs text-[#7A6B60] mt-0.5">Manage bespoke cake briefs, quotation lifecycle, and kitchen baking stages.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Branch Selector or Scoped Badge -->
        <div
          v-if="isBranchScoped"
          class="bg-[#FAF3EC] border border-[#EACBB8] rounded-xl px-3.5 py-2 text-xs font-bold text-[#BA5420] flex items-center space-x-1.5 shadow-2xs"
        >
          <span>📍</span>
          <span>{{ userBranchName }} Only</span>
        </div>
        <select
          v-else
          v-model="selectedBranch"
          @change="loadRequests"
          class="bg-white border border-[#D8CDC0] rounded-xl px-3.5 py-2 text-xs font-semibold text-[#4A3B32] shadow-2xs focus:ring-1 focus:ring-[#BA5420] cursor-pointer"
        >
          <option value="all">All Branches</option>
          <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
            {{ b.branch_name }}
          </option>
        </select>

        <router-link
          to="/admin/staff-reports"
          class="px-3.5 py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-xl transition shadow-2xs flex items-center space-x-1"
        >
          <span>📋</span>
          <span>Submit Cake Report</span>
        </router-link>

        <select
          v-model="selectedStatusFilter"
          @change="loadRequests"
          class="bg-white border border-[#D8CDC0] rounded-xl px-3.5 py-2 text-xs font-semibold text-[#4A3B32] shadow-2xs focus:ring-1 focus:ring-[#BA5420]"
        >
          <option value="all">All Statuses</option>
          <option value="Submitted">Submitted (Waiting Quote)</option>
          <option value="Quoted">Quoted (Awaiting Deposit)</option>
          <option value="Confirmed">Confirmed (Deposit Paid)</option>
          <option value="Preparing">Preparing (In Oven / Kitchen)</option>
          <option value="Ready">Ready (For Handover)</option>
          <option value="Delivered">Delivered / Handed Over</option>
          <option value="Rejected">Rejected</option>
        </select>

        <!-- Date Filter (Step 6) -->
        <select
          v-model="selectedDateFilter"
          @change="loadRequests"
          class="bg-white border border-[#D8CDC0] rounded-xl px-3.5 py-2 text-xs font-semibold text-[#4A3B32] shadow-2xs focus:ring-1 focus:ring-[#BA5420] cursor-pointer"
        >
          <option value="all">All Dates</option>
          <option value="today">Today</option>
          <option value="this_week">This Week</option>
          <option value="this_month">This Month</option>
          <option value="custom">Custom Date Range</option>
        </select>
      </div>

      <!-- Custom Date Inputs -->
      <div v-if="selectedDateFilter === 'custom'" class="flex flex-wrap items-center gap-3 p-3 bg-[#FAF7F2] rounded-xl border border-[#E9E0D4] text-xs">
        <div class="flex items-center space-x-2">
          <label class="font-semibold text-[#65554B]">From:</label>
          <input
            v-model="customStartDate"
            @change="loadRequests"
            type="date"
            class="bg-white border border-[#D8CDC0] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
          />
        </div>
        <div class="flex items-center space-x-2">
          <label class="font-semibold text-[#65554B]">To:</label>
          <input
            v-model="customEndDate"
            @change="loadRequests"
            type="date"
            class="bg-white border border-[#D8CDC0] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
          />
        </div>
        <button
          type="button"
          @click="clearCustomDates"
          class="font-semibold text-[#BA5420] hover:underline cursor-pointer"
        >
          Reset Dates
        </button>
      </div>
    </div>

    <!-- 4 Summary Stat Cards (100% Real Database Driven) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Submitted</div>
        <div class="text-3xl font-bold text-[#241710]">{{ summary.submitted || 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Waiting for a quote</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Quoted</div>
        <div class="text-3xl font-bold text-[#241710]">{{ summary.quoted || 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Waiting for customer acceptance</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Confirmed</div>
        <div class="text-3xl font-bold text-[#241710]">{{ summary.confirmed || 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Deposit paid &amp; scheduled</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Ready for Handover</div>
        <div class="text-3xl font-bold text-[#241710]">{{ summary.ready_this_week || 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Awaiting pickup or delivery</div>
      </div>
    </div>

    <!-- Notification / Success Message -->
    <div v-if="successMsg" class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <span>✓</span>
        <span>{{ successMsg }}</span>
      </div>
      <button @click="successMsg = ''" class="text-emerald-700 hover:text-emerald-900 font-bold">✕</button>
    </div>

    <!-- Main Content Grid: Requests Table + Quote & Lifecycle Side Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Requests Table (Col span 2) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#EFE8DF] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center space-x-2">
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Requests</h3>
            <span class="text-xs text-[#8A7B71]">({{ filteredRequests.length }} records)</span>
          </div>

          <div class="relative w-full sm:w-64">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by request #, customer, flavor..."
              class="w-full bg-[#FAF7F5] border border-[#D8CDC0] rounded-xl pl-8 pr-3 py-1.5 text-xs text-[#2A1D15] focus:outline-none focus:border-[#BA5420]"
            />
            <span class="absolute left-2.5 top-2 text-xs text-gray-400">🔍</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[620px] text-left text-sm">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-4">Request #</th>
                <th class="py-3 px-3">Branch</th>
                <th class="py-3 px-3">Customer</th>
                <th class="py-3 px-3">Configuration</th>
                <th class="py-3 px-3">Needed by</th>
                <th class="py-3 px-3">Quote</th>
                <th class="py-3 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-if="filteredRequests.length === 0">
                <td colspan="7" class="py-12 text-center text-xs text-[#8A7B71]">
                  No custom cake requests found matching current filters.
                </td>
              </tr>
              <tr
                v-for="req in filteredRequests"
                :key="req.request_id"
                @click="selectRequest(req)"
                class="hover:bg-[#FCFAF7] transition cursor-pointer"
                :class="selectedRequest?.request_id === req.request_id ? 'bg-[#FAF3EC]' : ''"
              >
                <td class="py-3.5 px-4 font-mono text-xs font-bold text-[#BA5420]">
                  {{ req.request_number }}
                </td>
                <td class="py-3.5 px-3 text-xs text-[#4A3B32] font-medium">
                  {{ req.branch?.branch_name || 'Bole' }}
                </td>
                <td class="py-3.5 px-3 text-xs text-[#382C24]">
                  <div class="font-bold text-[#241710]">{{ req.customer_name || req.user?.full_name || 'Customer' }}</div>
                  <div v-if="req.customer_phone || req.user?.phone" class="text-[11px] text-[#8A7B71]">
                    {{ req.customer_phone || req.user?.phone }}
                  </div>
                </td>
                <td class="py-3.5 px-3 text-xs text-[#5A4B41]">
                  <div class="font-medium text-[#2A1D15]">{{ req.cake_size }}</div>
                  <div class="text-[11px] text-[#7A6B60] truncate max-w-[140px]">{{ req.flavor }}</div>
                </td>
                <td class="py-3.5 px-3 text-xs text-[#7A6B60] whitespace-nowrap">
                  {{ req.needed_by }}
                </td>
                <td class="py-3.5 px-3 text-xs font-bold text-[#241710] whitespace-nowrap">
                  {{ req.quoted_price ? `ETB ${Number(req.quoted_price).toLocaleString()}` : '—' }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-[11px] font-bold rounded-full capitalize"
                    :class="statusBadgeStyle(req.status)"
                  >
                    {{ req.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Side Panel: Complete Details, Attached Photos, and Dynamic Status Progression -->
      <div v-if="selectedRequest" class="bg-white border border-[#E9E0D4] rounded-2xl shadow-2xs p-5 sm:p-6 space-y-4">
        <!-- Header -->
        <div class="border-b border-[#EFE8DF] pb-3">
          <div class="flex items-center justify-between">
            <span class="font-mono text-xs font-bold text-[#BA5420]">#{{ selectedRequest.request_number }}</span>
            <span
              class="px-2.5 py-0.5 text-[11px] font-bold rounded-full capitalize"
              :class="statusBadgeStyle(selectedRequest.status)"
            >
              {{ selectedRequest.status }}
            </span>
          </div>
          <h3 class="text-lg font-serif font-bold text-[#2A1D15] mt-1">
            {{ selectedRequest.branch?.branch_name || 'Bakery Branch' }}
          </h3>
          <div class="text-xs text-[#7A6B60]">Needed by: <strong class="text-[#2A1D15]">{{ selectedRequest.needed_by }}</strong></div>
        </div>

        <!-- Customer Contact Details -->
        <div class="bg-[#FAF7F5] p-3 rounded-xl border border-[#EFE8DF] space-y-1.5 text-xs">
          <div class="text-[10px] uppercase font-bold tracking-wider text-[#8A7B71]">Customer Contact</div>
          <div class="font-bold text-[#2A1D15] text-sm">
            👤 {{ selectedRequest.customer_name || selectedRequest.user?.full_name || 'Customer' }}
          </div>
          <div v-if="selectedRequest.customer_phone || selectedRequest.user?.phone" class="text-[#6A5A50] font-medium flex items-center space-x-1.5">
            <span>📞</span>
            <a :href="`tel:${selectedRequest.customer_phone || selectedRequest.user?.phone}`" class="hover:underline text-[#BA5420]">
              {{ selectedRequest.customer_phone || selectedRequest.user?.phone }}
            </a>
          </div>
          <div v-if="selectedRequest.user?.email" class="text-[#7A6B60] text-[11px]">
            ✉️ {{ selectedRequest.user.email }}
          </div>
        </div>

        <!-- Design Brief & Specifications -->
        <div class="space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-[#7A6B60]">Size &amp; Tiers:</span>
            <span class="font-bold text-[#2A1D15] text-right">{{ selectedRequest.cake_size }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#7A6B60]">Flavor Profile:</span>
            <span class="font-bold text-[#2A1D15] text-right">{{ selectedRequest.flavor }}</span>
          </div>
          <div v-if="selectedRequest.message" class="bg-rose-50/60 p-2.5 rounded-lg border border-rose-100">
            <span class="text-[10px] uppercase font-bold text-[#DE1B54] block mb-0.5">Inscription on Cake:</span>
            <span class="font-serif italic font-semibold text-[#DE1B54]">&ldquo;{{ selectedRequest.message }}&rdquo;</span>
          </div>
          <div v-if="selectedRequest.design" class="space-y-0.5">
            <span class="text-[10px] uppercase font-bold text-[#7A6B60] block">Design Notes:</span>
            <p class="text-xs text-[#4A3B32] bg-[#FAF7F5] p-2.5 rounded-lg border border-[#EFE8DF] leading-relaxed">
              {{ selectedRequest.design }}
            </p>
          </div>
        </div>

        <!-- Reference Images Attached by Customer -->
        <div class="space-y-1.5 pt-1">
          <div class="text-[10px] uppercase font-bold tracking-wider text-[#8A7B71] flex items-center justify-between">
            <span>Customer Reference Photos</span>
            <span>({{ (selectedRequest.images && selectedRequest.images.length) || 0 }})</span>
          </div>
          <div v-if="selectedRequest.images && selectedRequest.images.length > 0" class="flex space-x-2 overflow-x-auto pb-1">
            <img
              v-for="(img, idx) in selectedRequest.images"
              :key="idx"
              :src="img.image_url"
              alt="Reference"
              @click="previewImage = img.image_url"
              class="w-16 h-16 object-cover rounded-xl border border-[#DACDC0] shadow-2xs shrink-0 cursor-pointer hover:opacity-90 hover:scale-105 transition-transform"
              title="Click to zoom"
            />
          </div>
          <div v-else class="text-[11px] text-[#8A7B71] italic bg-[#FAF7F5] p-2.5 rounded-lg border border-dashed border-[#EFE8DF] text-center">
            No design photos attached by customer.
          </div>
        </div>

        <!-- ================= DYNAMIC ACTION CONTROLS BASED ON STATUS ================= -->
        
        <!-- 1. STATUS: Submitted -> Show Quote Form (Manager) or Pending Banner (Seller) -->
        <div v-if="selectedRequest.status === 'Submitted'" class="pt-2 border-t border-[#EFE8DF] space-y-3">
          <div class="text-xs font-bold text-[#2A1D15]">Quotation &amp; Review</div>

          <!-- Manager Quote Form -->
          <form v-if="isManager" @submit.prevent="submitQuote" class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-[#65554B] mb-1">Quoted Price (ETB) *</label>
              <input
                v-model.number="quoteForm.quoted_price"
                type="number"
                min="1"
                placeholder="Enter quoted price in ETB"
                class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
                required
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-[#65554B] mb-1">Deposit Required</label>
              <select
                v-model.number="quoteForm.deposit_percentage"
                class="w-full border border-[#D8CDC0] rounded-xl px-3 py-2 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option :value="30">30% Deposit to lock date</option>
                <option :value="50">50% Standard Deposit</option>
                <option :value="100">100% Full Prepayment</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-[#65554B] mb-1">Rejection Reason (if declining)</label>
              <input
                v-model="rejectionReason"
                type="text"
                placeholder="Kitchen fully booked for date, etc."
                class="w-full border border-[#D8CDC0] rounded-xl px-3 py-1.5 text-xs text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>

            <div class="flex space-x-2 pt-1">
              <button
                type="submit"
                :disabled="isProcessing"
                class="flex-1 py-2.5 px-3 bg-[#BA5420] hover:bg-[#9E4314] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer"
              >
                {{ isProcessing ? 'Sending Quote...' : '✓ Send Official Quote' }}
              </button>
              <button
                type="button"
                @click="rejectRequest"
                :disabled="isProcessing"
                class="px-4 py-2.5 bg-white border border-[#D8CDC0] hover:bg-rose-50 hover:text-rose-700 text-[#6A5A50] text-xs font-bold rounded-xl transition cursor-pointer"
              >
                Decline
              </button>
            </div>
          </form>

          <!-- Seller / Staff Awaiting Quote Info -->
          <div v-else class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5 text-xs text-amber-900">
            <div class="font-bold flex items-center space-x-1.5">
              <span>⏳</span>
              <span>Awaiting Branch Manager Quotation</span>
            </div>
            <p class="text-[11px] text-amber-800 leading-snug">
              This custom cake request is pending quotation by the {{ selectedRequest.branch?.branch_name || userBranchName }} Branch Manager. Sellers can track status and advance kitchen baking once confirmed.
            </p>
          </div>
        </div>

        <!-- 2. STATUS: Quoted -> Awaiting Customer Acceptance -->
        <div v-else-if="selectedRequest.status === 'Quoted'" class="pt-2 border-t border-[#EFE8DF] space-y-3">
          <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5 text-xs text-amber-900">
            <div class="font-bold flex items-center space-x-1.5">
              <span>⏳</span>
              <span>Quote Sent: ETB {{ Number(selectedRequest.quoted_price).toLocaleString() }}</span>
            </div>
            <p class="text-[11px] text-amber-800 leading-snug">
              Deposit required: {{ selectedRequest.deposit_required_percentage || 50 }}% (ETB {{ ((Number(selectedRequest.quoted_price) * (Number(selectedRequest.deposit_required_percentage) || 50)) / 100).toLocaleString() }}). Waiting for customer acceptance in My Account.
            </p>
          </div>

          <div class="flex space-x-2">
            <button
              type="button"
              @click="promptUpdateQuote"
              class="flex-1 py-2 px-3 bg-white border border-[#D8CDC0] hover:bg-[#FAF7F5] text-xs font-semibold text-[#4A3B32] rounded-xl transition cursor-pointer"
            >
              Update Quote Price
            </button>
            <button
              type="button"
              @click="rejectRequest"
              class="py-2 px-3 bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold rounded-xl transition cursor-pointer"
            >
              Decline
            </button>
          </div>
        </div>

        <!-- 3. STATUS: Confirmed -> Deposit Paid, Ready to Start Baking -->
        <div v-else-if="selectedRequest.status === 'Confirmed'" class="pt-2 border-t border-[#EFE8DF] space-y-3">
          <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl space-y-1 text-xs text-emerald-900">
            <div class="font-bold flex items-center space-x-1.5">
              <span>✓</span>
              <span>Deposit Paid &amp; Order Confirmed!</span>
            </div>
            <p class="text-[11px] text-emerald-800">
              Quoted: ETB {{ Number(selectedRequest.quoted_price).toLocaleString() }}. Deposit paid: ETB {{ Number(selectedRequest.deposit_paid || (Number(selectedRequest.quoted_price) * 0.5)).toLocaleString() }}. Ready for kitchen scheduling.
            </p>
          </div>

          <button
            type="button"
            @click="updateStatus('Preparing')"
            :disabled="isProcessing"
            class="w-full py-3 px-4 bg-[#BA5420] hover:bg-[#9E4314] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer flex items-center justify-center space-x-2"
          >
            <span>🍳</span>
            <span>Start Baking / Preparing in Kitchen</span>
          </button>
        </div>

        <!-- 4. STATUS: Preparing -> Artisan Decorating in Progress -->
        <div v-else-if="selectedRequest.status === 'Preparing'" class="pt-2 border-t border-[#EFE8DF] space-y-3">
          <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl space-y-1 text-xs text-blue-900">
            <div class="font-bold flex items-center space-x-1.5">
              <span>👨‍🍳</span>
              <span>Baking &amp; Decorating in Progress</span>
            </div>
            <p class="text-[11px] text-blue-800">
              Bakehouse ovens fired, sponge tiers cooling, and master decorator applying custom fondant/piping.
            </p>
          </div>

          <button
            type="button"
            @click="updateStatus('Ready')"
            :disabled="isProcessing"
            class="w-full py-3 px-4 bg-[#2E7D32] hover:bg-[#256628] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer flex items-center justify-center space-x-2"
          >
            <span>🎂</span>
            <span>Mark Cake Finished &amp; Ready for Handover</span>
          </button>
        </div>

        <!-- 5. STATUS: Ready -> Ready for Handover -->
        <div v-else-if="selectedRequest.status === 'Ready'" class="pt-2 border-t border-[#EFE8DF] space-y-3">
          <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-xl space-y-1 text-xs text-purple-900">
            <div class="font-bold flex items-center space-x-1.5">
              <span>🎉</span>
              <span>Cake Finished &amp; Ready at Branch!</span>
            </div>
            <p class="text-[11px] text-purple-800">
              Boxed and stored in chilled display. Awaiting customer collection or courier dispatch.
            </p>
          </div>

          <button
            type="button"
            @click="updateStatus('Delivered')"
            :disabled="isProcessing"
            class="w-full py-3 px-4 bg-[#2E7D32] hover:bg-[#256628] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer flex items-center justify-center space-x-2"
          >
            <span>✓</span>
            <span>Complete &amp; Hand Over to Customer</span>
          </button>
        </div>

        <!-- 6. STATUS: Delivered / Completed -->
        <div v-else-if="selectedRequest.status === 'Delivered' || selectedRequest.status === 'Picked up'" class="pt-2 border-t border-[#EFE8DF] space-y-2">
          <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center space-x-2 font-bold">
            <span>✓</span>
            <span>Order Completed &amp; Handed Over.</span>
          </div>
        </div>

        <!-- 7. STATUS: Rejected or Cancelled -->
        <div v-else-if="selectedRequest.status === 'Rejected' || selectedRequest.status === 'Cancelled'" class="pt-2 border-t border-[#EFE8DF] space-y-2">
          <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
            <div class="font-bold">Order {{ selectedRequest.status }}</div>
            <p class="text-[11px]">Reason: {{ selectedRequest.rejection_reason || 'Declined or cancelled.' }}</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Zoom Preview Modal -->
    <div
      v-if="previewImage"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
      @click="previewImage = null"
    >
      <div class="relative max-w-xl w-full flex items-center justify-center" @click.stop>
        <img :src="previewImage" alt="Preview" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl" />
        <button
          type="button"
          @click="previewImage = null"
          class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-black font-bold flex items-center justify-center shadow-lg cursor-pointer"
        >
          ✕
        </button>
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
const userBranchName = computed(() => authStore.user?.branch?.branch_name || 'My Branch');
const isManager = computed(() => ['Branch Manager', 'Branch Admin', 'Main Branch Manager'].includes(authStore.user?.role));

const branches = ref([]);
const selectedBranch = ref('all');
const selectedStatusFilter = ref('all');
const selectedDateFilter = ref('all');
const customStartDate = ref('');
const customEndDate = ref('');
const searchQuery = ref('');
const requests = ref([]);
const summary = ref({
  submitted: 0,
  quoted: 0,
  confirmed: 0,
  ready_this_week: 0,
});

const clearCustomDates = () => {
  customStartDate.value = '';
  customEndDate.value = '';
  selectedDateFilter.value = 'all';
  loadRequests();
};

const selectedRequest = ref(null);
const quoteForm = ref({
  quoted_price: null,
  deposit_percentage: 50,
});
const rejectionReason = ref('');
const successMsg = ref('');
const isProcessing = ref(false);
const previewImage = ref(null);

const filteredRequests = computed(() => {
  return requests.value.filter((r) => {
    if (selectedStatusFilter.value !== 'all' && r.status !== selectedStatusFilter.value) {
      return false;
    }
    if (!searchQuery.value.trim()) return true;
    const q = searchQuery.value.toLowerCase();
    const num = (r.request_number || '').toLowerCase();
    const name = (r.customer_name || r.user?.full_name || '').toLowerCase();
    const phone = (r.customer_phone || r.user?.phone || '').toLowerCase();
    const flavor = (r.flavor || '').toLowerCase();
    return num.includes(q) || name.includes(q) || phone.includes(q) || flavor.includes(q);
  });
});

const statusBadgeStyle = (status) => {
  switch (status) {
    case 'Submitted':
      return 'bg-amber-50 text-amber-800 border border-amber-200';
    case 'Quoted':
      return 'bg-amber-100 text-amber-900 border border-amber-300';
    case 'Confirmed':
      return 'bg-emerald-50 text-emerald-800 border border-emerald-200';
    case 'Preparing':
      return 'bg-rose-50 text-[#DE1B54] border border-rose-200';
    case 'Ready':
      return 'bg-purple-50 text-purple-800 border border-purple-200';
    case 'Delivered':
    case 'Picked up':
      return 'bg-gray-100 text-gray-700 border border-gray-300';
    case 'Rejected':
    case 'Cancelled':
      return 'bg-rose-50 text-rose-700 border border-rose-200';
    default:
      return 'bg-gray-50 text-gray-700 border border-gray-200';
  }
};

const loadRequests = async () => {
  try {
    const params = {};
    if (isBranchScoped.value && userBranchId.value) {
      params.branch_id = userBranchId.value;
    } else if (selectedBranch.value !== 'all') {
      params.branch_id = selectedBranch.value;
    }
    if (selectedStatusFilter.value !== 'all') {
      params.status = selectedStatusFilter.value;
    }
    if (selectedDateFilter.value && selectedDateFilter.value !== 'all') {
      params.date = selectedDateFilter.value;
      if (selectedDateFilter.value === 'custom') {
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
      }
    }
    const res = await api.get('/admin/custom-cakes', { params });
    requests.value = res.data.requests || [];
    summary.value = res.data.summary || { submitted: 0, quoted: 0, confirmed: 0, ready_this_week: 0 };

    if (!selectedRequest.value && requests.value.length > 0) {
      selectedRequest.value = requests.value[0];
    } else if (selectedRequest.value) {
      selectedRequest.value = requests.value.find((r) => r.request_id === selectedRequest.value.request_id) || requests.value[0] || null;
    }

    if (selectedRequest.value?.quoted_price) {
      quoteForm.value.quoted_price = Number(selectedRequest.value.quoted_price);
      quoteForm.value.deposit_percentage = selectedRequest.value.deposit_required_percentage ? Number(selectedRequest.value.deposit_required_percentage) : 50;
    }
  } catch (err) {
    console.error('Failed to load custom cakes', err);
  }
};

const selectRequest = (req) => {
  selectedRequest.value = req;
  quoteForm.value.quoted_price = req.quoted_price ? Number(req.quoted_price) : null;
  quoteForm.value.deposit_percentage = req.deposit_required_percentage ? Number(req.deposit_required_percentage) : 50;
  rejectionReason.value = '';
};

const submitQuote = async () => {
  if (!selectedRequest.value || !quoteForm.value.quoted_price) return;
  isProcessing.value = true;
  try {
    const res = await api.patch(`/admin/custom-cakes/${selectedRequest.value.request_id}/quote`, {
      quoted_price: quoteForm.value.quoted_price,
      deposit_required_percentage: quoteForm.value.deposit_percentage,
    });
    selectedRequest.value = res.data;
    successMsg.value = `Official quote sent to customer for #${selectedRequest.value.request_number}: ETB ${Number(quoteForm.value.quoted_price).toLocaleString()}`;
    await loadRequests();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit quote');
  } finally {
    isProcessing.value = false;
  }
};

const promptUpdateQuote = async () => {
  if (!selectedRequest.value) return;
  const newPrice = prompt(`Enter new quoted price (ETB) for #${selectedRequest.value.request_number}:`, selectedRequest.value.quoted_price || '');
  if (!newPrice || isNaN(Number(newPrice))) return;

  isProcessing.value = true;
  try {
    const res = await api.patch(`/admin/custom-cakes/${selectedRequest.value.request_id}/quote`, {
      quoted_price: Number(newPrice),
      deposit_required_percentage: quoteForm.value.deposit_percentage,
    });
    selectedRequest.value = res.data;
    successMsg.value = `Updated quote to ETB ${Number(newPrice).toLocaleString()}`;
    await loadRequests();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update quote');
  } finally {
    isProcessing.value = false;
  }
};

const updateStatus = async (newStatus) => {
  if (!selectedRequest.value) return;
  isProcessing.value = true;
  try {
    await api.patch(`/admin/custom-cakes/${selectedRequest.value.request_id}/status`, {
      status: newStatus,
    });
    successMsg.value = `Custom cake #${selectedRequest.value.request_number} marked as "${newStatus}"!`;
    await loadRequests();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update status');
  } finally {
    isProcessing.value = false;
  }
};

const rejectRequest = async () => {
  if (!selectedRequest.value) return;
  const reason = rejectionReason.value || prompt(`Enter reason for declining #${selectedRequest.value.request_number}:`, 'Cannot fulfill requested design on schedule');
  if (reason === null) return;

  isProcessing.value = true;
  try {
    await api.patch(`/admin/custom-cakes/${selectedRequest.value.request_id}/status`, {
      status: 'Rejected',
      rejection_reason: reason,
    });
    successMsg.value = `Request #${selectedRequest.value.request_number} declined.`;
    await loadRequests();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to decline request');
  } finally {
    isProcessing.value = false;
  }
};

onMounted(async () => {
  try {
    const bRes = await api.get('/branches');
    branches.value = bRes.data || [];
  } catch (e) {}
  loadRequests();
});
</script>
