<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-16 font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#EFE8DE] pb-5">
      <div>
        <div class="flex items-center space-x-2.5">
          <span class="text-2xl">📋</span>
          <h1 class="font-serif text-2xl sm:text-3xl font-black text-[#2B1810]">
            Branch Order Reports &amp; Verification
          </h1>
        </div>
        <p class="text-sm text-[#7D6E64] mt-1">
          Review &amp; approve branch seller daily sales reports, and finalize branch sales reports for your branch.
        </p>
      </div>

      <!-- Tab Buttons -->
      <div class="flex flex-wrap items-center gap-1.5 bg-[#F3ECE1] p-1 rounded-2xl border border-[#E5DACD] self-start sm:self-auto">
        <button
          @click="activeTab = 'verify'"
          class="px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer"
          :class="activeTab === 'verify' ? 'bg-[#3D251E] text-white shadow-xs' : 'text-[#6E5D52] hover:text-[#2B1810]'"
        >
          🔍 Verify Seller Reports
          <span v-if="pendingCount > 0" class="ml-1 px-1.5 py-0.5 bg-amber-500 text-white rounded-full text-[10px]">
            {{ pendingCount }}
          </span>
        </button>
        <button
          @click="activeTab = 'consolidate'"
          class="px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer"
          :class="activeTab === 'consolidate' ? 'bg-[#3D251E] text-white shadow-xs' : 'text-[#6E5D52] hover:text-[#2B1810]'"
        >
          📑 Finalize Branch Report
        </button>
        <button
          @click="activeTab = 'submissions'"
          class="px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer"
          :class="activeTab === 'submissions' ? 'bg-[#3D251E] text-white shadow-xs' : 'text-[#6E5D52] hover:text-[#2B1810]'"
        >
          🏛️ Branch Sales Reports ({{ branchSubmissions.length }})
        </button>
      </div>
    </div>

    <!-- Scoped Branch Notice -->
    <div class="p-3.5 bg-[#FAF7F2] border border-[#E5DCD0] rounded-2xl flex items-center justify-between text-xs text-[#6B5A4E] shadow-2xs">
      <div class="flex items-center space-x-2">
        <span class="text-base">🔒</span>
        <span>Branch: <strong>{{ branchName }}</strong>. You only receive reports submitted by sellers in your assigned branch.</span>
      </div>
      <span class="text-[11px] font-bold px-2.5 py-0.5 bg-[#57392C] text-white rounded-full">
        Branch Manager Level
      </span>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: VERIFY SELLER REPORTS                                      -->
    <!-- ================================================================= -->
    <div v-show="activeTab === 'verify'" class="space-y-4">
      <!-- Filter Bar -->
      <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs">
        <div class="flex items-center space-x-2">
          <span class="text-xs font-bold text-[#7D6E64] uppercase">Status Filter:</span>
          <button
            v-for="st in ['All', 'Pending Verification', 'Approved', 'Returned', 'Draft']"
            :key="st"
            @click="filterStatus = st; fetchSellerReports()"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
            :class="filterStatus === st ? 'bg-[#3D251E] text-white' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
          >
            {{ st }}
          </button>
        </div>

        <button
          @click="fetchSellerReports"
          class="px-3 py-1.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer"
        >
          🔄 Refresh
        </button>
      </div>

      <!-- Seller Reports Table -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
                <th class="py-3 px-3">Report Ref</th>
                <th class="py-3 px-3">Seller Name</th>
                <th class="py-3 px-3">Date</th>
                <th class="py-3 px-3 text-center">Regular Orders</th>
                <th class="py-3 px-3 text-center">Custom Cakes</th>
                <th class="py-3 px-3 text-right">Daily Revenue</th>
                <th class="py-3 px-3 text-right">Payments In</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F2ECE4]">
              <tr v-if="sellerReports.length === 0">
                <td colspan="9" class="py-8 text-center text-[#9E8E82] text-xs">
                  No seller reports found matching this criteria.
                </td>
              </tr>
              <tr v-for="rep in sellerReports" :key="rep.id" class="hover:bg-[#FAF7F2]/60 transition">
                <td class="py-3 px-3 font-mono font-bold text-[#3D251E]">
                  {{ rep.report_reference }}
                </td>
                <td class="py-3 px-3 font-semibold text-[#2B1810]">
                  {{ rep.seller?.full_name || rep.seller?.email || 'Seller #' + rep.seller_id }}
                </td>
                <td class="py-3 px-3 text-[#6E5D52]">
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
                <td class="py-3 px-3 text-right font-semibold text-emerald-700">
                  ETB {{ Number(rep.total_payments_received || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                </td>
                <td class="py-3 px-3">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-block"
                    :class="{
                      'bg-amber-50 text-amber-800 border-amber-200': rep.status === 'Pending Verification' || rep.status === 'Submitted',
                      'bg-emerald-50 text-emerald-800 border-emerald-200': rep.status === 'Approved',
                      'bg-rose-50 text-rose-800 border-rose-200': ['Returned', 'Returned for Correction', 'Rejected'].includes(rep.status),
                      'bg-stone-100 text-stone-700 border-stone-200': rep.status === 'Draft'
                    }"
                  >
                    {{ rep.status }}
                  </span>
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    @click="openVerificationModal(rep.id)"
                    class="px-3 py-1.5 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-2xs"
                  >
                    {{ (rep.status === 'Pending Verification' || rep.status === 'Submitted') ? '🔍 Review & Verify' : 'View Details' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: SUBMIT VERIFIED BRANCH SALES REPORT                        -->
    <!-- ================================================================= -->
    <div v-show="activeTab === 'consolidate'" class="space-y-6">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-2xs space-y-5">
        <div class="border-b border-[#F4EFEA] pb-4">
          <h2 class="font-serif font-black text-xl text-[#2B1810]">
            Consolidate &amp; Finalize Branch Sales Report
          </h2>
          <p class="text-xs text-[#7D6E64] mt-1">
            This tool automatically consolidates all approved seller sales reports for the selected period. As Branch Manager, you have complete authority to manage and review all sales reports within your branch.
          </p>
        </div>

        <!-- Period Selection -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Period Type</label>
            <select
              v-model="consolidationForm.period_type"
              class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810]"
            >
              <option value="Daily">Daily</option>
              <option value="Weekly">Weekly</option>
              <option value="Monthly">Monthly</option>
              <option value="Custom">Custom</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Start Date</label>
            <input
              v-model="consolidationForm.period_start"
              type="date"
              class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">End Date</label>
            <input
              v-model="consolidationForm.period_end"
              type="date"
              class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810]"
            />
          </div>

          <div class="flex items-end">
            <button
              @click="fetchPendingConsolidation"
              :disabled="loadingConsolidation"
              class="w-full px-4 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-xs rounded-xl transition cursor-pointer flex items-center justify-center space-x-1 shadow-2xs"
            >
              <span>⚡</span>
              <span>{{ loadingConsolidation ? 'Loading...' : 'Gather Approved Reports' }}</span>
            </button>
          </div>
        </div>

        <!-- Preview of Consolidated Numbers -->
        <div v-if="pendingData" class="space-y-4 pt-3 border-t border-[#F4EFEA]">
          <div class="flex items-center justify-between">
            <div class="text-xs font-bold text-[#2B1810] uppercase tracking-wider">
              Ready for Consolidation: <span class="text-emerald-700">{{ pendingData.seller_reports_count }} Approved Seller Report(s)</span> from <span class="text-[#3D251E]">{{ pendingData.sellers_count }} unique seller(s)</span>
            </div>
            <div class="font-mono text-xs text-[#7D6E64]">Ref: {{ pendingData.suggested_reference }}</div>
          </div>

          <div v-if="pendingData.seller_reports_count === 0" class="p-6 bg-[#FAF7F2] rounded-xl text-center text-xs text-[#8E7E73]">
            No approved seller reports found for this branch in the selected date range. Ensure seller reports have been verified and approved first.
          </div>

          <div v-else class="space-y-4">
            <!-- Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
              <div class="bg-[#FAF7F2] p-3.5 rounded-xl border border-[#E9E0D4]">
                <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Total Regular Orders</div>
                <div class="text-xl font-serif font-black text-[#2B1810] mt-1">{{ pendingData.summary.total_regular_orders }}</div>
                <div class="text-[11px] text-[#6E5D52] mt-0.5">ETB {{ Number(pendingData.summary.total_regular_revenue).toFixed(2) }}</div>
              </div>

              <div class="bg-[#FAF7F2] p-3.5 rounded-xl border border-[#E9E0D4]">
                <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Total Custom Cakes</div>
                <div class="text-xl font-serif font-black text-[#2B1810] mt-1">{{ pendingData.summary.total_custom_cake_orders }}</div>
                <div class="text-[11px] text-[#6E5D52] mt-0.5">ETB {{ Number(pendingData.summary.total_custom_cake_revenue).toFixed(2) }}</div>
              </div>

              <div class="bg-[#FAF7F2] p-3.5 rounded-xl border border-[#E9E0D4]">
                <div class="text-[10px] font-bold text-emerald-700 uppercase">Advance Deposits</div>
                <div class="text-xl font-serif font-black text-emerald-700 mt-1">ETB {{ Number(pendingData.summary.total_advance_payments).toFixed(2) }}</div>
                <div class="text-[11px] text-[#D97706] mt-0.5">Due: ETB {{ Number(pendingData.summary.total_outstanding_balances).toFixed(2) }}</div>
              </div>

              <div class="bg-gradient-to-r from-[#3D251E] to-[#57392C] text-white p-3.5 rounded-xl shadow-xs">
                <div class="text-[10px] font-bold text-[#D8CDC5] uppercase">Total Branch Revenue</div>
                <div class="text-2xl font-serif font-black text-amber-200 mt-1">
                  ETB {{ Number(pendingData.summary.total_branch_revenue).toFixed(2) }}
                </div>
                <div class="text-[11px] text-[#E5DACD] mt-0.5">Payments In: ETB {{ Number(pendingData.summary.total_payments_received).toFixed(2) }}</div>
              </div>
            </div>

            <!-- List of Included Seller Reports -->
            <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
              <div class="px-3 py-2 bg-[#FAF7F2] text-[11px] font-bold uppercase tracking-wider text-[#6E5D52] border-b border-[#E9E0D4]">
                Seller Daily Reports Included in Consolidation ({{ pendingData.reports.length }})
              </div>
              <div class="divide-y divide-[#F2ECE4] max-h-48 overflow-y-auto">
                <div v-for="sr in pendingData.reports" :key="sr.id" class="px-3 py-2 flex items-center justify-between text-xs hover:bg-[#FAF7F2]/50">
                  <div class="flex items-center space-x-3">
                    <span class="font-mono font-bold text-[#3D251E]">{{ sr.report_reference }}</span>
                    <span class="text-[#2B1810] font-semibold">{{ sr.seller?.full_name || sr.seller?.email }}</span>
                    <span class="text-[#7D6E64]">({{ formatDate(sr.report_date) }})</span>
                  </div>
                  <div class="font-serif font-bold text-[#2B1810]">
                    ETB {{ Number(sr.combined_daily_revenue).toFixed(2) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Manager Notes -->
            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">
                Branch Manager Notes / Reconciliation Remarks (Optional)
              </label>
              <textarea
                v-model="consolidationForm.manager_notes"
                rows="3"
                placeholder="e.g. All seller reports reconciled against physical POS receipts, kitchen logs, and deposit drawer. Verified complete."
                class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
              ></textarea>
            </div>

            <!-- Accuracy Confirmation Checkbox (Required) -->
            <label class="flex items-start space-x-3 p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl cursor-pointer">
              <input
                v-model="consolidationForm.confirmed_accuracy"
                type="checkbox"
                class="mt-0.5 rounded text-[#3D251E] focus:ring-[#3D251E] w-4 h-4 cursor-pointer"
              />
              <span class="text-xs text-[#92400E] font-medium leading-relaxed">
                <strong>Accuracy Confirmation:</strong> I confirm that I have cross-checked product quantities, selling prices, and payments across all approved seller reports for this period. This consolidated branch sales report is complete, accurate, and finalized.
              </span>
            </label>

            <!-- Submit Button -->
            <div class="flex justify-end pt-2">
              <button
                @click="submitConsolidatedReport"
                :disabled="submittingConsolidation || !consolidationForm.confirmed_accuracy"
                class="px-6 py-3 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-sm rounded-xl transition cursor-pointer shadow-xs disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
              >
                <span>✅</span>
                <span>{{ submittingConsolidation ? 'Saving...' : 'Finalize &amp; Save Branch Sales Report' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: CONSOLIDATED BRANCH SUBMISSIONS                            -->
    <!-- ================================================================= -->
    <div v-show="activeTab === 'submissions'" class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-2xs space-y-4">
      <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
        <div>
          <h2 class="text-sm font-bold uppercase tracking-wider text-[#2B1810]">
            Finalized Branch Sales Reports
          </h2>
          <p class="text-xs text-[#7D6E64]">Historical consolidated sales reports managed and verified within your branch.</p>
        </div>
        <button
          @click="fetchBranchSubmissions"
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
              <th class="py-3 px-3">Period</th>
              <th class="py-3 px-3">Date Range</th>
              <th class="py-3 px-3 text-center">Sellers</th>
              <th class="py-3 px-3 text-right">Regular Rev</th>
              <th class="py-3 px-3 text-right">Custom Cake Rev</th>
              <th class="py-3 px-3 text-right">Total Revenue</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-3">Manager Notes</th>
              <th class="py-3 px-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F2ECE4]">
            <tr v-if="branchSubmissions.length === 0">
              <td colspan="10" class="py-8 text-center text-[#9E8E82] text-xs">
                No finalized branch reports saved yet.
              </td>
            </tr>
            <tr v-for="bRep in branchSubmissions" :key="bRep.id" class="hover:bg-[#FAF7F2]/60 transition">
              <td class="py-3 px-3 font-mono font-bold text-[#3D251E]">
                {{ bRep.report_reference }}
              </td>
              <td class="py-3 px-3 font-semibold text-[#2B1810]">
                {{ bRep.period_type }}
              </td>
              <td class="py-3 px-3 text-[#6E5D52]">
                {{ formatDate(bRep.period_start) }} – {{ formatDate(bRep.period_end) }}
              </td>
              <td class="py-3 px-3 text-center font-bold text-[#2B1810]">
                {{ bRep.sellers_included_count }}
              </td>
              <td class="py-3 px-3 text-right">
                ETB {{ Number(bRep.total_regular_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3 text-right">
                ETB {{ Number(bRep.total_custom_cake_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3 text-right font-serif font-black text-[#2B1810]">
                ETB {{ Number(bRep.total_branch_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-block bg-emerald-50 text-emerald-800 border-emerald-200"
                >
                  {{ ['Finalized', 'Approved', 'Verified by Super Admin'].includes(bRep.status) ? 'Finalized' : bRep.status }}
                </span>
              </td>
              <td class="py-3 px-3 text-xs max-w-xs truncate" :title="bRep.manager_notes">
                <span v-if="bRep.manager_notes" class="text-[#6E5D52] font-medium">
                  "{{ bRep.manager_notes }}"
                </span>
                <span v-else class="text-[#B29E91] italic">—</span>
              </td>
              <td class="py-3 px-3 text-center">
                <button
                  @click="inspectConsolidated(bRep.id)"
                  class="text-xs text-[#3D251E] font-bold hover:underline cursor-pointer"
                >
                  Inspect
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL: VERIFY SELLER REPORT (MANAGER VERIFICATION FORM)            -->
    <!-- ================================================================= -->
    <div
      v-if="verifyingReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="verifyingReport = null"
    >
      <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[92vh] overflow-y-auto p-6 space-y-5 border border-[#E9E0D4] shadow-xl">
        <div class="flex items-center justify-between border-b border-[#F2ECE4] pb-3">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Manager Verification &amp; Audit</div>
            <h3 class="font-serif font-black text-xl text-[#2B1810]">
              {{ verifyingReport.report_reference }}
            </h3>
            <p class="text-xs text-[#7D6E64] mt-0.5">
              Seller: <strong>{{ verifyingReport.seller?.full_name || verifyingReport.seller?.email }}</strong> |
              Branch: <strong>{{ verifyingReport.branch?.branch_name }}</strong> |
              Date: <strong>{{ formatDate(verifyingReport.report_date) }}</strong> |
              Status: <strong>{{ verifyingReport.status }}</strong>
            </p>
          </div>
          <button
            @click="verifyingReport = null"
            class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold p-1 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-[#FAF7F2] p-4 rounded-xl border border-[#E9E0D4]">
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Regular Revenue</div>
            <div class="font-bold text-sm text-[#2B1810]">ETB {{ Number(verifyingReport.regular_order_revenue).toFixed(2) }}</div>
            <div class="text-[10px] text-[#7D6E64]">{{ verifyingReport.regular_products_sold }} items sold</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Custom Cake Revenue</div>
            <div class="font-bold text-sm text-[#2B1810]">ETB {{ Number(verifyingReport.custom_cake_revenue).toFixed(2) }}</div>
            <div class="text-[10px] text-[#7D6E64]">{{ verifyingReport.custom_cakes_sold }} cakes booked</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Payments In Today</div>
            <div class="font-bold text-sm text-emerald-700">ETB {{ Number(verifyingReport.total_payments_received).toFixed(2) }}</div>
            <div class="text-[10px] text-[#D97706]">Outstanding: ETB {{ Number(verifyingReport.total_outstanding_balances).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Combined Daily Revenue</div>
            <div class="font-bold text-base text-[#3D251E]">ETB {{ Number(verifyingReport.combined_daily_revenue).toFixed(2) }}</div>
          </div>
        </div>

        <!-- Regular Orders Review Table -->
        <div class="space-y-2">
          <div class="font-bold text-xs uppercase tracking-wider text-[#2B1810] flex items-center justify-between">
            <span>1. Regular Orders ({{ verifyingReport.orders?.length || 0 }})</span>
            <span class="text-[11px] text-[#7D6E64] font-normal">Cross-check item quantities &amp; unit prices</span>
          </div>
          <div class="overflow-x-auto border border-[#E9E0D4] rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px] uppercase">
                <tr>
                  <th class="p-2.5">Order #</th>
                  <th class="p-2.5">Item Name</th>
                  <th class="p-2.5">Category</th>
                  <th class="p-2.5 text-right">Qty</th>
                  <th class="p-2.5 text-right">Unit Price</th>
                  <th class="p-2.5 text-right">Total</th>
                  <th class="p-2.5">Order Status</th>
                  <th class="p-2.5">Payment</th>
                  <th class="p-2.5">Customer Info</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F2ECE4]">
                <tr v-for="o in verifyingReport.orders" :key="o.id">
                  <td class="p-2.5 font-mono font-bold">{{ o.order_number }}</td>
                  <td class="p-2.5 font-semibold text-[#2B1810]">{{ o.product_name }}</td>
                  <td class="p-2.5">{{ o.category }}</td>
                  <td class="p-2.5 text-right font-bold">{{ o.quantity }}</td>
                  <td class="p-2.5 text-right">ETB {{ Number(o.unit_price).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold text-[#2B1810]">ETB {{ Number(o.total_amount).toFixed(2) }}</td>
                  <td class="p-2.5">{{ o.order_status }}</td>
                  <td class="p-2.5 font-semibold" :class="o.payment_status === 'Paid' ? 'text-emerald-700' : 'text-amber-700'">{{ o.payment_status }}</td>
                  <td class="p-2.5 text-[#6E5D52]">{{ o.customer_info || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Custom Cakes Review Table -->
        <div class="space-y-2">
          <div class="font-bold text-xs uppercase tracking-wider text-[#2B1810] flex items-center justify-between">
            <span>2. Custom Cake Orders &amp; Advances ({{ verifyingReport.custom_cakes?.length || 0 }})</span>
            <span class="text-[11px] text-[#7D6E64] font-normal">Cross-check deposits &amp; balance records</span>
          </div>
          <div class="overflow-x-auto border border-[#E9E0D4] rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px] uppercase">
                <tr>
                  <th class="p-2.5">Cake #</th>
                  <th class="p-2.5">Customer</th>
                  <th class="p-2.5">Design / Flavor</th>
                  <th class="p-2.5">Size</th>
                  <th class="p-2.5 text-right">Unit Price</th>
                  <th class="p-2.5 text-right">Total Price</th>
                  <th class="p-2.5 text-right">Advance Paid</th>
                  <th class="p-2.5 text-right">Balance Due</th>
                  <th class="p-2.5">Delivery</th>
                  <th class="p-2.5">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F2ECE4]">
                <tr v-for="c in verifyingReport.custom_cakes" :key="c.id">
                  <td class="p-2.5 font-mono font-bold">{{ c.cake_order_number }}</td>
                  <td class="p-2.5 font-semibold text-[#2B1810]">{{ c.customer_name }}</td>
                  <td class="p-2.5">
                    <div class="font-medium text-[#2B1810]">{{ c.cake_type_design }}</div>
                    <div v-if="c.customization_details" class="text-[10px] text-[#7D6E64] italic truncate max-w-xs">&ldquo;{{ c.customization_details }}&rdquo;</div>
                    <div v-if="c.system_custom_cake?.snapshot_url || c.system_custom_cake?.images?.length" class="mt-1">
                      <button
                        type="button"
                        @click.stop="previewCakeModal = c.system_custom_cake"
                        class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[9px] font-bold bg-rose-50 hover:bg-rose-100 text-[#DE1B54] border border-rose-200 cursor-pointer shadow-2xs transition"
                      >
                        <span>🎂 View 3D Snapshot &amp; Photos</span>
                      </button>
                    </div>
                  </td>
                  <td class="p-2.5">{{ c.size_weight }}</td>
                  <td class="p-2.5 text-right">ETB {{ Number(c.unit_price).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold">ETB {{ Number(c.total_price).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold text-emerald-700">ETB {{ Number(c.advance_payment).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold text-amber-700">ETB {{ Number(c.remaining_balance).toFixed(2) }}</td>
                  <td class="p-2.5">{{ formatDate(c.delivery_date) }}</td>
                  <td class="p-2.5">{{ c.order_status }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Manager Review Note Input -->
        <div>
          <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">
            Verification Remarks / Correction Note (Required if returning report)
          </label>
          <textarea
            v-model="reviewNotes"
            rows="2"
            placeholder="e.g. Approved: reconciled against physical receipt counter. OR: Return note: Please check Custom Cake #CCR-002 advance deposit receipt."
            class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-3 border-t border-[#F2ECE4]">
          <button
            @click="verifyingReport = null"
            class="px-4 py-2 bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE] text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Close
          </button>

          <div class="flex items-center space-x-3">
            <button
              @click="submitVerification('reject')"
              :disabled="verifyingSubmitting"
              class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 font-bold text-xs rounded-xl transition cursor-pointer flex items-center space-x-1"
            >
              <span>❌</span>
              <span>Reject with Reason</span>
            </button>

            <button
              @click="submitVerification('approve')"
              :disabled="verifyingSubmitting"
              class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-xs flex items-center space-x-1"
            >
              <span>✅</span>
              <span>Approve Directly</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: INSPECT CONSOLIDATED BRANCH SUBMISSION -->
    <div
      v-if="inspectingReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="inspectingReport = null"
    >
      <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 space-y-5 border border-[#E9E0D4] shadow-xl">
        <div class="flex items-center justify-between border-b border-[#F2ECE4] pb-3">
          <div>
            <h3 class="font-serif font-black text-xl text-[#2B1810]">
              {{ inspectingReport.report_reference }}
            </h3>
            <p class="text-xs text-[#7D6E64]">
              Period: {{ inspectingReport.period_type }} ({{ formatDate(inspectingReport.period_start) }} – {{ formatDate(inspectingReport.period_end) }}) | Status: <strong>{{ inspectingReport.status }}</strong>
            </p>
          </div>
          <button @click="inspectingReport = null" class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold cursor-pointer">✕</button>
        </div>

        <div v-if="inspectingReport.manager_notes" class="p-3 bg-[#FAF7F2] border border-[#E9E0D4] rounded-xl text-xs text-[#2B1810]">
          <strong>Manager Remarks:</strong> {{ inspectingReport.manager_notes }}
        </div>

        <!-- Breakdown Tables -->
        <div v-if="inspectingReport.breakdown_json" class="space-y-4">
          <!-- Product Breakdown -->
          <div v-if="inspectingReport.breakdown_json.products?.length" class="space-y-1.5">
            <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">Products Sold</div>
            <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] font-bold text-[10px]">
                  <tr>
                    <th class="p-2">Item</th>
                    <th class="p-2">Category</th>
                    <th class="p-2 text-right">Quantity</th>
                    <th class="p-2 text-right">Total Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F2ECE4]">
                  <tr v-for="(p, i) in inspectingReport.breakdown_json.products" :key="i">
                    <td class="p-2 font-semibold">{{ p.name }}</td>
                    <td class="p-2">{{ p.category }}</td>
                    <td class="p-2 text-right font-bold">{{ p.quantity }}</td>
                    <td class="p-2 text-right font-bold">ETB {{ Number(p.total).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Custom Cakes Breakdown -->
          <div v-if="inspectingReport.breakdown_json.custom_cakes?.length" class="space-y-1.5">
            <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">Custom Cakes Booked</div>
            <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] font-bold text-[10px]">
                  <tr>
                    <th class="p-2">Category &amp; Design</th>
                    <th class="p-2">Size</th>
                    <th class="p-2 text-right">Qty</th>
                    <th class="p-2 text-right">Total</th>
                    <th class="p-2 text-right">Advance</th>
                    <th class="p-2 text-right">Balance</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F2ECE4]">
                  <tr v-for="(c, i) in inspectingReport.breakdown_json.custom_cakes" :key="i">
                    <td class="p-2 font-semibold">{{ c.category }} - {{ c.design }}</td>
                    <td class="p-2">{{ c.size }}</td>
                    <td class="p-2 text-right">{{ c.quantity }}</td>
                    <td class="p-2 text-right font-bold">ETB {{ Number(c.total).toFixed(2) }}</td>
                    <td class="p-2 text-right text-emerald-700">ETB {{ Number(c.advance).toFixed(2) }}</td>
                    <td class="p-2 text-right text-amber-700">ETB {{ Number(c.balance).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button @click="inspectingReport = null" class="px-5 py-2 bg-[#3D251E] text-white text-xs font-bold rounded-xl cursor-pointer">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: 3D CAKE SNAPSHOT & DESIGN SPECIFICATION PREVIEW -->
    <div
      v-if="previewCakeModal"
      class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="previewCakeModal = null"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] overflow-y-auto p-6 space-y-5 border border-[#E9E0D4] shadow-2xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-[#F2ECE4] pb-4">
          <div>
            <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FAF7F2] text-[#3D251E] border border-[#E9E0D4] mb-1">
              <span>🎂 Custom Cake Design Verification</span>
            </div>
            <h3 class="font-serif font-black text-xl text-[#2B1810]">
              {{ previewCakeModal.cake_type || previewCakeModal.cake_type_design || 'Custom 3D Cake Design' }}
            </h3>
            <p class="text-xs text-[#7D6E64]">
              Customer: <strong>{{ previewCakeModal.customer_name }}</strong>
              <span v-if="previewCakeModal.customer_phone"> &bull; {{ previewCakeModal.customer_phone }}</span>
            </p>
          </div>
          <button
            @click="previewCakeModal = null"
            class="w-8 h-8 rounded-full bg-[#FAF7F2] hover:bg-[#F2ECE4] text-[#7D6E64] hover:text-[#2B1810] flex items-center justify-center font-bold text-lg cursor-pointer transition"
          >
            ✕
          </button>
        </div>

        <!-- 3D Snapshot Display -->
        <div v-if="previewCakeModal.snapshot_url" class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-[#2B1810] flex items-center space-x-1">
              <span>✨ 3D Studio Snapshot</span>
            </span>
            <span class="text-[10px] font-medium text-[#7D6E64]">Captured from Customer 3D Cake Builder</span>
          </div>
          <div class="relative rounded-2xl overflow-hidden border border-[#E9E0D4] bg-gradient-to-b from-[#FFF5F2] to-[#FAF7F2] flex items-center justify-center p-4 shadow-inner">
            <img
              :src="previewCakeModal.snapshot_url"
              alt="3D Cake Design Snapshot"
              class="max-h-72 w-auto object-contain rounded-xl drop-shadow-lg"
            />
          </div>
        </div>

        <!-- Reference Photos Uploaded by Customer -->
        <div v-if="previewCakeModal.images && previewCakeModal.images.length" class="space-y-2">
          <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810] flex items-center space-x-1">
            <span>📸 Customer Reference Photos ({{ previewCakeModal.images.length }})</span>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="(img, idx) in previewCakeModal.images"
              :key="idx"
              class="aspect-square rounded-xl overflow-hidden border border-[#E9E0D4] bg-[#FAF7F2] group relative cursor-pointer"
              @click="openFullImage(img.image_url || img.image_path)"
              title="Click to view full image in new tab"
            >
              <img
                :src="img.image_url || img.image_path"
                :alt="'Reference ' + (idx + 1)"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              />
            </div>
          </div>
        </div>

        <!-- Specifications Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 bg-[#FAF7F2] p-4 rounded-2xl border border-[#E9E0D4] text-xs">
          <div>
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Tiers / Size</div>
            <div class="font-bold text-[#2B1810] mt-0.5">
              {{ previewCakeModal.tiers ? previewCakeModal.tiers + ' Tier(s)' : previewCakeModal.size_weight || 'Standard' }}
            </div>
          </div>
          <div>
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Flavor</div>
            <div class="font-bold text-[#2B1810] mt-0.5">{{ previewCakeModal.flavor || 'Custom Choice' }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Frosting / Finish</div>
            <div class="font-bold text-[#2B1810] mt-0.5">{{ previewCakeModal.frosting || 'Vanilla Buttercream' }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Total Agreed Price</div>
            <div class="font-bold text-[#2B1810] mt-0.5">ETB {{ Number(previewCakeModal.total_price || previewCakeModal.price || 0).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold text-emerald-800 uppercase">Advance Deposit</div>
            <div class="font-bold text-emerald-700 mt-0.5">ETB {{ Number(previewCakeModal.advance_payment || 0).toFixed(2) }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold text-amber-800 uppercase">Balance Due</div>
            <div class="font-bold text-amber-700 mt-0.5">ETB {{ Number(previewCakeModal.remaining_balance || 0).toFixed(2) }}</div>
          </div>
        </div>

        <!-- Dedication & Special Instructions -->
        <div v-if="previewCakeModal.dedication_text || previewCakeModal.special_instructions" class="space-y-2 text-xs">
          <div v-if="previewCakeModal.dedication_text" class="p-3 bg-amber-50/60 border border-amber-200/60 rounded-xl">
            <span class="font-bold text-amber-900 block mb-0.5">🎂 Inscription / Plaque Text:</span>
            <span class="italic text-amber-950 font-serif text-sm">&ldquo;{{ previewCakeModal.dedication_text }}&rdquo;</span>
          </div>
          <div v-if="previewCakeModal.special_instructions" class="p-3 bg-[#FAF7F2] border border-[#E9E0D4] rounded-xl text-[#2B1810]">
            <strong class="text-[#7D6E64] block mb-0.5">Customer Instructions:</strong>
            <p>{{ previewCakeModal.special_instructions }}</p>
          </div>
        </div>

        <!-- Close button -->
        <div class="flex justify-end pt-2 border-t border-[#F2ECE4]">
          <button
            @click="previewCakeModal = null"
            class="px-5 py-2 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl cursor-pointer transition shadow-xs"
          >
            Close Preview
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useDialogStore } from '../../stores/dialog';
import api from '../../api';

const route = useRoute();
const authStore = useAuthStore();
const toast = useToastStore();
const dialog = useDialogStore();

const activeTab = ref('verify');
const filterStatus = ref('All');
const sellerReports = ref([]);
const branchSubmissions = ref([]);

const branchName = computed(() => authStore.user?.branch?.branch_name || 'My Branch');

// Verification modal state
const verifyingReport = ref(null);
const reviewNotes = ref('');
const verifyingSubmitting = ref(false);

// Consolidation form state
const consolidationForm = reactive({
  period_type: 'Daily',
  period_start: new Date().toISOString().substring(0, 10),
  period_end: new Date().toISOString().substring(0, 10),
  confirmed_accuracy: false,
  manager_notes: '',
});
const loadingConsolidation = ref(false);
const submittingConsolidation = ref(false);
const pendingData = ref(null);

// Inspection modal
const inspectingReport = ref(null);

// 3D Cake Design Preview modal
const previewCakeModal = ref(null);

function openFullImage(url) {
  if (!url) return;
  window.open(url, '_blank');
}

const pendingCount = computed(() => {
  return sellerReports.value.filter(r => r.status === 'Pending Verification').length;
});

function formatDate(d) {
  if (!d) return '—';
  try {
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch (e) {
    return d;
  }
}

// Fetch seller reports for manager's branch
async function fetchSellerReports() {
  try {
    let url = '/admin/reports/seller/daily-reports';
    if (filterStatus.value && filterStatus.value !== 'All') {
      url += `?status=${encodeURIComponent(filterStatus.value)}`;
    }
    const res = await api.get(url);
    if (res.data && res.data.success) {
      sellerReports.value = res.data.data?.data || res.data.data || [];
    }
  } catch (e) {
    console.error('Failed to fetch seller reports:', e);
  }
}

// Open verification modal
async function openVerificationModal(id) {
  try {
    const res = await api.get(`/admin/reports/seller/daily-reports/${id}`);
    if (res.data && res.data.success) {
      verifyingReport.value = res.data.report;
      reviewNotes.value = verifyingReport.value.manager_notes || '';
    }
  } catch (e) {
    toast.error('Failed to load report lines for verification.');
  }
}

// Submit manager decision (approve or reject)
async function submitVerification(action) {
  if ((action === 'return' || action === 'reject') && !reviewNotes.value.trim()) {
    toast.warning('Please enter a note explaining the reason / what needs correction before rejecting this report.');
    return;
  }

  const confirmMsg = action === 'approve'
    ? 'Confirm direct approval of this seller daily sales report?'
    : 'Return this report to the seller with your correction notes?';

  const confirmed = await dialog.confirm({
    title: action === 'approve' ? 'Approve Report' : 'Return Report',
    message: confirmMsg,
    confirmText: action === 'approve' ? 'Approve' : 'Return for Correction',
    variant: action === 'approve' ? 'success' : 'warning',
  });
  if (!confirmed) return;

  verifyingSubmitting.value = true;
  try {
    const res = await api.post(`/admin/reports/manager/verify/${verifyingReport.value.id}`, {
      action: action,
      manager_notes: reviewNotes.value,
    });

    if (res.data && res.data.success) {
      toast.success(res.data.message || (action === 'approve' ? 'Report approved successfully!' : 'Report returned for correction.'));
      verifyingReport.value = null;
      await fetchSellerReports();
    }
  } catch (err) {
    console.error('Verification failed:', err);
    toast.error(err.response?.data?.message || 'Verification failed. Please try again.');
  } finally {
    verifyingSubmitting.value = false;
  }
}

// Gather approved seller reports for consolidation
async function fetchPendingConsolidation() {
  loadingConsolidation.value = true;
  try {
    const res = await api.get(`/admin/reports/manager/pending-consolidation?from_date=${consolidationForm.period_start}&to_date=${consolidationForm.period_end}`);
    if (res.data && res.data.success) {
      pendingData.value = res.data;
    }
  } catch (e) {
    console.error('Failed to load pending consolidation:', e);
    toast.error('Failed to gather approved reports.');
  } finally {
    loadingConsolidation.value = false;
  }
}

// Submit consolidated branch report to Super Admin
async function submitConsolidatedReport() {
  if (!consolidationForm.confirmed_accuracy) {
    toast.warning('Please check the confirmation box verifying the report accuracy.');
    return;
  }

  const count = pendingData.value?.seller_reports_count || 0;
  if (count === 0) {
    toast.warning('No approved seller reports found to consolidate.');
    return;
  }

  const confirmMsg = `Finalize consolidated branch sales report with ${count} seller report(s) totaling ETB ${Number(pendingData.value.summary.total_branch_revenue).toFixed(2)}? This action finalizes the report for your branch.`;
  const confirmed = await dialog.confirm({
    title: 'Finalize Branch Sales Report',
    message: confirmMsg,
    confirmText: 'Finalize & Submit',
    variant: 'primary',
  });
  if (!confirmed) return;

  submittingConsolidation.value = true;
  try {
    const payload = {
      period_type: consolidationForm.period_type,
      period_start: consolidationForm.period_start,
      period_end: consolidationForm.period_end,
      confirmed_accuracy: true,
      manager_notes: consolidationForm.manager_notes,
      report_reference: pendingData.value?.suggested_reference,
    };

    const res = await api.post('/admin/reports/manager/consolidate-and-submit', payload);
    if (res.data && res.data.success) {
      toast.success(res.data.message || 'Branch sales report finalized successfully!');
      pendingData.value = null;
      consolidationForm.confirmed_accuracy = false;
      consolidationForm.manager_notes = '';
      await fetchBranchSubmissions();
      await fetchSellerReports();
      activeTab.value = 'submissions';
    }
  } catch (err) {
    console.error('Consolidation failed:', err);
    toast.error(err.response?.data?.message || 'Failed to submit consolidated report.');
  } finally {
    submittingConsolidation.value = false;
  }
}

// Fetch historical branch submissions
async function fetchBranchSubmissions() {
  try {
    const res = await api.get('/admin/reports/manager/consolidated-reports');
    if (res.data && res.data.success) {
      branchSubmissions.value = res.data.data?.data || res.data.data || [];
    }
  } catch (e) {
    console.error('Failed to fetch branch submissions:', e);
  }
}

// Inspect consolidated report
async function inspectConsolidated(id) {
  try {
    const res = await api.get(`/admin/reports/manager/consolidated-reports/${id}`);
    if (res.data && res.data.success) {
      inspectingReport.value = res.data.report;
    }
  } catch (e) {
    try {
      await openVerificationModal(id);
      activeTab.value = 'verify';
    } catch (err) {
      toast.error('Failed to load report details.');
    }
  }
}

watch(() => route.query.report_id, async (newId) => {
  if (newId) {
    activeTab.value = 'submissions';
    await inspectConsolidated(newId);
  }
});

onMounted(async () => {
  await fetchSellerReports();
  await fetchBranchSubmissions();
  await fetchPendingConsolidation();
  if (route.query.report_id) {
    activeTab.value = 'submissions';
    await inspectConsolidated(route.query.report_id);
  }
});
</script>

