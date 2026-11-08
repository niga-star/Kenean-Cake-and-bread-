<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-[#E9E0D4] shadow-sm">
      <div class="flex items-center space-x-3.5">
        <div class="w-12 h-12 rounded-2xl bg-[#FAF0E6] text-[#BA5420] flex items-center justify-center text-xl shrink-0 border border-[#F0DFD0] shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">
            {{ isSuperAdmin ? 'Executive Branch Reports' : (isManager ? 'Branch Operational Reports Desk' : 'Submit Shift / Work Report') }}
          </h1>
          <p class="text-xs text-[#7A6B60] mt-0.5">
            {{ isSuperAdmin ? 'Central monitoring of reports and sales summaries submitted by branch managers' : (isManager ? 'Review branch staff daily reports and submit consolidated reports to Super Admin' : 'Report daily sales, order activities, deliveries, and shift findings to your Branch Manager') }}
          </p>
        </div>
      </div>

      <button
        @click="isSubmitModalOpen = true"
        class="inline-flex items-center justify-center space-x-1.5 px-4 py-2 text-xs sm:text-sm font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm cursor-pointer"
      >
        <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        <span>{{ isManager ? '+ Submit Report to Super Admin' : '+ Submit New Report' }}</span>
      </button>
    </div>

    <!-- Alert / Status Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-xl text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>

    <!-- Reporting Hierarchy Diagram Card -->
    <div class="bg-gradient-to-r from-[#FAF6F0] via-white to-[#FAF6F0] border border-[#E9E0D4] rounded-2xl p-5 shadow-xs">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center space-x-3">
          <span class="text-2xl">📊</span>
          <div>
            <div class="font-bold text-[#2A1D15]">Official Bakery Reporting Hierarchy (Section 7)</div>
            <div class="text-[#7A6B60] text-[11px]">Branch Staff (Sellers/Servants/Delivery) ➔ Branch Manager ➔ Super Admin</div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 font-medium text-[11px]">
          <span class="px-2.5 py-1 rounded-lg bg-white border border-[#D8CDC0] text-[#4A3B32] shadow-2xs">
            1. Sellers / Staff Submit
          </span>
          <span class="text-[#BA5420] font-bold">➔</span>
          <span class="px-2.5 py-1 rounded-lg bg-[#FAF0E6] border border-[#F0DFD0] text-[#BA5420] font-bold shadow-2xs">
            2. Manager Verifies &amp; Forwards
          </span>
          <span class="text-[#BA5420] font-bold">➔</span>
          <span class="px-2.5 py-1 rounded-lg bg-[#FFF3E0] border border-[#FFE0B2] text-[#E65100] font-bold shadow-2xs">
            3. Super Admin Review
          </span>
        </div>
      </div>
    </div>

    <!-- Filter Bar & Reports Table -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm overflow-hidden space-y-4 p-5 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#EFE8DF] pb-4">
        <div>
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">Submitted Reports Log</h3>
          <p class="text-xs text-[#7A6B60] mt-0.5">
            {{ isSuperAdmin ? 'Monitoring all branches across the entire business' : (isManager ? 'Reports submitted by your branch staff' : 'Your personal submitted reports') }}
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <!-- Branch Selector (Super Admin only) -->
          <div v-if="isSuperAdmin" class="flex items-center space-x-1.5">
            <label class="text-xs font-semibold text-[#65554B]">Branch:</label>
            <select
              v-model="selectedBranchFilter"
              @change="loadReports"
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]"
            >
              <option value="all">All Branches</option>
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }}
              </option>
            </select>
          </div>

          <!-- Report Type Filter -->
          <div class="flex items-center space-x-1.5">
            <label class="text-xs font-semibold text-[#65554B]">Type:</label>
            <select
              v-model="selectedTypeFilter"
              @change="loadReports"
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]"
            >
              <option value="all">All Report Types</option>
              <option value="Daily Sales">Daily Sales</option>
              <option value="Order Activity">Order Activity</option>
              <option value="Delivery Summary">Delivery Summary</option>
              <option value="Custom Cake Report">Custom Cake Report</option>
              <option value="Price Update">Price Update</option>
              <option value="Shift Log">Shift Log</option>
              <option value="Task Report">Task Report</option>
              <option value="Product Information">Product Information</option>
              <option value="Branch Performance">Branch Performance</option>
            </select>
          </div>

          <!-- Date Filter (Step 6/7) -->
          <div class="flex items-center space-x-1.5">
            <label class="text-xs font-semibold text-[#65554B]">Date:</label>
            <select
              v-model="selectedDateFilter"
              @change="loadReports"
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]"
            >
              <option value="all">All Dates</option>
              <option value="today">Today</option>
              <option value="this_week">This Week</option>
              <option value="this_month">This Month</option>
              <option value="custom">Custom Date</option>
            </select>
          </div>

          <input
            v-if="selectedDateFilter === 'custom'"
            type="date"
            v-model="customDate"
            @change="loadReports"
            class="bg-white border border-[#D8CDC0] rounded-lg px-2 py-1 text-xs text-[#2D231D]"
          />
        </div>
      </div>

      <!-- Reports Table -->
      <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
        <table class="w-full text-left text-xs min-w-[760px]">
          <thead class="bg-[#FAF6F0] text-[#88786B] uppercase font-semibold border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Report Title</th>
              <th class="py-3 px-4">Submitted By</th>
              <th class="py-3 px-4">Branch</th>
              <th class="py-3 px-4">Type</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="rep in filteredReports" :key="rep.report_id" class="hover:bg-[#FCFAF7] transition">
              <td class="py-3 px-4 font-mono text-[#5A4A3E]">
                {{ new Date(rep.created_at).toLocaleDateString() }}
                <span class="block text-[10px] text-[#8A7B71]">{{ new Date(rep.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
              </td>
              <td class="py-3 px-4">
                <div class="font-bold text-[#2A1D15]">{{ rep.title }}</div>
                <div class="text-[11px] text-[#7A6B60] max-w-sm truncate" :title="rep.content">{{ rep.content }}</div>
              </td>
              <td class="py-3 px-4">
                <div class="font-semibold text-[#2D231D]">{{ rep.user?.full_name }}</div>
                <div class="text-[10px] text-[#7A6B60] flex items-center space-x-1">
                  <span>{{ rep.user?.role }}</span>
                  <span v-if="rep.user?.phone">· {{ rep.user.phone }}</span>
                </div>
              </td>
              <td class="py-3 px-4 font-medium text-[#4A3B32]">
                {{ rep.branch?.branch_name || 'Branch' }}
                <span v-if="rep.branch?.branch_code" class="block text-[10px] text-[#8A7B71] font-mono">{{ rep.branch.branch_code }}</span>
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                  :class="{
                    'bg-[#FAF0E6] text-[#BA5420] border-[#F0D5C1]': rep.report_type === 'Price Update',
                    'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]': rep.report_type === 'Daily Sales',
                    'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': rep.report_type === 'Order Activity' || rep.report_type === 'Custom Cake Report',
                    'bg-[#F3EFE9] text-[#55463C] border-[#D5C7B8]': !['Price Update', 'Daily Sales', 'Order Activity', 'Custom Cake Report'].includes(rep.report_type)
                  }"
                >
                  {{ rep.report_type }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                  :class="{
                    'bg-amber-100 text-amber-800 border-amber-200': rep.status === 'Submitted',
                    'bg-purple-100 text-purple-800 border-purple-200': rep.status === 'Forwarded to Super Admin',
                    'bg-emerald-100 text-emerald-800 border-emerald-200': rep.status === 'Reviewed' || rep.status === 'Resolved',
                  }"
                >
                  {{ rep.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-1.5" @click.stop>
                <button
                  @click="openViewReportModal(rep)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-2xs transition cursor-pointer"
                >
                  View
                </button>

                <template v-if="isManager && rep.status === 'Submitted'">
                  <button
                    @click="openReviewModal(rep)"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-md shadow-2xs transition cursor-pointer"
                    title="Review report and forward verified summary to Super Admin"
                  >
                    Verify &amp; Forward
                  </button>
                </template>

                <template v-if="isSuperAdmin && rep.status !== 'Resolved'">
                  <button
                    @click="markResolved(rep)"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#2E7D32] hover:bg-[#256628] text-white rounded-md shadow-2xs transition cursor-pointer"
                  >
                    Resolve
                  </button>
                </template>
              </td>
            </tr>
            <tr v-if="filteredReports.length === 0">
              <td colspan="7" class="py-8 text-center text-xs text-[#8A7B71]">
                No reports found matching your criteria.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: Submit New Report                      -->
    <!-- ============================================== -->
    <div v-if="isSubmitModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">
              {{ isManager ? 'Submit Consolidated Report to Super Admin' : 'Submit Staff Report to Branch Manager' }}
            </h3>
            <p class="text-[11px] text-[#7A6B60]">
              {{ isManager ? 'Your consolidated report will be delivered directly to the Super Admin' : 'Your report will be reviewed by your assigned Branch Manager' }}
            </p>
          </div>
          <button @click="isSubmitModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitReport" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Report Category *</label>
            <select
              v-model="newReportForm.report_type"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              required
            >
              <option value="Daily Sales">Daily Sales (Counter Sales, Register Receipts, Total Revenue)</option>
              <option value="Order Activity">Order Activity (Order Dispatches, Fulfillment Progress)</option>
              <option value="Delivery Summary">Delivery Summary (Completed Courier Trips, Fees Collected)</option>
              <option value="Custom Cake Report">Custom Cake Report (Bespoke Cake Production Updates)</option>
              <option value="Shift Log">Shift Log (Kitchen Preparation, Stock Usage, Baking Shifts)</option>
              <option value="Task Report">Task Report (Routine Sanitation, Maintenance, Equipment)</option>
              <option value="Product Information">Product Information (Stock Feedback, Low Stock, Shelf Display)</option>
              <option v-if="isManagerOrAdmin" value="Branch Performance">Branch Performance (Consolidated Performance Summary)</option>
              <option v-if="isManagerOrAdmin" value="Price Update">Price Update (Branch Price Adjustments Notification)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Report Title *</label>
            <input
              v-model="newReportForm.title"
              type="text"
              required
              placeholder="e.g. Daily Sales & Counter Cash Summary"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Detailed Findings, Sales &amp; Order Details *</label>
            <textarea
              v-model="newReportForm.content"
              rows="5"
              required
              placeholder="Provide a detailed breakdown of activities, order numbers, cash amounts, customer feedback, or operational issues..."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="isSubmitModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingReport"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer"
            >
              {{ isSubmittingReport ? 'Submitting...' : 'Send Report' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: Manager Review & Forward Dialog        -->
    <!-- ============================================== -->
    <div v-if="reviewingReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">Verify &amp; Forward Report</h3>
            <p class="text-xs text-[#8A7B71]">Review report by {{ reviewingReport.user?.full_name }} and forward to Super Admin</p>
          </div>
          <button @click="reviewingReport = null" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="bg-[#FAF7F2] p-3 rounded-lg border border-[#EFE4D6]">
            <div class="font-bold text-[#2A1D15]">{{ reviewingReport.title }}</div>
            <div class="text-[11px] text-[#7A6B60] mt-1">{{ reviewingReport.content }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch Manager Verification Notes</label>
            <textarea
              v-model="managerReviewNotes"
              rows="3"
              placeholder="e.g. Verified against cash register and delivery courier timestamps. Ready for executive sign-off."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-2">
          <button
            @click="reviewingReport = null"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="submitForwardReport"
            class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition cursor-pointer"
          >
            Forward to Super Admin
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: View Full Report Details               -->
    <!-- ============================================== -->
    <div v-if="viewingReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#BA5420] bg-[#FAF2EB] px-2 py-0.5 rounded-full border border-[#ECD7C7]">
              {{ viewingReport.report_type }}
            </span>
            <h3 class="font-serif font-bold text-base text-[#2A1D15] mt-1">{{ viewingReport.title }}</h3>
          </div>
          <button @click="viewingReport = null" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-2 bg-[#FAF7F2] p-3 rounded-xl border border-[#EFE4D6]">
            <div><span class="text-[#8A7B71]">Author:</span> <strong class="text-[#2A1D15]">{{ viewingReport.user?.full_name }} ({{ viewingReport.user?.role }})</strong></div>
            <div><span class="text-[#8A7B71]">Branch:</span> <strong class="text-[#2A1D15]">{{ viewingReport.branch?.branch_name || 'HQ' }}</strong></div>
            <div><span class="text-[#8A7B71]">Phone:</span> <span class="font-mono text-[#2A1D15]">{{ viewingReport.user?.phone || '—' }}</span></div>
            <div><span class="text-[#8A7B71]">Date:</span> <span class="text-[#2A1D15]">{{ new Date(viewingReport.created_at).toLocaleString() }}</span></div>
          </div>

          <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#8A7B71] mb-1">Report Content</label>
            <div class="bg-white p-3.5 rounded-xl text-[#3A2E26] whitespace-pre-wrap leading-relaxed border border-[#EFE4D6] max-h-56 overflow-y-auto">
              {{ viewingReport.content }}
            </div>
          </div>

          <div v-if="viewingReport.manager_notes" class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
            <span class="font-bold text-purple-900 block mb-0.5">Manager Review Notes:</span>
            <p class="text-purple-800 text-[11px]">{{ viewingReport.manager_notes }}</p>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="viewingReport = null"
            class="px-4 py-2 text-xs font-semibold bg-[#2A1D15] hover:bg-[#1C130D] text-white rounded-lg transition cursor-pointer"
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
const reports = ref([]);
const selectedBranchFilter = ref('all');
const selectedTypeFilter = ref('all');
const selectedDateFilter = ref('all');
const customDate = ref('');
const successMsg = ref('');

// Modals
const isSubmitModalOpen = ref(false);
const isSubmittingReport = ref(false);
const viewingReport = ref(null);
const reviewingReport = ref(null);
const managerReviewNotes = ref('');

const newReportForm = ref({
  report_type: 'Daily Sales',
  title: '',
  content: '',
});

const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isManager = computed(() => ['Branch Manager', 'Branch Admin', 'Main Branch Manager'].includes(authStore.user?.role));
const isManagerOrAdmin = computed(() => isSuperAdmin.value || isManager.value);

const filteredReports = computed(() => {
  return reports.value;
});

const loadReports = async () => {
  try {
    const params = {};
    if (selectedBranchFilter.value !== 'all') {
      params.branch_id = selectedBranchFilter.value;
    }
    if (selectedTypeFilter.value !== 'all') {
      params.report_type = selectedTypeFilter.value;
    }
    if (selectedDateFilter.value !== 'all') {
      if (selectedDateFilter.value === 'custom') {
        params.date = customDate.value;
      } else {
        params.date_range = selectedDateFilter.value;
      }
    }

    const [repRes, brRes] = await Promise.all([
      api.get('/admin/staff-reports', { params }),
      api.get('/branches'),
    ]);
    reports.value = repRes.data;
    branches.value = brRes.data;
  } catch (err) {
    console.error('Failed to load reports', err);
  }
};

const submitReport = async () => {
  isSubmittingReport.value = true;
  try {
    await api.post('/admin/staff-reports', newReportForm.value);
    isSubmitModalOpen.value = false;
    successMsg.value = 'Report submitted successfully.';
    newReportForm.value = {
      report_type: 'Daily Sales',
      title: '',
      content: '',
    };
    await loadReports();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit report.');
  } finally {
    isSubmittingReport.value = false;
  }
};

const openViewReportModal = (rep) => {
  viewingReport.value = rep;
};

const openReviewModal = (rep) => {
  reviewingReport.value = rep;
  managerReviewNotes.value = 'Verified against cash register and delivery courier timestamps.';
};

const submitForwardReport = async () => {
  if (!reviewingReport.value) return;
  try {
    await api.patch(`/admin/staff-reports/${reviewingReport.value.report_id}/review`, {
      action: 'forward',
      status: 'Forwarded to Super Admin',
      manager_notes: managerReviewNotes.value,
    });
    successMsg.value = `Report "${reviewingReport.value.title}" verified and forwarded to Super Admin.`;
    reviewingReport.value = null;
    await loadReports();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to forward report.');
  }
};

const markResolved = async (rep) => {
  try {
    await api.patch(`/admin/staff-reports/${rep.report_id}/review`, {
      action: 'resolve',
      status: 'Resolved',
      manager_notes: 'Reviewed and acknowledged by Super Admin.',
    });
    successMsg.value = `Report marked as Resolved.`;
    await loadReports();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to resolve report.');
  }
};

onMounted(() => {
  loadReports();
});
</script>