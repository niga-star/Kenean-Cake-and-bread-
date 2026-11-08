<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center space-x-3.5">
        <div class="w-12 h-12 rounded-2xl bg-[#FAF0E6] text-[#BA5420] flex items-center justify-center text-xl shrink-0 border border-[#F0DFD0] shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">
            {{ isManagerOrAdmin ? 'Staff Reporting & Hierarchy' : 'Submit Shift / Work Report' }}
          </h2>
          <p class="text-xs text-[#7A6B60] mt-0.5">
            {{ isManagerOrAdmin ? 'Review branch reports and forward consolidated summaries to Super Admin' : 'Report daily sales, shift logs, and delivery summaries to your Branch Manager' }}
          </p>
        </div>
      </div>

      <button
        @click="isSubmitModalOpen = true"
        class="inline-flex items-center space-x-1.5 px-4 py-2 text-xs sm:text-sm font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>{{ isManager ? 'Submit Manager Report to Super Admin' : 'Submit New Staff Report' }}</span>
      </button>
    </div>

    <!-- Alert / Status Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>

    <!-- Reporting Hierarchy Diagram Card -->
    <div class="bg-gradient-to-r from-[#FAF6F0] via-white to-[#FAF6F0] border border-[#E9E0D4] rounded-2xl p-5 shadow-xs">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center space-x-3">
          <span class="text-2xl">📊</span>
          <div>
            <div class="font-bold text-[#2A1D15]">Official Reporting Hierarchy</div>
            <div class="text-[#7A6B60] text-[11px]">Branch Staff → Branch Manager → Super Admin</div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 font-medium text-[11px]">
          <span class="px-2.5 py-1 rounded-lg bg-white border border-[#D8CDC0] text-[#4A3B32] shadow-2xs">
            1. Sellers / Servants / Delivery Staff
          </span>
          <span class="text-[#BA5420] font-bold">➔</span>
          <span class="px-2.5 py-1 rounded-lg bg-[#FAF0E6] border border-[#F0DFD0] text-[#BA5420] font-bold shadow-2xs">
            2. Branch Manager
          </span>
          <span class="text-[#BA5420] font-bold">➔</span>
          <span class="px-2.5 py-1 rounded-lg bg-[#FFF3E0] border border-[#FFE0B2] text-[#E65100] font-bold shadow-2xs">
            3. Super Admin
          </span>
        </div>
      </div>
    </div>

    <!-- Filter & Reports Table -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm overflow-hidden space-y-4 p-5 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#EFE8DF] pb-4">
        <div>
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">Submitted Reports Log</h3>
          <p class="text-xs text-[#7A6B60] mt-0.5">
            {{ isSuperAdmin ? 'Monitoring all branches across the entire business' : (isManager ? 'Reports submitted by your branch staff' : 'Your personal submitted reports') }}
          </p>
        </div>

        <div class="flex items-center space-x-2">
          <label class="text-xs font-medium text-[#7D6E64]">Type:</label>
          <select
            v-model="selectedTypeFilter"
            @change="loadReports"
            class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]"
          >
            <option value="all">All Report Types</option>
            <option value="Daily Sales">Daily Sales</option>
            <option value="Delivery Summary">Delivery Summary</option>
            <option value="Shift Log">Shift Log</option>
            <option value="Task Report">Task Report</option>
            <option value="Branch Performance">Branch Performance</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
        <table class="w-full text-left text-xs min-w-[700px]">
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
            <tr v-for="rep in filteredReports" :key="rep.report_id" class="hover:bg-[#FCFAF7]">
              <td class="py-3 px-4 font-mono text-[#5A4A3E]">{{ new Date(rep.created_at).toLocaleDateString() }}</td>
              <td class="py-3 px-4">
                <div class="font-bold text-[#2A1D15]">{{ rep.title }}</div>
                <div class="text-[11px] text-[#7A6B60] max-w-sm truncate" :title="rep.content">{{ rep.content }}</div>
              </td>
              <td class="py-3 px-4">
                <div class="font-semibold text-[#2D231D]">{{ rep.user?.full_name }}</div>
                <div class="text-[10px] text-[#7A6B60]">{{ rep.user?.role }}</div>
              </td>
              <td class="py-3 px-4 font-medium text-[#4A3B32]">{{ rep.branch?.branch_name || 'HQ' }}</td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FAF2EB] text-[#BA5420] border border-[#ECD7C7]">
                  {{ rep.report_type }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="{
                    'bg-blue-100 text-blue-800 border border-blue-200': rep.status === 'Submitted',
                    'bg-purple-100 text-purple-800 border border-purple-200': rep.status === 'Forwarded to Super Admin',
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': rep.status === 'Reviewed' || rep.status === 'Resolved',
                  }"
                >
                  {{ rep.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-1.5">
                <button
                  @click="openViewReportModal(rep)"
                  class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-2xs transition cursor-pointer"
                >
                  View
                </button>

                <template v-if="isManager && rep.status === 'Submitted'">
                  <button
                    @click="reviewAndForward(rep, 'Forwarded to Super Admin')"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-md shadow-2xs transition cursor-pointer"
                    title="Forward this staff report to Super Admin"
                  >
                    Forward to Super Admin
                  </button>
                </template>

                <template v-if="isSuperAdmin && rep.status !== 'Resolved'">
                  <button
                    @click="reviewAndForward(rep, 'Resolved')"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#2E7D32] hover:bg-[#256628] text-white rounded-md shadow-2xs transition cursor-pointer"
                  >
                    Mark Resolved
                  </button>
                </template>
              </td>
            </tr>
            <tr v-if="filteredReports.length === 0">
              <td colspan="7" class="py-6 text-center text-xs text-[#8A7B71]">
                No reports found matching your criteria.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: Submit New Report -->
    <div v-if="isSubmitModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">
            {{ isManager ? 'Submit Consolidated Report to Super Admin' : 'Submit Staff Report to Branch Manager' }}
          </h3>
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
              <option value="Daily Sales">Daily Sales (Counter Sales, Receipts, Cash Balance)</option>
              <option value="Delivery Summary">Delivery Summary (Completed Trips, Fees Collected)</option>
              <option value="Shift Log">Shift Log (Kitchen Preparation, Stock Used)</option>
              <option value="Task Report">Task Report (Routine Duties, Maintenance, Sanitation)</option>
              <option v-if="isManagerOrAdmin" value="Branch Performance">Branch Performance Summary (For Super Admin)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Report Title *</label>
            <input
              v-model="newReportForm.title"
              type="text"
              required
              placeholder="e.g. Bole Morning Shift Daily Sales Summary"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Detailed Findings & Notes *</label>
            <textarea
              v-model="newReportForm.content"
              rows="5"
              required
              placeholder="Provide a clear breakdown of activities, counter sales, order numbers, cash amounts, or issues encountered..."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="isSubmitModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingReport"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isSubmittingReport ? 'Submitting...' : 'Send Report' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: View Report Detail -->
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
          <div class="flex justify-between text-[#7A6B60] pb-2 border-b border-[#F0EAE1]">
            <span>Submitted by: <strong>{{ viewingReport.user?.full_name }}</strong> ({{ viewingReport.user?.role }})</span>
            <span>{{ new Date(viewingReport.created_at).toLocaleString() }}</span>
          </div>

          <div class="bg-[#FAF7F2] p-4 rounded-xl text-[#3A2E26] whitespace-pre-wrap leading-relaxed border border-[#EFE4D6]">
            {{ viewingReport.content }}
          </div>

          <div v-if="viewingReport.manager_notes" class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
            <span class="font-bold text-purple-900 block mb-0.5">Manager Notes:</span>
            <p class="text-purple-800 text-[11px]">{{ viewingReport.manager_notes }}</p>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="viewingReport = null"
            class="px-4 py-2 text-xs font-semibold bg-[#2A1D15] hover:bg-[#1C130D] text-white rounded-lg transition"
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
const reports = ref([]);
const selectedTypeFilter = ref('all');
const successMsg = ref('');
const isSubmitModalOpen = ref(false);
const isSubmittingReport = ref(false);
const viewingReport = ref(null);

const newReportForm = ref({
  report_type: 'Daily Sales',
  title: '',
  content: '',
});

const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isManager = computed(() => ['Branch Manager', 'Branch Admin', 'Main Branch Manager'].includes(authStore.user?.role));
const isManagerOrAdmin = computed(() => isSuperAdmin.value || isManager.value);

const filteredReports = computed(() => {
  if (selectedTypeFilter.value === 'all') return reports.value;
  return reports.value.filter(r => r.report_type === selectedTypeFilter.value);
});

const loadReports = async () => {
  try {
    const res = await api.get('/admin/staff-reports', {
      params: { report_type: selectedTypeFilter.value }
    });
    reports.value = res.data;
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

const reviewAndForward = async (rep, targetStatus) => {
  const notes = prompt(`Enter optional remarks for ${targetStatus}:`) || '';
  try {
    await api.patch(`/admin/staff-reports/${rep.report_id}/review`, {
      status: targetStatus,
      manager_notes: notes,
    });
    successMsg.value = `Report status updated to: ${targetStatus}`;
    await loadReports();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update report review.');
  }
};

onMounted(() => {
  loadReports();
});
</script>

