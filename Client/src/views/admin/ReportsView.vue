<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-16 font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#EFE8DE] pb-5">
      <div>
        <div class="flex items-center space-x-2.5">
          <span class="text-2xl">🏛️</span>
          <h1 class="font-serif text-2xl sm:text-3xl font-black text-[#2B1810]">
            Branch Sales &amp; Order Reports
          </h1>
        </div>
        <p class="text-sm text-[#7D6E64] mt-1">
          Centralized headquarters monitoring of verified branch sales reports submitted by Branch Managers.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
        <button
          @click="fetchOverviewData"
          class="px-3.5 py-2 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer flex items-center space-x-1 shadow-2xs"
        >
          <span>🔄</span>
          <span>Refresh</span>
        </button>

        <button
          @click="exportCSV"
          class="px-3.5 py-2 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer flex items-center space-x-1 shadow-2xs"
        >
          <span>📥</span>
          <span>Export CSV</span>
        </button>

        <button
          @click="printReport"
          class="px-4 py-2 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
        >
          <span>🖨️</span>
          <span>Print / PDF</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar: Date Range and Branch Filter -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex flex-wrap items-center justify-between gap-3 text-xs">
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-bold text-[#7D6E64] uppercase text-[11px]">Period:</span>
        <button
          v-for="p in [
            { label: 'All Time', val: 'all' },
            { label: 'Today', val: 'today' },
            { label: 'This Week', val: 'this_week' },
            { label: 'This Month', val: 'this_month' }
          ]"
          :key="p.val"
          @click="setPeriod(p.val)"
          class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
          :class="activePeriod === p.val ? 'bg-[#3D251E] text-white shadow-2xs' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
        >
          {{ p.label }}
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <span class="font-bold text-[#7D6E64] uppercase text-[11px]">Filter Branch:</span>
        <select
          v-model="selectedBranchId"
          @change="fetchOverviewData"
          class="px-3 py-1.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] cursor-pointer"
        >
          <option value="all">All Branches</option>
          <option v-for="b in branchesList" :key="b.id" :value="b.id">
            {{ b.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 5A: OVERVIEW KPI CARDS                                    -->
    <!-- ================================================================= -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-3 sm:gap-4 text-xs">
      <!-- 1. Total Branches -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Total Branches</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#2B1810]">
          {{ kpis.total_branches || 0 }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Active operational outlets</div>
      </div>

      <!-- 2. Submitted Reports & Awaiting Review -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Branch Reports</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#2B1810]">
          {{ kpis.total_submitted_reports || 0 }}
        </div>
        <div class="text-[11px] font-bold flex items-center space-x-1" :class="kpis.awaiting_review_count > 0 ? 'text-amber-700' : 'text-emerald-700'">
          <span>{{ kpis.awaiting_review_count || 0 }} awaiting review</span>
        </div>
      </div>

      <!-- 3. Verified Reports -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Verified Reports</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-emerald-700">
          {{ kpis.verified_reports_count || 0 }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Officially approved by HQ</div>
      </div>

      <!-- 4. Combined Multi-Branch Revenue (Verified Official) -->
      <div class="bg-gradient-to-r from-[#3D251E] to-[#57392C] text-white rounded-2xl p-4 sm:p-5 shadow-xs space-y-1">
        <div class="flex items-center justify-between">
          <div class="text-[10px] font-bold uppercase tracking-widest text-[#D8CDC5]">Verified Revenue (Official)</div>
          <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">HQ Approved</span>
        </div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-amber-200">
          ETB {{ Number(kpis.verified_revenue !== undefined ? kpis.verified_revenue : (kpis.total_revenue || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
        </div>
        <div class="text-[11px] text-[#E5DACD] flex items-center justify-between">
          <span>Official HQ audit approved</span>
          <span v-if="kpis.pending_verification_revenue > 0" class="text-amber-300 font-semibold text-[10px]">
            +ETB {{ Number(kpis.pending_verification_revenue).toLocaleString(undefined, { maximumFractionDigits: 0 }) }} in review
          </span>
        </div>
      </div>

      <!-- 5. Regular Orders Across All Branches -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Total Regular Orders</div>
        <div class="text-2xl font-serif font-black text-[#2B1810]">
          {{ kpis.total_regular_orders || 0 }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Bakery retail transactions</div>
      </div>

      <!-- 6. Custom Cake Orders Across All Branches -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-[#7D6E64]">Total Custom Cakes</div>
        <div class="text-2xl font-serif font-black text-[#2B1810]">
          {{ kpis.total_custom_cakes || 0 }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Special celebration cakes</div>
      </div>

      <!-- 7. Advance Payments Collected -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Advance Deposits</div>
        <div class="text-2xl font-serif font-black text-emerald-700">
          ETB {{ Number(kpis.total_advance_payments || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Cash &amp; bank deposits received</div>
      </div>

      <!-- 8. Outstanding Balances -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Outstanding Balances</div>
        <div class="text-2xl font-serif font-black text-amber-700">
          ETB {{ Number(kpis.total_outstanding_balances || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
        </div>
        <div class="text-[11px] text-[#7D6E64]">Receivable on cake delivery</div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 5B: BRANCH-BY-BRANCH PERFORMANCE TABLE                    -->
    <!-- ================================================================= -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-2xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#F4EFEA] pb-3">
        <div>
          <h2 class="font-serif font-black text-lg text-[#2B1810]">
            Branch-by-Branch Performance Overview
          </h2>
          <p class="text-xs text-[#7D6E64]">
            Click on any branch to inspect its submitted orders, custom cakes, revenue, and manager verification trail.
          </p>
        </div>
        <span class="text-xs font-bold text-[#7D6E64]">
          Showing {{ branchPerformance.length }} Active Branches
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
              <th class="py-3 px-3">Branch</th>
              <th class="py-3 px-3 text-center">Regular Orders</th>
              <th class="py-3 px-3 text-center">Custom Cakes</th>
              <th class="py-3 px-3 text-right">Total Revenue</th>
              <th class="py-3 px-3">Manager</th>
              <th class="py-3 px-3">Report Status</th>
              <th class="py-3 px-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F2ECE4]">
            <tr v-if="branchPerformance.length === 0">
              <td colspan="7" class="py-8 text-center text-[#9E8E82] text-xs">
                No branch performance data recorded yet.
              </td>
            </tr>
            <tr
              v-for="b in branchPerformance"
              :key="b.branch_id"
              class="hover:bg-[#FAF7F2]/70 transition cursor-pointer"
              @click="onRowClick(b)"
            >
              <td class="py-3.5 px-3">
                <div class="font-bold text-[#2B1810] text-sm">{{ b.branch_name }}</div>
                <div class="text-[#7D6E64] text-[11px] font-mono">{{ b.branch_code }} • {{ b.city || 'Addis Ababa' }}</div>
              </td>
              <td class="py-3.5 px-3 text-center font-bold text-[#2B1810]">
                {{ b.regular_orders }}
              </td>
              <td class="py-3.5 px-3 text-center font-bold text-[#2B1810]">
                {{ b.custom_cakes }}
              </td>
              <td class="py-3.5 px-3 text-right">
                <div class="font-serif font-black text-sm text-[#2B1810]">
                  ETB {{ Number(b.total_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                </div>
                <div v-if="b.pending_revenue > 0" class="text-[10px] text-amber-700 font-semibold">
                  +ETB {{ Number(b.pending_revenue).toLocaleString(undefined, { maximumFractionDigits: 0 }) }} in review
                </div>
              </td>
              <td class="py-3.5 px-3 font-semibold text-[#6E5D52]">
                {{ b.manager_name }}
              </td>
              <td class="py-3.5 px-3">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-block"
                  :class="{
                    'bg-emerald-50 text-emerald-800 border-emerald-200': b.latest_report_status === 'Verified by Super Admin',
                    'bg-blue-50 text-blue-800 border-blue-200': b.latest_report_status === 'Submitted to Super Admin',
                    'bg-rose-50 text-rose-800 border-rose-200': b.latest_report_status === 'Returned for Clarification',
                    'bg-stone-100 text-stone-600 border-stone-200': b.latest_report_status === 'No Submissions'
                  }"
                >
                  {{ b.latest_report_status }}
                </span>
              </td>
              <td class="py-3.5 px-3 text-center" @click.stop>
                <button
                  v-if="b.latest_report_id"
                  @click="inspectReport(b.latest_report_id)"
                  class="px-3 py-1.5 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-2xs"
                >
                  Inspect Report
                </button>
                <span v-else class="text-[#B29E91] text-xs italic">Awaiting</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- RECENT CONSOLIDATED BRANCH SUBMISSIONS LIST                       -->
    <!-- ================================================================= -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl p-6 shadow-2xs space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F4EFEA] pb-3">
        <div>
          <h2 class="font-serif font-black text-lg text-[#2B1810]">
            All Branch Consolidated Submissions
          </h2>
          <p class="text-xs text-[#7D6E64]">
            Review all consolidated branch reports submitted by branch managers for official verification.
          </p>
        </div>

        <!-- Filter Submissions by Status -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
          <button
            v-for="st in [
              { label: 'All Submissions', val: 'all' },
              { label: 'Awaiting Review', val: 'Submitted to Super Admin' },
              { label: 'Verified', val: 'Verified by Super Admin' },
              { label: 'Clarification Needed', val: 'Returned for Clarification' }
            ]"
            :key="st.val"
            @click="submissionFilter = st.val"
            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer text-[11px]"
            :class="submissionFilter === st.val ? 'bg-[#3D251E] text-white shadow-2xs' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
          >
            {{ st.label }}
            <span
              v-if="st.val === 'Submitted to Super Admin' && kpis.awaiting_review_count > 0"
              class="ml-1 px-1.5 py-0.2 rounded-full text-[9px] bg-amber-400 text-[#3D251E] font-black"
            >
              {{ kpis.awaiting_review_count }}
            </span>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
              <th class="py-3 px-3">Report Ref</th>
              <th class="py-3 px-3">Branch</th>
              <th class="py-3 px-3">Period</th>
              <th class="py-3 px-3">Dates</th>
              <th class="py-3 px-3 text-center">Sellers</th>
              <th class="py-3 px-3 text-right">Regular Rev</th>
              <th class="py-3 px-3 text-right">Custom Cake Rev</th>
              <th class="py-3 px-3 text-right">Total Revenue</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F2ECE4]">
            <tr v-if="filteredSubmissions.length === 0">
              <td colspan="10" class="py-8 text-center text-[#9E8E82] text-xs">
                No consolidated branch reports found for this filter.
              </td>
            </tr>
            <tr v-for="r in filteredSubmissions" :key="r.id" class="hover:bg-[#FAF7F2]/60 transition">
              <td class="py-3 px-3 font-mono font-bold text-[#3D251E]">
                {{ r.report_reference }}
              </td>
              <td class="py-3 px-3 font-bold text-[#2B1810]">
                {{ r.branch?.branch_name || 'Branch #' + r.branch_id }}
              </td>
              <td class="py-3 px-3 font-semibold text-[#6E5D52]">
                {{ r.period_type }}
              </td>
              <td class="py-3 px-3 text-[#6E5D52]">
                {{ formatDate(r.period_start) }} – {{ formatDate(r.period_end) }}
              </td>
              <td class="py-3 px-3 text-center font-bold text-[#2B1810]">
                {{ r.sellers_included_count }}
              </td>
              <td class="py-3 px-3 text-right">
                ETB {{ Number(r.total_regular_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3 text-right">
                ETB {{ Number(r.total_custom_cake_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3 text-right font-serif font-black text-[#2B1810]">
                ETB {{ Number(r.total_branch_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-3">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-block"
                  :class="{
                    'bg-emerald-50 text-emerald-800 border-emerald-200': r.status === 'Verified by Super Admin',
                    'bg-blue-50 text-blue-800 border-blue-200': r.status === 'Submitted to Super Admin',
                    'bg-rose-50 text-rose-800 border-rose-200': r.status === 'Returned for Clarification',
                  }"
                >
                  {{ r.status }}
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <button
                  @click="inspectReport(r.id)"
                  class="px-3 py-1.5 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-2xs"
                >
                  {{ r.status === 'Submitted to Super Admin' ? 'Audit & Verify' : 'View Full Details' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 5C & 5D: SUPER ADMIN DEEP SALES INSPECTION & VERIFY MODAL -->
    <!-- ================================================================= -->
    <div
      v-if="activeInspection"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="activeInspection = null"
    >
      <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[92vh] overflow-y-auto p-6 space-y-5 border border-[#E9E0D4] shadow-xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-[#F2ECE4] pb-4">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Super Admin Inspection &amp; Headquarters Approval</div>
            <h3 class="font-serif font-black text-2xl text-[#2B1810]">
              {{ activeInspection.report_reference }}
            </h3>
            <p class="text-xs text-[#7D6E64] mt-0.5">
              Branch: <strong>{{ activeInspection.branch?.branch_name }}</strong> |
              Manager: <strong>{{ activeInspection.manager?.full_name || activeInspection.manager?.email }}</strong> |
              Period: <strong>{{ activeInspection.period_type }} ({{ formatDate(activeInspection.period_start) }} – {{ formatDate(activeInspection.period_end) }})</strong> |
              Status: <strong>{{ activeInspection.status }}</strong>
            </p>
          </div>
          <button @click="activeInspection = null" class="text-[#7D6E64] hover:text-[#2B1810] text-2xl font-bold cursor-pointer">✕</button>
        </div>

        <!-- Verification Status Banner -->
        <div
          v-if="activeInspection.status === 'Verified by Super Admin'"
          class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between shadow-2xs"
        >
          <div class="flex items-center space-x-3">
            <span class="text-2xl">✅</span>
            <div>
              <div class="font-bold text-sm text-emerald-950">Officially Verified &amp; Recorded in Corporate Ledger</div>
              <div class="text-xs text-emerald-800 mt-0.5">
                Verified by {{ activeInspection.super_admin?.full_name || 'Super Admin' }} on {{ formatDate(activeInspection.verified_at) }}. Revenue is officially recognized in financial statements.
              </div>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
            OFFICIAL REVENUE
          </span>
        </div>

        <div
          v-else-if="activeInspection.status === 'Returned for Clarification'"
          class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center space-x-3 shadow-2xs"
        >
          <span class="text-2xl">⚠️</span>
          <div>
            <div class="font-bold text-sm text-amber-950">Returned for Branch Manager Clarification</div>
            <div class="text-xs text-amber-800 mt-0.5">
              Awaiting Branch Manager {{ activeInspection.manager?.full_name || '' }} to provide clarification and re-submit this report.
            </div>
          </div>
        </div>

        <div
          v-else
          class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-center space-x-3 shadow-2xs"
        >
          <span class="text-2xl">📥</span>
          <div>
            <div class="font-bold text-sm text-blue-950">Awaiting Super Admin Audit &amp; Verification</div>
            <div class="text-xs text-blue-800 mt-0.5">
              Submitted by Branch Manager {{ activeInspection.manager?.full_name || '' }}. Please audit products, custom cakes, deposits, and verify below.
            </div>
          </div>
        </div>

        <!-- Manager Notes -->
        <div v-if="activeInspection.manager_notes" class="p-3.5 bg-[#FAF7F2] border border-[#E9E0D4] rounded-xl text-xs text-[#2B1810]">
          <strong>Branch Manager Reconciliation Notes:</strong>
          <p class="mt-0.5 text-[#6E5D52]">{{ activeInspection.manager_notes }}</p>
        </div>

        <!-- Super Admin Notes if already returned previously -->
        <div v-if="activeInspection.super_admin_notes" class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900">
          <strong>HQ Review &amp; Clarification Feedback:</strong>
          <p class="mt-0.5">{{ activeInspection.super_admin_notes }}</p>
        </div>

        <!-- Financial Summary Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-[#FAF7F2] p-4 rounded-xl border border-[#E9E0D4]">
          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Regular Revenue</div>
            <div class="font-serif font-black text-base text-[#2B1810]">
              ETB {{ Number(activeInspection.total_regular_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </div>
            <div class="text-[10px] text-[#7D6E64]">{{ activeInspection.total_regular_orders }} orders ({{ activeInspection.total_regular_products_sold }} items)</div>
          </div>

          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Custom Cake Revenue</div>
            <div class="font-serif font-black text-base text-[#2B1810]">
              ETB {{ Number(activeInspection.total_custom_cake_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </div>
            <div class="text-[10px] text-[#7D6E64]">{{ activeInspection.total_custom_cake_orders }} cakes booked</div>
          </div>

          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Advance Deposits</div>
            <div class="font-serif font-black text-base text-emerald-700">
              ETB {{ Number(activeInspection.total_advance_payments).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </div>
            <div class="text-[10px] text-amber-700">Due: ETB {{ Number(activeInspection.total_outstanding_balances).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</div>
          </div>

          <div>
            <div class="text-[#7D6E64] text-[10px] uppercase font-bold">Total Branch Revenue</div>
            <div class="font-serif font-black text-lg text-[#3D251E]">
              ETB {{ Number(activeInspection.total_branch_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </div>
            <div class="text-[10px] text-emerald-800">Cash In: ETB {{ Number(activeInspection.total_payments_received).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</div>
          </div>
        </div>

        <!-- Sellers Involved Breakdown -->
        <div v-if="activeInspection.seller_reports?.length" class="space-y-2">
          <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">
            Sellers Reporting in this Period ({{ activeInspection.seller_reports.length }} reports)
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
            <div
              v-for="sr in activeInspection.seller_reports"
              :key="sr.id"
              class="p-3 bg-white border border-[#E9E0D4] rounded-xl flex items-center justify-between"
            >
              <div>
                <div class="font-bold text-[#2B1810]">{{ sr.seller?.full_name || sr.seller?.email }}</div>
                <div class="text-[11px] text-[#7D6E64] font-mono">{{ sr.report_reference }} • {{ formatDate(sr.report_date) }}</div>
              </div>
              <div class="text-right">
                <div class="font-serif font-bold text-[#2B1810]">ETB {{ Number(sr.combined_daily_revenue).toFixed(2) }}</div>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">{{ sr.status }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Breakdown from JSON (Products & Custom Cakes) -->
        <div v-if="activeInspection.breakdown_json" class="space-y-4">
          <!-- Products breakdown -->
          <div v-if="activeInspection.breakdown_json.products?.length" class="space-y-1.5">
            <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">Products &amp; Items Sold Breakdown</div>
            <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px] uppercase">
                  <tr>
                    <th class="p-2.5">Product Name</th>
                    <th class="p-2.5">Category</th>
                    <th class="p-2.5 text-right">Quantity Sold</th>
                    <th class="p-2.5 text-right">Total Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F2ECE4]">
                  <tr v-for="(p, idx) in activeInspection.breakdown_json.products" :key="idx">
                    <td class="p-2.5 font-semibold text-[#2B1810]">{{ p.name }}</td>
                    <td class="p-2.5 text-[#6E5D52]">{{ p.category }}</td>
                    <td class="p-2.5 text-right font-bold text-[#2B1810]">{{ p.quantity }}</td>
                    <td class="p-2.5 text-right font-bold text-[#2B1810]">ETB {{ Number(p.total).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Custom Cakes breakdown -->
          <div v-if="activeInspection.breakdown_json.custom_cakes?.length" class="space-y-1.5">
            <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">Custom Cakes Category Breakdown</div>
            <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px] uppercase">
                  <tr>
                    <th class="p-2.5">Category &amp; Design</th>
                    <th class="p-2.5">Size/Weight</th>
                    <th class="p-2.5 text-right">Quantity</th>
                    <th class="p-2.5 text-right">Total Price</th>
                    <th class="p-2.5 text-right">Advance Paid</th>
                    <th class="p-2.5 text-right">Balance Due</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F2ECE4]">
                  <tr v-for="(c, idx) in activeInspection.breakdown_json.custom_cakes" :key="idx">
                    <td class="p-2.5 font-semibold text-[#2B1810]">{{ c.category }} — {{ c.design }}</td>
                    <td class="p-2.5 text-[#6E5D52]">{{ c.size }}</td>
                    <td class="p-2.5 text-right font-bold">{{ c.quantity }}</td>
                    <td class="p-2.5 text-right font-bold text-[#2B1810]">ETB {{ Number(c.total).toFixed(2) }}</td>
                    <td class="p-2.5 text-right font-bold text-emerald-700">ETB {{ Number(c.advance).toFixed(2) }}</td>
                    <td class="p-2.5 text-right font-bold text-amber-700">ETB {{ Number(c.balance).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Individual Custom Cake Orders & 3D Design Audits -->
        <div v-if="consolidatedCustomCakes.length" class="space-y-2">
          <div class="flex items-center justify-between">
            <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">
              Custom Cake Line Audits &amp; 3D Designs ({{ consolidatedCustomCakes.length }} orders)
            </div>
            <span class="text-[10px] text-[#7D6E64]">Inspect Three.js 3D snapshot, specs, &amp; advance deposits</span>
          </div>
          <div class="border border-[#E9E0D4] rounded-xl overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#FAF7F2] text-[#6E5D52] font-bold text-[10px] uppercase">
                <tr>
                  <th class="p-2.5">Order Ref</th>
                  <th class="p-2.5">Customer</th>
                  <th class="p-2.5">Cake Design / Specs</th>
                  <th class="p-2.5 text-right">Total Price</th>
                  <th class="p-2.5 text-right">Advance Paid</th>
                  <th class="p-2.5 text-right">Balance Due</th>
                  <th class="p-2.5 text-center">3D Design</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F2ECE4]">
                <tr v-for="c in consolidatedCustomCakes" :key="c.id">
                  <td class="p-2.5 font-mono font-bold">{{ c.cake_order_number }}</td>
                  <td class="p-2.5 font-semibold text-[#2B1810]">{{ c.customer_name }}</td>
                  <td class="p-2.5">
                    <div class="font-medium text-[#2B1810]">{{ c.cake_type_design }}</div>
                    <div v-if="c.customization_details" class="text-[10px] text-[#7D6E64] italic truncate max-w-xs">&ldquo;{{ c.customization_details }}&rdquo;</div>
                  </td>
                  <td class="p-2.5 text-right font-bold text-[#2B1810]">ETB {{ Number(c.total_price).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold text-emerald-700">ETB {{ Number(c.advance_payment).toFixed(2) }}</td>
                  <td class="p-2.5 text-right font-bold text-amber-700">ETB {{ Number(c.remaining_balance).toFixed(2) }}</td>
                  <td class="p-2.5 text-center">
                    <button
                      v-if="c.system_custom_cake?.snapshot_url || c.system_custom_cake?.images?.length"
                      type="button"
                      @click.stop="previewCakeModal = c.system_custom_cake"
                      class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[9px] font-bold bg-rose-50 hover:bg-rose-100 text-[#DE1B54] border border-rose-200 cursor-pointer shadow-2xs transition"
                    >
                      <span>🎂 View 3D Snapshot</span>
                    </button>
                    <span v-else class="text-[10px] text-[#B29E91] italic">Standard</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Super Admin Decision Form -->
        <div v-if="activeInspection.status !== 'Verified by Super Admin'" class="bg-[#FAF7F2] p-4 rounded-xl border border-[#E9E0D4] space-y-3">
          <div class="text-xs font-bold uppercase tracking-wider text-[#2B1810]">
            Super Admin Action (Verification or Return for Clarification)
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">
              Feedback Notes (Required when returning report for clarification)
            </label>
            <textarea
              v-model="superAdminNotes"
              rows="2"
              placeholder="e.g. Verified by Super Admin: Reconciled with central ERP ledger. OR: Return note: Please verify custom cake advances for this week."
              class="w-full px-3.5 py-2 bg-white border border-[#E2D8CC] rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
            ></textarea>
          </div>

          <div class="flex items-center justify-between pt-1">
            <button
              @click="activeInspection = null"
              class="px-4 py-2 bg-white border border-[#E2D8CC] text-[#6E5D52] font-bold text-xs rounded-xl cursor-pointer"
            >
              Close
            </button>

            <div class="flex items-center space-x-3">
              <button
                @click="submitSuperAdminDecision('return')"
                :disabled="decisionSubmitting"
                class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 font-bold text-xs rounded-xl transition cursor-pointer"
              >
                ↩️ Return for Clarification
              </button>

              <button
                @click="submitSuperAdminDecision('verify')"
                :disabled="decisionSubmitting"
                class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-xs"
              >
                ✅ Mark as Verified
              </button>
            </div>
          </div>
        </div>

        <!-- If already verified -->
        <div v-else class="flex justify-end pt-2">
          <button
            @click="activeInspection = null"
            class="px-5 py-2 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl cursor-pointer transition shadow-xs"
          >
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
              <span>🎂 Super Admin 3D Design Audit</span>
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
import { ref, reactive, computed, onMounted } from 'vue';
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const dialog = useDialogStore();
const toast = useToastStore();

const activePeriod = ref('all');
const selectedBranchId = ref('all');
const branchesList = ref([]);
const submissionFilter = ref('all');

// 3D Cake Design Preview modal
const previewCakeModal = ref(null);

function openFullImage(url) {
  if (!url) return;
  window.open(url, '_blank');
}

const kpis = reactive({
  total_branches: 0,
  total_submitted_reports: 0,
  awaiting_review_count: 0,
  verified_reports_count: 0,
  total_regular_orders: 0,
  total_custom_cakes: 0,
  total_revenue: 0,
  total_advance_payments: 0,
  total_outstanding_balances: 0,
  total_payments_received: 0,
});

const branchPerformance = ref([]);
const recentReports = ref([]);

const filteredSubmissions = computed(() => {
  if (!recentReports.value) return [];
  if (submissionFilter.value === 'all') return recentReports.value;
  return recentReports.value.filter(r => r.status === submissionFilter.value);
});

// Deep inspection modal
const activeInspection = ref(null);
const superAdminNotes = ref('');
const decisionSubmitting = ref(false);

const consolidatedCustomCakes = computed(() => {
  if (!activeInspection.value?.seller_reports) return [];
  const list = [];
  activeInspection.value.seller_reports.forEach(sr => {
    if (sr.custom_cakes) {
      list.push(...sr.custom_cakes);
    }
  });
  return list;
});

function formatDate(d) {
  if (!d) return '—';
  try {
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch (e) {
    return d;
  }
}

function setPeriod(p) {
  activePeriod.value = p;
  fetchOverviewData();
}

async function fetchOverviewData() {
  try {
    let url = '/admin/reports/super-admin/overview';
    const params = [];
    if (selectedBranchId.value !== 'all') {
      params.push(`branch_id=${selectedBranchId.value}`);
    }

    const today = new Date();
    if (activePeriod.value === 'today') {
      const todayStr = today.toISOString().substring(0, 10);
      params.push(`from_date=${todayStr}`);
      params.push(`to_date=${todayStr}`);
    } else if (activePeriod.value === 'this_week') {
      const startOfWeek = new Date(today);
      startOfWeek.setDate(today.getDate() - today.getDay());
      params.push(`from_date=${startOfWeek.toISOString().substring(0, 10)}`);
      params.push(`to_date=${today.toISOString().substring(0, 10)}`);
    } else if (activePeriod.value === 'this_month') {
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      params.push(`from_date=${startOfMonth.toISOString().substring(0, 10)}`);
      params.push(`to_date=${today.toISOString().substring(0, 10)}`);
    }

    if (params.length > 0) {
      url += '?' + params.join('&');
    }

    const res = await api.get(url);
    if (res.data && res.data.success) {
      Object.assign(kpis, res.data.kpis);
      branchPerformance.value = res.data.branch_performance || [];
      recentReports.value = res.data.recent_reports || [];
      branchesList.value = res.data.branches || [];
    }
  } catch (e) {
    console.error('Failed to fetch Super Admin overview:', e);
  }
}

function onRowClick(b) {
  if (b.latest_report_id) {
    inspectReport(b.latest_report_id);
  }
}

// Inspect consolidated report
async function inspectReport(id) {
  try {
    const res = await api.get(`/admin/reports/super-admin/consolidated/${id}`);
    if (res.data && res.data.success) {
      activeInspection.value = res.data.report;
      superAdminNotes.value = activeInspection.value.super_admin_notes || '';
    }
  } catch (e) {
    toast.error('Failed to load consolidated report details.');
  }
}

// Super Admin decision: verify or return
async function submitSuperAdminDecision(action) {
  if (action === 'return' && !superAdminNotes.value.trim()) {
    toast.warning('Please provide clarification notes explaining what the Branch Manager must verify.');
    return;
  }

  const confirmTitle = action === 'verify' ? 'Verify Consolidated Report' : 'Return Report for Clarification';
  const confirmMsg = action === 'verify'
    ? 'Mark this consolidated branch report as Verified by Super Admin?'
    : 'Return this report to the Branch Manager for clarification?';

  const confirmed = await dialog.confirm({
    title: confirmTitle,
    message: confirmMsg,
    confirmText: action === 'verify' ? 'Verify Report' : 'Return Report',
    variant: action === 'verify' ? 'success' : 'warning',
  });
  if (!confirmed) return;

  decisionSubmitting.value = true;
  try {
    const res = await api.post(`/admin/reports/super-admin/consolidated/${activeInspection.value.id}/verify`, {
      action: action,
      super_admin_notes: superAdminNotes.value,
    });

    if (res.data && res.data.success) {
      toast.success(res.data.message || (action === 'verify' ? 'Report verified successfully!' : 'Report returned.'));
      activeInspection.value = null;
      await fetchOverviewData();
    }
  } catch (err) {
    console.error('Decision submission failed:', err);
    toast.error(err.response?.data?.message || 'Action failed.');
  } finally {
    decisionSubmitting.value = false;
  }
}

function exportCSV() {
  const headers = ['Branch', 'Regular Orders', 'Custom Cakes', 'Total Revenue (ETB)', 'Manager', 'Report Status'];
  const rows = branchPerformance.value.map(b => [
    `"${b.branch_name}"`,
    b.regular_orders,
    b.custom_cakes,
    b.total_revenue,
    `"${b.manager_name}"`,
    `"${b.latest_report_status}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `SweetBakes_Branch_Sales_Report_${new Date().toISOString().substring(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function printReport() {
  window.print();
}

onMounted(async () => {
  await fetchOverviewData();
});
</script>
